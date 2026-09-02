// assets/js/app.js
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebarMenu');
    const toggler = document.querySelector('.navbar-toggler');

    if (sidebar && toggler) {
        toggler.addEventListener('click', function() {
            if (!window.matchMedia('(max-width: 767.98px)').matches) {
                return;
            }

            const isOpen = sidebar.classList.contains('show');
            sidebar.classList.toggle('show', !isOpen);
            toggler.setAttribute('aria-expanded', String(!isOpen));
        });

        document.addEventListener('click', function(e) {
            if (!window.matchMedia('(max-width: 767.98px)').matches) {
                return;
            }
            if (sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) &&
                !toggler.contains(e.target)) {
                sidebar.classList.remove('show');
                toggler.setAttribute('aria-expanded', 'false');
            }
        });
    }
});
