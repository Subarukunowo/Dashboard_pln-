<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #333; padding-bottom:10px; flex-wrap:wrap; gap:8px;">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div class="card-title">{{ __('bpp.title') }}</div>
            <span id="bppFallbackWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>
        <div style="display:flex; gap:5px; flex-wrap:wrap; align-items:center;">
            <select id="filterBpp" aria-label="{{ __('bpp.filter.kategori') }}" style="font-size:0.7rem; padding:4px;">
                <option value="BPP PEMBANGKIT">{{ __('bpp.kategori.pembangkit') }}</option>
                <option value="BPP S.D TRANSMISI">{{ __('bpp.kategori.transmisi') }}</option>
                <option value="BPP S.D DISTRIBUSI TM">{{ __('bpp.kategori.distribusi_tm') }}</option>
                <option value="BPP S.D DISTRIBUSI TR">{{ __('bpp.kategori.distribusi_tr') }}</option>
            </select>
            <select id="filterBulanBpp" aria-label="{{ __('bpp.filter.bulan') }}" style="font-size:0.7rem; padding:4px;">
                <option value="all">{{ __('bpp.filter.semua_bulan') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <button id="btnMuatBpp"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #2196f3; background:rgba(33,150,243,0.1); color:#2196f3; cursor:pointer; white-space:nowrap;">
                ＋ Muat Data
            </button>
            <button id="btnBuatBpp"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #00ff88; background:rgba(0,255,136,0.08); color:#00ff88; cursor:pointer; white-space:nowrap;">
                ✚ Buat Data
            </button>
            <button id="btnHapusBpp"
                style="font-size:0.7rem; padding:4px 10px; border-radius:6px; border:1px solid #f44336; background:rgba(244,67,54,0.1); color:#f44336; cursor:pointer; white-space:nowrap;">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    {{-- Chart --}}
    <div class="chart-wrapper" style="width:100%; min-height:300px; margin-bottom:20px;">
        <canvas id="bppComparisonChart"></canvas>
    </div>

    {{-- Tabel Ringkasan --}}
    <div class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:24px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#252525; z-index:1;">
                <tr style="font-size:0.65rem; text-transform:uppercase;">
                    <th style="padding:10px;">{{ __('bpp.table.bulan') }}</th>
                    <th style="padding:10px; color:var(--accent-orange);">{{ __('bpp.table.konsolidasi') }}</th>
                    <th style="padding:10px; color:var(--accent-green);">{{ __('bpp.table.internal') }}</th>
                    <th style="padding:10px;">{{ __('bpp.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="bppTableBody"></tbody>
        </table>
    </div>

    {{-- Edit Data BPP --}}
    <div class="edit-section-label" style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
        <div class="card-title" style="margin:0;">Edit Data BPP</div>
        <span id="bppEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(255,152,0,0.12); border:1px solid rgba(255,152,0,0.3); color:#ff9800;">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="bppEditBppContainer" class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.15); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:420px; margin-bottom:20px;"></div>

    {{-- Edit Data HJR --}}
    <div class="edit-section-label card-title" style="margin-bottom:8px;">Edit Data HJR Rata-rata</div>
    <div id="bppEditHjrContainer" class="custom-scroll" style="width:100%; background:rgba(0,0,0,0.15); border-radius:8px; border:1px solid #333; overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatBpp" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:#fff;">Muat Data BPP</div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunBpp" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="font-size:0.72rem; color:#666; margin-bottom:16px;">Jika data tahun ini kosong, sistem akan otomatis menampilkan data tahun sebelumnya.</div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatBpp" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiMuatBpp" style="padding:6px 14px; border-radius:6px; border:none; background:#2196f3; color:#fff; cursor:pointer; font-size:0.8rem;">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatBpp" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Buat Data BPP Kosong</div>
        <div style="font-size:0.72rem; color:#666; margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun dan kategori yang dipilih.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunBpp" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y') + 1, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Kategori BPP</label>
            <select id="buatTipeBpp" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="pembangkit">BPP Pembangkit</option>
                <option value="transmisi">BPP s.d Transmisi</option>
                <option value="distribusi_tm">BPP s.d Distribusi TM</option>
                <option value="distribusi_tr">BPP s.d Distribusi TR</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatBpp" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiBuatBpp" style="padding:6px 14px; border-radius:6px; border:none; background:#00ff88; color:#000; cursor:pointer; font-size:0.8rem; font-weight:600;">Buat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusBpp" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#1e1e1e; border:1px solid #333; border-radius:12px; padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:#fff;">Hapus Data BPP</div>
        <div style="font-size:0.78rem; color:#aaa; margin-bottom:16px;">Pilih tahun dan kategori yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunBpp" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:12px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Kategori BPP</label>
            <select id="hapusTipeBpp" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="pembangkit">BPP Pembangkit</option>
                <option value="transmisi">BPP s.d Transmisi</option>
                <option value="distribusi_tm">BPP s.d Distribusi TM</option>
                <option value="distribusi_tr">BPP s.d Distribusi TR</option>
                <option value="semua">Semua Kategori</option>
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Tipe Data</label>
            <select id="hapusTipeDataBpp" style="width:100%; padding:6px; border-radius:6px; border:1px solid #333; background:#252525; color:#fff; font-size:0.8rem;">
                <option value="konsolidasi">Konsolidasi</option>
                <option value="internal">Internal</option>
                <option value="semua">Semua</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusBpp" style="padding:6px 14px; border-radius:6px; border:1px solid #444; background:transparent; color:#aaa; cursor:pointer; font-size:0.8rem;">Batal</button>
            <button id="btnKonfirmasiHapusBpp" style="padding:6px 14px; border-radius:6px; border:none; background:#f44336; color:#fff; cursor:pointer; font-size:0.8rem;">Hapus</button>
        </div>
    </div>
</div>
