import os
import sys
import json
import requests
import io
import warnings

# --- 1. SILENCE WARNINGS ---
warnings.filterwarnings("ignore")
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'

import google.generativeai as genai

# Fix Windows Output Encoding
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

API_KEY = os.getenv("GEMINI_API_KEY")
GAS_URL = os.getenv("GAS_DATA_URL")

class PLNBot:
    def __init__(self):
        if not API_KEY:
            sys.exit(1)
            
        genai.configure(api_key=API_KEY)
        self.data_konteks = self._get_external_data()
        
        # --- 2. RIGID SYSTEM INSTRUCTION ---
        self.system_instruction = f"""
        ROLE: 
        Kamu adalah 'Pakar Data PLN Batam', asisten profesional untuk Dashboard Kinerja 2025.
        
        SUMBER DATA (MARKDOWN TABLES):
        {self.data_konteks}
        
        ATURAN ANALISIS:
        1. Bacalah tabel di atas dengan teliti. Kolom pertama biasanya adalah Nama Indikator, dan kolom berikutnya adalah bulan (Januari - Desember).
        2. Jika user bertanya angka BPP, pastikan kamu melihat baris yang benar (misal: BPP Pembangkit) dan kolom bulan yang tepat.
        3. Bedakan antara tabel "RENCANA/TARGET" dan tabel "REALISASI" berdasarkan header yang ada di data.
        4. Sajikan jawaban dalam format tabel Markdown kembali agar user mudah membaca perbandingannya.
        5. DILARANG menyebutkan "skripsi" atau "mahasiswa". Kamu adalah sistem internal PLN Batam.
        """

        self.model = genai.GenerativeModel(
            model_name="gemini-2.5-flash", 
            system_instruction=self.system_instruction
        )

    def _get_external_data(self):
        """Menarik data dan memformatnya menjadi Markdown Table yang presisi"""
        try:
            if not GAS_URL: return "Data Kosong."
            
            # Handle Redirect Google Apps Script
            response = requests.get(GAS_URL, timeout=25, allow_redirects=True)
            response.raise_for_status()
            
            raw_data = response.json()
            formatted_text = ""

            if isinstance(raw_data, dict):
                for sheet_name, rows in raw_data.items():
                    if not rows: continue
                    
                    formatted_text += f"\n### DATA: {sheet_name}\n"
                    
                    for i, row in enumerate(rows):
                        # Bersihkan data: hilangkan None dan spasi berlebih
                        clean_row = [str(c).strip() if c is not None else "" for c in row]
                        
                        # Hanya proses jika baris tidak sepenuhnya kosong
                        if any(clean_row):
                            # Buat format baris tabel Markdown
                            formatted_text += "| " + " | ".join(clean_row) + " |\n"
                            
                            # Tambahkan separator Markdown setelah baris header pertama
                            if i == 0:
                                separator = "| " + " | ".join(["---"] * len(clean_row)) + " |"
                                formatted_text += separator + "\n"
            
            return formatted_text if formatted_text else "Data tidak ditemukan di spreadsheet."
        except Exception as e:
            return f"Data Kosong. Gagal sinkronisasi: {str(e)}"

    def ask(self, query):
        try:
            chat = self.model.start_chat()
            response = chat.send_message(query)
            return response.text.strip()
        except Exception as e:
            return f"Maaf, AI sedang mengalami overload: {str(e)}"

if __name__ == "__main__":
    if len(sys.argv) > 1:
        bot = PLNBot()
        print(bot.ask(sys.argv[1]))