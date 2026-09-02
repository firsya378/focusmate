// js/auth.js - Auth Check

// Cek login di setiap halaman
document.addEventListener('DOMContentLoaded', function() {
    // Halaman yang tidak memerlukan login
    const publicPages = ['login.html', 'register.html'];
    const currentPage = window.location.pathname.split('/').pop();
    
    if (!publicPages.includes(currentPage)) {
        if (!isLoggedIn()) {
            window.location.href = 'login.html';
        }
    }
    
    // Jika sudah login, redirect dari halaman login/register
    if (publicPages.includes(currentPage) && isLoggedIn()) {
        window.location.href = 'dashboard.html';
    }
});