/**
 * Katy & Woof - Admin UI Module v6.0
 */
const AdminUI = {
    AUTH_KEY: 'Asesor25',
    tabsLoaded: false,
    lastDiagnostics: null,

    attemptAuth() {
        const input = document.getElementById('auth-input');
        if (input.value === this.AUTH_KEY) {
            localStorage.setItem('kw_admin_key', input.value);
            localStorage.setItem('kw_admin', 'ok');
            this.unlock();
        } else {
            alert("Acceso Denegado: Clave incorrecta");
        }
    },

    async testConnection() {
        try {
            const res = await AdminAPI.fetch('test_connection');
            let info = `${res.msg}\nPHP: ${res.php_version}\nFinfo: ${res.finfo_enabled ? "Sí" : "No"}\nWebP: ${res.webp_support ? "Sí" : "No"}\nDB: ${res.db_status}\nTablas: ${res.tables.join(', ')}\nUpload Writable: ${res.upload_dir_writable ? "Sí" : "No"}`;
            alert(info);
        } catch (e) {
            alert("Error de conexión: " + e.message);
        }
    },

    async unlock() {
        document.getElementById('login-portal').classList.add('hidden');
        document.getElementById('admin-content').classList.remove('hidden');
        setTimeout(() => document.getElementById('admin-content').style.opacity = '1', 100);
        await this.ensureTabsLoaded();
        this.loadAll();
    },

    logout() {
        localStorage.removeItem('kw_admin');
        localStorage.removeItem('kw_admin_key');
        window.location.reload();
    },

    switchTab(id) {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        const tabPanel = document.getElementById(`tab-${id}`);
        const tabButton = document.querySelector(`[data-tab="${id}"]`);

        if (!tabPanel || !tabButton) {
            console.error(`Tab no disponible: ${id}`);
            this.showToast(`No se pudo abrir la pestaña "${id}"`, 'error');
            return;
        }

        tabPanel.classList.remove('hidden');
        tabButton.classList.add('active');
        // refresh data when user returns to a section
        if (id === 'identity') {
            AdminTaxonomy.loadSettings();
        } else if (id === 'portfolio') {
            PortfolioManager.instance.load();
        } else if (id === 'services') {
            ServicesManager.instance.load();
        } else if (id === 'blog') {
            BlogManager.instance.load();
        } else if (id === 'system') {
            AdminSystem.loadInitialStatus();
        }
    },

    async ensureTabsLoaded() {
        if (this.tabsLoaded) return;

        const container = document.getElementById('tab-container');
        if (!container) {
            throw new Error('No existe #tab-container para montar las pestañas');
        }

        const tabFiles = [
            'tab-identity.html',
            'tab-visuals.html',
            'tab-portfolio.html',
            'tab-services.html',
            'tab-blog.html',
            'tab-proceso.html',
            'tab-settings.html',
            'tab-products.html',
            'tab-orders.html',
            'tab-coupons.html',
            'tab-system.html'
        ];

        const responses = await Promise.allSettled(tabFiles.map(async (fileName) => {
            const res = await fetch(`admin/${fileName}?v=${Date.now()}`);
            if (!res.ok) {
                throw new Error(`No se pudo cargar admin/${fileName} (HTTP ${res.status})`);
            }
            const html = await res.text();
            return { fileName, html };
        }));

        const loaded = [];
        const failed = [];

        responses.forEach((entry) => {
            if (entry.status === 'fulfilled') {
                loaded.push(entry.value);
            } else {
                failed.push(entry.reason?.message || 'Error desconocido al cargar tabs');
            }
        });

        container.innerHTML = loaded.map(item => item.html).join('\n');
        this.tabsLoaded = failed.length === 0;

        this.lastDiagnostics = {
            checkedAt: new Date().toISOString(),
            totalTabs: tabFiles.length,
            loadedTabs: loaded.length,
            failedTabs: failed,
            ok: failed.length === 0
        };

        this.renderVisualDiagnostics(this.lastDiagnostics);

        if (failed.length > 0) {
            throw new Error(`Fallo la carga de ${failed.length} pestaña(s). Revisa el diagnóstico visual.`);
        }

        if (typeof initImageUploads === 'function') {
            initImageUploads();
        }

        await this.initOptionalModules();
    },

    renderVisualDiagnostics(report) {
        const banner = document.getElementById('admin-diagnostics-banner');
        if (!banner || !report) return;

        const checkedAt = new Date(report.checkedAt).toLocaleString('es-ES');

        if (report.ok) {
            banner.className = 'mb-6 rounded-2xl border p-4 text-[10px] uppercase tracking-widest font-bold border-emerald-300 bg-emerald-50 text-emerald-700';
            banner.innerHTML = `
                <div>Autodiagnostico: OK</div>
                <div class="mt-1 text-[9px]">Tabs cargados: ${report.loadedTabs}/${report.totalTabs} | ${checkedAt}</div>
            `;
        } else {
            const failedHtml = report.failedTabs
                .map(msg => `<div class="text-[9px] normal-case">- ${msg}</div>`)
                .join('');

            banner.className = 'mb-6 rounded-2xl border p-4 text-[10px] uppercase tracking-widest font-bold border-red-300 bg-red-50 text-red-700';
            banner.innerHTML = `
                <div>Autodiagnostico: ERROR</div>
                <div class="mt-1 text-[9px]">Tabs cargados: ${report.loadedTabs}/${report.totalTabs} | ${checkedAt}</div>
                <div class="mt-2 space-y-1">${failedHtml}</div>
            `;
        }

        banner.classList.remove('hidden');
    },

    runVisualDiagnostics() {
        if (!this.lastDiagnostics) {
            this.showToast('Aun no hay diagnostico disponible', 'info');
            return;
        }

        this.renderVisualDiagnostics(this.lastDiagnostics);
        const msg = this.lastDiagnostics.ok
            ? 'Diagnostico actualizado: sin errores'
            : `Diagnostico actualizado: ${this.lastDiagnostics.failedTabs.length} error(es)`;
        this.showToast(msg, this.lastDiagnostics.ok ? 'success' : 'error');
    },

    async initOptionalModules() {
        const moduleInits = [
            'ProductAdmin',
            'OrderAdmin',
            'CouponAdmin'
        ];

        for (const moduleName of moduleInits) {
            const moduleRef = window[moduleName];
            if (!moduleRef) continue;

            // Soporta ambos patrones: instancia existente o clase global
            if (typeof moduleRef === 'function') {
                window[moduleName] = new moduleRef();
            }

            if (typeof window[moduleName]?.init === 'function') {
                try {
                    await window[moduleName].init();
                } catch (err) {
                    console.warn(`Error inicializando ${moduleName}:`, err);
                }
            }
        }
    },

    toggleLoading(show) {
        document.getElementById('loading-screen').style.display = show ? 'flex' : 'none';
    },

    /**
     * Muestra un toast en la esquina inferior.
     * @param {string} msg Texto a mostrar
     * @param {'success'|'error'|'info'} [type='info'] Tipo para colorear el toast
     */
    showToast(msg, type = 'info') {
        const t = document.getElementById('toast');
        // limpiar clases anteriores
        t.classList.remove('toast-success', 'toast-error', 'toast-info');
        t.classList.add(`toast-${type}`);

        // añadir icono según tipo
        let icon = '';
        if (type === 'success') icon = '✔️ ';
        else if (type === 'error') icon = '✖️ ';
        t.innerText = icon + msg;

        t.classList.add('active');
        setTimeout(() => t.classList.remove('active'), 3000);
    },

    /**
     * Muestra un mensaje breve cerca de un formulario específico
     * útil para feedback inmediato sin mirar la esquina superior.
     */
    showFormMessage(formId, msg, type = 'success') {
        const form = document.getElementById(formId);
        if (!form) return;
        let layer = form.querySelector('.form-toast');
        if (!layer) {
            layer = document.createElement('div');
            form.appendChild(layer);
        }
        // adapt styles by type
        layer.className = 'form-toast text-[10px] font-bold p-2 rounded mt-2';
        if (type === 'success') {
            layer.classList.add('text-emerald-700', 'bg-emerald-50', 'border', 'border-emerald-200');
        } else if (type === 'error') {
            layer.classList.add('text-red-700', 'bg-red-50', 'border', 'border-red-200');
        } else {
            layer.classList.add('text-stone-700', 'bg-stone-50', 'border', 'border-stone-200');
        }

        layer.textContent = msg;
        layer.classList.add('visible');
        setTimeout(() => {
            layer.classList.remove('visible');
        }, 2500);
    },


    async loadAll() {
        this.toggleLoading(true);
        try {
            // Test connection first
            const test = await AdminAPI.fetch('test_connection');
            console.log("Atelier Connection:", test);

            // Load sections independently
            const loaders = [
                { name: 'Listas', fn: () => AdminTaxonomy.loadLists() },
                { name: 'Ajustes', fn: () => AdminTaxonomy.loadSettings() },
                { name: 'Portafolio', fn: () => PortfolioManager.instance.load() },
                { name: 'Servicios', fn: () => ServicesManager.instance.load() },
                { name: 'Blog', fn: () => BlogManager.instance.load() },
                { name: 'Proceso', fn: () => ProcessManager.instance.load() }
            ];

            for (const loader of loaders) {
                try {
                    await loader.fn();
                } catch (err) {
                    console.warn(`Error loading ${loader.name}:`, err);
                    this.showToast(`Error al cargar ${loader.name}`, 'error');
                }
            }
        } catch (e) {
            console.error("Critical Load Error:", e);
            this.showToast("Error de conexión: " + (e.message || "Servidor no disponible"), 'error');
        }
        this.toggleLoading(false);
    }
};