/**
 * Shared toast feedback helper, wrapping SweetAlert2 (already loaded on
 * every page that needs this). Usage: window.notify('success', 'Product created');
 */
window.notify = function (type, message) {
    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert2 not loaded on this page; notify() call ignored:', type, message);
        return;
    }
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type === 'success' ? 'success' : 'error',
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
};
