<script>
document.addEventListener('DOMContentLoaded', function () {

    @if(session('pending'))
    Swal.fire({
        icon: 'info',
        title: 'Menunggu Persetujuan',
        html: '<p style="color:#aaa;font-size:0.9rem">{{ session('pending') }}</p><small style="color:#666">Admin akan mengirim notifikasi email setelah perangkat disetujui.</small>',
        confirmButtonColor: '#ff9800',
        confirmButtonText: 'Mengerti',
        background: '#1a1a1a',
        color: '#fff',
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Akses Ditolak',
        text: '{{ session('error') }}',
        confirmButtonColor: '#f44336',
        confirmButtonText: 'OK',
        background: '#1a1a1a',
        color: '#fff',
    });
    @endif

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '{{ session('success') }}',
        confirmButtonColor: '#00ff88',
        confirmButtonText: 'OK',
        background: '#1a1a1a',
        color: '#fff',
    });
    @endif

});
</script>