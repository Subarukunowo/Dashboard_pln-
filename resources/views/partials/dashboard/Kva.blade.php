{{-- CARD 1: Rencana vs Realisasi Penambahan KVA --}}
<div class="card col-12 preview-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="card-title">{{ __('kva.title') }}</div>
            <span id="kvaWarning" style="display:none; font-size:0.7rem; padding:4px 10px; border-radius:12px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
                ⚠ Data ini merupakan data tahun lalu
            </span>
        </div>

        <div class="btn-action-group">
            <div class="filters">
                <label for="filterTarifKva" class="sr-only">{{ __('kva.filter.tarif') }}</label>
                <select id="filterTarifKva">
                    {{-- diisi otomatis oleh KvaModule.js dari data.golonganDropdown --}}
                </select>

                <label for="filterViewKva" class="sr-only">{{ __('kva.filter.tampilan') }}</label>
                <select id="filterViewKva">
                    <option value="bulanan">{{ __('kva.filter.bulanan') }}</option>
                    <option value="akumulasi">{{ __('kva.filter.akumulasi') }}</option>
                </select>

                <label for="filterBulanKva" class="sr-only">{{ __('kva.filter.bulan') }}</label>
                <select id="filterBulanKva">
                    @foreach(range(0, 11) as $i)
                        <option value="{{ $i }}" {{ $i == 11 ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i + 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button id="btnMuatKva" class="btn-action btn-action--load">
                ＋ Muat Data
            </button>
            <button id="btnBuatKva" class="btn-action btn-action--create">
                ✚ Buat Data
            </button>
            <button id="btnHapusKva" class="btn-action btn-action--delete">
                🗑 Hapus Data
            </button>
        </div>
    </div>

    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartKvaBar"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('kva.table.bulan') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('kva.table.rencana') }}</th>
                    <th style="color:var(--accent-green);">{{ __('kva.table.realisasi') }}</th>
                    <th>{{ __('kva.table.selisih') }}</th>
                </tr>
            </thead>
            <tbody id="kvaSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('kva.delta_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartKvaDelta"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('kva.table.bulan') }}</th>
                    <th>{{ __('kva.table.selisih_kva') }}</th>
                </tr>
            </thead>
            <tbody id="tabelKvaDelta"></tbody>
        </table>
    </div>
</div>

