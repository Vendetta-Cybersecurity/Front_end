/**
 * JavaScript básico para Figger Energy SAS
 * Validaciones de formularios y funciones de interacción DOM
 */

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    console.log('Figger Energy SAS - Sistema cargado correctamente');
    
    // Inicializar validaciones de formularios
    inicializarValidaciones();
    
    // Configurar eventos de la interfaz
    configurarEventos();
    
    // Manejar mensajes temporales
    manejarMensajesTemporales();
});

/**
 * Inicializar todas las validaciones de formularios
 */
function inicializarValidaciones() {
    // Validación del formulario de login
    const formularioLogin = document.getElementById('form-login');
    if (formularioLogin) {
        formularioLogin.addEventListener('submit', validarLogin);
    }
    
    // Validación del formulario de registro
    const formularioRegistro = document.getElementById('form-registro');
    if (formularioRegistro) {
        formularioRegistro.addEventListener('submit', validarRegistro);
    }
    
    // Validación del formulario de contacto
    const formularioContacto = document.getElementById('form-contacto');
    if (formularioContacto) {
        formularioContacto.addEventListener('submit', validarContacto);
    }
    
    // Validación en tiempo real para todos los campos
    const campos = document.querySelectorAll('input, textarea');
    campos.forEach(campo => {
        campo.addEventListener('input', function() {
            validarCampoEnTiempoReal(this);
        });
    });
}

/**
 * Configurar eventos generales de la interfaz
 */
function configurarEventos() {
    // Confirmar logout
    const enlacesLogout = document.querySelectorAll('a[href="logout.php"]');
    enlacesLogout.forEach(enlace => {
        enlace.addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de que desea cerrar sesión?')) {
                e.preventDefault();
            }
        });
    });
    
    // Mostrar/ocultar contraseña
    const botonesTogglePassword = document.querySelectorAll('.toggle-password');
    botonesTogglePassword.forEach(boton => {
        boton.addEventListener('click', togglePassword);
    });
}

/**
 * Validar formulario de login
 */
function validarLogin(evento) {
    evento.preventDefault();
    
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    let valido = true;
    
    // Validar email
    if (!validarEmail(email.value)) {
        mostrarError(email, 'Ingrese un email válido');
        valido = false;
    } else {
        limpiarError(email);
    }
    
    // Validar contraseña
    if (password.value.length < 4) {
        mostrarError(password, 'La contraseña debe tener al menos 4 caracteres');
        valido = false;
    } else {
        limpiarError(password);
    }
    
    if (valido) {
        // Si la validación pasa, enviar el formulario
        document.getElementById('form-login').submit();
    }
}

/**
 * Validar formulario de registro
 */
function validarRegistro(evento) {
    evento.preventDefault();
    
    const nombre = document.getElementById('nombre');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmarPassword = document.getElementById('confirmar_password');
    const rol = document.getElementById('rol');
    let valido = true;
    
    // Validar nombre
    if (nombre.value.trim().length < 3) {
        mostrarError(nombre, 'El nombre debe tener al menos 3 caracteres');
        valido = false;
    } else {
        limpiarError(nombre);
    }
    
    // Validar email
    if (!validarEmail(email.value)) {
        mostrarError(email, 'Ingrese un email válido');
        valido = false;
    } else {
        limpiarError(email);
    }
    
    // Validar contraseña
    if (password.value.length < 6) {
        mostrarError(password, 'La contraseña debe tener al menos 6 caracteres');
        valido = false;
    } else {
        limpiarError(password);
    }
    
    // Validar confirmación de contraseña
    if (password.value !== confirmarPassword.value) {
        mostrarError(confirmarPassword, 'Las contraseñas no coinciden');
        valido = false;
    } else {
        limpiarError(confirmarPassword);
    }
    
    // Validar rol
    if (rol.value === '') {
        mostrarError(rol, 'Seleccione un rol');
        valido = false;
    } else {
        limpiarError(rol);
    }
    
    if (valido) {
        // Si la validación pasa, enviar el formulario
        document.getElementById('form-registro').submit();
    }
}

/**
 * Validar formulario de contacto
 */
function validarContacto(evento) {
    evento.preventDefault();
    
    const nombre = document.getElementById('nombre');
    const email = document.getElementById('email');
    const asunto = document.getElementById('asunto');
    const mensaje = document.getElementById('mensaje');
    let valido = true;
    
    // Validar nombre
    if (nombre.value.trim().length < 3) {
        mostrarError(nombre, 'El nombre debe tener al menos 3 caracteres');
        valido = false;
    } else {
        limpiarError(nombre);
    }
    
    // Validar email
    if (!validarEmail(email.value)) {
        mostrarError(email, 'Ingrese un email válido');
        valido = false;
    } else {
        limpiarError(email);
    }
    
    // Validar asunto
    if (asunto.value.trim().length < 5) {
        mostrarError(asunto, 'El asunto debe tener al menos 5 caracteres');
        valido = false;
    } else {
        limpiarError(asunto);
    }
    
    // Validar mensaje
    if (mensaje.value.trim().length < 20) {
        mostrarError(mensaje, 'El mensaje debe tener al menos 20 caracteres');
        valido = false;
    } else {
        limpiarError(mensaje);
    }
    
    if (valido) {
        // Si la validación pasa, enviar el formulario
        document.getElementById('form-contacto').submit();
    }
}

