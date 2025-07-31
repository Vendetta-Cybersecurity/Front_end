/**
 * Figger Energy SAS - Components
 * Componentes reutilizables de UI
 */

/**
 * Sistema de notificaciones Toast
 */
class ToastManager {
    constructor() {
        this.container = null;
        this.toasts = [];
        this.init();
    }

    init() {
        // Crear contenedor de toasts si no existe
        this.container = document.getElementById('toast-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    }

    show(message, type = 'info', duration = 5000) {
        const toast = this.createToast(message, type, duration);
        this.container.appendChild(toast);
        this.toasts.push(toast);

        // Mostrar con animación
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Auto-ocultar
        if (duration > 0) {
            setTimeout(() => {
                this.hide(toast);
            }, duration);
        }

        return toast;
    }

    createToast(message, type, duration) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        toast.innerHTML = `
            <div class="toast-header">
                <div class="toast-icon">${icons[type] || icons.info}</div>
                <h5 class="toast-title">${this.getTypeTitle(type)}</h5>
                <button class="toast-close" type="button" aria-label="Cerrar">×</button>
            </div>
            <div class="toast-body">${message}</div>
        `;

        // Evento de cierre
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => this.hide(toast));

        return toast;
    }

    getTypeTitle(type) {
        const titles = {
            success: 'Éxito',
            error: 'Error',
            warning: 'Advertencia',
            info: 'Información'
        };
        return titles[type] || titles.info;
    }

    hide(toast) {
        toast.classList.remove('show');
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            
            const index = this.toasts.indexOf(toast);
            if (index > -1) {
                this.toasts.splice(index, 1);
            }
        }, 300);
    }

    clear() {
        this.toasts.forEach(toast => this.hide(toast));
    }
}

/**
 * Sistema de Modal
 */
class ModalManager {
    constructor() {
        this.activeModal = null;
        this.init();
    }

    init() {
        // Cerrar modal con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.hide();
            }
        });

        // Cerrar modal al hacer click en el overlay
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal') && this.activeModal) {
                this.hide();
            }
        });
    }

    show(modalElement) {
        if (this.activeModal) {
            this.hide();
        }

        this.activeModal = modalElement;
        modalElement.classList.add('show');
        document.body.style.overflow = 'hidden';

        // Focus en el modal
        const firstFocusable = modalElement.querySelector('input, button, select, textarea, [tabindex]');
        if (firstFocusable) {
            firstFocusable.focus();
        }
    }

    hide() {
        if (this.activeModal) {
            this.activeModal.classList.remove('show');
            document.body.style.overflow = '';
            this.activeModal = null;
        }
    }

    create(options = {}) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        
        const dialog = document.createElement('div');
        dialog.className = `modal-dialog ${options.size || ''}`;
        
        const content = document.createElement('div');
        content.className = 'modal-content';
        
        // Header
        if (options.title) {
            const header = document.createElement('div');
            header.className = 'modal-header';
            header.innerHTML = `
                <h4 class="modal-title">${options.title}</h4>
                <button type="button" class="modal-close" aria-label="Cerrar">×</button>
            `;
            content.appendChild(header);
            
            // Evento de cierre
            header.querySelector('.modal-close').addEventListener('click', () => this.hide());
        }
        
        // Body
        const body = document.createElement('div');
        body.className = 'modal-body';
        if (options.content) {
            body.innerHTML = options.content;
        }
        content.appendChild(body);
        
        // Footer
        if (options.footer) {
            const footer = document.createElement('div');
            footer.className = 'modal-footer';
            footer.innerHTML = options.footer;
            content.appendChild(footer);
        }
        
        dialog.appendChild(content);
        modal.appendChild(dialog);
        document.body.appendChild(modal);
        
        return modal;
    }
}

/**
 * Sistema de Loading
 */
class LoadingManager {
    constructor() {
        this.overlay = null;
        this.activeLoaders = new Set();
    }

    show(message = 'Cargando...', target = null) {
        const loaderId = Date.now() + Math.random();
        this.activeLoaders.add(loaderId);

        if (target) {
            this.showInElement(target, message, loaderId);
        } else {
            this.showOverlay(message, loaderId);
        }

        return loaderId;
    }

    showOverlay(message, loaderId) {
        if (!this.overlay) {
            this.overlay = document.createElement('div');
            this.overlay.className = 'loading-overlay';
            this.overlay.innerHTML = `
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">${message}</div>
                </div>
            `;
            document.body.appendChild(this.overlay);
        } else {
            this.overlay.querySelector('.loading-text').textContent = message;
        }
        
        this.overlay.dataset.loaderId = loaderId;
        this.overlay.style.display = 'flex';
    }

    showInElement(element, message, loaderId) {
        const existing = element.querySelector('.loading-container');
        if (existing) {
            existing.remove();
        }

        const loader = document.createElement('div');
        loader.className = 'loading-container';
        loader.dataset.loaderId = loaderId;
        loader.innerHTML = `
            <div class="loading-spinner"></div>
            <div class="loading-text">${message}</div>
        `;
        
        element.style.position = 'relative';
        element.appendChild(loader);
    }

