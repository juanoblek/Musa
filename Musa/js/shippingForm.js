import CONFIG from './config.js';

export class ShippingForm {
    constructor() {
        console.log('Inicializando ShippingForm...');
        
        // Elementos del formulario
        this.departamentoSelect = document.getElementById('departamento');
        this.ciudadSelect = document.getElementById('ciudad');
        this.form = document.querySelector('.needs-validation');
        this.shippingInfoEl = document.getElementById('tiempoEntrega');
        
        // Inicializar el formulario
        this.init();
        this.setupEventListeners();
    }

    init() {
        try {
            console.log('Inicializando formulario de envío...');
            
            // Verificar elementos requeridos
            if (!this.departamentoSelect) {
                throw new Error('No se encontró el elemento select de departamentos (#departamento)');
            }
            if (!this.ciudadSelect) {
                throw new Error('No se encontró el elemento select de ciudades (#ciudad)');
            }
            
            // Cargar departamentos alfabéticamente
            const departamentos = Object.keys(CONFIG.SHIPPING.departamentos).sort((a, b) => {
                return this.formatDepartamento(a).localeCompare(this.formatDepartamento(b));
            });
            
            // Poblar el select de departamentos
            this.populateDepartamentos(departamentos);
            
            // Deshabilitar el select de ciudades inicialmente
            this.ciudadSelect.disabled = true;
            
            // Actualizar costos iniciales
            this.updateShippingInfo();
            
            console.log('Formulario de envío inicializado correctamente');
        } catch (error) {
            console.error('Error al inicializar el formulario:', error);
            this.showError('Error al cargar el formulario de envío: ' + error.message);
        }
    }

    populateDepartamentos(departamentos) {
        console.log('Poblando departamentos:', departamentos);
        this.departamentoSelect.innerHTML = '<option value="">Selecciona un departamento</option>';
        departamentos.forEach(dep => {
            const option = document.createElement('option');
            option.value = dep;
            option.textContent = this.formatDepartamento(dep);
            this.departamentoSelect.appendChild(option);
        });
    }

