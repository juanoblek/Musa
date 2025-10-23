// Configuración global del sitio
const CONFIG = {
    // Bold.co configuration
    BOLD: {
        API_KEY: 'TU_API_KEY_LIVE',
        CLIENT_ID: 'TU_CLIENT_ID',
        WEBHOOK_SECRET: 'whsec_XXXX',
        ENVIRONMENT: 'production',
        REDIRECTION_URL: window.location.origin + '/orden-completada'
    },
    
    // Configuración de envíos    
    SHIPPING: {
        FREE_SHIPPING_MIN: 0, // Free shipping threshold (always free)
        STANDARD_SHIPPING: 0, // ENVÍO SIEMPRE GRATIS
        departamentos: {
            "bogota": ["Bogotá D.C."],
            "cundinamarca": ["Madrid", "Zipaquirá", "Chía", "Soacha", "Funza", "Mosquera", "Facatativá"],
            "antioquia": ["Medellín", "Bello", "Envigado", "Itagüí", "Rionegro", "Sabaneta"],
            "valle": ["Cali", "Palmira", "Buenaventura", "Yumbo", "Jamundí", "Tuluá", "Buga"],
            "atlantico": ["Barranquilla", "Soledad", "Malambo"],
            "santander": ["Bucaramanga", "Floridablanca", "Girón", "Piedecuesta"],
            "bolivar": ["Cartagena", "Magangué", "Turbaco"]
        },
        costoEnvio: {
            "bogota": 0,
            "cundinamarca": 0,
            "valle": 0,
            "antioquia": 0,
            "atlantico": 0,
            "default": 0 // ENVÍO SIEMPRE GRATIS
        },
        tiempoEntrega: {
            "bogota": "1-2 días hábiles",
            "cundinamarca": "2-3 días hábiles",
            "valle": "3-4 días hábiles",
            "antioquia": "3-4 días hábiles",
            "atlantico": "4-5 días hábiles",
            "default": "4-6 días hábiles"
        }
    },
    
    // Configuración de impuestos
    TAX_RATE: 0 // Sin impuestos - 0% tax rate
};

// Make CONFIG globally accessible for non-module scripts
if (typeof window !== 'undefined') {
    window.CONFIG = CONFIG;
    window.COLOMBIA_CONFIG = {
        departamentos: CONFIG.SHIPPING.departamentos
    };
}

export default CONFIG;
export const COLOMBIA_CONFIG = {
    departamentos: CONFIG.SHIPPING.departamentos
};
