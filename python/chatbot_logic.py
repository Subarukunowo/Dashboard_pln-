import sys
import os
import requests
import io
import warnings
import socket
import traceback
from requests.adapters import HTTPAdapter, ConnectionError
from urllib3.util.retry import Retry
from urllib3.connection import HTTPConnection, HTTPSConnection
from urllib3.connectionpool import HTTPConnectionPool, HTTPSConnectionPool

warnings.filterwarnings("ignore")
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

# 🔧 PATCH 1: Force IPv4 dengan cara yang kompatibel dengan urllib3/requests
def force_ipv4_socket(family, type, proto, flags, sa):
    if family == socket.AF_UNSPEC:
        # Coba IPv4 dulu
        try:
            return [(socket.AF_INET, type, proto, '', sa)]
        except:
            pass
    elif family == socket.AF_INET6:
        # Skip IPv6, return empty agar fallback ke IPv4
        return []
    return [(family, type, proto, '', sa)]

# Patch socket.getaddrinfo secara global
_original_getaddrinfo = socket.getaddrinfo
socket.getaddrinfo = lambda *args, **kwargs: [
    r for r in _original_getaddrinfo(*args, **kwargs) 
    if r[0] == socket.AF_INET
]

# 🔧 PATCH 2: Custom HTTPConnection yang memaksa IPv4
class IPv4HTTPConnection(HTTPConnection):
    def __init__(self, *args, **kwargs):
        kwargs['family'] = socket.AF_INET  # Force IPv4
        super().__init__(*args, **kwargs)

class IPv4HTTPSConnection(HTTPSConnection):
    def __init__(self, *args, **kwargs):
        kwargs['family'] = socket.AF_INET  # Force IPv4
        super().__init__(*args, **kwargs)

# 🔧 PATCH 3: Custom Connection Pools
class IPv4HTTPConnectionPool(HTTPConnectionPool):
    ConnectionCls = IPv4HTTPConnection

class IPv4HTTPSConnectionPool(HTTPSConnectionPool):
    ConnectionCls = IPv4HTTPSConnection

# Register custom pools ke urllib3
import urllib3
urllib3.connectionpool.HTTPConnectionPool = IPv4HTTPConnectionPool
urllib3.connectionpool.HTTPSConnectionPool = IPv4HTTPSConnectionPool


API_KEY = os.getenv("GEMINI_API_KEY")
GAS_URL = os.getenv("GAS_DATA_URL")
HEADERS = {
    "User-Agent": "PLNBot/1.0 (Python-requests)",
    "Accept": "application/json"
}
GEMINI_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent"


