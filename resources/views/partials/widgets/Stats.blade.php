{{-- Stats.blade.php --}}
{{-- Semua card stats untuk semua section. Ditampilkan/disembunyikan via JS (data-stat-section) --}}
{{-- Diisi oleh initStats() dari UpdateStats.js --}}

{{-- ── BPP ──────────────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="bpp" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">BPP Konsolidasi</div>
    <div id="statBppPembangkit" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statBppPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="bpp" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">BPP Target</div>
    <div id="statBppTransmisi" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>
<div class="card col-4" data-stat-section="bpp" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Selisih MoM</div>
    <div id="statBppMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── LOSSES ───────────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="losses" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Losses Jaringan</div>
    <div id="statLossesRealisasi" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statLossesPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="losses" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Target Losses</div>
    <div id="statLossesTarget" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>
<div class="card col-4" data-stat-section="losses" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Selisih MoM</div>
    <div id="statLossesMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── PENJUALAN ────────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="penjualan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Realisasi Penjualan</div>
    <div id="statRealisasiPenjualan" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statPenjualanPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="penjualan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Target Penjualan</div>
    <div id="statTargetPenjualan" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statPenjualanCapai" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="penjualan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">MoM Penjualan</div>
    <div id="statPenjualanMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── BABIN ────────────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="babin" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Realisasi Babin</div>
    <div id="statBabinRealisasi" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statBabinPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="babin" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Target Babin</div>
    <div id="statBabinTarget" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statBabinCapai" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="babin" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">MoM Babin</div>
    <div id="statBabinMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── JAM NYALA ────────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="jamnyala" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Jam Nyala Realisasi</div>
    <div id="statJamNyalaRealisasi" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statJamNyalaPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="jamnyala" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Jam Nyala Target</div>
    <div id="statJamNyalaTarget" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statJamNyalaPencapaian" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="jamnyala" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">MoM Jam Nyala</div>
    <div id="statJamNyalaMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── GOLONGAN TARIF (HJR) ─────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="goltarif" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">HJR Realisasi</div>
    <div id="statHjrTr" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statHjrPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="goltarif" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">HJR Target</div>
    <div id="statHjrTarget" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>
<div class="card col-4" data-stat-section="goltarif" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">MoM HJR</div>
    <div id="statHjrMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── PENDAPATAN ───────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="pendapatan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Realisasi Pendapatan</div>
    <div id="statRealisasiPendapatan" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statPendapatanPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="pendapatan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Target Pendapatan</div>
    <div id="statTargetPendapatan" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statPendapatanCapai" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="pendapatan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">MoM Pendapatan</div>
    <div id="statPendapatanMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── RPKWH ────────────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="rpkwh" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">RPKWH Realisasi</div>
    <div id="statRpkwhRealisasi" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statRpkwhPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="rpkwh" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">RPKWH Target</div>
    <div id="statRpkwhTarget" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statRpkwhCapai" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="rpkwh" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">MoM RPKWH</div>
    <div id="statRpkwhMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── PELANGGAN ────────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="pelanggan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Total Pelanggan</div>
    <div id="statTotalCustomer" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statTotalCustomerPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="pelanggan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Target Pelanggan</div>
    <div id="statTargetPelanggan" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statPelangganCapai" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="pelanggan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Pelanggan Baru (MoM)</div>
    <div id="statNewCustomer" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── KVA ──────────────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="kva" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">KVA Realisasi</div>
    <div id="statKvaRealisasi" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statKvaPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="kva" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">KVA Target</div>
    <div id="statKvaTarget" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statKvaCapai" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="kva" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">MoM KVA</div>
    <div id="statKvaMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── TUNGGAKAN ────────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="tunggakan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Jumlah Tunggakan</div>
    <div id="statJmlTunggakan" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statTunggakanPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="tunggakan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Pendapatan</div>
    <div id="statPendapatanTunggakan" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statRatioTunggakan" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="tunggakan" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">MoM Tunggakan</div>
    <div id="statTunggakanMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>

{{-- ── P2TL ─────────────────────────────────────────────────────────────────── --}}
<div class="card col-4" data-stat-section="p2tl" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Pemeriksaan P2TL</div>
    <div id="statP2tlPemeriksaan" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statP2tlPeriod" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="p2tl" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">Temuan P2TL</div>
    <div id="statP2tlTemuan" class="stat-value"><span style="color:#555;font-size:0.8rem;">—</span></div>
    <div id="statP2tlTagihan" style="font-size:0.65rem; color:#555; margin-top:4px;"></div>
</div>
<div class="card col-4" data-stat-section="p2tl" style="display:none;">
    <div class="card-title" style="font-size:0.7rem; text-transform:uppercase; color:#888; margin-bottom:4px;">MoM P2TL</div>
    <div id="statP2tlMom" class="stat-change"><span style="color:#555;font-size:0.8rem;">—</span></div>
</div>
