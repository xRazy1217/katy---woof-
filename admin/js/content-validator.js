/**
 * Katy & Woof - Content Validator Module v6.0
 * Validaciones compartidas para contenido
 */

const ContentValidator = {
    /**
     * Valida una imagen completa usando ImageUploadUtils
     * @param {File} file - Archivo de imagen
     * @returns {Promise<{valid: boolean, error?: string}>}
     */
    async validateImage(file) {
        if (!file) return { valid: true };

        try {
            const validation = await ImageUploadUtils.validateImageComplete(file);
            return {
                valid: validation.valid,
                error: validation.error
            };
        } catch (e) {
            return {
                valid: false,
                error: "Error al validar imagen: " + e.message
            };
        }
    },

    /**
     * Valida campos requeridos básicos
     * @param {Object} fields - Objeto con campos a validar
     * @param {Array} required - Array de nombres de campos requeridos
     * @returns {{valid: boolean, error?: string}}
     */
    validateRequired(fields, required) {
        for (const field of required) {
            if (!fields[field] || fields[field].toString().trim() === '') {
                return {
                    valid: false,
                    error: `El campo ${field} es requerido`
                };
            }
        }
        return { valid: true };
    }
};