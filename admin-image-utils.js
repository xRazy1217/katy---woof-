/**
 * Katy & Woof - Image Upload Utilities v1.0
 * 
 * Funciones para validación y preview de imágenes antes de subir
 * al panel de administración
 */

const ImageUploadUtils = {
    
    // Constantes de validación
    MAX_FILE_SIZE: 10 * 1024 * 1024,      // 10MB
    MIN_WIDTH: 300,
    MAX_WIDTH: 4096,
    MIN_HEIGHT: 300,
    MAX_HEIGHT: 4096,
    ALLOWED_TYPES: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    ALLOWED_EXTENSIONS: ['jpg', 'jpeg', 'png', 'webp', 'gif'],

    /**
     * Valida un archivo antes de subirlo
     * Retorna error string si hay problema, null si es válido
     */
    validateFile(file) {
        if (!file) return "No hay archivo seleccionado";
        
        // Validar tamaño
        if (file.size > this.MAX_FILE_SIZE) {
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            return `Archivo muy grande (${sizeMB}MB). Máximo 10MB`;
        }
        
        // Validar tipo MIME
        if (!this.ALLOWED_TYPES.includes(file.type)) {
            return `Formato no permitido: ${file.type}. Solo: JPG, PNG, WebP, GIF`;
        }
        
        // Validar extensión
        const ext = file.name.split('.').pop()?.toLowerCase();
        if (!this.ALLOWED_EXTENSIONS.includes(ext)) {
            return `Extensión no permitida: .${ext}. Usa: .jpg, .png, .webp, .gif`;
        }
        
        return null;
    },

    /**
     * Valida dimensiones de imagen
     * Retorna error string si hay problema, null si es válido
     */
    validateDimensions(width, height) {
        if (width < this.MIN_WIDTH || height < this.MIN_HEIGHT) {
            return `Imagen demasiado pequeña. Mínimo ${this.MIN_WIDTH}x${this.MIN_HEIGHT}px. Tu imagen: ${width}x${height}px`;
        }
        
        if (width > this.MAX_WIDTH || height > this.MAX_HEIGHT) {
            return `Imagen demasiado grande. Máximo ${this.MAX_WIDTH}x${this.MAX_HEIGHT}px. Tu imagen: ${width}x${height}px`;
        }
        
        return null;
    },

    /**
     * Valida archivo completamente (tipo, tamaño, dimensiones)
     * Retorna objeto: { valid: bool, error: string, width: int, height: int }
     */
    async validateImageComplete(file) {
        // Validar archivo básicamente
        const basicError = this.validateFile(file);
        if (basicError) {
            return { valid: false, error: basicError };
        }
        
        // Validar dimensiones
        return new Promise((resolve) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                const img = new Image();
                
                img.onload = () => {
                    const dimensionError = this.validateDimensions(img.width, img.height);
                    
                    if (dimensionError) {
                        resolve({ valid: false, error: dimensionError });
                    } else {
                        resolve({
                            valid: true,
                            width: img.width,
                            height: img.height,
                            size: file.size
                        });
                    }
                };
                
                img.onerror = () => {
                    resolve({ valid: false, error: "No se puede leer la imagen" });
                };
                
                img.src = e.target.result;
            };
            
            reader.onerror = () => {
                resolve({ valid: false, error: "Error al leer el archivo" });
            };
            
            reader.readAsDataURL(file);
        });
    },

    /**
     * Crea preview de imagen en elemento especificado
     */
    showPreview(file, previewElementId) {
        const preview = document.getElementById(previewElementId);
        if (!preview) return;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    },

    /**
     * Muestra información del archivo (tamaño, dimensiones)
     */
    showFileInfo(file, width, height, infoElementId) {
        const infoEl = document.getElementById(infoElementId);
        if (!infoEl) return;
        
        const sizeKB = (file.size / 1024).toFixed(1);
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        const sizeText = sizeKB > 1024 ? `${sizeMB} MB` : `${sizeKB} KB`;
        
        infoEl.innerHTML = `
            <div class="text-[10px] space-y-1">
                <div><strong>📦 Tamaño:</strong> ${sizeText}</div>
                <div><strong>📐 Dimensiones:</strong> ${width}x${height}px</div>
                <div><strong>🎨 Tipo:</strong> ${file.type}</div>
                <div class="text-stone-500 mt-2">ℹ️ Se optimizará a WebP en el servidor</div>
            </div>
        `;
    },

    /**
     * Limpia preview e info
     */
    clearPreview(previewElementId, infoElementId) {
        const preview = document.getElementById(previewElementId);
        const info = document.getElementById(infoElementId);
        
        if (preview) {
            preview.src = '';
            preview.style.display = 'none';
        }
        if (info) {
            info.innerHTML = '';
        }
    },

    /**
     * Helper: formatea bytes a texto legible
     */
    formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    },

    /**
     * Helper: calcula ahorro estimado (solo estimado, no es exacto)
     * WebP es típicamente 25-35% menor que JPG
     */
    estimateSavings(originalSize) {
        // Estimación: 30% de ahorro con WebP
        const estimatedSize = originalSize * 0.70;
        const savings = originalSize - estimatedSize;
        const savingsPercent = Math.round((savings / originalSize) * 100);
        return {
            estimatedSize: this.formatBytes(estimatedSize),
            savings: this.formatBytes(savings),
            percent: savingsPercent
        };
    }
};

