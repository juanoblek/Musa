/**
 * MODAL RESPONSIVE MANAGER
 * Sistema profesional para gestión de modales responsive
 * Implementa mejores prácticas de UX/UI y accesibilidad
 */

class ModalResponsiveManager {
    constructor() {
        this.init();
        this.setupEventListeners();
    }

    init() {
        console.log('🎭 Modal Responsive Manager - Inicializando...');
        this.adjustModalSizes();
        this.setupAccessibility();
        this.setupScrollManagement();
    }

    setupEventListeners() {
        // Reajustar modales cuando cambie el tamaño de ventana
        window.addEventListener('resize', this.debounce(() => {
            this.adjustModalSizes();
        }, 250));

        // Gestionar apertura/cierre de modales
        document.addEventListener('show.bs.modal', (e) => {
            this.onModalShow(e);
        });

        document.addEventListener('shown.bs.modal', (e) => {
            this.onModalShown(e);
        });

        document.addEventListener('hide.bs.modal', (e) => {
            this.onModalHide(e);
        });
    }

    adjustModalSizes() {
        const modals = document.querySelectorAll('.modal');
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        modals.forEach(modal => {
            const modalDialog = modal.querySelector('.modal-dialog');
            if (!modalDialog) return;

            // Ajustar según el tamaño de pantalla
            if (viewportWidth < 576) {
                this.applyMobileLayout(modalDialog, modal);
            } else if (viewportWidth < 768) {
                this.applyTabletLayout(modalDialog, modal);
            } else {
                this.applyDesktopLayout(modalDialog, modal);
            }

            // Ajustar altura máxima
            this.adjustModalHeight(modal, viewportHeight);
        });
    }

    applyMobileLayout(modalDialog, modal) {
        // Forzar ancho completo en móvil
        modalDialog.style.margin = '0.25rem';
        modalDialog.style.maxWidth = 'calc(100vw - 0.5rem)';
        modalDialog.style.width = 'calc(100vw - 0.5rem)';

        // Ajustar padding del contenido
        const modalBody = modal.querySelector('.modal-body');
        if (modalBody) {
            modalBody.style.padding = '1rem';
        }

        // Ajustar header
        const modalHeader = modal.querySelector('.modal-header');
        if (modalHeader) {
            modalHeader.style.padding = '1rem 1rem 0.5rem 1rem';
        }

        // Ajustar footer
        const modalFooter = modal.querySelector('.modal-footer');
        if (modalFooter) {
            modalFooter.style.padding = '0.75rem 1rem';
            modalFooter.style.flexDirection = 'column';
            modalFooter.style.gap = '0.5rem';
        }
    }

    applyTabletLayout(modalDialog, modal) {
        modalDialog.style.margin = '1rem auto';
        modalDialog.style.maxWidth = '540px';
        modalDialog.style.width = 'auto';

        // Restaurar padding normal
        this.resetModalPadding(modal);
    }

    applyDesktopLayout(modalDialog, modal) {
        modalDialog.style.margin = '2rem auto';
        modalDialog.style.width = 'auto';

        // Ajustar tamaños específicos para desktop
        if (modalDialog.classList.contains('modal-xl')) {
            modalDialog.style.maxWidth = window.innerWidth > 1200 ? '1140px' : '95vw';
        } else if (modalDialog.classList.contains('modal-lg')) {
            modalDialog.style.maxWidth = '900px';
        }

        // Restaurar padding normal
        this.resetModalPadding(modal);
    }

    resetModalPadding(modal) {
        const modalBody = modal.querySelector('.modal-body');
        const modalHeader = modal.querySelector('.modal-header');
        const modalFooter = modal.querySelector('.modal-footer');

        if (modalBody) modalBody.style.padding = '';
        if (modalHeader) modalHeader.style.padding = '';
        if (modalFooter) {
            modalFooter.style.padding = '';
            modalFooter.style.flexDirection = '';
            modalFooter.style.gap = '';
        }
    }

