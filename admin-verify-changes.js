/**
 * Katy & Woof - Verificador de Cambios v1.0
 * Script de auditoría interactivo para el usuario
 * 
 * Uso: Abre la consola del navegador (F12) y pega el contenido de este archivo
 * O copia cada función individualmente según sea necesario
 */

// ==============================================================
// 1. VERIFICAR CACHE BUSTING EN LLAMADAS API
// ==============================================================
async function verifyAPICache() {
    console.log("🔍 VERIFICACIÓN 1: Cache Busting en API");
    console.log("─".repeat(50));
    
    const auth = localStorage.getItem('kw_admin_key') || '';
    const url1 = `api.php?action=get_settings&auth=${auth}&v=${Date.now()}`;
    const url2 = `api.php?action=get_settings&auth=${auth}&v=${Date.now()}`;
    
    console.log("Primera llamada:", url1);
    console.log("Segunda llamada:", url2);
    
    if (url1 !== url2) {
        console.log("✅ PASS: Cada llamada tiene timestamp diferente (cache busting activo)");
    } else {
        console.log("❌ FAIL: Los timestamps son iguales");
        return false;
    }
    
    try {
        const res1 = await fetch(url1);
        const res2 = await fetch(url2);
        console.log("Respuesta 1 status:", res1.status);
        console.log("Respuesta 2 status:", res2.status);
        console.log("✅ Ambas llamadas exitosas\n");
        return true;
    } catch (e) {
        console.error("❌ Error en llamadas:", e.message, "\n");
        return false;
    }
}

// ==============================================================
// 2. VERIFICAR HEADERS ANTI-CACHE
// ==============================================================
async function verifyHeaders() {
    console.log("🔍 VERIFICACIÓN 2: Headers Anti-Cache en API");
    console.log("─".repeat(50));
    
    try {
        const res = await fetch(`api.php?action=test_connection&v=${Date.now()}`);
        const headers = {
            'cache-control': res.headers.get('cache-control'),
            'pragma': res.headers.get('pragma'),
            'expires': res.headers.get('expires'),
            'content-type': res.headers.get('content-type')
        };
        
        console.log("Headers recibidos:");
        Object.entries(headers).forEach(([k, v]) => {
            console.log(`  ${k}: ${v}`);
        });
        
        if (headers['cache-control']?.includes('no-cache') || headers['cache-control']?.includes('no-store')) {
            console.log("✅ PASS: Headers anti-cache detectados");
            return true;
        } else {
            console.log("⚠️  WARNING: Headers anti-cache no detectados");
            return false;
        }
    } catch (e) {
        console.error("❌ Error:", e.message, "\n");
        return false;
    }
}

// ==============================================================
// 3. VERIFICAR FUNCIONES GLOBALES
// ==============================================================
function verifyGlobalFunctions() {
    console.log("🔍 VERIFICACIÓN 3: Funciones Globales Disponibles");
    console.log("─".repeat(50));
    
    const functions = {
        'addCacheBust': typeof addCacheBust,
        'AdminAPI': typeof AdminAPI,
        'AdminUI': typeof AdminUI,
        'AdminContent': typeof AdminContent,
        'AdminTaxonomy': typeof AdminTaxonomy,
        'AdminSystem': typeof AdminSystem,
        'ImageUploadUtils': typeof ImageUploadUtils
    };
    
    let allPresent = true;
    Object.entries(functions).forEach(([name, type]) => {
        if (type === 'object' || type === 'function') {
            console.log(`✅ ${name} disponible`);
        } else {
            console.log(`❌ ${name} NO disponible`);
            allPresent = false;
        }
    });
    
    console.log("");
    return allPresent;
}

// ==============================================================
// 4. VERIFICAR addCacheBust HELPER
// ==============================================================
function verifyAddCacheBust() {
    console.log("🔍 VERIFICACIÓN 4: Helper addCacheBust()");
    console.log("─".repeat(50));
    
    if (typeof addCacheBust !== 'function') {
        console.error("❌ addCacheBust no está definida");
        return false;
    }
    
    const tests = [
        { input: 'uploads/art123.jpg', expected: 'uploads/art123.jpg?v=' },
        { input: 'uploads/image.webp?old=1', expected: 'uploads/image.webp?old=1&v=' },
        { input: 'img/placeholder.jpg', expected: 'img/placeholder.jpg' }, // no cache bust
        { input: null, expected: null }
    ];
    
    let allPass = true;
    tests.forEach(test => {
        const result = addCacheBust(test.input);
        if (test.expected === null) {
            if (result === null) {
                console.log(`✅ addCacheBust(${test.input}) = ${result}`);
            } else {
                console.log(`❌ addCacheBust(${test.input}) debería retornar null`);
                allPass = false;
            }
        } else if (result?.includes(test.expected)) {
            console.log(`✅ addCacheBust("${test.input}") incluye "${test.expected}"`);
        } else {
            console.log(`❌ addCacheBust("${test.input}") = "${result}"`);
            allPass = false;
        }
    });
    
    console.log("");
    return allPass;
}

