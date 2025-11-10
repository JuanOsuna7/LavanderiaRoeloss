/**
 * Sistema de Alertas Personalizado - Lavandería Roeloss
 * Reemplaza las alertas nativas del navegador con alertas estilizadas
 */

// Función para crear alertas personalizadas
function showCustomAlert(message, type = 'info', title = null, buttons = null) {
    // Crear overlay
    const overlay = document.createElement('div');
    overlay.className = 'custom-alert-overlay';
    
    // Crear alerta
    const alert = document.createElement('div');
    alert.className = `custom-alert ${type}`;
    
    // Icono según el tipo
    let icon = '';
    switch(type) {
        case 'success':
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            title = title || '¡Éxito!';
            break;
        case 'error':
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            title = title || 'Error';
            break;
        case 'warning':
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>';
            title = title || 'Advertencia';
            break;
        default:
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            title = title || 'Información';
    }
    
    // Construir HTML
    alert.innerHTML = `
        <div class="custom-alert-icon">${icon}</div>
        <div class="custom-alert-title">${title}</div>
        <div class="custom-alert-message">${message}</div>
        <div class="custom-alert-actions">
            ${buttons || '<button class="custom-alert-btn custom-alert-btn-primary" onclick="closeCustomAlert()">Aceptar</button>'}
        </div>
    `;
    
    // Agregar al DOM
    document.body.appendChild(overlay);
    document.body.appendChild(alert);
    
    // Focus en el primer botón
    setTimeout(() => {
        const firstBtn = alert.querySelector('.custom-alert-btn');
        if (firstBtn) firstBtn.focus();
    }, 100);
    
    // Cerrar con Escape
    const handleEscape = (e) => {
        if (e.key === 'Escape') {
            closeCustomAlert();
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
    
    return { overlay, alert };
}

// Función para cerrar alertas
function closeCustomAlert() {
    const overlay = document.querySelector('.custom-alert-overlay');
    const alert = document.querySelector('.custom-alert');
    
    if (overlay) overlay.remove();
    if (alert) alert.remove();
}

// Función para confirmación
function showCustomConfirm(message, onConfirm, onCancel = null, title = '¿Estás seguro?') {
    const buttons = `
        <button class="custom-alert-btn custom-alert-btn-danger" onclick="confirmAction(true)">Sí, continuar</button>
        <button class="custom-alert-btn custom-alert-btn-secondary" onclick="confirmAction(false)">Cancelar</button>
    `;
    
    window.currentConfirmCallback = { onConfirm, onCancel };
    showCustomAlert(message, 'warning', title, buttons);
}

// Función para manejar confirmación
function confirmAction(confirmed) {
    const callbacks = window.currentConfirmCallback;
    closeCustomAlert();
    
    if (confirmed && callbacks.onConfirm) {
        callbacks.onConfirm();
    } else if (!confirmed && callbacks.onCancel) {
        callbacks.onCancel();
    }
    
    window.currentConfirmCallback = null;
}

// Funciones de utilidad específicas
function showSuccess(message, title = '¡Éxito!') {
    showCustomAlert(message, 'success', title);
}

function showError(message, title = 'Error') {
    showCustomAlert(message, 'error', title);
}

function showWarning(message, title = 'Advertencia') {
    showCustomAlert(message, 'warning', title);
}

function showInfo(message, title = 'Información') {
    showCustomAlert(message, 'info', title);
}

// Función de confirmación con Promise
function customConfirm(message, title = '¿Estás seguro?') {
    return new Promise((resolve) => {
        showCustomConfirm(message, () => resolve(true), () => resolve(false), title);
    });
}

// Reemplazar funciones nativas (opcional)
function replaceNativeAlerts() {
    // Guardar referencias originales
    window.originalAlert = window.alert;
    window.originalConfirm = window.confirm;
    
    // Reemplazar alert
    window.alert = function(message) {
        showCustomAlert(message);
    };
    
    // Reemplazar confirm
    window.confirm = function(message) {
        return new Promise((resolve) => {
            showCustomConfirm(message, () => resolve(true), () => resolve(false));
        });
    };
}

// Auto-inicializar cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    // Activar reemplazo de alertas nativas
    // replaceNativeAlerts(); // Descomenta si quieres reemplazar automáticamente
});