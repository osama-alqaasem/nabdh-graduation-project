import './bootstrap';

window.toggleTheme = function () {
    const html = document.documentElement;
    const icon = document.getElementById('themeIcon');

    let current = html.getAttribute('data-bs-theme') || 'light';

    if (current === 'dark') {
        html.setAttribute('data-bs-theme', 'light');
        localStorage.setItem('theme', 'light');
    } else {
        html.setAttribute('data-bs-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    }

    updateIcon();
};

function updateIcon() {
    const icon = document.getElementById('themeIcon');
    const theme = document.documentElement.getAttribute('data-bs-theme');

    if (!icon) return;

    icon.className = theme === 'dark'
        ? 'fa-solid fa-moon'
        : 'fa-solid fa-sun';
}

// عند تحميل الصفحة
document.addEventListener("DOMContentLoaded", function () {
    const saved = localStorage.getItem('theme') || 'light';

    document.documentElement.setAttribute('data-bs-theme', saved);

    updateIcon();
});