class PLNBot:
    def __init__(self):
        if not API_KEY:
            print("ERROR: GEMINI_API_KEY tidak ditemukan di environment variable.")
            sys.exit(1)
        
        # 🔧 DEBUG: Tes DNS resolution dari dalam Python
        self._test_dns_resolution()
        
        self.data_konteks = self._get_external_data()
        self.system_instruction = f"""
        ROLE: 
        Kamu adalah 'Pakar Data PLN Batam', asisten profesional untuk Dashboard Kinerja 2025.
        
        SUMBER DATA (MARKDOWN TABLES):
        {self.data_konteks}
        
        ATURAN ANALISIS:
        1. Bacalah tabel di atas dengan teliti.
        2. Bedakan antara tabel RENCANA/TARGET dan REALISASI.
        3. Sajikan jawaban dalam format tabel Markdown.
        4. DILARANG menyebutkan skripsi atau mahasiswa.
        """

    def _test_dns_resolution(self):
        """Tes DNS resolution dari dalam Python untuk debugging"""
        try:
            # Cara 1: socket.getaddrinfo
            results = socket.getaddrinfo('generativelanguage.googleapis.com', 443)
            print(f"[DEBUG] socket.getaddrinfo: {len(results)} result(s) found")
            for r in results:
                print(f"  - Family: {r[0]}, Address: {r[4][0]}")
            
            # Cara 2: Coba resolve langsung
            ip = socket.gethostbyname('generativelanguage.googleapis.com')
            print(f"[DEBUG] socket.gethostbyname: {ip}")
        except Exception as e:
            print(f"[DEBUG] DNS Test Failed: {type(e).__name__} - {e}")

    def _get_session(self):
        session = requests.Session()
        
        retry = Retry(
            total=3, 
            backoff_factor=2, 
            status_forcelist=[500, 502, 503, 504],
            allowed_methods=["GET", "POST"]
        )
        adapter = HTTPAdapter(max_retries=retry, pool_connections=10, pool_maxsize=20)
        session.mount("https://", adapter)
        session.mount("http://", adapter)
        
        # Proxy support
        proxies = {}
        for env_var in ["HTTPS_PROXY", "HTTP_PROXY", "ALL_PROXY"]:
            if os.getenv(env_var):
                proxies[env_var.lower().replace("_proxy", "")] = os.getenv(env_var)
        if proxies:
            session.proxies.update(proxies)
            print(f"[DEBUG] Using proxy: {proxies}")
        
        session.headers.update(HEADERS)
        
        # SSL verification toggle
        self.verify_ssl = os.getenv("SSL_VERIFY", "true").lower() == "true"
        if not self.verify_ssl:
            print("[WARNING] SSL verification disabled!")
            session.verify = False
        
        return session

    def _get_external_data(self):
        if not GAS_URL:
            return "Data Kosong: GAS_DATA_URL tidak dikonfigurasi."
        try:
            session = self._get_session()
            response = session.get(GAS_URL, timeout=45, allow_redirects=True, verify=self.verify_ssl)
            response.raise_for_status()
            raw_data = response.json()
            formatted_text = ""
            if isinstance(raw_data, dict):
                for sheet_name, rows in raw_data.items():
                    if not rows:
                        continue
                    formatted_text += f"\n### DATA: {sheet_name}\n"
                    for i, row in enumerate(rows):
                        clean_row = [str(c).strip() if c is not None else "" for c in row]
                        if any(clean_row):
                            formatted_text += "| " + " | ".join(clean_row) + " |\n"
                            if i == 0:
                                formatted_text += "| " + " | ".join(["---"] * len(clean_row)) + " |\n"
            return formatted_text if formatted_text else "Data tidak ditemukan."
        except Exception as e:
            return f"Data Kosong: {type(e).__name__} - {str(e)}"

    def ask(self, query):
        try:
            session = self._get_session()
            payload = {
                "system_instruction": {"parts": [{"text": self.system_instruction}]},
                "contents": [{"role": "user", "parts": [{"text": query}]}],
                "generationConfig": {"temperature": 0.3}
            }
            
            print(f"[DEBUG] POST to: {GEMINI_URL}")
            
            response = session.post(
                f"{GEMINI_URL}?key={API_KEY}",
                json=payload,
                timeout=60,
                verify=self.verify_ssl
            )
            response.raise_for_status()
            result = response.json()
            
            if "candidates" not in result or not result["candidates"]:
                return "Maaf, AI tidak dapat menghasilkan jawaban."
                
            return result["candidates"][0]["content"]["parts"][0]["text"].strip()
            
        except requests.exceptions.ConnectionError as e:
            # 🔧 Tambahkan detail debugging untuk ConnectionError
            if "NameResolutionError" in str(e) or "getaddrinfo failed" in str(e):
                return (
                    f"❌ DNS Resolution Error dari Python!\n"
                    f"→ Meski nslookup di CMD berhasil, Python tidak bisa resolve domain.\n"
                    f"→ Kemungkinan: Python menggunakan DNS server berbeda atau terpengaruh kebijakan jaringan.\n\n"
                    f"SOLUSI:\n"
                    f"1. Coba jalankan script dengan Python yang sama dengan sistem (bukan virtualenv/conda yang terisolasi)\n"
                    f"2. Cek: `python -c \"import socket; print(socket.getaddrinfo('google.com', 443))\"`\n"
                    f"3. Jika masih gagal, coba gunakan IP address langsung (tidak direkomendasikan)\n"
                    f"4. Hubungi tim IT PLN Batam untuk whitelist akses Python ke *.googleapis.com\n\n"
                    f"Detail error: {str(e)}"
                )
            return f"❌ Connection Error: {str(e)}"
        except Exception as e:
            traceback.print_exc()
            return f"❌ Error: {type(e).__name__} - {str(e)}"


if __name__ == "__main__":
    if len(sys.argv) > 1:
        bot = PLNBot()
        print(bot.ask(sys.argv[1]))
    else:
        print("Usage: python chatbot_logic.py \"pertanyaan kamu\"")