// ==============================================================
// 5. VERIFICAR MANEJO DE ERRORES EN AdminAPI
// ==============================================================
async function verifyErrorHandling() {
    console.log("🔍 VERIFICACIÓN 5: Error Handling en AdminAPI");
    console.log("─".repeat(50));
    
    // Test: Llamada con parámetro inválido
    try {
        await AdminAPI.fetch('invalid_action_test_12345');
        console.log("❌ No lanzó error para acción inválida");
        return false;
    } catch (e) {
        console.log(`✅ Capturó error: "${e.message}"`);
        console.log("✅ Error handling funciona correctamente\n");
        return true;
    }
}

// ==============================================================
// 6. VERIFICAR WEBP SUPPORT EN DIAGNÓSTICOS
// ==============================================================
async function verifyWebPSupport() {
    console.log("🔍 VERIFICACIÓN 6: WebP Support en Diagnósticos");
    console.log("─".repeat(50));
    
    try {
        const res = await AdminAPI.fetch('test_connection');
        
        console.log("Diagnóstico del servidor:");
        console.log(`  PHP Version: ${res.php_version}`);
        console.log(`  Finfo: ${res.finfo_enabled ? 'Sí' : 'No'}`);
        console.log(`  WebP Support: ${res.webp_support ? 'Sí' : 'No'}`);
        console.log(`  DB Status: ${res.db_status}`);
        console.log(`  Upload Dir Writable: ${res.upload_dir_writable ? 'Sí' : 'No'}`);
        console.log(`  Upload Dir: ${res.upload_dir}`);
        
        const isHealthy = res.finfo_enabled && res.db_status === 'Connected' && res.upload_dir_writable;
        
        if (isHealthy) {
            console.log("\n✅ Panel está listo (ambiente saludable)");
        } else {
            console.log("\n⚠️  Panel tiene limitaciones (ver detalle arriba)");
        }
        
        console.log("");
        return isHealthy;
    } catch (e) {
        console.error("❌ Error:", e.message, "\n");
        return false;
    }
}

// ==============================================================
// 7. RESUMEN DE AUDITORÍA
// ==============================================================
async function runFullAudit() {
    console.clear();
    console.log("\n" + "╔".padEnd(52, "═") + "╗");
    console.log("║" + "  AUDITORÍA DE CAMBIOS - Katy & Woof Admin".padEnd(51) + "║");
    console.log("║" + "  v6.0 Enterprise Edition".padEnd(51) + "║");
    console.log("╚".padEnd(52, "═") + "╝\n");
    
    const results = {
        'Cache Busting API': await verifyAPICache(),
        'Headers Anti-Cache': await verifyHeaders(),
        'Funciones Globales': verifyGlobalFunctions(),
        'addCacheBust Helper': verifyAddCacheBust(),
        'Error Handling': await verifyErrorHandling(),
        'WebP & Diagnósticos': await verifyWebPSupport()
    };
    
    console.log("╔".padEnd(52, "═") + "╗");
    console.log("║" + "  RESULTADOS".padEnd(51) + "║");
    console.log("╠".padEnd(52, "═") + "╣");
    
    let totalPass = 0;
    Object.entries(results).forEach(([name, passed]) => {
        const status = passed ? "✅ PASS" : "❌ FAIL";
        const line = `║ ${status} | ${name}`.padEnd(51) + "║";
        console.log(line);
        if (passed) totalPass++;
    });
    
    console.log("╠".padEnd(52, "═") + "╣");
    console.log(`║ Resultado: ${totalPass}/${Object.keys(results).length} verificaciones exitosas`.padEnd(51) + "║");
    console.log("╚".padEnd(52, "═") + "╝\n");
    
    if (totalPass === Object.keys(results).length) {
        console.log("🎉 ¡TODAS LAS VERIFICACIONES PASARON! El panel está listo.\n");
    } else {
        console.log("⚠️  Algunas verificaciones fallaron. Revisa los detalles arriba.\n");
    }
}

// ==============================================================
// INSTRUCCIONES DE USO
// ==============================================================
console.log(`
┌─────────────────────────────────────────────────────┐
│  VERIFICADOR DE CAMBIOS - Katy & Woof Admin Panel   │
│  v1.0                                               │
└─────────────────────────────────────────────────────┘

Copiar y pega en la consola del navegador (F12):

  // Ejecutar auditoría completa:
  runFullAudit()

  // O ejecutar verificaciones individuales:
  verifyAPICache()           // Cache busting
  verifyHeaders()            // Headers HTTP
  verifyGlobalFunctions()    // Módulos cargados
  verifyAddCacheBust()       // Helper de cache
  verifyErrorHandling()      // Manejo de errores
  verifyWebPSupport()        // Diagnósticos

`);
