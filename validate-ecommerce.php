<?php
/**
 * Validación del Sistema E-commerce - Katy & Woof
 * Fase 1: Verificación completa de componentes
 */

require_once 'ecommerce-config.php';
require_once 'api-products.php';
require_once 'api-cart.php';
require_once 'api-checkout.php';
require_once 'flow-payment.php';

class EcommerceValidator {
    private $results = [];
    private $db;

    public function __construct() {
        $this->db = getEcommerceDB();
    }

    /**
     * Ejecutar todas las validaciones
     */
    public function runAllValidations() {
        $this->results = [];

        $this->validateDatabaseConnection();
        $this->validateDatabaseSchema();
        $this->validateApiEndpoints();
        $this->validateFlowConfiguration();
        $this->validateFilePermissions();
        $this->validateInitialData();

        return $this->results;
    }

    /**
     * Validar conexión a base de datos
     */
    private function validateDatabaseConnection() {
        $testName = 'Conexión a Base de Datos';

        try {
            $info = $this->db->getDatabaseInfo();
            $ping = $this->db->ping();

            if ($ping) {
                $this->addResult($testName, true, 'Conexión exitosa', $info);
            } else {
                $this->addResult($testName, false, 'No se pudo conectar a la base de datos');
            }
        } catch (Exception $e) {
            $this->addResult($testName, false, 'Error de conexión: ' . $e->getMessage());
        }
    }

    /**
     * Validar esquema de base de datos
     */
    private function validateDatabaseSchema() {
        $testName = 'Esquema de Base de Datos';

        $requiredTables = [
            'products', 'product_categories', 'product_variations',
            'cart_items', 'user_addresses', 'orders', 'order_items',
            'order_status_history', 'coupons', 'order_coupons',
            'ecommerce_settings', 'shipping_zones', 'shipping_methods',
            'product_views', 'search_queries'
        ];

        $missingTables = [];
        $errors = [];

        foreach ($requiredTables as $table) {
            try {
                $result = $this->db->query("SHOW TABLES LIKE ?", [$table])->fetch();
                if (!$result) {
                    $missingTables[] = $table;
                }
            } catch (Exception $e) {
                $errors[] = "Error verificando tabla {$table}: " . $e->getMessage();
            }
        }

        if (empty($missingTables) && empty($errors)) {
            $this->addResult($testName, true, 'Todas las tablas existen', [
                'tables_found' => count($requiredTables),
                'tables_required' => count($requiredTables)
            ]);
        } else {
            $message = '';
            if (!empty($missingTables)) {
                $message .= 'Tablas faltantes: ' . implode(', ', $missingTables) . '. ';
            }
            if (!empty($errors)) {
                $message .= 'Errores: ' . implode('; ', $errors);
            }
            $this->addResult($testName, false, $message);
        }
    }

    /**
     * Validar endpoints de API
     */
    private function validateApiEndpoints() {
        $testName = 'Endpoints de API';

        $endpoints = [
            'products' => 'GET',
            'categories' => 'GET',
            'cart' => 'GET',
            'settings' => 'GET'
        ];

        $workingEndpoints = 0;
        $failedEndpoints = [];

        foreach ($endpoints as $endpoint => $method) {
            try {
                // Simular llamada a API (en un entorno real usarías curl o similar)
                $apiClass = null;
                switch ($endpoint) {
                    case 'products':
                        $apiClass = getProductAPI();
                        $result = $apiClass->getProducts();
                        break;
                    case 'categories':
                        $apiClass = getProductAPI();
                        $result = $apiClass->getCategories();
                        break;
                    case 'cart':
                        $apiClass = getCartAPI();
                        $result = $apiClass->getCart();
                        break;
                    case 'settings':
                        // Para settings, verificar configuración básica
                        $settings = $this->db->select("SELECT COUNT(*) as count FROM ecommerce_settings");
                        $result = ['success' => true, 'data' => ['count' => $settings[0]['count']]];
                        break;
                }

                if ($result && isset($result['success']) && $result['success']) {
                    $workingEndpoints++;
                } else {
                    $failedEndpoints[] = $endpoint . ' (respuesta inválida)';
                }

            } catch (Exception $e) {
                $failedEndpoints[] = $endpoint . ' (' . $e->getMessage() . ')';
            }
        }

        if ($workingEndpoints === count($endpoints)) {
            $this->addResult($testName, true, 'Todos los endpoints funcionan correctamente', [
                'endpoints_tested' => $workingEndpoints,
                'endpoints_total' => count($endpoints)
            ]);
        } else {
            $this->addResult($testName, false, 'Endpoints con problemas: ' . implode(', ', $failedEndpoints), [
                'working' => $workingEndpoints,
                'total' => count($endpoints)
            ]);
        }
    }

    /**
     * Validar configuración de Flow
     */
    private function validateFlowConfiguration() {
        $testName = 'Configuración Flow Payment';

        try {
            $flow = getFlowPayment();
            $configCheck = $flow->verifyConfiguration();

            if ($configCheck['valid']) {
                $this->addResult($testName, true, 'Configuración de Flow correcta', $configCheck['config']);
            } else {
                $this->addResult($testName, false, 'Problemas de configuración: ' . implode(', ', $configCheck['issues']), $configCheck);
            }
        } catch (Exception $e) {
            $this->addResult($testName, false, 'Error verificando Flow: ' . $e->getMessage());
        }
    }