    hide(loaderId = null) {
        if (loaderId) {
            this.activeLoaders.delete(loaderId);
            
            // Ocultar loader específico
            const loader = document.querySelector(`[data-loader-id="${loaderId}"]`);
            if (loader) {
                loader.remove();
            }
        } else {
            // Ocultar todos los loaders
            this.activeLoaders.clear();
        }

        // Ocultar overlay si no hay loaders activos
        if (this.activeLoaders.size === 0 && this.overlay) {
            this.overlay.style.display = 'none';
        }
    }

    hideAll() {
        this.activeLoaders.clear();
        
        if (this.overlay) {
            this.overlay.style.display = 'none';
        }
        
        // Remover todos los loaders inline
        document.querySelectorAll('.loading-container').forEach(loader => {
            loader.remove();
        });
    }
}

/**
 * Componente de tabla con paginación y filtros
 */
class DataTable {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            data: [],
            columns: [],
            pageSize: 10,
            searchable: true,
            sortable: true,
            filterable: false,
            selectable: false,
            actions: [],
            emptyMessage: 'No hay datos disponibles',
            ...options
        };
        
        this.filteredData = [];
        this.currentPage = 1;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.searchTerm = '';
        this.filters = {};
        
        this.init();
    }

    init() {
        this.render();
        this.bindEvents();
    }

    render() {
        this.container.innerHTML = `
            <div class="datatable-container">
                ${this.renderHeader()}
                ${this.renderTable()}
                ${this.renderPagination()}
            </div>
        `;
    }

    renderHeader() {
        if (!this.options.searchable && !this.options.filterable) {
            return '';
        }

        return `
            <div class="datatable-header">
                ${this.options.searchable ? this.renderSearch() : ''}
                ${this.options.filterable ? this.renderFilters() : ''}
            </div>
        `;
    }

    renderSearch() {
        return `
            <div class="search-box">
                <input type="text" class="search-input form-control" placeholder="Buscar..." value="${this.searchTerm}">
                <span class="search-icon">🔍</span>
                <button class="search-clear" style="display: ${this.searchTerm ? 'block' : 'none'}">×</button>
            </div>
        `;
    }

    renderFilters() {
        // Implementar filtros dinámicos basados en columnas
        return '<div class="datatable-filters"></div>';
    }

    renderTable() {
        this.applyFilters();
        
        if (this.filteredData.length === 0) {
            return `
                <div class="datatable-empty">
                    <p>${this.options.emptyMessage}</p>
                </div>
            `;
        }

        const startIndex = (this.currentPage - 1) * this.options.pageSize;
        const endIndex = startIndex + this.options.pageSize;
        const pageData = this.filteredData.slice(startIndex, endIndex);

        return `
            <div class="table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            ${this.options.selectable ? '<th><input type="checkbox" class="select-all"></th>' : ''}
                            ${this.options.columns.map(col => this.renderColumnHeader(col)).join('')}
                            ${this.options.actions.length > 0 ? '<th>Acciones</th>' : ''}
                        </tr>
                    </thead>
                    <tbody>
                        ${pageData.map(row => this.renderRow(row)).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    renderColumnHeader(column) {
        const sortIcon = this.sortColumn === column.key 
            ? (this.sortDirection === 'asc' ? '↑' : '↓') 
            : '';
        
        const sortClass = this.options.sortable && column.sortable !== false ? 'sortable' : '';
        
        return `<th class="${sortClass}" data-key="${column.key}">${column.title} ${sortIcon}</th>`;
    }

    renderRow(row) {
        return `
            <tr data-id="${row.id || ''}">
                ${this.options.selectable ? `<td><input type="checkbox" class="row-select" value="${row.id || ''}"></td>` : ''}
                ${this.options.columns.map(col => this.renderCell(row, col)).join('')}
                ${this.options.actions.length > 0 ? this.renderActions(row) : ''}
            </tr>
        `;
    }

    renderCell(row, column) {
        let value = row[column.key];
        
        if (column.render && typeof column.render === 'function') {
            value = column.render(value, row);
        } else if (column.type === 'date' && value) {
            value = FormatUtils.formatDate(value);
        } else if (column.type === 'currency' && value) {
            value = FormatUtils.formatCurrency(value);
        } else if (column.type === 'badge' && value) {
            value = `<span class="badge badge-${column.badgeClass || 'primary'}">${value}</span>`;
        }
        
        return `<td>${value || ''}</td>`;
    }

    renderActions(row) {
        const actions = this.options.actions.map(action => {
            const disabled = action.disabled && action.disabled(row) ? 'disabled' : '';
            return `<button class="btn btn-sm action-btn ${action.class || ''}" 
                           data-action="${action.name}" 
                           data-id="${row.id || ''}" 
                           ${disabled}
                           title="${action.title || action.name}">
                        ${action.icon || action.name}
                    </button>`;
        }).join('');
        
        return `<td class="actions">${actions}</td>`;
    }

    renderPagination() {
        const totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
        
        if (totalPages <= 1) return '';
        
        return `
            <nav class="datatable-pagination">
                <ul class="pagination">
                    <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="prev">Anterior</a>
                    </li>
                    ${this.renderPageNumbers(totalPages)}
                    <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="next">Siguiente</a>
                    </li>
                </ul>
                <div class="pagination-info">
                    Mostrando ${((this.currentPage - 1) * this.options.pageSize) + 1} a 
                    ${Math.min(this.currentPage * this.options.pageSize, this.filteredData.length)} 
                    de ${this.filteredData.length} registros
                </div>
            </nav>
        `;
    }

    renderPageNumbers(totalPages) {
        const pages = [];
        const maxVisible = 5;
        
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            pages.push(`
                <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }
        
        return pages.join('');
    }

    bindEvents() {
        // Búsqueda
        const searchInput = this.container.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', DOMUtils.debounce((e) => {
                this.searchTerm = e.target.value;
                this.currentPage = 1;
                this.render();
            }, 300));
        }

        // Limpiar búsqueda
        const searchClear = this.container.querySelector('.search-clear');
        if (searchClear) {
            searchClear.addEventListener('click', () => {
                this.searchTerm = '';
                this.currentPage = 1;
                this.render();
            });
        }

        // Ordenamiento
        this.container.addEventListener('click', (e) => {
            if (e.target.classList.contains('sortable') || e.target.closest('.sortable')) {
                const th = e.target.closest('th');
                const key = th.dataset.key;
                
                if (this.sortColumn === key) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortColumn = key;
                    this.sortDirection = 'asc';
                }
                
                this.render();
            }
        });

        // Paginación
        this.container.addEventListener('click', (e) => {
            if (e.target.classList.contains('page-link')) {
                e.preventDefault();
                
                const page = e.target.dataset.page;
                const totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
                
                if (page === 'prev' && this.currentPage > 1) {
                    this.currentPage--;
                } else if (page === 'next' && this.currentPage < totalPages) {
                    this.currentPage++;
                } else if (!isNaN(page)) {
                    this.currentPage = parseInt(page);
                }
                
                this.render();
            }
        });

        // Acciones
        this.container.addEventListener('click', (e) => {
            if (e.target.classList.contains('action-btn')) {
                const action = e.target.dataset.action;
                const id = e.target.dataset.id;
                const row = this.options.data.find(r => r.id == id);
                
                const actionConfig = this.options.actions.find(a => a.name === action);
                if (actionConfig && actionConfig.handler) {
                    actionConfig.handler(row, id);
                }
            }
        });

        // Selección
        this.container.addEventListener('change', (e) => {
            if (e.target.classList.contains('select-all')) {
                const checkboxes = this.container.querySelectorAll('.row-select');
                checkboxes.forEach(cb => cb.checked = e.target.checked);
            }
        });
    }

    applyFilters() {
        this.filteredData = this.options.data.slice();
        
        // Aplicar búsqueda
        if (this.searchTerm) {
            this.filteredData = this.filteredData.filter(row => {
                return this.options.columns.some(col => {
                    const value = row[col.key];
                    return value && value.toString().toLowerCase().includes(this.searchTerm.toLowerCase());
                });
            });
        }
        
        // Aplicar filtros adicionales
        Object.keys(this.filters).forEach(key => {
            const filterValue = this.filters[key];
            if (filterValue) {
                this.filteredData = this.filteredData.filter(row => {
                    return row[key] === filterValue;
                });
            }
        });
        
        // Aplicar ordenamiento
        if (this.sortColumn) {
            this.filteredData.sort((a, b) => {
                const aValue = a[this.sortColumn];
                const bValue = b[this.sortColumn];
                
                if (aValue < bValue) return this.sortDirection === 'asc' ? -1 : 1;
                if (aValue > bValue) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        }
    }

    updateData(newData) {
        this.options.data = newData;
        this.currentPage = 1;
        this.render();
    }

    getSelectedRows() {
        const checkboxes = this.container.querySelectorAll('.row-select:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    setFilter(key, value) {
        this.filters[key] = value;
        this.currentPage = 1;
        this.render();
    }

    clearFilters() {
        this.filters = {};
        this.searchTerm = '';
        this.currentPage = 1;
        this.render();
    }
}

// Instancias globales
const toastManager = new ToastManager();
const modalManager = new ModalManager();
const loadingManager = new LoadingManager();

// Funciones globales para fácil acceso
window.showToast = (message, type, duration) => toastManager.show(message, type, duration);
window.showModal = (modal) => modalManager.show(modal);
window.hideModal = () => modalManager.hide();
window.createModal = (options) => modalManager.create(options);
window.showLoading = (message, target) => loadingManager.show(message, target);
window.hideLoading = (id) => loadingManager.hide(id);
window.hideAllLoading = () => loadingManager.hideAll();

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        ToastManager,
        ModalManager,
        LoadingManager,
        DataTable,
        toastManager,
        modalManager,
        loadingManager
    };
}