    formatDepartamento(dep) {
        return dep
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    updateCiudades(departamento) {
        console.log('Actualizando ciudades para departamento:', departamento);
        this.ciudadSelect.innerHTML = '<option value="">Selecciona una ciudad</option>';
        
        if (!departamento) {
            this.ciudadSelect.disabled = true;
            this.ciudadSelect.style.opacity = '0.5';
            return;
        }

        try {
            const ciudades = CONFIG.SHIPPING.departamentos[departamento]?.sort() || [];
            console.log('Ciudades encontradas:', ciudades);
            
            if (ciudades.length === 0) {
                console.error('No se encontraron ciudades para el departamento:', departamento);
                this.ciudadSelect.innerHTML = '<option value="">No hay ciudades disponibles</option>';
                this.ciudadSelect.disabled = true;
                return;
            }

            // Ocultar mientras se actualiza
            this.ciudadSelect.style.opacity = '0';
            this.ciudadSelect.disabled = false;

            ciudades.forEach(ciudad => {
                const option = document.createElement('option');
                option.value = ciudad;
                option.textContent = ciudad;
                this.ciudadSelect.appendChild(option);
            });

            // Mostrar con animación
            setTimeout(() => {
                this.ciudadSelect.style.transition = 'opacity 0.3s ease';
                this.ciudadSelect.style.opacity = '1';
            }, 50);

        } catch (error) {
            console.error('Error al cargar las ciudades:', error);
            this.ciudadSelect.innerHTML = '<option value="">Error al cargar ciudades</option>';
            this.ciudadSelect.disabled = true;
        }
    }

    updateShippingInfo(departamento = '') {
        const costoEnvio = CONFIG.SHIPPING.costoEnvio[departamento] || CONFIG.SHIPPING.costoEnvio.default;
        const tiempoEntrega = CONFIG.SHIPPING.tiempoEntrega[departamento] || CONFIG.SHIPPING.tiempoEntrega.default;

        // Actualizar elementos en el DOM
        document.querySelectorAll('[data-envio]').forEach(el => {
            el.textContent = new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(costoEnvio);
        });

        // Actualizar tiempo de entrega
        if (this.shippingInfoEl) {
            this.shippingInfoEl.textContent = tiempoEntrega;
        }
    }

    validateForm() {
        const departamento = this.departamentoSelect.value;
        const ciudad = this.ciudadSelect.value;
        let isValid = true;

        // Validar departamento
        if (!departamento) {
            this.departamentoSelect.classList.add('is-invalid');
            this.departamentoSelect.classList.remove('is-valid');
            isValid = false;
        } else {
            this.departamentoSelect.classList.add('is-valid');
            this.departamentoSelect.classList.remove('is-invalid');
        }

        // Validar ciudad
        if (!ciudad) {
            this.ciudadSelect.classList.add('is-invalid');
            this.ciudadSelect.classList.remove('is-valid');
            isValid = false;
        } else {
            this.ciudadSelect.classList.add('is-valid');
            this.ciudadSelect.classList.remove('is-invalid');
        }

        return { isValid, departamento, ciudad };
    }

    showError(message = 'Por favor selecciona un departamento y una ciudad.') {
        const errorMessage = document.createElement('div');
        errorMessage.className = 'alert alert-danger mt-3';
        errorMessage.textContent = message;
        
        // Remover mensaje de error anterior si existe
        const existingError = this.form?.querySelector('.alert-danger');
        if (existingError) {
            existingError.remove();
        }
        
        if (this.form) {
            this.form.appendChild(errorMessage);
            
            // Scroll al primer error
            const firstInvalid = this.form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    logFormState(event = null) {
        console.group('Estado del Formulario de Envío');
        if (event) {
            console.log('Evento:', event.type);
        }
        console.log('Departamento seleccionado:', this.departamentoSelect?.value);
        console.log('Ciudad seleccionada:', this.ciudadSelect?.value);
        console.log('Departamento válido:', this.departamentoSelect?.classList.contains('is-valid'));
        console.log('Ciudad válida:', this.ciudadSelect?.classList.contains('is-valid'));
        console.log('Formulario válido:', this.form?.checkValidity());
        console.groupEnd();
    }

    setupEventListeners() {
        console.log('Configurando event listeners...');
        
        // Manejar cambio de departamento
        if (this.departamentoSelect) {
            this.departamentoSelect.addEventListener('change', (e) => {
                console.log('Cambio en departamento:', e.target.value);
                const departamento = e.target.value;
                this.updateCiudades(departamento);
                this.updateShippingInfo(departamento);
                this.logFormState(e);

                if (departamento) {
                    this.departamentoSelect.classList.add('is-valid');
                    this.departamentoSelect.classList.remove('is-invalid');
                    this.ciudadSelect.classList.remove('is-valid', 'is-invalid');
                } else {
                    this.departamentoSelect.classList.remove('is-valid');
                    this.departamentoSelect.classList.add('is-invalid');
                    this.ciudadSelect.disabled = true;
                }
            });
        }

        // Manejar cambio de ciudad
        if (this.ciudadSelect) {
            this.ciudadSelect.addEventListener('change', (e) => {
                console.log('Cambio en ciudad:', e.target.value);
                const ciudad = e.target.value;
                if (ciudad) {
                    this.ciudadSelect.classList.add('is-valid');
                    this.ciudadSelect.classList.remove('is-invalid');
                } else {
                    this.ciudadSelect.classList.remove('is-valid');
                    this.ciudadSelect.classList.add('is-invalid');
                }
                this.logFormState(e);
            });
        }

        // Manejar envío del formulario
        if (this.form) {
            console.log('Configurando validación del formulario');
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                console.log('Formulario enviado, validando...');
                
                const { isValid, departamento, ciudad } = this.validateForm();

                if (!isValid) {
                    console.log('Formulario inválido');
                    this.showError();
                    return;
                }

                console.log('Formulario válido, emitiendo evento shippingFormValid');
                // Si todo está válido, disparar evento personalizado
                const event = new CustomEvent('shippingFormValid', {
                    detail: {
                        departamento,
                        ciudad,
                        costoEnvio: CONFIG.SHIPPING.costoEnvio[departamento] || CONFIG.SHIPPING.costoEnvio.default,
                        tiempoEntrega: CONFIG.SHIPPING.tiempoEntrega[departamento] || CONFIG.SHIPPING.tiempoEntrega.default
                    }
                });
                document.dispatchEvent(event);
                this.logFormState(e);
            });
        }
    }
}

export default ShippingForm;
