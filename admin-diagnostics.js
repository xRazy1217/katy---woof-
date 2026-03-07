/**
 * Admin Diagnostics v1.0
 * 
 * Script para diagnosticar problemas de carga del admin panel
 * Verifica que todos los módulos, objetos y funciones estén disponibles
 * Ejecutar en la consola del navegador
 */

const AdminDiagnostics = {
    results: {
        modules: {},
        dom: {},
        auth: {},
        errors: []
    },

    /**
     * Ejecuta diagnóstico completo
     */
    async runFullDiagnostics() {
        console.clear();
        console.log('🔍 INICIANDO DIAGNÓSTICO DEL ADMIN PANEL...\n');
        
        this.checkModules();
        this.checkDOM();
        this.checkAuthentication();
        this.checkEventHandlers();
        
        this.printReport();
        return this.results;
    },

    /**
     * Verifica que todos los módulos estén cargados
     */
    checkModules() {
        console.log('📦 Verificando módulos...');
        
        const modules = [
            'AdminAPI',
            'AdminUI',
            'AdminContent',
            'AdminTaxonomy',
            'AdminSystem',
            'ImageUploadUtils',
            'initImageUploads'
        ];
        
        modules.forEach(module => {
            const exists = typeof window[module] !== 'undefined';
            this.results.modules[module] = {
                loaded: exists,
                type: exists ? typeof window[module] : 'undefined'
            };
            
            if (!exists) {
                this.results.errors.push(`❌ Módulo no cargado: ${module}`);
                console.log(`  ❌ ${module}`);
            } else {
                console.log(`  ✅ ${module}`);
            }
        });
    },

    /**
     * Verifica elementos del DOM críticos
     */
    checkDOM() {
        console.log('\n🖼️ Verificando elementos del DOM...');
        
        const elements = [
            'auth-container',
            'admin-panel',
            'tab-container',
            'portfolio-list',
            'services-list',
            'blog-list',
            'art-file',
            'service-file',
            'blog-file',
            'toast'
        ];
        
        elements.forEach(id => {
            const el = document.getElementById(id);
            const exists = el !== null;
            this.results.dom[id] = {
                exists: exists,
                visible: exists ? getComputedStyle(el).display !== 'none' : null
            };
            
            if (!exists) {
                this.results.errors.push(`❌ Elemento del DOM no encontrado: #${id}`);
                console.log(`  ❌ #${id}`);
            } else {
                console.log(`  ✅ #${id}`);
            }
        });
    },

    /**
     * Verifica autenticación y estado
     */
    checkAuthentication() {
        console.log('\n🔐 Verificando autenticación...');
        
        const authToken = localStorage.getItem('kw_admin');
        const authKeyField = document.getElementById('auth-key');
        
        this.results.auth = {
            token: authToken ? '✅ Existe' : '❌ No existe',
            tokenValue: authToken || 'N/A',
            isUnlocked: authToken === 'ok'
        };
        
        console.log(`  Token: ${this.results.auth.token}`);
        console.log(`  Desbloqueado: ${this.results.auth.isUnlocked ? '✅' : '❌'}`);
    },

    /**
     * Verifica event handlers
     */
    checkEventHandlers() {
        console.log('\n⚙️ Verificando event handlers...');
        
        // Intentar acceder a métodos críticos
        const criticalMethods = [
            { obj: 'AdminUI', method: 'switchTab' },
            { obj: 'AdminUI', method: 'attemptAuth' },
            { obj: 'AdminUI', method: 'logout' },
            { obj: 'AdminUI', method: 'unlock' },
            { obj: 'AdminContent', method: 'savePortfolio' },
            { obj: 'AdminContent', method: 'saveService' },
            { obj: 'AdminContent', method: 'saveBlog' },
            { obj: 'AdminTaxonomy', method: 'saveIdentitySettings' },
            { obj: 'AdminTaxonomy', method: 'saveVisualSettings' },
            { obj: 'AdminSystem', method: 'auditSchema' },
            { obj: 'AdminSystem', method: 'syncDatabase' }
        ];
        
        criticalMethods.forEach(({ obj, method }) => {
            const objExists = typeof window[obj] !== 'undefined';
            const methodExists = objExists && typeof window[obj][method] === 'function';
            
            if (!methodExists) {
                this.results.errors.push(`❌ Método no encontrado: ${obj}.${method}()`);
                console.log(`  ❌ ${obj}.${method}()`);
            } else {
                console.log(`  ✅ ${obj}.${method}()`);
            }
        });
    },

    /**
     * Imprime reporte de diagnóstico
     */
    printReport() {
        console.log('\n' + '='.repeat(60));
        console.log('📋 REPORTE DE DIAGNÓSTICO');
        console.log('='.repeat(60));
        
        if (this.results.errors.length === 0) {
            console.log('\n✅ TODO PARECE ESTAR BIEN! No se encontraron errores.');
        } else {
            console.log(`\n⚠️ Se encontraron ${this.results.errors.length} error(es):\n`);
            this.results.errors.forEach(error => {
                console.log(`  ${error}`);
            });
        }
        
        console.log('\n📊 RESUMEN:');
        console.log(`  Módulos cargados: ${Object.values(this.results.modules).filter(m => m.loaded).length}/${Object.keys(this.results.modules).length}`);
        console.log(`  Elementos DOM: ${Object.values(this.results.dom).filter(d => d.exists).length}/${Object.keys(this.results.dom).length}`);
        console.log(`  Errores: ${this.results.errors.length}`);
        console.log('\n' + '='.repeat(60));
        console.log('Para más detalles, revisar AdminDiagnostics.results\n');
    },

    /**
     * Simula un usuario haciendo clic en un botón para detectar errores
     */
    simulateClick(elementId) {
        const element = document.getElementById(elementId);
        if (!element) {
            console.error(`Elemento no encontrado: #${elementId}`);
            return;
        }
        
        try {
            console.log(`Simulando click en #${elementId}...`);
            element.click();
            console.log('✅ Click simulado sin errores');
        } catch (err) {
            console.error(`❌ Error al simular click:`, err);
        }
    },

    /**
     * Revisa el console log para errores
     */
    logNetworkRequests() {
        console.log('\n🌐 Interceptando siguientes peticiones de red...');
        console.log('Intenta hacer una acción en el admin (ej: guardar, cargar)');
        console.log('Los detalles aparecerán aquí.\n');
        
        // Overrides fetch para mostrar todas las peticiones
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            console.log('📡 Request:', args[0], args[1] || '');
            return originalFetch.apply(this, args).then(response => {
                console.log('📡 Response:', response.status, response.statusText);
                return response;
            }).catch(err => {
                console.error('📡 Error:', err);
                throw err;
            });
        };
    }
};

// Alias más corto
const diagnose = () => AdminDiagnostics.runFullDiagnostics();
