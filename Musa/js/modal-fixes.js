/**
 * MODAL FIXES - Correcciones específicas para modales problemáticos
 * Se ejecuta después del Modal Responsive Manager para aplicar fixes específicos
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Aplicando fixes específicos para modales...');
    
    // Fix para ProductViewModal - Asegurar que las imágenes sean responsive
    const productModal = document.getElementById('ProductViewModal');
    if (productModal) {
        // Fix para header layout
        const fixHeaderLayout = () => {
            const header = productModal.querySelector('.modal-header');
            if (header) {
                // Asegurar que el flexbox funcione correctamente
                header.style.display = 'flex';
                header.style.justifyContent = 'space-between';
                header.style.alignItems = 'center';
                
                // Verificar que el botón de cerrar esté posicionado correctamente
                const closeBtn = header.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.style.position = 'relative';
                    closeBtn.style.zIndex = '10';
                    closeBtn.style.flexShrink = '0';
                }
                
                // Verificar que el contenido del título tenga el espacio correcto
                const titleContainer = header.querySelector('.d-flex');
                if (titleContainer) {
                    titleContainer.style.flex = '1';
                    titleContainer.style.marginRight = '1rem';
                }
            }
        };
        
        const images = productModal.querySelectorAll('img');
        images.forEach(img => {
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
        });
        
        // Fix para la lupa de imágenes en móvil
        const setupMobileMagnifier = () => {
            if (window.innerWidth < 768) {
                const magnifierContainer = productModal.querySelector('.magnifier-container');
                if (magnifierContainer) {
                    magnifierContainer.style.display = 'none';
                }
            }
        };
        
        // Aplicar fixes cuando el modal se muestra
        productModal.addEventListener('shown.bs.modal', () => {
            fixHeaderLayout();
            setupMobileMagnifier();
        });
        
        // Aplicar fixes en resize
        window.addEventListener('resize', () => {
            fixHeaderLayout();
            setupMobileMagnifier();
        });
        
        // Aplicar fix inicial si el modal ya está visible
        if (productModal.classList.contains('show')) {
            fixHeaderLayout();
        }
    }
    
    // Fix para CartModal - Layout responsive del carrito
    const cartModal = document.getElementById('CartModal');
    if (cartModal) {
        const fixCartLayout = () => {
            const row = cartModal.querySelector('.modal-body .row');
            if (row && window.innerWidth < 992) {
                row.classList.remove('g-0');
                row.classList.add('g-3');
                
                const cols = row.querySelectorAll('[class*="col-"]');
                cols.forEach(col => {
                    col.classList.remove('col-lg-8', 'col-lg-4');
                    col.classList.add('col-12');
                });
            }
        };
        
        cartModal.addEventListener('shown.bs.modal', fixCartLayout);
        window.addEventListener('resize', fixCartLayout);
    }
    
    // Fix para PaymentModal - Formularios responsive
    const paymentModal = document.getElementById('PaymentModal');
    if (paymentModal) {
        const fixPaymentForm = () => {
            const formGroups = paymentModal.querySelectorAll('.row .col-md-6');
            if (window.innerWidth < 768) {
                formGroups.forEach(group => {
                    group.classList.remove('col-md-6');
                    group.classList.add('col-12');
                });
            }
        };
        
        paymentModal.addEventListener('shown.bs.modal', fixPaymentForm);
        window.addEventListener('resize', fixPaymentForm);
    }
    
    // Fix para AdminPanelModal - Tabla responsive
    const adminModal = document.getElementById('AdminPanelModal');
    if (adminModal) {
        const fixAdminTable = () => {
            const tables = adminModal.querySelectorAll('table');
            tables.forEach(table => {
                if (!table.parentElement.classList.contains('table-responsive')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'table-responsive';
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                }
            });
        };
        
        adminModal.addEventListener('shown.bs.modal', fixAdminTable);
    }
    
    // Fix general para todos los modales - Prevenir body scroll
    document.addEventListener('show.bs.modal', function(e) {
        document.body.style.overflow = 'hidden';
    });
    
    document.addEventListener('hidden.bs.modal', function(e) {
        // Solo restaurar scroll si no hay otros modales abiertos
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length === 0) {
            document.body.style.overflow = '';
        }
    });
    
    // Fix para touch devices - Mejorar la experiencia táctil
    if ('ontouchstart' in window) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.webkitOverflowScrolling = 'touch';
        });
    }
    
    // Fix para iOS Safari - Viewport height issues
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    if (isIOS) {
        const fixIOSViewport = () => {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
            
            const modals = document.querySelectorAll('.modal-dialog');
            modals.forEach(modal => {
                modal.style.maxHeight = 'calc(var(--vh, 1vh) * 90)';
            });
        };
        
        window.addEventListener('resize', fixIOSViewport);
        fixIOSViewport();
    }
    
    console.log('✅ Fixes específicos para modales aplicados');
});

// Función utility para refrescar todos los fixes
window.refreshModalFixes = function() {
    console.log('🔄 Refrescando fixes de modales...');
    
    // Disparar evento resize para reactivar todos los listeners
    window.dispatchEvent(new Event('resize'));
    
    // Si existe el modal manager, refrescarlo también
    if (window.modalManager) {
        window.modalManager.refresh();
    }
};