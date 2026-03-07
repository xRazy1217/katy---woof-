/**
 * Katy & Woof - Gallery Page Module v1.0
 * Manejo de la página de galería con carga dinámica y reveal
 */

class GalleryPage {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.loadGallery();
        });
    }

    async loadGallery() {
        try {
            const res = await fetch(`api.php?t=${Date.now()}`);
            const data = await res.json();
            const gallery = document.getElementById('dynamic-gallery');
            gallery.innerHTML = data.map(item => `
                <div class="gallery-item group reveal">
                    <img src="${item.img_url}" class="gallery-img" alt="${item.name}" loading="lazy" referrerPolicy="no-referrer">
                    <div class="gallery-overlay">
                        <span class="text-white text-2xl font-bold serif italic mb-2">${item.name}</span>
                        <p class="text-white/60 text-[9px] font-black uppercase tracking-widest">${item.description || ''}</p>
                    </div>
                </div>
            `).join('');
            this.initReveal();
        } catch (e) {
            document.getElementById('dynamic-gallery').innerHTML = "<p class='col-span-full text-center italic py-20 text-stone-400'>El Atelier está preparando nuevas piezas...</p>";
        }
    }

    initReveal() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('active'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    }
}

// Inicializar la galería
new GalleryPage();