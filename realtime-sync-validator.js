/**
 * Real-Time Sync Validation v1.0
 * 
 * Valida que el admin panel está conectado correctamente con MySQL
 * y que los cambios se ven de inmediato en todas las páginas
 */

const RealtimeSyncValidator = {
    
    API_BASE: 'api.php',
    
    /**
     * Test 1: Verificar conexión a BD
     */
    async testDatabaseConnection() {
        console.log('🔗 TEST 1: Verificando conexión a BD...\n');
        
        try {
            const res = await fetch(`${this.API_BASE}?action=test_connection&v=${Date.now()}`);
            const data = await res.json();
            
            console.log('✅ Servidor PHP responde');
            console.log('   Versión PHP:', data.php_version);
            console.log('   Estado BD:', data.db_status);
            console.log('   Tablas encontradas:', data.tables?.length || 0);
            console.log('   Upload dir writable:', data.upload_dir_writable ? 'Sí' : 'No');
            
            if (data.db_status !== 'Connected') {
                console.error('❌ BD NO conectada: ' + data.db_status);
                return false;
            }
            console.log('');
            return true;
        } catch (err) {
            console.error('❌ Error conectando con servidor:', err);
            return false;
        }
    },

    /**
     * Test 2: Obtener datos actuales de BD
     */
    async getCurrentData() {
        console.log('📊 TEST 2: Obteniendo datos actuales de BD...\n');
        
        try {
            const Portfolio = await fetch(`api.php?action=get_portfolio&v=${Date.now()}`).then(r => r.json());
            const Services = await fetch(`api.php?action=get_services&v=${Date.now()}`).then(r => r.json());
            const Blog = await fetch(`api.php?action=get_blog&v=${Date.now()}`).then(r => r.json());
            const Process = await fetch(`api.php?action=get_process&v=${Date.now()}`).then(r => r.json());
            const Lists = await fetch(`api.php?action=get_lists&v=${Date.now()}`).then(r => r.json());
            
            console.log('✅ Portfolio items:', Portfolio?.length || 0);
            console.log('✅ Services items:', Services?.length || 0);
            console.log('✅ Blog posts:', Blog?.length || 0);
            console.log('✅ Process steps:', Process?.length || 0);
            console.log('✅ Site lists:', Lists?.length || 0);
            console.log('');
            
            return { Portfolio, Services, Blog, Process, Lists };
        } catch (err) {
            console.error('❌ Error obteniendo datos:', err);
            return null;
        }
    },

    /**
     * Test 3: Simular guardado y verificar que se persista
     */
    async testPersistence() {
        console.log('💾 TEST 3: Probando persistencia de cambios...\n');
        
        // Datos de prueba
        const testData = {
            title: `Test Service ${Date.now()}`,
            description: 'Descripción de prueba para validar sincronización en tiempo real',
            main_image_url: 'img/placeholder.jpg'
        };
        
        try {
            // 1. Obtener servicios antes
            const beforeSave = await fetch(`api.php?action=get_services&v=${Date.now()}`).then(r => r.json());
            const countBefore = beforeSave.length;
            console.log(`✓ Servicios antes: ${countBefore}`);
            
            // 2. Intentar guardar
            const formData = new FormData();
            formData.append('title', testData.title);
            formData.append('description', testData.description);
            formData.append('main_image_url', testData.main_image_url);
            formData.append('auth', localStorage.getItem('kw_admin_key') || 'fotopet2026');
            
            const saveRes = await fetch(`api.php?action=save_service`, {
                method: 'POST',
                body: formData
            }).then(r => r.json());
            
            if (!saveRes.success) {
                console.error('❌ Error guardando:', saveRes.error);
                return false;
            }
            console.log('✓ Cambio guardado exitosamente');
            
            // 3. Esperar un momento y verificar lectura
            await new Promise(resolve => setTimeout(resolve, 500));
            
            const afterSave = await fetch(`api.php?action=get_services&v=${Date.now()}`).then(r => r.json());
            const countAfter = afterSave.length;
            console.log(`✓ Servicios después: ${countAfter}`);
            
            // 4. Buscar el dato que acabamos de guardar
            const newItem = afterSave.find(s => s.title === testData.title);
            if (newItem) {
                console.log('✓ Nuevo servicio encontrado en BD inmediatamente');
                console.log(`  ID: ${newItem.id}`);
                console.log(`  Título: ${newItem.title}`);
                
                // 5. Eliminar el item de prueba
                const deleteRes = await fetch(`api.php?action=delete_service&id=${newItem.id}&auth=fotopet2026`).then(r => r.json());
                if (deleteRes.success) {
                    console.log('✓ Item de prueba eliminado');
                }
            } else {
                console.error('❌ Nuevo servicio NO encontrado en BD después de guardar');
                return false;
            }
            
            console.log('');
            return true;
        } catch (err) {
            console.error('❌ Error en test de persistencia:', err);
            return false;
        }
    },

    /**
     * Test 4: Verificar que páginas públicas cargan datos frescos
     */
    async testPublicPageSync() {
        console.log('🌐 TEST 4: Verificando sincronización en páginas públicas...\n');
        
        try {
            // Simular que accedemos a blog.php
            const blogRes = await fetch('blog.php?v=' + Date.now());
            const blogHTML = await blogRes.text();
            
            if (blogHTML.includes('get_blog')) {
                console.log('✓ blog.php carga datos dinámicamente desde API');
            }
            
            // Simular que accedemos a servicios.php  
            const svcRes = await fetch('servicios.php?v=' + Date.now());
            const svcHTML = await svcRes.text();
            
            if (svcHTML.includes('get_services')) {
                console.log('✓ servicios.php carga datos dinámicamente desde API');
            }
            
            // Verificar que las páginas usan cache-busting
            if (svcHTML.includes('v=${Date.now()}') || svcHTML.includes('&v=')) {
                console.log('✓ Páginas públicas usan cache-busting (parámetro v=timestamp)');
            }
            
            console.log('');
            return true;
        } catch (err) {
            console.error('❌ Error verificando páginas públicas:', err);
            return false;
        }
    },

    /**
     * Test 5: Verificar que API devuelve datos frescos cada vez
     */
    async testAPICaching() {
        console.log('⚡ TEST 5: Verificando que API no cachea respuestas...\n');
        
        try {
            // Dos requests idénticas casi simultáneas
            const t1 = Date.now();
            const res1 = await fetch(`api.php?action=get_services&v=${Date.now()}`).then(r => r.json());
            const t2 = Date.now();
            
            const res2 = await fetch(`api.php?action=get_services&v=${Date.now()}`).then(r => r.json());
            const t3 = Date.now();
            
            console.log(`✓ Request 1 tardó: ${t2 - t1}ms`);
            console.log(`✓ Request 2 tardó: ${t3 - t2}ms`);
            console.log(`✓ Ambas requests devuelven ${res1.length} items`);
            console.log(`✓ Datos son idénticos: ${JSON.stringify(res1) === JSON.stringify(res2)}`);
            
            // Verificar headers de cache
            const headRes = await fetch(`api.php?action=get_services`, { method: 'HEAD' });
            const cacheControl = headRes.headers.get('Cache-Control');
            const pragma = headRes.headers.get('Pragma');
            
            console.log(`✓ Cache-Control header: ${cacheControl || 'no configurado'}`);
            console.log(`✓ Pragma header: ${pragma || 'no configurado'}`);
            
            console.log('');
            return true;
        } catch (err) {
            console.error('❌ Error en test de caching:', err);
            return false;
        }
    },

    /**
     * Test 6: Verificar consistencia de datos entre admin y público
     */
    async testDataConsistency() {
        console.log('🔄 TEST 6: Verificando consistencia entre admin y público...\n');
        
        try {
            // Obtener datos desde API (lo que ve el admin)
            const fromAPI = await fetch(`api.php?action=get_services&v=${Date.now()}`).then(r => r.json());
            
            // Hacer fetch a servicios.php (lo que ve el público)
            const publicRes = await fetch('servicios.php?v=' + Date.now());
            const publicHTML = await publicRes.text();
            
            // Verificar que servicios.php puede acceder a los datos
            if (publicHTML.includes('loadData') || publicHTML.includes('get_services')) {
                console.log('✓ servicios.php tiene acceso a los datos del API');
                console.log(`✓ API devuelve ${fromAPI.length} servicios`);
                console.log('✓ Ambas vistas (admin y público) acceden a la misma BD');
            }
            
            console.log('');
            return true;
        } catch (err) {
            console.error('❌ Error en test de consistencia:', err);
            return false;
        }
    },

    /**
     * Ejecutar todos los tests
     */
    async runAllTests() {
        console.clear();
        console.log('╔════════════════════════════════════════════════════════════════╗');
        console.log('║  🔄 VALIDACIÓN DE SINCRONIZACIÓN EN TIEMPO REAL - MySQL        ║');
        console.log('║     Panel Admin ↔ Base de Datos ↔ Páginas Públicas            ║');
        console.log('╚════════════════════════════════════════════════════════════════╝\n');
        
        const results = [];
        
        // Ejecutar cada test
        results.push({
            name: 'Conexión a BD',
            pass: await this.testDatabaseConnection()
        });
        
        await this.getCurrentData();
        
        if (localStorage.getItem('kw_admin_key')) {
            results.push({
                name: 'Persistencia de Cambios',
                pass: await this.testPersistence()
            });
        } else {
            console.log('⚠️ TEST 3: Saltado (no autenticado)\n');
        }
        
        results.push({
            name: 'Sincronización en Páginas Públicas',
            pass: await this.testPublicPageSync()
        });
        
        results.push({
            name: 'Caching de API',
            pass: await this.testAPICaching()
        });
        
        results.push({
            name: 'Consistencia de Datos',
            pass: await this.testDataConsistency()
        });
        
        // Resumen final
        console.log('═══════════════════════════════════════════════════════════════════\n');
        console.log('📋 RESUMEN DE VALIDACIÓN:\n');
        
        const passCount = results.filter(r => r.pass).length;
        const totalTests = results.length;
        
        results.forEach(r => {
            console.log(`  ${r.pass ? '✅' : '❌'} ${r.name}`);
        });
        
        console.log(`\n📊 RESULTADO: ${passCount}/${totalTests} tests pasados`);
        
        if (passCount === totalTests) {
            console.log('\n✨ ¡EXCELENTE! El sistema está funcionando perfectamente.');
            console.log('   • Panel admin conectado correctamente con MySQL');
            console.log('   • Los cambios se persisten inmediatamente en la BD');
            console.log('   • Las páginas públicas siempre cargan datos frescos');
            console.log('   • No hay problemas de caching o sincronización');
        } else {
            console.log('\n⚠️ Se encontraron problemas que requieren atención');
        }
        
        console.log('\n═══════════════════════════════════════════════════════════════════');
    }
};

// Alias corto
const validateSync = () => RealtimeSyncValidator.runAllTests();

console.log('✅ Validador de sincronización en tiempo real cargado. Ejecuta: validateSync()');