/**
 * Inicializa todos los inputs de archivo en la página
 * Llamar una sola vez al cargar el admin
 */
function initImageUploads() {
    try {
        // Buscar todos los inputs de archivo
        const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
        
        fileInputs.forEach(input => {
            // Si ya está envuelto en file-input-wrapper, saltar el envoltura
            const alreadyWrapped = input.parentElement && input.parentElement.classList.contains('file-input-wrapper');
            if (alreadyWrapped) {
                // Continuar con listener - no envolver nuevamente
            } else {
                // envolver en wrapper si no existe, para espaciado
                const wrapper = document.createElement('div');
                wrapper.className = 'file-input-wrapper';
                input.parentElement.insertBefore(wrapper, input);
                wrapper.appendChild(input);
            }
            
            const inputId = input.id;
            if (!inputId) return; // Skip inputs sin ID
            
            // Buscar elementos de preview e info correspondientes
            let prefix = inputId.replace('-file', '');
            if (prefix === inputId) {
                // Sin '-file', intentar remover '-input'
                prefix = inputId.replace('-input', '');
            }
            
            const previewId = `${prefix}-preview`;
            const infoId = `${prefix}-file-info`;
            
            // Agregar listener
            input.addEventListener('change', async function(e) {
                if (!this.files || !this.files[0]) return;
                
                const file = this.files[0];
                
                // Validar completamente
                const validation = await ImageUploadUtils.validateImageComplete(file);
                
                if (!validation.valid) {
                    // Mostrar error
                    const infoEl = document.getElementById(infoId);
                    if (infoEl) {
                        infoEl.innerHTML = `<div class="text-red-500 text-[10px] font-bold">❌ ${validation.error}</div>`;
                    }
                    // Limpiar preview
                    ImageUploadUtils.clearPreview(previewId, infoId);
                    this.value = ''; // Limpiar input
                } else {
                    // Mostrar preview e info
                    ImageUploadUtils.showPreview(file, previewId);
                    
                    const savings = ImageUploadUtils.estimateSavings(validation.size);
                    
                    let infoHTML = `
                        <div class="text-[10px] space-y-1 text-stone-700">
                            <div>✅ <strong>Tamaño:</strong> ${ImageUploadUtils.formatBytes(validation.size)}</div>
                            <div>✅ <strong>Dimensiones:</strong> ${validation.width}x${validation.height}px</div>
                            <div class="bg-emerald-50 p-2 rounded mt-2 text-emerald-700">
                                💾 Después de optimizar: ~${savings.estimatedSize} (≈${savings.percent}% menos)
                            </div>
                        </div>
                    `;
                    
                    const infoEl = document.getElementById(infoId);
                    if (infoEl) {
                        infoEl.innerHTML = infoHTML;
                    }
                }
            });
        });
    } catch (err) {
        console.error('Error en initImageUploads:', err);
    }
}

// NOTA: initImageUploads() se llama desde admin.html en DOMContentLoaded
// para garantizar que todos los módulos estén completamente cargados
