/**
 * Validación del Sistema E-commerce - Katy & Woof
 * Fase 1: Verificación de archivos y estructura
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

class EcommerceValidator {
    constructor() {
        this.results = [];
        this.projectRoot = __dirname;
    }

    /**
     * Ejecutar todas las validaciones
     */
    async runAllValidations() {
        this.results = [];

        this.validateFileStructure();
        this.validateRequiredFiles();
        this.validateDatabaseSchema();
        this.validateConfigurationFiles();
        this.validateApiStructure();

        return this.results;
    }

    /**
     * Validar estructura de archivos
     */
    validateFileStructure() {
        const testName = 'Estructura de Archivos';

        const requiredStructure = [
            'INIT_ECOMMERCE_SCHEMA.sql',
            'ecommerce-config.php',
            'api-products.php',
            'api-cart.php',
            'api-checkout.php',
            'flow-payment.php',
            'ecommerce-api.php',
            'ecommerce-webhook.php',
            'validate-ecommerce.php',
            'logs/'
        ];

        const missingFiles = [];
        const existingFiles = [];

        requiredStructure.forEach(item => {
            const fullPath = path.join(this.projectRoot, item);
            if (fs.existsSync(fullPath)) {
                existingFiles.push(item);
            } else {
                missingFiles.push(item);
            }
        });

        if (missingFiles.length === 0) {
            this.addResult(testName, true, 'Todos los archivos requeridos existen', {
                files_found: existingFiles.length,
                files_required: requiredStructure.length
            });
        } else {
            this.addResult(testName, false, `Archivos faltantes: ${missingFiles.join(', ')}`, {
                missing: missingFiles,
                found: existingFiles
            });
        }
    }

    /**
     * Validar archivos requeridos
     */
    validateRequiredFiles() {
        const testName = 'Archivos PHP Requeridos';

        const phpFiles = [
            'ecommerce-config.php',
            'api-products.php',
            'api-cart.php',
            'api-checkout.php',
            'flow-payment.php',
            'ecommerce-api.php',
            'ecommerce-webhook.php'
        ];

        const invalidFiles = [];

        phpFiles.forEach(file => {
            const filePath = path.join(this.projectRoot, file);

            if (!fs.existsSync(filePath)) {
                invalidFiles.push(`${file} (no existe)`);
                return;
            }

            try {
                const content = fs.readFileSync(filePath, 'utf8');

                // Verificaciones básicas de contenido
                if (!content.includes('<?php')) {
                    invalidFiles.push(`${file} (no es archivo PHP válido)`);
                }

                // Verificar que tenga estructura básica
                if (file === 'ecommerce-config.php' && !content.includes('class EcommerceDatabase')) {
                    invalidFiles.push(`${file} (falta clase EcommerceDatabase)`);
                }

                if (file.includes('api-') && !content.includes('require_once \'ecommerce-config.php\'')) {
                    invalidFiles.push(`${file} (no incluye configuración)`);
                }

            } catch (error) {
                invalidFiles.push(`${file} (error de lectura: ${error.message})`);
            }
        });

        if (invalidFiles.length === 0) {
            this.addResult(testName, true, 'Todos los archivos PHP son válidos');
        } else {
            this.addResult(testName, false, `Problemas encontrados: ${invalidFiles.join(', ')}`);
        }
    }

    /**
     * Validar esquema de base de datos
     */
    validateDatabaseSchema() {
        const testName = 'Esquema de Base de Datos';

        const schemaFile = path.join(this.projectRoot, 'INIT_ECOMMERCE_SCHEMA.sql');

        if (!fs.existsSync(schemaFile)) {
            this.addResult(testName, false, 'Archivo de esquema no encontrado');
            return;
        }

        try {
            const content = fs.readFileSync(schemaFile, 'utf8');

            const requiredTables = [
                'products', 'product_categories', 'product_variations',
                'cart_items', 'user_addresses', 'orders', 'order_items',
                'coupons', 'ecommerce_settings', 'shipping_zones'
            ];

            const foundTables = [];
            const missingTables = [];

            requiredTables.forEach(table => {
                if (content.includes(`CREATE TABLE ${table}`)) {
                    foundTables.push(table);
                } else {
                    missingTables.push(table);
                }
            });

            if (missingTables.length === 0) {
                this.addResult(testName, true, 'Esquema completo con todas las tablas requeridas', {
                    tables_found: foundTables.length,
                    tables_required: requiredTables.length
                });
            } else {
                this.addResult(testName, false, `Tablas faltantes en esquema: ${missingTables.join(', ')}`, {
                    missing: missingTables,
                    found: foundTables
                });
            }

        } catch (error) {
            this.addResult(testName, false, `Error leyendo esquema: ${error.message}`);
        }
    }

    /**
     * Validar archivos de configuración
     */
    validateConfigurationFiles() {
        const testName = 'Archivos de Configuración';

        const configChecks = [
            {
                file: 'ecommerce-config.php',
                checks: [
                    'class EcommerceDatabase',
                    'EcommerceDatabase::getInstance()',
                    'jsonResponse(',
                    'validateCSRFToken('
                ]
            },
            {
                file: 'flow-payment.php',
                checks: [
                    'class FlowPayment',
                    'createPayment(',
                    'processWebhook(',
                    'FLOW_API_KEY_AQUI'
                ]
            }
        ];

        const failedChecks = [];

        configChecks.forEach(({ file, checks }) => {
            const filePath = path.join(this.projectRoot, file);

            if (!fs.existsSync(filePath)) {
                failedChecks.push(`${file} no existe`);
                return;
            }

            try {
                const content = fs.readFileSync(filePath, 'utf8');

                checks.forEach(check => {
                    if (!content.includes(check)) {
                        failedChecks.push(`${file} falta: ${check}`);
                    }
                });

            } catch (error) {
                failedChecks.push(`${file} error: ${error.message}`);
            }
        });

        if (failedChecks.length === 0) {
            this.addResult(testName, true, 'Archivos de configuración completos');
        } else {
            this.addResult(testName, false, `Problemas de configuración: ${failedChecks.join(', ')}`);
        }
    }

    /**
     * Validar estructura de API
     */
    validateApiStructure() {
        const testName = 'Estructura de API';

        const apiFiles = [
            'api-products.php',
            'api-cart.php',
            'api-checkout.php'
        ];

        const requiredClasses = [
            'ProductAPI',
            'CartAPI',
            'CheckoutAPI'
        ];

        const failedChecks = [];

        apiFiles.forEach((file, index) => {
            const filePath = path.join(this.projectRoot, file);
            const expectedClass = requiredClasses[index];

            if (!fs.existsSync(filePath)) {
                failedChecks.push(`${file} no existe`);
                return;
            }

            try {
                const content = fs.readFileSync(filePath, 'utf8');

                if (!content.includes(`class ${expectedClass}`)) {
                    failedChecks.push(`${file} falta clase ${expectedClass}`);
                }

                // Verificar métodos básicos
                const basicMethods = ['__construct'];
                basicMethods.forEach(method => {
                    if (!content.includes(`function ${method}`)) {
                        failedChecks.push(`${file} falta método ${method}`);
                    }
                });

            } catch (error) {
                failedChecks.push(`${file} error: ${error.message}`);
            }
        });

        // Verificar archivo principal de API
        const mainApiFile = path.join(this.projectRoot, 'ecommerce-api.php');
        if (!fs.existsSync(mainApiFile)) {
            failedChecks.push('ecommerce-api.php no existe');
        } else {
            try {
                const content = fs.readFileSync(mainApiFile, 'utf8');
                if (!content.includes('handleProductsEndpoint') ||
                    !content.includes('handleCartEndpoint') ||
                    !content.includes('handleCheckoutEndpoint')) {
                    failedChecks.push('ecommerce-api.php falta estructura de routing');
                }
            } catch (error) {
                failedChecks.push(`ecommerce-api.php error: ${error.message}`);
            }
        }

        if (failedChecks.length === 0) {
            this.addResult(testName, true, 'Estructura de API completa');
        } else {
            this.addResult(testName, false, `Problemas en API: ${failedChecks.join(', ')}`);
        }
    }

    /**
     * Agregar resultado de validación
     */
    addResult(testName, passed, message, details = null) {
        this.results.push({
            test: testName,
            passed: passed,
            message: message,
            details: details,
            timestamp: new Date().toISOString()
        });
    }

    /**
     * Obtener resumen de resultados
     */
    getSummary() {
        const total = this.results.length;
        const passed = this.results.filter(r => r.passed).length;

        return {
            total_tests: total,
            passed_tests: passed,
            failed_tests: total - passed,
            success_rate: total > 0 ? Math.round((passed / total) * 100) : 0,
            all_passed: passed === total
        };
    }

    /**
     * Obtener todos los resultados
     */
    getResults() {
        return this.results;
    }

    /**
     * Imprimir resultados en formato legible
     */
    printResults() {
        const summary = this.getSummary();

        console.log('\n=== VALIDACIÓN DEL SISTEMA E-COMMERCE ===\n');

        console.log('Resumen:');
        console.log(`- Tests totales: ${summary.total_tests}`);
        console.log(`- Tests exitosos: ${summary.passed_tests}`);
        console.log(`- Tests fallidos: ${summary.failed_tests}`);
        console.log(`- Tasa de éxito: ${summary.success_rate}%`);
        console.log(`- Estado general: ${summary.all_passed ? '✅ TODOS LOS TESTS PASARON' : '❌ ALGUNOS TESTS FALLARON'}\n`);

        console.log('Detalle de tests:');
        this.results.forEach(result => {
            const status = result.passed ? '✅' : '❌';
            console.log(`${status} ${result.test}: ${result.message}`);

            if (!result.passed && result.details) {
                console.log(`   Detalles: ${JSON.stringify(result.details, null, 2)}`);
            }
        });

        console.log('\n=== FIN DEL REPORTE ===\n');
    }
}

// Ejecutar validación
async function main() {
    const validator = new EcommerceValidator();
    await validator.runAllValidations();
    validator.printResults();

    const summary = validator.getSummary();
    process.exit(summary.all_passed ? 0 : 1);
}

if (import.meta.url === `file://${process.argv[1]}`) {
    main().catch(error => {
        console.error('Error ejecutando validación:', error);
        process.exit(1);
    });
}

export default EcommerceValidator;