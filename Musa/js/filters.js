
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-button');
    const products = document.querySelectorAll('.filter');

    // Mostrar todos los productos inicialmente
    products.forEach(product => product.style.display = 'block');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Actualizar botones activos
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filtrar productos
            products.forEach(product => {
                if (filter === 'all') {
                    product.style.display = 'block';
                } else {
                    product.style.display = product.classList.contains(filter) ? 'block' : 'none';
                }
            });
        });
    });
});
