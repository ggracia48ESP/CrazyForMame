document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-lightbox]');
        if (!trigger) return;
        event.preventDefault();

        const overlay = document.createElement('div');
        overlay.className = 'lightbox';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-label', 'Imagen ampliada');
        overlay.innerHTML = '<button class="lightbox-close" type="button" aria-label="Cerrar imagen">×</button><div class="lightbox-frame"><img alt=""></div>';
        const image = overlay.querySelector('img');
        image.src = trigger.href;
        image.alt = trigger.querySelector('img')?.alt || '';

        const close = () => { overlay.remove(); document.removeEventListener('keydown', onKeydown); };
        const onKeydown = (keyEvent) => { if (keyEvent.key === 'Escape') close(); };
        overlay.addEventListener('click', (clickEvent) => { if (clickEvent.target === overlay) close(); });
        overlay.querySelector('.lightbox-close').addEventListener('click', close);
        document.addEventListener('keydown', onKeydown);
        document.body.appendChild(overlay);
    });
});
