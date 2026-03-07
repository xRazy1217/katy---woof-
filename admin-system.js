/**
 * Katy & Woof - Admin System Panel v1.0
 * 
 * Gestor de la pestaña "Estado del Sistema"
 * Auditoría y sincronización de esquema MySQL
 */

const AdminSystem = {
    
    // Estado actual de la auditoría
    currentAudit: null,
    authKey: 'Asesor25',

    /**
     * Inicializa el módulo del sistema
     * Se ejecuta cuando se carga la página
     */
    async init() {
        console.log('AdminSystem initialized');
        // Cargar estado inicial cuando se abre la pestaña
        document.querySelector('[data-tab="system"]').addEventListener('click', () => {
            this.loadInitialStatus();
        });
    },

    /**
     * Carga el estado inicial (conexión + auditoría)
     */
    async loadInitialStatus() {
        await this.checkDatabaseStatus();
        await this.auditSchema();
    },

    /**
     * Obtiene y muestra el estado de la conexión a Base de Datos
     */
    async checkDatabaseStatus() {
        try {
            const response = await fetch(`api.php?action=get_db_status`);
            const result = await response.json();
            
            if (result.success && result.data) {
                const data = result.data;
                
                // Actualizar indicador de conexión
                const indicator = document.getElementById('db-status-indicator');
                const statusText = document.getElementById('db-status-text');
                const detailsDiv = document.getElementById('db-details');
                
                indicator.className = 'w-12 h-12 rounded-full mx-auto mt-3 flex items-center justify-center bg-emerald-500';
                indicator.innerHTML = '<span class="text-white text-2xl font-black">✓</span>';
                statusText.textContent = 'Conectada';
                statusText.className = 'mt-3 font-bold text-sm text-emerald-600';
                
                // Actualizar detalles
                document.getElementById('db-host').textContent = data.host || 'N/A';
                document.getElementById('db-name').textContent = data.database || 'N/A';
                document.getElementById('db-version').textContent = data.mysql_version || 'N/A';
                document.getElementById('db-table-count').textContent = data.table_count || 0;
                document.getElementById('db-size').textContent = (data.size_mb || 0).toFixed(2);
                detailsDiv.classList.remove('hidden');
                
            } else {
                this.setConnectionFailed(result.error || 'Error desconocido');
            }
        } catch (error) {
            console.error('Error checking database status:', error);
            this.setConnectionFailed('Error de conexión');
        }
    },

    /**
     * Actualiza la UI cuando la conexión falla
     */
    setConnectionFailed(errorMsg) {
        const indicator = document.getElementById('db-status-indicator');
        const statusText = document.getElementById('db-status-text');
        
        indicator.className = 'w-12 h-12 rounded-full bg-red-500 mx-auto mt-3 flex items-center justify-center';
        indicator.innerHTML = '<span class="text-white text-2xl font-black">✗</span>';
        statusText.textContent = 'Desconectada';
        statusText.className = 'mt-3 font-bold text-sm text-red-600';
        
        console.error('Database connection error:', errorMsg);
    },

    /**
     * Realiza una auditoría completa del esquema
     */
    async auditSchema() {
        try {
            AdminUI.toggleLoading(true);
            
            const response = await fetch(`api.php?action=audit_schema`);
            const result = await response.json();
            
            if (result.success && result.data) {
                this.currentAudit = result.data;
                this.displayAuditResults(result.data);
                this.updateSyncButtonState();
            } else {
                AdminUI.showToast('Error en auditoría: ' + (result.error || 'Desconocido'), 'error');
            }
            
        } catch (error) {
            console.error('Error during audit:', error);
            AdminUI.showToast('Error al auditar esquema', 'error');
        } finally {
            AdminUI.toggleLoading(false);
        }
    },

    /**
     * Muestra los resultados de la auditoría en la UI
     */
    displayAuditResults(audit) {
        // Actualizar contadores
        document.getElementById('audit-ok-count').textContent = audit.ok_tables;
        document.getElementById('audit-issues-count').textContent = audit.tables_with_issues;
        document.getElementById('audit-missing-tables-count').textContent = audit.missing_tables_count || 0;
        document.getElementById('audit-missing-columns-count').textContent = audit.missing_columns_count || 0;
        
        // Mensaje de estado general
        const statusMsg = document.getElementById('audit-status-message');
        const statusText = document.getElementById('audit-status-text');
        
        if (audit.overall_status === 'OK') {
            statusMsg.classList.remove('hidden', 'bg-orange-50', 'border-orange-400');
            statusMsg.classList.add('bg-emerald-50', 'border-emerald-400');
            statusText.innerHTML = '✓ <strong>Esquema sincronizado</strong>. Todo en orden.';
            statusText.className = 'text-emerald-700';
        } else if (audit.overall_status === 'NEEDS_SYNC') {
            statusMsg.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-400');
            statusMsg.classList.add('bg-orange-50', 'border-orange-400');
            statusText.innerHTML = '⚠️ <strong>Esquema desincronizado</strong>. Se recomienda sincronizar.';
            statusText.className = 'text-orange-700';
        }
        
        // Listar estado de cada tabla
        this.displayTableStatus(audit.tables);
        
        // Actualizar timestamp
        const timestamp = new Date(audit.timestamp);
        document.getElementById('audit-timestamp').textContent = timestamp.toLocaleString('es-ES');
    },

    /**
     * Muestra el estado de cada tabla
     */
    displayTableStatus(tables) {
        const container = document.getElementById('audit-tables-list');
        container.innerHTML = '';
        
        Object.keys(tables).forEach(tableName => {
            const table = tables[tableName];
            const statusClass = table.status === 'OK' 
                ? 'border-emerald-300 bg-emerald-50' 
                : 'border-orange-300 bg-orange-50';
            
            const statusIcon = table.status === 'OK' ? '✓' : '⚠️';
            const statusLabel = this.getStatusLabel(table.status);
            
            let html = `
                <div class="p-4 rounded-xl border-l-4 ${statusClass}">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-[11px] uppercase tracking-widest">
                                ${statusIcon} ${tableName}
                            </h3>
                            <p class="text-[10px] text-stone-600 mt-1">${statusLabel}</p>
                        </div>
                        <div class="text-3xl font-black opacity-20">${statusIcon}</div>
                    </div>
            `;
            
            // Mostrar columnas faltantes si las hay
            if (table.missing_columns && table.missing_columns.length > 0) {
                html += `
                    <div class="mt-3 pt-3 border-t border-stone-200 text-[9px] font-mono space-y-1">
                        <p class="font-bold text-stone-600">Columnas faltantes:</p>
                `;
                
                table.missing_columns.forEach(col => {
                    html += `<div class="text-stone-700"><code>${col.name}</code> → ${col.type}</div>`;
                });
                
                html += `</div>`;
            }
            
            html += `</div>`;
            container.innerHTML += html;
        });
    },

    /**
     * Obtiene la etiqueta de estado en español
     */
    getStatusLabel(status) {
        const labels = {
            'OK': '✓ Tabla completa y sincronizada',
            'MISSING_TABLE': '✗ Tabla no existe',
            'MISSING_COLUMNS': '⚠️ Faltan algunas columnas'
        };
        return labels[status] || status;
    },

    /**
     * Actualiza el estado del botón de sincronización
     * Habilitará solo si hay inconsistencias
     */
    updateSyncButtonState() {
        const syncBtn = document.getElementById('btn-sync-database');
        
        if (this.currentAudit && this.currentAudit.overall_status === 'NEEDS_SYNC') {
            syncBtn.disabled = false;
            syncBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            syncBtn.classList.add('bg-yellow-100', 'hover:bg-yellow-200', 'text-yellow-800');
            syncBtn.textContent = '🛠️ Reparar Estructura BD (⚠️ Detalles arriba)';
        } else {
            syncBtn.disabled = true;
            syncBtn.classList.add('opacity-50', 'cursor-not-allowed');
            syncBtn.classList.remove('bg-yellow-100', 'hover:bg-yellow-200', 'text-yellow-800');
            syncBtn.textContent = '✓ Estructura BD Correcta';
        }
    },

    /**
     * Alias semántico para el botón de reparación
     */
    async repairDatabase() {
        await this.syncDatabase();
    },

    /**
     * Ejecuta la sincronización de la base de datos
     * Requiere confirmación del usuario
     */
    async syncDatabase() {
        if (!this.currentAudit || this.currentAudit.overall_status !== 'NEEDS_SYNC') {
            AdminUI.showToast('No hay cambios para sincronizar', 'info');
            return;
        }
        
        // Confirmación del usuario
        const confirmation = confirm(
            '⚠️ REPARACIÓN DE ESTRUCTURA DE BASE DE DATOS\n\n' +
            'Se ejecutarán los siguientes cambios:\n\n' +
            `• Tablas faltantes: ${this.currentAudit.missing_tables_count || 0}\n` +
            `• Columnas faltantes: ${this.currentAudit.missing_columns_count || 0}\n\n` +
            '¿Deseas continuar? Esta acción no puede deshacerse fácilmente.'
        );
        
        if (!confirmation) {
            AdminUI.showToast('Reparación cancelada', 'info');
            return;
        }
        
        try {
            AdminUI.toggleLoading(true);
            document.getElementById('btn-sync-database').disabled = true;
            
            // Ejecutar reparación de esquema
            const response = await fetch(
                `api.php?action=repair_database&auth=${this.authKey}`,
                { method: 'POST' }
            );
            
            const result = await response.json();
            
            if (result.success) {
                this.displaySyncSuccess(result);
                const toastMessage = result.changes_required
                    ? '✓ Reparación completada exitosamente'
                    : '✓ No fue necesario reparar: esquema ya sincronizado';
                AdminUI.showToast(toastMessage, 'success');
                
                // Volver a auditar para actualizar état
                setTimeout(() => {
                    this.auditSchema();
                }, 1500);
            } else {
                AdminUI.showToast('❌ Error al reparar: ' + (result.error || result.message || 'Error desconocido'), 'error');
                console.error('Sync error:', result);
            }
            
        } catch (error) {
            console.error('Error during sync:', error);
            AdminUI.showToast('Error al reparar estructura de base de datos', 'error');
        } finally {
            AdminUI.toggleLoading(false);
            document.getElementById('btn-sync-database').disabled = false;
        }
    },

    /**
     * Muestra el resultado exitoso de la sincronización
     */
    displaySyncSuccess(result) {
        const resultDiv = document.getElementById('sync-result');
        const detailsDiv = document.getElementById('sync-result-details');
        
        let detailsHtml = `
            <div>📦 <strong>Tablas creadas:</strong> ${result.tables_created || 0}</div>
            <div>➕ <strong>Columnas agregadas:</strong> ${result.columns_added || 0}</div>
        `;

        if (!result.changes_required) {
            detailsHtml += '<div class="mt-2 text-green-700">No había inconsistencias por reparar.</div>';
        }
        
        if (result.executed_statements && result.executed_statements.length > 0) {
            detailsHtml += '<div class="mt-4 pt-4 border-t border-green-300"><p class="font-bold mb-2">Sentencias ejecutadas:</p>';
            
            result.executed_statements.slice(0, 5).forEach(stmt => {
                const preview = stmt.substring(0, 60) + (stmt.length > 60 ? '...' : '');
                detailsHtml += `<div class="text-[9px] text-green-700">${preview}</div>`;
            });
            
            if (result.executed_statements.length > 5) {
                detailsHtml += `<div class="text-[9px] text-stone-500 italic">...y ${result.executed_statements.length - 5} más</div>`;
            }
            
            detailsHtml += '</div>';
        }
        
        detailsDiv.innerHTML = detailsHtml;
        resultDiv.classList.remove('hidden');
        
        // Auto-hide después de 8 segundos
        setTimeout(() => {
            resultDiv.classList.add('hidden');
        }, 8000);
    }
};

// NOTA: AdminSystem.init() se llama desde admin.html en DOMContentLoaded
// para evitar conflictos con otros DOMContentLoaded listeners