    adjustModalHeight(modal, viewportHeight) {
        const modalBody = modal.querySelector('.modal-body');
        if (!modalBody) return;

        const modalHeader = modal.querySelector('.modal-header');
        const modalFooter = modal.querySelector('.modal-footer');

        let reservedHeight = 100; // Margen base
        if (modalHeader) reservedHeight += modalHeader.offsetHeight;
        if (modalFooter) reservedHeight += modalFooter.offsetHeight;

        const maxHeight = viewportHeight - reservedHeight;
        modalBody.style.maxHeight = `${maxHeight}px`;
        modalBody.style.overflowY = 'auto';
    }

    setupAccessibility() {
        // Mejorar accesibilidad de los modales
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            // Asegurar que tenga role="dialog"
            modal.setAttribute('role', 'dialog');
            
            // Mejorar navegación con teclado
            modal.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const closeButton = modal.querySelector('[data-bs-dismiss="modal"]');
                    if (closeButton) closeButton.click();
                }
            });
        });
    }

    setupScrollManagement() {
        // Gestionar scroll del body cuando se abren modales
        document.addEventListener('show.bs.modal', () => {
            document.body.style.paddingRight = this.getScrollbarWidth() + 'px';
        });

        document.addEventListener('hidden.bs.modal', () => {
            document.body.style.paddingRight = '';
        });
    }

    onModalShow(e) {
        const modal = e.target;
        console.log(`🎭 Abriendo modal: ${modal.id}`);
        
        // Aplicar ajustes específicos según el tipo de modal
        this.applyModalSpecificAdjustments(modal);
    }

    onModalShown(e) {
        const modal = e.target;
        
        // Enfocar el primer elemento interactivo
        const firstInput = modal.querySelector('input, select, textarea, button');
        if (firstInput && !firstInput.hasAttribute('data-no-autofocus')) {
            setTimeout(() => firstInput.focus(), 100);
        }

        // Reajustar tamaños después de que el modal esté completamente visible
        this.adjustModalSizes();
    }

    onModalHide(e) {
        const modal = e.target;
        console.log(`🎭 Cerrando modal: ${modal.id}`);
    }

    applyModalSpecificAdjustments(modal) {
        const modalId = modal.id;

        switch (modalId) {
            case 'ProductViewModal':
                this.adjustProductModal(modal);
                break;
            case 'CartModal':
                this.adjustCartModal(modal);
                break;
            case 'PaymentModal':
                this.adjustPaymentModal(modal);
                break;
            case 'AdminPanelModal':
            case 'ProductFormModal':
                this.adjustAdminModal(modal);
                break;
        }
    }

    adjustProductModal(modal) {
        if (window.innerWidth < 768) {
            const rows = modal.querySelectorAll('.row');
            rows.forEach(row => {
                row.style.flexDirection = 'column';
            });
        }
    }

    adjustCartModal(modal) {
        if (window.innerWidth < 992) {
            const cartColumns = modal.querySelectorAll('.col-lg-8, .col-lg-4');
            cartColumns.forEach(col => {
                col.style.maxWidth = '100%';
                col.style.flex = '0 0 100%';
            });
        }
    }

    adjustPaymentModal(modal) {
        // Ajustes específicos para el modal de pago
        const formControls = modal.querySelectorAll('.form-control, .form-select');
        if (window.innerWidth < 576) {
            formControls.forEach(control => {
                control.style.fontSize = '16px'; // Previene zoom en iOS
            });
        }
    }

    adjustAdminModal(modal) {
        // Los modales de admin necesitan más espacio
        const modalDialog = modal.querySelector('.modal-dialog');
        if (modalDialog && window.innerWidth > 768) {
            modalDialog.style.maxWidth = '95vw';
        }
    }

    getScrollbarWidth() {
        const scrollDiv = document.createElement('div');
        scrollDiv.style.width = '100px';
        scrollDiv.style.height = '100px';
        scrollDiv.style.overflow = 'scroll';
        scrollDiv.style.position = 'absolute';
        scrollDiv.style.top = '-9999px';
        
        document.body.appendChild(scrollDiv);
        const scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
        document.body.removeChild(scrollDiv);
        
        return scrollbarWidth;
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Método público para reajustar manualmente
    refresh() {
        this.adjustModalSizes();
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.modalManager = new ModalResponsiveManager();
    console.log('✅ Modal Responsive Manager inicializado');
});

// Exportar para uso global
window.ModalResponsiveManager = ModalResponsiveManager;