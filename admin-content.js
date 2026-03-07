/**
 * Katy & Woof - Admin Content Module v6.0 (Legacy Wrapper)
 * Wrapper para compatibilidad con código existente - delega a managers modulares
 */

// Mantener compatibilidad con llamadas existentes
const AdminContent = {
    // Portafolio
    loadPortfolio() {
        return PortfolioManager.instance.load();
    },

    editArt(id) {
        return PortfolioManager.edit(id);
    },

    savePortfolio() {
        return PortfolioManager.save();
    },

    deleteArt(id) {
        return PortfolioManager.delete(id);
    },

    // Servicios
    loadServices() {
        return ServicesManager.instance.load();
    },

    editService(id) {
        return ServicesManager.edit(id);
    },

    saveService() {
        return ServicesManager.save();
    },

    deleteService(id) {
        return ServicesManager.delete(id);
    },

    // Blog
    loadBlog() {
        return BlogManager.instance.load();
    },

    editBlog(id) {
        return BlogManager.edit(id);
    },

    saveBlog() {
        return BlogManager.save();
    },

    deleteBlog(id) {
        return BlogManager.delete(id);
    },

    // Proceso
    loadProcessSteps() {
        return ProcessManager.instance.load();
    },

    editProcess(id) {
        return ProcessManager.edit(id);
    },

    saveProcessStep() {
        return ProcessManager.save();
    },

    deleteProcessStep(id) {
        return ProcessManager.delete(id);
    },

    // Global Cancel
    cancelEdit(section) {
        switch (section) {
            case 'portfolio':
                PortfolioManager.cancelEdit();
                break;
            case 'services':
                ServicesManager.cancelEdit();
                break;
            case 'blog':
                BlogManager.cancelEdit();
                break;
            case 'proceso':
                ProcessManager.cancelEdit();
                break;
        }
    }
};