    /**
     * Validar permisos de archivos
     */
    private function validateFilePermissions() {
        $testName = 'Permisos de Archivos';

        $requiredFiles = [
            'ecommerce-config.php',
            'api-products.php',
            'api-cart.php',
            'api-checkout.php',
            'flow-payment.php',
            'ecommerce-api.php',
            'ecommerce-webhook.php'
        ];

        $missingFiles = [];
        $unreadableFiles = [];

        foreach ($requiredFiles as $file) {
            $filePath = __DIR__ . '/' . $file;

            if (!file_exists($filePath)) {
                $missingFiles[] = $file;
            } elseif (!is_readable($filePath)) {
                $unreadableFiles[] = $file;
            }
        }

        // Verificar directorio de logs
        $logsDir = __DIR__ . '/logs';
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }

        if (empty($missingFiles) && empty($unreadableFiles)) {
            $this->addResult($testName, true, 'Todos los archivos necesarios existen y son legibles');
        } else {
            $message = '';
            if (!empty($missingFiles)) {
                $message .= 'Archivos faltantes: ' . implode(', ', $missingFiles) . '. ';
            }
            if (!empty($unreadableFiles)) {
                $message .= 'Archivos no legibles: ' . implode(', ', $unreadableFiles);
            }
            $this->addResult($testName, false, $message);
        }
    }

    /**
     * Validar datos iniciales
     */
    private function validateInitialData() {
        $testName = 'Datos Iniciales';

        try {
            $checks = [];

            // Verificar productos
            $products = $this->db->selectOne("SELECT COUNT(*) as count FROM products WHERE status = 'publish'");
            $checks['products'] = $products['count'] > 0;

            // Verificar categorías
            $categories = $this->db->selectOne("SELECT COUNT(*) as count FROM product_categories WHERE status = 'active'");
            $checks['categories'] = $categories['count'] > 0;

            // Verificar configuraciones
            $settings = $this->db->selectOne("SELECT COUNT(*) as count FROM ecommerce_settings");
            $checks['settings'] = $settings['count'] > 0;

            // Verificar zonas de envío
            $shipping = $this->db->selectOne("SELECT COUNT(*) as count FROM shipping_zones");
            $checks['shipping_zones'] = $shipping['count'] > 0;

            $allPresent = !in_array(false, $checks, true);

            if ($allPresent) {
                $this->addResult($testName, true, 'Datos iniciales completos', $checks);
            } else {
                $missing = array_keys(array_filter($checks, function($v) { return !$v; }));
                $this->addResult($testName, false, 'Datos faltantes: ' . implode(', ', $missing), $checks);
            }

        } catch (Exception $e) {
            $this->addResult($testName, false, 'Error verificando datos iniciales: ' . $e->getMessage());
        }
    }

    /**
     * Agregar resultado de validación
     */
    private function addResult($testName, $passed, $message, $details = null) {
        $this->results[] = [
            'test' => $testName,
            'passed' => $passed,
            'message' => $message,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Obtener resumen de resultados
     */
    public function getSummary() {
        $total = count($this->results);
        $passed = count(array_filter($this->results, function($r) { return $r['passed']; }));

        return [
            'total_tests' => $total,
            'passed_tests' => $passed,
            'failed_tests' => $total - $passed,
            'success_rate' => $total > 0 ? round(($passed / $total) * 100, 2) : 0,
            'all_passed' => $passed === $total
        ];
    }

    /**
     * Obtener todos los resultados
     */
    public function getResults() {
        return $this->results;
    }

    /**
     * Imprimir resultados en formato legible
     */
    public function printResults() {
        $summary = $this->getSummary();

        echo "\n=== VALIDACIÓN DEL SISTEMA E-COMMERCE ===\n\n";

        echo "Resumen:\n";
        echo "- Tests totales: {$summary['total_tests']}\n";
        echo "- Tests exitosos: {$summary['passed_tests']}\n";
        echo "- Tests fallidos: {$summary['failed_tests']}\n";
        echo "- Tasa de éxito: {$summary['success_rate']}%\n";
        echo "- Estado general: " . ($summary['all_passed'] ? "✅ TODOS LOS TESTS PASARON" : "❌ ALGUNOS TESTS FALLARON") . "\n\n";

        echo "Detalle de tests:\n";
        foreach ($this->results as $result) {
            $status = $result['passed'] ? "✅" : "❌";
            echo "{$status} {$result['test']}: {$result['message']}\n";

            if (!$result['passed'] && $result['details']) {
                echo "   Detalles: " . json_encode($result['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            }
        }

        echo "\n=== FIN DEL REPORTE ===\n";
    }
}

// Función para ejecutar validación desde línea de comandos
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $validator = new EcommerceValidator();
    $validator->runAllValidations();
    $validator->printResults();

    $summary = $validator->getSummary();
    exit($summary['all_passed'] ? 0 : 1);
}

?>