/**
 * Petits comportements UI :
 *   - alertes auto-fermables
 *   - fermeture du menu mobile au clic sur l'overlay
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert[data-autoclose]').forEach(el => {
        setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 5000);
    });

    const app = document.getElementById('app');
    if (app) {
        app.addEventListener('click', (ev) => {
            if (app.classList.contains('is-open') &&
                ev.target === app /* clic sur le ::before overlay = sur l'élément app lui-même */) {
                app.classList.remove('is-open');
            }
        });
    }
});
