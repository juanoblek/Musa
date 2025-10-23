// Configuración de envíos
const SHIPPING_CONFIG = {
    departamentos: {
        "cundinamarca": ["Madrid", "Zipaquirá", "Chía", "Soacha", "Funza", "Mosquera", "Facatativá"],
        "antioquia": ["Medellín", "Bello", "Envigado", "Itagüí", "Rionegro", "Sabaneta"],
        "bogota": ["Bogotá D.C."],
        "valle": ["Cali", "Palmira", "Buenaventura", "Yumbo", "Jamundí", "Tuluá", "Buga"],
        "atlantico": ["Barranquilla", "Soledad", "Malambo"],
        "santander": ["Bucaramanga", "Floridablanca", "Girón", "Piedecuesta"],
        "bolivar": ["Cartagena", "Magangué", "Turbaco"],
        "norte_santander": ["Cúcuta", "Los Patios", "Villa del Rosario"],
        "caldas": ["Manizales", "Chinchiná", "La Dorada"],
        "risaralda": ["Pereira", "Dosquebradas", "Santa Rosa de Cabal"],
        "quindio": ["Armenia", "Calarcá", "La Tebaida"],
        "tolima": ["Ibagué", "Espinal", "Melgar"],
        "huila": ["Neiva", "Pitalito", "Garzón"],
        "cauca": ["Popayán", "Santander de Quilichao", "Puerto Tejada"],
        "nariño": ["Pasto", "Ipiales", "Tumaco"],
        "boyaca": ["Tunja", "Duitama", "Sogamoso"],
        "meta": ["Villavicencio", "Acacías", "Granada"]
    },
    costoEnvio: {
        "bogota": 0,
        "cundinamarca": 0,
        "default": 0 // ENVÍO SIEMPRE GRATIS
    },
    tiempoEntrega: {
        "bogota": "1-2 días hábiles",
        "cundinamarca": "2-3 días hábiles",
        "default": "3-5 días hábiles"
    }
};

export default SHIPPING_CONFIG;
