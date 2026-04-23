/**
 * toast.js
 * Membaca flash session dari data-* attribute pada <body>
 * dan menampilkan SweetAlert2 toast.
 *
 * Dipanggil dengan defer — berjalan setelah DOM + SweetAlert2 ready.
 */
window.addEventListener('load', function () {
    if (typeof Swal === 'undefined') return;

    const Toast = Swal.mixin({
        toast:             true,
        position:          'top-end',
        showConfirmButton: false,
        timer:             3000,
        timerProgressBar:  true,
    });

    const b       = document.body.dataset;
    const success = b.flashSuccess;
    const error   = b.flashError;
    const warning = b.flashWarning;
    const pending = b.flashPending;

    if (success) Toast.fire({ icon: 'success', title: success });
    if (error)   Toast.fire({ icon: 'error',   title: error });
    if (warning) Toast.fire({ icon: 'warning', title: warning });
    if (pending) {
        Swal.fire({
            icon:               'info',
            title:              'Menunggu Persetujuan',
            text:               pending,
            confirmButtonColor: '#ff9800',
            confirmButtonText:  'OK',
        });
    }
});