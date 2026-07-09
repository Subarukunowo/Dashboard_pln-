{{-- CARD 1: Pemeriksaan & Temuan --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title">{{ __('p2tl.title') }}</div>

        <div class="btn-action-group">
            <div class="filters">
                <label for="filterBulanP2tl" class="sr-only">{{ __('p2tl.filter.bulan') }}</label>
                <select id="filterBulanP2tl">
                    <option value="all">{{ __('p2tl.filter.semua') }}</option>
                    @foreach(range(0, 11) as $i)
                        <option value="{{ $i }}">
                            {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button id="btnMuatP2tl" class="btn-action btn-action--load">
                ＋ Muat Data
            </button>
            <button id="btnBuatP2tl" class="btn-action btn-action--create">
                ✚ Buat Data
            </button>
            <button id="btnHapusP2tl" class="btn-action btn-action--delete">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartP2tlTemuan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('p2tl.table.bulan') }}</th>
                    <th style="color:var(--accent-blue);">{{ __('p2tl.table.pemeriksaan') }}</th>
                    <th style="color:var(--accent-red);">{{ __('p2tl.table.temuan') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('p2tl.table.temuan_kwh') }}</th>
                </tr>
            </thead>
            <tbody id="p2tlSideTable"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Temuan kWh --}}
<div class="card col-12 preview-card">
    <div class="card-title">{{ __('p2tl.kwh_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartP2tlKwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('p2tl.table.bulan') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('p2tl.table.temuan_kwh') }}</th>
                </tr>
            </thead>
            <tbody id="tabelP2tlKwh"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: Tagihan Susulan --}}
<div class="card col-12">
    <div class="card-title">{{ __('p2tl.tagsus_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartP2tlTagsus"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('p2tl.table.bulan') }}</th>
                    <th style="color:var(--accent-green);">{{ __('p2tl.table.tagsus') }}</th>
                </tr>
            </thead>
            <tbody id="tabelP2tlTagsus"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: Tabel P2TL --}}
<div class="card col-12">
    <div class="card-title">{{ __('p2tl.detail_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid var(--border-color); border-radius:8px;">
        <table style="white-space:nowrap;">
            <thead>
                <tr>
                    <th>{{ __('p2tl.table.no') }}</th>
                    <th>{{ __('p2tl.table.bulan') }}</th>
                    <th>{{ __('p2tl.table.jlh_pemeriksaan') }}</th>
                    <th>{{ __('p2tl.table.jlh_temuan') }}</th>
                    <th>{{ __('p2tl.table.temuan_kwh') }}</th>
                    <th>{{ __('p2tl.table.tagsus') }}</th>
                </tr>
            </thead>
            <tbody id="tabelP2tl"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin:16px 0 8px;">
        <div class="card-title" style="margin:0;">Edit Data</div>
        <span id="p2tlEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="p2tlEditContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatP2tl" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Muat Data P2TL</div>

        <div style="margin-bottom:16px;">
            <label for="muatTahunP2tl" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunP2tl" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatP2tl" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiMuatP2tl" class="btn-action btn-action--solid btn-action--load">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusP2tl" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data P2TL</div>
        <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>

        <div style="margin-bottom:16px;">
            <label for="hapusTahunP2tl" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunP2tl" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusP2tl" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiHapusP2tl" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatP2tl" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Buat Data P2TL</div>
        <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>

        <div style="margin-bottom:16px;">
            <label for="buatTahunP2tl" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunP2tl" style="width:100%;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatP2tl" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiBuatP2tl" class="btn-action btn-action--solid btn-action--create">Buat</button>
        </div>
    </div>
</div>