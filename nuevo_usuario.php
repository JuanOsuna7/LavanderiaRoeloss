<?php 
    require_once 'navbar.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar nuevo cliente</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <main>
        <div class="form-container">
            <h1>Registrar nuevo usuario</h1>
            
            <!-- Mensajes de éxito/error -->
            <div id="mensaje" class="mensaje"></div>

            <form id="usuarioForm">
                <div class="form-group">
                    <label for="nombres">Nombres:</label>
                    <input type="text" id="nombres" name="nombres" placeholder="Ingresa el nombre" 
                           pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+" 
                           title="Solo se permiten letras y espacios"
                           maxlength="50" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="aPaterno">Apellido paterno:</label>
                        <input type="text" id="aPaterno" name="aPaterno" placeholder="Ingresa el apellido paterno" 
                               pattern="[A-Za-zÀ-ÿ\u00f1\u00d1]+" 
                               title="Solo se permiten letras"
                               maxlength="30" required>
                    </div>
                    <div class="form-group">
                        <label for="aMaterno">Apellido materno:</label>
                        <input type="text" id="aMaterno" name="aMaterno" placeholder="Ingresa el apellido materno" 
                               pattern="[A-Za-zÀ-ÿ\u00f1\u00d1]+" 
                               title="Solo se permiten letras"
                               maxlength="30" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" required autocomplete="username">
                    <div id="errorUsuario" class="error-message">
                        El usuario es requerido
                    </div>
                </div>

                <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                    <span class="toggle-password" onclick="togglePassword()">
                        <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </span>
                </div>
                <div id="errorPassword" class="error-message">
                    La contraseña es requerida
                </div>
            </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                        Registrar cliente
                    </button>
                    <button type="button" class="btn-secondary" onclick="limpiarFormulario()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3,6 5,6 21,6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Limpiar formulario
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Validaciones en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const pass = document.getElementById('password');
            const nombres = document.getElementById('nombres');
            const aPaterno = document.getElementById('aPaterno');
            const aMaterno = document.getElementById('aMaterno');
            const usuario = document.getElementById('usuario');

            // Validación para nombres - solo letras y espacios
            nombres.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^A-Za-zÀ-ÿ\u00f1\u00d1\s]/g, '');
                if (this.value.length > 50) {
                    this.value = this.value.slice(0, 50);
                }
            });

            // Validación para apellidos - solo letras
            [aPaterno, aMaterno].forEach(function(input) {
                input.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^A-Za-zÀ-ÿ\u00f1\u00d1]/g, '');
                    if (this.value.length > 30) {
                        this.value = this.value.slice(0, 30);
                    }
                });
            });

        });

        document.getElementById('usuarioForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validaciones adicionales antes del envío
            const pass = document.getElementById('password').value;
            const nombres = document.getElementById('nombres').value.trim();
            const aPaterno = document.getElementById('aPaterno').value.trim();
            const aMaterno = document.getElementById('aMaterno').value.trim();
            const usuario = document.getElementById('usuario').value.trim();

            // Validar que los nombres no estén vacíos después de trim
            if (nombres.length < 4) {
                mostrarMensaje('El nombre debe tener al menos 4 caracteres', 'error');
                return;
            }

            if (aPaterno.length < 4) {
                mostrarMensaje('El apellido paterno debe tener al menos 4 caracteres', 'error');
                return;
            }

            if (aMaterno.length < 4) {
                mostrarMensaje('El apellido materno debe tener al menos 4 caracteres', 'error');
                return;
            }

            if (pass.length < 8) {
                mostrarMensaje('La contraseña debe tener al menos 8 caracteres', 'error');
                return;
            }

            if (usuario.length < 4) {
                mostrarMensaje('El usuario debe tener al menos 4 caracteres', 'error');
                return;
            }
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('guardar_usuario.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    mostrarMensaje(data.message, 'exito');
                    this.reset();
                    
                    // Redireccionar al index después de 2 segundos
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    mostrarMensaje(data.message, 'error');
                }
                
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('Error de conexión', 'error');
            }
        });

        function mostrarMensaje(texto, tipo) {
            const mensaje = document.getElementById('mensaje');
            
            if (tipo === 'exito') {
                const icono = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>';
                mensaje.className = 'mensaje mensaje-exito';
                mensaje.innerHTML = `${icono} <strong>¡Éxito!</strong> ${texto}`;
            } else {
                const icono = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
                mensaje.className = 'mensaje mensaje-error';
                mensaje.innerHTML = `${icono} <strong>Error:</strong> ${texto}`;
            }
            
            mensaje.style.display = 'flex';
            
            // Solo ocultar automáticamente los mensajes de error
            if (tipo === 'error') {
                setTimeout(() => {
                    mensaje.style.display = 'none';
                }, 50000);
            }
        }

         function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Cambiar a ícono de ojo cerrado
                eyeIcon.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                `;
            } else {
                passwordInput.type = 'password';
                // Cambiar a ícono de ojo abierto
                eyeIcon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                `;
            }
        }

        function limpiarFormulario() {
            document.getElementById('usuarioForm').reset();
            document.getElementById('mensaje').style.display = 'none';
        }

        async function cerrarSesion() {
            const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
            
            if (confirmed) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>