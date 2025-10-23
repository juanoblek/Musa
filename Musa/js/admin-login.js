// Admin Login Module - Ultra Simple Implementation
console.log('🔧 Admin Login Module Loading...');

// Ultra simple admin login function
window.adminLogin = function() {
    console.log('🔑 Admin Login Function Called');
    
    const username = document.getElementById('adminUsername')?.value?.trim();
    const password = document.getElementById('adminPassword')?.value?.trim();
    
    console.log('📝 Username:', username);
    console.log('📝 Password:', password);
    
    // Validate
    if (!username || !password) {
        alert('Por favor, ingresa usuario y contraseña.');
        return;
    }
    
    if (username === 'admin' && password === 'musa2024') {
        console.log('✅ Credentials valid');
        console.log('🎯 REDIRECTING NOW...');
        
        // Stop all events
        event?.preventDefault?.();
        event?.stopPropagation?.();
        
        // Force redirect immediately
        document.location = 'admin-panel.html';
        
    } else {
        alert('Credenciales incorrectas');
    }
};

// Also create backup functions
window.doLogin = window.adminLogin;
window.forceLogin = window.adminLogin;

console.log('✅ Admin Login Functions Ready');
