<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });

    @if(session('success'))
        Toast.fire({ icon: 'success', title: '{{ session('success') }}' });
    @endif

    @if(session('error'))
        Toast.fire({ icon: 'error', title: '{{ session('error') }}' });
    @endif

    @if(session('pending'))
        Swal.fire({
            icon: 'info',
            title: 'Menunggu Persetujuan',
            text: '{{ session('pending') }}',
            confirmButtonColor: '#ff9800',
            confirmButtonText: 'OK'
        });
    @endif

    @if(session('warning'))
        Toast.fire({ icon: 'warning', title: '{{ session('warning') }}' });
    @endif
</script>