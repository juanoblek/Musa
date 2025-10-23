// Mock API para productos - Simulación completa de base de datos
class MockAPI {
    constructor() {
        // Datos de productos simulando la base de datos
        this.productos = [
            {
                id: 1,
                nombre: "Chaqueta Deportiva Premium",
                descripcion: "Chaqueta deportiva de alta calidad perfecta para entrenamientos y actividades al aire libre",
                precio: 89.99,
                precio_oferta: 69.99,
                stock: 15,
                categoria: "Deportiva",
                categoria_id: 1,
                imagen_principal: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=400&fit=crop",
                imagenes: [
                    "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=400&fit=crop",
                    "https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=400&h=400&fit=crop"
                ],
                destacado: true,
                en_oferta: true,
                activo: true,
                variantes: [
                    { talla: "S", color: "Negro", stock: 5 },
                    { talla: "M", color: "Negro", stock: 5 },
                    { talla: "L", color: "Negro", stock: 5 }
                ],
                badges: [
                    { tipo: 'oferta', texto: 'Oferta' },
                    { tipo: 'destacado', texto: 'Destacado' }
                ]
            },
            {
                id: 2,
                nombre: "Pantalón Casual Elegant",
                descripcion: "Pantalón casual perfecto para el día a día, cómodo y con estilo moderno",
                precio: 65.50,
                precio_oferta: 49.99,
                stock: 20,
                categoria: "Casual",
                categoria_id: 2,
                imagen_principal: "https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=400&h=400&fit=crop",
                imagenes: [
                    "https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=400&h=400&fit=crop"
                ],
                destacado: false,
                en_oferta: true,
                activo: true,
                variantes: [
                    { talla: "28", color: "Azul Marino", stock: 7 },
                    { talla: "30", color: "Azul Marino", stock: 8 },
                    { talla: "32", color: "Azul Marino", stock: 5 }
                ],
                badges: [
                    { tipo: 'oferta', texto: 'Oferta' }
                ]
            },
            {
                id: 3,
                nombre: "Vestido de Noche Elegante",
                descripcion: "Vestido elegante perfecto para ocasiones especiales y eventos formales",
                precio: 120.00,
                precio_oferta: null,
                stock: 8,
                categoria: "Formal",
                categoria_id: 3,
                imagen_principal: "https://images.unsplash.com/photo-1566479179817-c6df57b6a9c1?w=400&h=400&fit=crop",
                imagenes: [
                    "https://images.unsplash.com/photo-1566479179817-c6df57b6a9c1?w=400&h=400&fit=crop",
                    "https://images.unsplash.com/photo-1582633423892-69c724baff70?w=400&h=400&fit=crop"
                ],
                destacado: true,
                en_oferta: false,
                activo: true,
                variantes: [
                    { talla: "XS", color: "Negro", stock: 2 },
                    { talla: "S", color: "Negro", stock: 3 },
                    { talla: "M", color: "Negro", stock: 3 }
                ],
                badges: [
                    { tipo: 'destacado', texto: 'Destacado' },
                    { tipo: 'stock-bajo', texto: 'Pocas unidades' }
                ]
            },
            {
                id: 4,
                nombre: "Camiseta Básica Cotton",
                descripcion: "Camiseta básica de algodón 100%, suave y cómoda para uso diario",
                precio: 25.99,
                precio_oferta: 19.99,
                stock: 30,
                categoria: "Básicos",
                categoria_id: 4,
                imagen_principal: "https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop",
                imagenes: [
                    "https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop"
                ],
                destacado: false,
                en_oferta: true,
                activo: true,
                variantes: [
                    { talla: "S", color: "Blanco", stock: 10 },
                    { talla: "M", color: "Blanco", stock: 10 },
                    { talla: "L", color: "Blanco", stock: 10 }
                ],
                badges: [
                    { tipo: 'oferta', texto: 'Oferta' }
                ]
            },
            {
                id: 5,
                nombre: "Jeans Skinny Fit",
                descripcion: "Jeans de corte skinny con diseño moderno y cómodo ajuste",
                precio: 75.00,
                precio_oferta: null,
                stock: 18,
                categoria: "Denim",
                categoria_id: 5,
                imagen_principal: "https://images.unsplash.com/photo-1542272604-787c3835535d?w=400&h=400&fit=crop",
                imagenes: [
                    "https://images.unsplash.com/photo-1542272604-787c3835535d?w=400&h=400&fit=crop"
                ],
                destacado: false,
                en_oferta: false,
                activo: true,
                variantes: [
                    { talla: "26", color: "Azul Índigo", stock: 6 },
                    { talla: "28", color: "Azul Índigo", stock: 6 },
                    { talla: "30", color: "Azul Índigo", stock: 6 }
                ],
                badges: []
            },
            {
                id: 6,
                nombre: "Blazer Ejecutivo",
                descripcion: "Blazer elegante para looks profesionales y formales",
                precio: 135.00,
                precio_oferta: null,
                stock: 12,
                categoria: "Formal",
                categoria_id: 3,
                imagen_principal: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop",
                imagenes: [
                    "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop"
                ],
                destacado: true,
                en_oferta: false,
                activo: true,
                variantes: [
                    { talla: "S", color: "Negro", stock: 4 },
                    { talla: "M", color: "Negro", stock: 4 },
                    { talla: "L", color: "Negro", stock: 4 }
                ],
                badges: [
                    { tipo: 'destacado', texto: 'Destacado' }
                ]
            }
        ];

        this.categorias = [
            { id: 1, nombre: "Deportiva", slug: "deportiva", descripcion: "Ropa deportiva y activewear", activo: true },
            { id: 2, nombre: "Casual", slug: "casual", descripcion: "Ropa casual para el día a día", activo: true },
            { id: 3, nombre: "Formal", slug: "formal", descripcion: "Ropa formal y elegante", activo: true },
            { id: 4, nombre: "Básicos", slug: "basicos", descripcion: "Prendas básicas esenciales", activo: true },
            { id: 5, nombre: "Denim", slug: "denim", descripcion: "Jeans y ropa de mezclilla", activo: true }
        ];
    }

    // Simular llamada a API de productos
    async getProductos(params = {}) {
        return new Promise((resolve) => {
            setTimeout(() => {
                let productos = [...this.productos];
                
                // Filtrar por categoría
                if (params.categoria) {
                    productos = productos.filter(p => p.categoria_id == params.categoria);
                }
                
                // Filtrar destacados
                if (params.destacados) {
                    productos = productos.filter(p => p.destacado);
                }
                
                // Filtrar ofertas
                if (params.ofertas) {
                    productos = productos.filter(p => p.en_oferta);
                }
                
                // Limitar resultados
                if (params.limite) {
                    productos = productos.slice(0, parseInt(params.limite));
                }
                
                console.log(`🎯 MockAPI: Enviando ${productos.length} productos`);
                
                resolve({
                    productos: productos,
                    categorias: this.categorias,
                    total: productos.length
                });
            }, 300); // Simular delay de red
        });
    }

    // Simular llamada a API de categorías
    async getCategorias() {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log(`🏷️ MockAPI: Enviando ${this.categorias.length} categorías`);
                resolve(this.categorias);
            }, 200);
        });
    }
}

// Crear instancia global
window.mockAPI = new MockAPI();

console.log('✅ Mock API inicializada con', window.mockAPI.productos.length, 'productos y', window.mockAPI.categorias.length, 'categorías');