{{-- CARD 2: Distribusi Golongan KVA --}}
<div class="card col-12 preview-card">
    <div class="card-title">{{ __('kva.distribusi_title') }}</div>

    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartKvaPie"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('kva.table.golongan') }}</th>
                    <th style="color:var(--accent-green);">{{ __('kva.table.nilai') }}</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody id="kvaPieTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('kva.gol_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:280px; margin-bottom:20px;">
        <canvas id="chartKvaGol"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('kva.table.golongan') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('kva.table.target_ini') }}</th>
                    <th style="color:var(--accent-blue);">{{ __('kva.table.real_lalu') }}</th>
                </tr>
            </thead>
            <tbody id="tabelKvaGol"></tbody>
        </table>
    </div>

    {{-- Tabel Edit Golongan (akumulasi per bulan terpilih) --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
        <div class="card-title" style="margin:0;">Edit Data per Golongan</div>
        <span id="kvaEditWarning2" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>
    </div>
    <div id="kvaEditGolonganContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- CARD 3: Perbandingan KVA Thn Lalu vs Thn Ini --}}
<div class="card col-12">
    <div class="card-title">{{ __('kva.perbandingan_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:300px; margin-bottom:20px;">
        <canvas id="chartKvaPerbandingan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px; margin-bottom:20px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('kva.table.bulan') }}</th>
                    <th style="color:var(--text-dim);">{{ __('kva.table.t_lalu') }}</th>
                    <th style="color:var(--accent-blue);">{{ __('kva.table.r_lalu') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('kva.table.t_ini') }}</th>
                    <th style="color:var(--accent-green);">{{ __('kva.table.r_ini') }}</th>
                    <th>Δ</th>
                </tr>
            </thead>
            <tbody id="kvaPerbandinganSideTable"></tbody>
        </table>
    </div>

    <div class="card-title" style="margin-top:10px; margin-bottom:15px;">{{ __('kva.delta_perbandingan_title') }}</div>
    <div class="chart-wrapper" style="width:100%; height:250px; margin-bottom:20px;">
        <canvas id="chartKvaDeltaPerbandingan"></canvas>
    </div>

    <div class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:340px;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('kva.table.bulan') }}</th>
                    <th>{{ __('kva.table.selisih_kva') }}</th>
                </tr>
            </thead>
            <tbody id="tabelKvaDeltaPerbandingan"></tbody>
        </table>
    </div>
</div>

{{-- CARD 4: RKAP KVA --}}
<div class="card col-12">
    <div class="card-title">{{ __('kva.rkap_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid var(--border-color); border-radius:8px;">
        <table style="white-space:nowrap;">
            <thead>
                <tr>
                    <th>{{ __('kva.table.golongan') }}</th>
                    <th>{{ __('kva.table.target_tw1') }}</th>
                    <th>{{ __('kva.table.target_tw2') }}</th>
                    <th>{{ __('kva.table.target_tw3') }}</th>
                    <th>{{ __('kva.table.target_tw4') }}</th>
                    <th>{{ __('kva.table.target_total') }}</th>
                    <th>{{ __('kva.table.real_tw1') }}</th>
                    <th>{{ __('kva.table.real_tw2') }}</th>
                    <th>{{ __('kva.table.real_tw3') }}</th>
                    <th>{{ __('kva.table.real_tw4') }}</th>
                    <th>{{ __('kva.table.real_total') }}</th>
                    <th>{{ __('kva.table.pct_capai') }}</th>
                </tr>
            </thead>
            <tbody id="tabelRkapKva"></tbody>
        </table>
    </div>
</div>

{{-- CARD 5: Tabel Perbandingan KVA --}}
<div class="card col-12">
    <div class="card-title">{{ __('kva.tabel_perbandingan_title') }}</div>
    <div class="custom-scroll" style="overflow-x:auto; border:1px solid var(--border-color); border-radius:8px; margin-bottom:16px;">
        <table style="white-space:nowrap;">
            <thead>
                <tr>
                    <th>{{ __('kva.table.bulan') }}</th>
                    <th style="color:var(--text-dim);">{{ __('kva.table.target_lalu') }}</th>
                    <th style="color:var(--accent-blue);">{{ __('kva.table.real_lalu') }}</th>
                    <th style="color:var(--accent-orange);">{{ __('kva.table.target_ini') }}</th>
                    <th style="color:var(--accent-green);">{{ __('kva.table.realisasi_ini') }}</th>
                    <th>{{ __('kva.table.selisih') }}</th>
                    <th>{{ __('kva.table.selisih_pct') }}</th>
                    <th>{{ __('kva.table.pct_capai_target') }}</th>
                    <th>{{ __('kva.table.pct_capai_real') }}</th>
                </tr>
            </thead>
            <tbody id="tabelPerbandinganKva"></tbody>
        </table>
    </div>

    {{-- Tabel Edit --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px; flex-wrap:wrap;">
        <div class="card-title" style="margin:0;">Edit Data</div>
        <span id="kvaEditWarning" style="display:none; font-size:0.7rem; padding:3px 8px; border-radius:10px; background:rgba(var(--accent-orange-rgb),0.12); border:1px solid rgba(var(--accent-orange-rgb),0.3); color:var(--accent-orange);">
            🔒 Data tahun lalu — hanya bisa dilihat
        </span>

        <div style="margin-left:auto; display:flex; align-items:center; gap:6px;">
            <label for="filterTarifKvaEdit" style="font-size:0.75rem; color:var(--text-secondary); white-space:nowrap;">
                {{ __('kva.filter.tarif') }}
            </label>
            <select id="filterTarifKvaEdit">
                {{-- diisi otomatis oleh KvaModule.js dari data.golonganDropdown --}}
            </select>
        </div>
    </div>
    <div id="kvaEditContainer" class="custom-scroll" style="width:100%; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; overflow-y:auto; max-height:420px;"></div>
</div>

{{-- Modal Muat Data --}}
<div id="modalMuatKva" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:16px; color:var(--text-primary);">Muat Data KVA</div>

        <div style="margin-bottom:12px;">
            <label for="muatTahunKva" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="muatTahunKva" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="muatTipeKva" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="muatTipeKva" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalMuatKva" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiMuatKva" class="btn-action btn-action--solid btn-action--load">Muat</button>
        </div>
    </div>
</div>

{{-- Modal Buat Data --}}
<div id="modalBuatKva" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Buat Data KVA</div>
        <div style="font-size:0.72rem; color:var(--text-dim); margin-bottom:16px;">
            Membuat 12 bulan data (nilai = 0) untuk sub golongan yang dipilih, termasuk JUMLAH-per-golongan dan JUMLAH total, beserta triwulan dan akumulasi otomatis.
        </div>

        <div style="margin-bottom:12px;">
            <label for="buatTahunKva" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="buatTahunKva" style="width:100%;">
                @foreach(range(date('Y') + 3, 2020) as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') + 1 ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:12px;">
            <label for="buatTipeKva" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="buatTipeKva" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalBuatKva" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiBuatKva" class="btn-action btn-action--solid btn-action--create">Buat</button>
        </div>
    </div>
</div>

{{-- Modal Hapus Data --}}
<div id="modalHapusKva" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius); padding:24px; width:320px;">
        <div style="font-size:0.95rem; font-weight:600; margin-bottom:8px; color:var(--text-primary);">Hapus Data KVA</div>
        <div style="font-size:0.78rem; color:var(--text-secondary); margin-bottom:16px;">Pilih tahun dan tipe data yang ingin dihapus. Tindakan ini tidak dapat dibatalkan.</div>

        <div style="margin-bottom:12px;">
            <label for="hapusTahunKva" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tahun</label>
            <select id="hapusTahunKva" style="width:100%;">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label for="hapusTipeKva" style="font-size:0.75rem; color:var(--text-secondary); display:block; margin-bottom:4px;">Tipe</label>
            <select id="hapusTipeKva" style="width:100%;">
                <option value="realisasi">Realisasi</option>
                <option value="target">Target</option>
                <option value="semua">Semua</option>
            </select>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnBatalHapusKva" class="btn-action btn-action--ghost">Batal</button>
            <button id="btnKonfirmasiHapusKva" class="btn-action btn-action--solid btn-action--delete">Hapus</button>
        </div>
    </div>
</div>