/**
 * Validar campo en tiempo real
 */
function validarCampoEnTiempoReal(campo) {
    const valor = campo.value.trim();
    const tipo = campo.type;
    const id = campo.id;
    
    // Limpiar errores previos
    limpiarError(campo);
    
    // Validaciones específicas por campo
    switch(id) {
        case 'email':
            if (valor && !validarEmail(valor)) {
                mostrarError(campo, 'Formato de email inválido');
            }
            break;
            
        case 'password':
            if (valor && valor.length < 6) {
                mostrarError(campo, 'Mínimo 6 caracteres');
            }
            break;
            
        case 'confirmar_password':
            const passwordPrincipal = document.getElementById('password');
            if (valor && passwordPrincipal && valor !== passwordPrincipal.value) {
                mostrarError(campo, 'Las contraseñas no coinciden');
            }
            break;
            
        case 'nombre':
            if (valor && valor.length < 3) {
                mostrarError(campo, 'Mínimo 3 caracteres');
            }
            break;
    }
}

/**
 * Validar formato de email
 */
function validarEmail(email) {
    const patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return patron.test(email);
}

/**
 * Mostrar error en un campo
 */
function mostrarError(campo, mensaje) {
    // Remover clases de éxito y agregar clase de error
    campo.classList.remove('campo-valido');
    campo.classList.add('campo-invalido');
    
    // Buscar o crear elemento de error
    let elementoError = campo.parentNode.querySelector('.texto-error');
    if (!elementoError) {
        elementoError = document.createElement('div');
        elementoError.className = 'texto-error';
        campo.parentNode.appendChild(elementoError);
    }
    
    elementoError.textContent = mensaje;
}

/**
 * Limpiar error de un campo
 */
function limpiarError(campo) {
    // Remover clase de error y agregar clase de éxito si hay contenido
    campo.classList.remove('campo-invalido');
    if (campo.value.trim()) {
        campo.classList.add('campo-valido');
    } else {
        campo.classList.remove('campo-valido');
    }
    
    // Remover mensaje de error
    const elementoError = campo.parentNode.querySelector('.texto-error');
    if (elementoError) {
        elementoError.remove();
    }
}

/**
 * Toggle para mostrar/ocultar contraseña
 */
function togglePassword(evento) {
    evento.preventDefault();
    const boton = evento.target;
    const campoPassword = boton.previousElementSibling;
    
    if (campoPassword.type === 'password') {
        campoPassword.type = 'text';
        boton.textContent = '👁️‍🗨️';
    } else {
        campoPassword.type = 'password';
        boton.textContent = '👁️';
    }
}

/**
 * Mostrar mensaje temporal
 */
function mostrarMensaje(mensaje, tipo = 'exito', duracion = 5000) {
    // Crear elemento de mensaje
    const elementoMensaje = document.createElement('div');
    elementoMensaje.className = `mensaje mensaje-${tipo}`;
    elementoMensaje.textContent = mensaje;
    
    // Insertar al inicio del contenido principal
    const contenidoPrincipal = document.querySelector('.contenido-principal') || document.body;
    contenidoPrincipal.insertBefore(elementoMensaje, contenidoPrincipal.firstChild);
    
    // Remover después de la duración especificada
    setTimeout(() => {
        if (elementoMensaje.parentNode) {
            elementoMensaje.parentNode.removeChild(elementoMensaje);
        }
    }, duracion);
}

/**
 * Confirmar acción (para eliminaciones, etc.)
 */
function confirmarAccion(mensaje = '¿Está seguro de realizar esta acción?') {
    return confirm(mensaje);
}

/**
 * Formatear fecha para mostrar en la interfaz
 */
function formatearFecha(fecha) {
    const opciones = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(fecha).toLocaleDateString('es-CO', opciones);
}

/**
 * Función para actualizar dinámicamente el contenido de las tablas
 */
function actualizarTabla(idTabla, datos) {
    const tabla = document.getElementById(idTabla);
    if (!tabla) return;
    
    const tbody = tabla.querySelector('tbody');
    if (!tbody) return;
    
    // Limpiar contenido actual
    tbody.innerHTML = '';
    
    // Agregar nuevos datos
    datos.forEach(fila => {
        const tr = document.createElement('tr');
        fila.forEach(celda => {
            const td = document.createElement('td');
            td.textContent = celda;
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
}

/**
 * Manejar mensajes temporales (como el de logout)
 */
function manejarMensajesTemporales() {
    const mensajeLogout = document.getElementById('mensaje-logout');
    
    if (mensajeLogout) {
        // Hacer desaparecer el mensaje después de 5 segundos
        setTimeout(function() {
            mensajeLogout.classList.add('fade-out');
            
            // Remover completamente el elemento después de la animación
            setTimeout(function() {
                if (mensajeLogout.parentNode) {
                    mensajeLogout.parentNode.removeChild(mensajeLogout);
                }
            }, 500); // 500ms para completar la transición CSS
        }, 5000); // 5 segundos antes de comenzar a desvanecer
    }
}

// Funciones globales disponibles para uso en HTML inline
window.FiggerEnergy = {
    validarEmail: validarEmail,
    mostrarMensaje: mostrarMensaje,
    confirmarAccion: confirmarAccion,
    formatearFecha: formatearFecha,
    actualizarTabla: actualizarTabla
};
