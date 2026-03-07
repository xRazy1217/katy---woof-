<?php
/**
 * Katy & Woof - Schema Manager v1.0
 * 
 * Gestor centralizado para auditoría y sincronización del esquema MySQL.
 * Proporciona funciones para comparar, detectar y reparar inconsistencias.
 */

require_once 'config.php';
require_once 'schema-definition.php';

class SchemaManager {
    
    private $pdo;
    private $idealSchema;
    private $errors = [];
    
    public function __construct() {
        $this->pdo = getDBConnection();
        $this->idealSchema = require 'schema-definition.php';
    }

    /**
     * Obtiene la estructura actual de una tabla desde information_schema
     * 
     * @param string $tableName Nombre de la tabla
     * @return array Estructura actual (nombre => tipo de dato)
     */
    public function getTableStructure($tableName) {
        try {
            $query = "
                SELECT COLUMN_NAME, COLUMN_TYPE 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([DB_NAME, $tableName]);
            
            $structure = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $structure[$row['COLUMN_NAME']] = $row['COLUMN_TYPE'];
            }
            return $structure;
        } catch (Exception $e) {
            $this->errors[] = "Error al obtener estructura de $tableName: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Verifica si una tabla existe en la base de datos
     * 
     * @param string $tableName
     * @return bool
     */
    public function tableExists($tableName) {
        try {
            $query = "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([DB_NAME, $tableName]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Realiza una auditoría completa del esquema
     * Compara el esquema ideal con el actual y detecta inconsistencias
     * 
     * @return array Reporte detallado con estado de cada tabla
     */
    public function auditSchema() {
        $report = [
            'database' => DB_NAME,
            'timestamp' => date('Y-m-d H:i:s'),
            'tables' => [],
            'overall_status' => 'OK',
            'total_tables' => count($this->idealSchema),
            'ok_tables' => 0,
            'tables_with_issues' => 0
        ];

        foreach ($this->idealSchema as $tableName => $tableSchema) {
            $tableReport = $this->auditTable($tableName, $tableSchema['columns']);
            $report['tables'][$tableName] = $tableReport;
            
            if ($tableReport['status'] === 'OK') {
                $report['ok_tables']++;
            } else {
                $report['tables_with_issues']++;
                $report['overall_status'] = 'NEEDS_SYNC';
            }
        }

        return $report;
    }

    /**
     * Auditoría de una tabla específica
     * 
     * @param string $tableName
     * @param array $expectedColumns Columnas esperadas
     * @return array Estado de la tabla
     */
    private function auditTable($tableName, $expectedColumns) {
        $report = [
            'name' => $tableName,
            'status' => 'OK',
            'issues' => [],
            'missing_columns' => []
        ];

        // Verificar si la tabla existe
        if (!$this->tableExists($tableName)) {
            $report['status'] = 'MISSING_TABLE';
            $report['issues'][] = "La tabla no existe";
            return $report;
        }

        // Obtener estructura actual
        $actualStructure = $this->getTableStructure($tableName);

        // Buscar columnas faltantes
        foreach ($expectedColumns as $columnName => $columnType) {
            if (!array_key_exists($columnName, $actualStructure)) {
                $report['status'] = 'MISSING_COLUMNS';
                $report['issues'][] = "Falta la columna: $columnName ($columnType)";
                $report['missing_columns'][] = [
                    'name' => $columnName,
                    'type' => $columnType
                ];
            }
        }

        return $report;
    }

    /**
     * Genera el SQL necesario para sincronizar la base de datos
     * Incluye CREATE TABLE y ALTER TABLE statements
     * 
     * @return array SQL statements a ejecutar
     */
    public function generateSyncSQL() {
        $sqlStatements = [];
        
        foreach ($this->idealSchema as $tableName => $tableSchema) {
            if (!$this->tableExists($tableName)) {
                // Tabla faltante - CREATE TABLE
                $sqlStatements[] = $this->generateCreateTableSQL($tableName, $tableSchema['columns']);
            } else {
                // Tabla existe - verificar columnas faltantes
                $missingColumns = $this->getMissingColumns($tableName, $tableSchema['columns']);
                if (!empty($missingColumns)) {
                    foreach ($missingColumns as $columnName => $columnType) {
                        $sqlStatements[] = "ALTER TABLE `$tableName` ADD COLUMN `$columnName` $columnType;";
                    }
                }
            }
        }
        
        return $sqlStatements;
    }

    /**
     * Detecta columnas faltantes en una tabla
     * 
     * @param string $tableName
     * @param array $expectedColumns
     * @return array Columnas faltantes
     */
    private function getMissingColumns($tableName, $expectedColumns) {
        $actualStructure = $this->getTableStructure($tableName);
        $missingColumns = [];
        
        foreach ($expectedColumns as $columnName => $columnType) {
            if (!array_key_exists($columnName, $actualStructure)) {
                $missingColumns[$columnName] = $columnType;
            }
        }
        
        return $missingColumns;
    }

    /**
     * Genera un statement CREATE TABLE completo
     * 
     * @param string $tableName
     * @param array $columns
     * @return string SQL CREATE TABLE
     */
    private function generateCreateTableSQL($tableName, $columns) {
        $columnDefinitions = [];
        
        foreach ($columns as $columnName => $columnType) {
            $columnDefinitions[] = "`$columnName` $columnType";
        }
        
        $columnString = implode(",\n  ", $columnDefinitions);
        
        return "CREATE TABLE IF NOT EXISTS `$tableName` (
  $columnString
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }

    /**
     * Ejecuta la sincronización de la base de datos
     * Usa transacciones para garantizar atomicidad
     * 
     * @return array Resultado con éxito/errores
     */
    public function syncDatabase() {
        $result = [
            'success' => false,
            'executed_statements' => [],
            'errors' => [],
            'tables_created' => 0,
            'columns_added' => 0
        ];

        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();

            $sqlStatements = $this->generateSyncSQL();

            foreach ($sqlStatements as $sql) {
                try {
                    $this->pdo->exec($sql);
                    $result['executed_statements'][] = $sql;

                    // Contar lo que se ejecutó
                    if (stripos($sql, 'CREATE TABLE') === 0) {
                        $result['tables_created']++;
                    } elseif (stripos($sql, 'ALTER TABLE') === 0) {
                        $result['columns_added']++;
                    }
                } catch (Exception $e) {
                    throw new Exception("Error ejecutando SQL: " . $e->getMessage() . "\n\nSQL: $sql");
                }
            }

            // Confirmar transacción
            $this->pdo->commit();
            $result['success'] = true;
            $result['message'] = 'Sincronización completada exitosamente';

        } catch (Exception $e) {
            // Revertir transacción en caso de error
            try {
                $this->pdo->rollBack();
            } catch (Exception $rollbackError) {
                // Ignorar error de rollback si la transacción no estaba activa
            }

            $result['success'] = false;
            $result['message'] = 'Error durante la sincronización: ' . $e->getMessage();
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Prueba la conexión a la base de datos
     * 
     * @return array Información de conexión
     */
    public function testConnection() {
        try {
            $stmt = $this->pdo->query("SELECT VERSION() as version");
            $versionRow = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'database' => DB_NAME,
                'host' => DB_HOST,
                'mysql_version' => $versionRow['version'] ?? 'Unknown'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene los errores registrados
     * 
     * @return array Array de errores
     */
    public function getErrors() {
        return $this->errors;
    }
}

// Exportar la clase para uso en api.php
return SchemaManager::class;
