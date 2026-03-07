/**
 * Katy & Woof - Footer Sync Module v1.1
 * Centraliza la hidratación de datos dinámicos en el pie de página.
 */
document.addEventListener('DOMContentLoaded', () => {
    const syncFooter = async () => {
        try {
            const res = await fetch(`api.php?action=get_settings&v=${Date.now()}`);
            if (!res.ok) return;
            
            const settings = await res.json();
            const getVal = (key, fallback = '...') => settings.find(s => s.setting_key === key)?.setting_value || fallback;

            // Datos de Contacto
            const email = getVal('contact_email');
            const wa = getVal('contact_whatsapp');
            const addr = getVal('contact_address');
            const instagram = getVal('social_instagram', '#');
            const philosophy = getVal('footer_philosophy');
            
            const emailEl = document.getElementById('footer-email');
            if(emailEl) { emailEl.href = `mailto:${email}`; emailEl.innerText = email; }

            const waEl = document.getElementById('footer-whatsapp');
            if(waEl) { waEl.href = `https://wa.me/${wa.replace(/\s+/g, '')}`; waEl.innerText = wa; }

            const addrEl = document.getElementById('footer-address');
            if(addrEl) { addrEl.innerText = addr; }

            const philosophyEl = document.getElementById('footer-philosophy');
            if(philosophyEl) { philosophyEl.innerText = philosophy; }

            const instaEl = document.getElementById('footer-instagram');
            if(instaEl) { instaEl.href = instagram; }

            const yearEl = document.getElementById('copyright-year');
            if(yearEl) { yearEl.textContent = new Date().getFullYear(); }

            // Actualizar favicon global si existe el elemento
            const siteFav = document.getElementById('site-favicon');
            if(siteFav) siteFav.href = getVal('site_favicon') + '?v=' + Date.now();

        } catch (e) {
            console.warn("Footer sync failed.", e);
        }
    };

    syncFooter();
});