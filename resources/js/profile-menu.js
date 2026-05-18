function initProfileMenu() {
    const wrap = document.getElementById('dash-profile-wrap');
    if (!wrap) return;

    const btn = document.getElementById('dash-profile-trigger');
    const dd = document.getElementById('dash-profile-dd');
    if (!btn || !dd) return;

    const close = () => {
        wrap.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
    };

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const open = !wrap.classList.contains('is-open');
        wrap.classList.toggle('is-open', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', (e) => {
        if (!wrap.contains(e.target)) close();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
    });
}

document.addEventListener('DOMContentLoaded', initProfileMenu);
window.initProfileMenu = initProfileMenu;
