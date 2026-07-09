<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:10px; flex-wrap:wrap; gap:8px;">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div class="card-title">{{ __('bpp.title') }}</div>
            <span id="bppFallbackWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>

        <div class="btn-action-group">
            <div class="filters">
                <label for="filterBpp" class="sr-only">{{ __('bpp.filter.kategori') }}</label>
                <select id="filterBpp">
                    <option value="BPP PEMBANGKIT">{{ __('bpp.kategori.pembangkit') }}</option>
                    <option value="BPP S.D TRANSMISI">{{ __('bpp.kategori.transmisi') }}</option>
                    <option value="BPP S.D DISTRIBUSI TM">{{ __('bpp.kategori.distribusi_tm') }}</option>
                    <option value="BPP S.D DISTRIBUSI TR">{{ __('bpp.kategori.distribusi_tr') }}</option>
                </select>

                <label for="filterBulanBpp" class="sr-only">{{ __('bpp.filter.bulan') }}</label>
                <select id="filterBulanBpp">
                    <option value="all">{{ __('bpp.filter.semua_bulan') }}</option>
                    @foreach(range(0, 11) as $i)
                        <option value="{{ $i }}">
                            {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button id="btnMuatBpp" class="btn-action btn-action--load">
                 Muat Data
            </button>
            <button id="btnBuatBpp" class="btn-action btn-action--create">
                ✚ Buat Data
            </button>
            <button id="btnHapusBpp" class="btn-action btn-action--delete">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    {{-- Chart BPP Trend --}}
    <div class="chart-wrapper" style="width:100%; min-height:300px; margin-bottom:20px;">
        <canvas id="bppComparisonChart"></canvas>
    </div>

    {{-- Chart BPP vs HJR --}}
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
        <div class="card-title" style="margin:0;">BPP Distribusi TR vs HJR</div>
        <span style="font-size:0.68rem; padding:2px 8px; border-radius:10px; background:rgba(79,195,247,0.08); border:1px solid rgba(79,195,247,0.2); color:#4fc3f7;">
            Auto — Pendapatan ÷ Penjualan
        </span>
    </div>
    <div class="chart-wrapper" style="width:100%; min-height:280px; margin-bottom:20px;">
        <canvas id="bppHjrComparisonChart"></canvas>
    </div>

    {{-- Tabel Ringkasan --}}
    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:24px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('bpp.table.bulan') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('bpp.table.konsolidasi') }}</th>
                    <th style="color:var(--accent-green);">{{ __('bpp.table.internal') }}</th>
                    <th>{{ __('bpp.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="bppTableBody"></tbody>
        </table>
    </div>

    {{-- Edit Data BPP --}}
    <div class="edit-section-label" style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
        <div class="card-title" style="margin:0;">Edit Data BPP</div>
        <span id="bppEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    {{-- Tab bar BPP — di LUAR container scroll agar tidak terpotong --}}
    <div id="bppTabBar" style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px;"></div>
    <div id="bppEditBppContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px; margin-bottom:20px;"></div>

    {{-- Edit Data HJR --}}
    <div class="edit-section-label card-title" style="margin-bottom:8px;">Edit Data HJR Rata-rata</div>
    {{-- Tab bar HJR — di LUAR container scroll --}}
    <div id="hjrTabBar" style="display:flex; gap:6px; margin-bottom:8px;"></div>
    <div id="bppEditHjrContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatBpp" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Muat Data BPP</div>

        <div style="margin-bottom:16px;">
            <label for="muatTahunBpp" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunBpp" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">Jika data tahun ini kosong, sistem akan otomatis menampilkan data tahun sebelumnya.</div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatBpp" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiMuatBpp" class="btn-action btn-action--solid btn-action--load">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatBpp" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Buat Data BPP Kosong</div>
        <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun dan kategori yang dipilih.</div>

        <div style="margin-bottom:12px;">
            <label for="buatTahunBpp" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunBpp" style="width:100%;">
                @foreach(range(date('Y') + 1, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="buatTipeBpp" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Kategori BPP</label>
            <select id="buatTipeBpp" style="width:100%;">
                <option value="pembangkit">BPP Pembangkit</option>
                <option value="transmisi">BPP s.d Transmisi</option>
                <option value="distribusi_tm">BPP s.d Distribusi TM</option>
                <option value="distribusi_tr">BPP s.d Distribusi TR</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatBpp" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiBuatBpp" class="btn-action btn-action--solid btn-action--create">Buat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusBpp" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data BPP</div>
        <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun dan kategori yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>

        <div style="margin-bottom:12px;">
            <label for="hapusTahunBpp" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunBpp" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:12px;">
            <label for="hapusTipeBpp" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Kategori BPP</label>
            <select id="hapusTipeBpp" style="width:100%;">
                <option value="pembangkit">BPP Pembangkit</option>
                <option value="transmisi">BPP s.d Transmisi</option>
                <option value="distribusi_tm">BPP s.d Distribusi TM</option>
                <option value="distribusi_tr">BPP s.d Distribusi TR</option>
                <option value="semua">Semua Kategori</option>
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="hapusTipeDataBpp" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe Data</label>
            <select id="hapusTipeDataBpp" style="width:100%;">
                <option value="konsolidasi">Konsolidasi</option>
                <option value="internal">Internal</option>
                <option value="semua">Semua</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusBpp" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiHapusBpp" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
        </div>
    </div>
</div>