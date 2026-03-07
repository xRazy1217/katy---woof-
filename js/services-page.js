/**
 * Katy & Woof - Services Page Module v1.0
 * Manejo de la página de servicios con filtros, grid y modal
 */

class ServicesPage {
    constructor() {
        this.allServicesData = [];
        this.modal = document.getElementById('service-modal');
        this.modalInner = document.getElementById('modal-inner');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadData();
    }

    setupEventListeners() {
        document.getElementById('close-modal').onclick = () => this.closeModal();
        this.modal.onclick = (e) => { if (e.target === this.modal) this.closeModal(); };
    }

    async loadData() {
        try {
            const res = await fetch(`api.php?action=get_services&v=${Date.now()}`);
            this.allServicesData = await res.json();
            this.renderFilters();
            this.renderServices(this.allServicesData);
        } catch (error) {
            console.error('Error loading services:', error);
        }
    }

    renderFilters() {
        const categories = ['Todos', ...new Set(this.allServicesData.map(s => s.category).filter(Boolean))];
        const filtersContainer = document.getElementById('filters-container');
        filtersContainer.innerHTML = categories.map(cat =>
            `<button class="filter-btn px-6 py-3 rounded-full text-xs font-black uppercase tracking-widest transition-colors ${cat === 'Todos' ? 'active' : ''}" data-category="${cat}">${cat}</button>`
        ).join('');

        filtersContainer.onclick = (e) => {
            if (e.target.classList.contains('filter-btn')) {
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                e.target.classList.add('active');
                const category = e.target.dataset.category;
                this.renderServices(category === 'Todos' ? this.allServicesData : this.allServicesData.filter(s => s.category === category));
            }
        };
    }

    renderServices(services) {
        // ordenar por categoría y título para que el grid se vea ordenado
        services.sort((a,b)=>{
            const ca = (a.category||'').toLowerCase();
            const cb = (b.category||'').toLowerCase();
            if (ca !== cb) return ca.localeCompare(cb);
            return a.title.localeCompare(b.title);
        });

        document.getElementById('services-grid').innerHTML = services.map(s => `
            <button class="gallery-item reveal group text-left" onclick="servicesPage.openServiceModal(${s.id})">
                <div class="aspect-[4/5] rounded-[2rem] overflow-hidden bg-stone-100 mb-8">
                    <img src="${s.main_image_url}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" referrerPolicy="no-referrer">
                </div>
                <span class="text-[9px] font-black uppercase tracking-[0.3em] text-stone-400 mb-2 block">${s.category || 'Atelier'}</span>
                <h2 class="text-2xl serif font-bold group-hover:text-midnight transition-colors mb-3">${s.title}</h2>
                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-[var(--pink-deep)]">Ver Detalle &rarr;</p>
            </button>
        `).join('');

        this.initReveal();
    }

    openServiceModal(id) {
        const s = this.allServicesData.find(item => item.id == id);
        if (!s) return;

        document.getElementById('modal-title').textContent = s.title;

        // Renderizar descripción con mejor formato
        const descriptionHTML = s.description
            .split('\n')
            .filter(p => p.trim()) // Filtrar líneas vacías
            .map(p => `<p class="mb-4 leading-relaxed">${p.trim()}</p>`)
            .join('');
        document.getElementById('modal-description').innerHTML = descriptionHTML;

        document.getElementById('modal-main-img').src = s.main_image_url;

        // Obtener número de WhatsApp desde PHP (se inyectará desde el HTML)
        const waNum = window.siteSettings?.contact_whatsapp?.replace(/\s+/g, '') || '';
        document.getElementById('modal-whatsapp').href = `https://wa.me/${waNum}?text=Me interesa: ${s.title}`;

        this.modal.classList.remove('opacity-0', 'pointer-events-none');
        this.modalInner.classList.remove('scale-95', 'opacity-0');
    }

    closeModal() {
        this.modalInner.classList.add('scale-95', 'opacity-0');
        this.modal.classList.add('opacity-0');
        setTimeout(() => this.modal.classList.add('pointer-events-none'), 500);
    }

    initReveal() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('active'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.servicesPage = new ServicesPage();
});