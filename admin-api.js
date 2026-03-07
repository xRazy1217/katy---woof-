/**
 * Katy & Woof - Admin API Module v6.0
 */

// Cache-busting helper para URLs de imágenes
const addCacheBust = (url) => {
    if (!url) return url;
    if (url.startsWith('img/')) return url; // placeholder, no cache bust
    return url.includes('?') ? url + '&v=' + Date.now() : url + '?v=' + Date.now();
};

const AdminAPI = {
    async fetch(action, params = {}) {
        const auth = localStorage.getItem('kw_admin_key') || '';
        const query = new URLSearchParams({ action, auth, v: Date.now(), ...params }).toString();
        const res = await fetch(`api.php?${query}`);
        const text = await res.text();
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("Invalid JSON from API:", text);
            throw new Error("Respuesta del servidor no es válida (JSON Error)");
        }

        if (!res.ok) {
            throw new Error(data.error || `Error del servidor (${res.status})`);
        }
        if (data.success === false) {
            throw new Error(data.error || "Error en la consulta");
        }
        return data;
    },

    async post(action, formData) {
        const auth = localStorage.getItem('kw_admin_key') || '';
        const res = await fetch(`api.php?action=${action}&auth=${auth}`, {
            method: 'POST',
            body: formData
        });
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            // if server returned non-JSON (e.g. 500), include raw text
            if (!res.ok) {
                throw new Error(`Servidor respondió con error: ${res.status} - ${text}`);
            }
            throw new Error("Respuesta del servidor no es válida (JSON)");
        }
        if (!res.ok) {
            throw new Error(data.error || `Error del servidor (${res.status})`);
        }
        if (data.success === false) {
            throw new Error(data.error || "Error desconocido en el servidor");
        }
        return data;
    },


    async delete(action, id) {
        const auth = localStorage.getItem('kw_admin_key') || '';
        const res = await fetch(`api.php?action=${action}&id=${id}&auth=${auth}&v=${Date.now()}`, {
            method: 'GET'
        });
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            if (!res.ok) {
                throw new Error(`Servidor respondió con error: ${res.status} - ${text}`);
            }
            throw new Error("Respuesta del servidor no es válida (JSON)");
        }
        if (!res.ok) {
            throw new Error(data.error || `Error del servidor (${res.status})`);
        }
        if (data.success === false) {
            throw new Error(data.error || "Error al eliminar");
        }
        return data;
    }
};