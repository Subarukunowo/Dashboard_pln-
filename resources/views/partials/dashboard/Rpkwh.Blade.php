{{-- CARD 1: Rencana vs Realisasi RPKWH --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
        <div class="card-title">{{ __('rpkwh.title') }}</div>

        <div class="btn-action-group">
            <div class="filters">
                <label for="filterTahunRpkwhAtas" class="sr-only">Tahun</label>
                <select id="filterTahunRpkwhAtas">
                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                </select>

                <label for="filterTarifRpkwh" class="sr-only">{{ __('rpkwh.filter.tarif') }}</label>
                <select id="filterTarifRpkwh"></select>

                <label for="filterViewRpkwh" class="sr-only">{{ __('rpkwh.filter.tampilan') }}</label>
                <select id="filterViewRpkwh">
                    <option value="bulanan">{{ __('rpkwh.filter.bulanan') }}</option>
                    <option value="akumulasi">{{ __('rpkwh.filter.akumulasi') }}</option>
                </select>
            </div>

            <button id="btnMuatRpkwh" class="btn-action btn-action--load">＋ Muat Data</button>
            <button id="btnBuatRpkwh" class="btn-action btn-action--create">✚ Buat Data</button>
            <button id="btnHapusRpkwh" class="btn-action btn-action--delete">🗑 Hapus Data</button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartRencanaRealisasi"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('rpkwh.table.bulan') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('rpkwh.table.rencana') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('rpkwh.table.realisasi') }}</th>
                    <th scope="col">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="rpkwhSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.delta_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartDeltaRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('rpkwh.table.bulan') }}</th>
                    <th scope="col">{{ __('rpkwh.table.selisih_rpkwh') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRpkwhDelta"></tbody>
        </table>
    </div>

    <div style="display:flex; align-items:center; gap:10px; margin:16px 0 8px; flex-wrap:wrap;">
        <div class="card-title" style="margin:0;">Edit Data</div>

        <label for="filterGolonganRpkwhEdit" class="sr-only">Golongan</label>
        <select id="filterGolonganRpkwhEdit"></select>

        <span id="rpkwhEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="rpkwhEditContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- CARD 2: Distribusi Golongan RPKWH --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div class="card-title" id="judulPieRpkwh">{{ __('rpkwh.pie_title') }}</div>

        <div class="filters">
            <label for="filterTahunRpkwh" class="sr-only">{{ __('rpkwh.filter.tahun') }}</label>
            <select id="filterTahunRpkwh">
                <option value="{{ now()->year }}">{{ now()->year }}</option>
            </select>

            <label for="filterBulanRpkwh" class="sr-only">{{ __('rpkwh.filter.bulan') }}</label>
            <select id="filterBulanRpkwh">
                <option value="all">{{ __('rpkwh.filter.semua') }}</option>
                @foreach(range(0, 11) as $i)
                    <option value="{{ $i }}">{{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartPieRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('rpkwh.table.golongan') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('rpkwh.table.nilai') }}</th>
                    <th scope="col">%</th>
                </tr>
            </thead>
            <tbody id="rpkwhPieTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.growth_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartGrowthRpkwh"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table style="white-space:nowrap;">
            <thead>
                <tr>
                    <th scope="col">{{ __('rpkwh.table.bulan') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('rpkwh.table.sosial') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('rpkwh.table.rtangga') }}</th>
                    <th scope="col" style="color:var(--accent-blue);">{{ __('rpkwh.table.bisnis') }}</th>
                    <th scope="col" style="color:#e91e63;">{{ __('rpkwh.table.industri') }}</th>
                    <th scope="col" style="color:#9c27b0;">{{ __('rpkwh.table.publik') }}</th>
                    <th scope="col" style="color:#00bcd4;">{{ __('rpkwh.table.multiguna') }}</th>
                </tr>
            </thead>
            <tbody id="tabelGrowthRpkwh"></tbody>
        </table>
    </div>
</div>

{{-- CARD 3: BPP vs HJR --}}
<div class="card col-12">
    <div class="card-title">{{ __('rpkwh.bpp_hjr_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartBppHjr"></canvas>
    </div>
    <div class="custom-scroll" style="overflow-x:auto; overflow-y:auto; max-height:340px; border:1px solid var(--border-color); border-radius:8px;">
        <table style="white-space:nowrap;">
            <thead>
                <tr>
                    <th scope="col">{{ __('rpkwh.table.bulan') }}</th>
                    <th scope="col" style="color:var(--accent-blue);">{{ __('rpkwh.table.hjr') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('rpkwh.table.bpp') }}</th>
                    <th scope="col">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="tabelBppHjr"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: Analisa RPKWH --}}
<div class="card col-12">
    <div class="card-title">{{ __('rpkwh.analisa_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:320px; margin-bottom:20px;">
        <canvas id="chartAnalisaTargetRealisasi"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('rpkwh.table.bulan') }}</th>
                    <th scope="col" style="color:var(--text-dim);">{{ __('rpkwh.table.t_lalu') }}</th>
                    <th scope="col" style="color:var(--accent-blue);">{{ __('rpkwh.table.r_lalu') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('rpkwh.table.t_ini') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('rpkwh.table.r_ini') }}</th>
                    <th scope="col">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="rpkwhAnalisaSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.delta_analisa_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartAnalisaDelta"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('rpkwh.table.bulan') }}</th>
                    <th scope="col">{{ __('rpkwh.table.selisih_rpkwh') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRpkwhAnalisaDelta"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('rpkwh.persen_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartPersenRpkwh"></canvas>
    </div>
    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ __('rpkwh.table.bulan') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('rpkwh.table.target') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('rpkwh.table.realisasi') }}</th>
                    <th scope="col">{{ __('rpkwh.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPersen"></tbody>
        </table>
    </div>
</div>

{{-- CARD 5: Tabel Analisa Detail RPKWH --}}
<div class="card col-12">
    <div class="card-title">{{ __('rpkwh.tabel_analisa_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid var(--border-color); border-radius:8px;">
        <table style="white-space:nowrap;">
            <thead>
                <tr>
                    <th scope="col">{{ __('rpkwh.table.bulan') }}</th>
                    <th scope="col" style="color:var(--text-dim);">{{ __('rpkwh.table.target_lalu') }}</th>
                    <th scope="col" style="color:var(--accent-blue);">{{ __('rpkwh.table.real_lalu') }}</th>
                    <th scope="col" style="color:var(--accent-orange);">{{ __('rpkwh.table.target_ini') }}</th>
                    <th scope="col" style="color:var(--accent-green);">{{ __('rpkwh.table.realisasi_ini') }}</th>
                    <th scope="col">{{ __('rpkwh.table.selisih') }}</th>
                    <th scope="col">{{ __('rpkwh.table.selisih_pct') }}</th>
                    <th scope="col">{{ __('rpkwh.table.pct_capai_target') }}</th>
                    <th scope="col">{{ __('rpkwh.table.pct_capai_real') }}</th>
                </tr>
            </thead>
            <tbody id="tabelAnalisa"></tbody>
        </table>
    </div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatRpkwh" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Muat Data RPKWH</div>
        <div style="margin-bottom:12px;">
            <label for="muatTahunRpkwh" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunRpkwh" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label for="muatTipeRpkwh" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipeRpkwh" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatRpkwh" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiMuatRpkwh" class="btn-action btn-action--solid btn-action--load">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatRpkwh" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Buat Data RPKWH</div>
        <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">Membuat 12 baris data kosong (nilai = 0) untuk tahun yang dipilih.</div>
        <div style="margin-bottom:12px;">
            <label for="buatTahunRpkwh" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunRpkwh" style="width:100%;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label for="buatTipeRpkwh" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipeRpkwh" style="width:100%;">
                <option value="target">Target</option>
                <option value="realisasi">Realisasi</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatRpkwh" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiBuatRpkwh" class="btn-action btn-action--solid btn-action--create">Buat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusRpkwh" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data RPKWH</div>
        <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>
        <div style="margin-bottom:12px;">
            <label for="hapusTahunRpkwh" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunRpkwh" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label for="hapusTipeRpkwh" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipeRpkwh" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusRpkwh" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiHapusRpkwh" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
        </div>
    </div>
</div>