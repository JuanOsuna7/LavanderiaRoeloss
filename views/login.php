<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Lavandería Roeloss</title>
    <link rel="stylesheet" href="../login.css">
    <style>
        /* Transición suave para la alerta de logout */
        .alert {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="fondo-ilustrado"></div>
    
    <!-- Partículas decorativas -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <div class="login-container">
        <div class="login-header">
            <div class="logo">LR</div>
            <h1>Bienvenido</h1>
            <p class="subtitle">Ingresa tus credenciales para acceder al sistema</p>
        </div>

        <!-- Mensaje global de alerta -->
        <div id="globalAlert" class="alert"></div>

        <!-- Mensaje de logout exitoso -->
        <?php if (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
        <div id="logoutAlert" class="alert success show">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 8px; vertical-align: middle;">
                <polyline points="20,6 9,17 4,12"/>
            </svg>
            Sesión cerrada correctamente
        </div>
        <?php endif; ?>

        <form id="loginForm" method="POST">
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

            <button type="submit" class="btn-login" style="margin-top: 25px;">
                <span class="spinner" id="spinner"></span>
                <span id="btnText">Iniciar Sesión</span>
            </button>
        </form>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const spinner = document.getElementById('spinner');
        const btnText = document.getElementById('btnText');
        const submitBtn = form.querySelector('.btn-login');
        const globalAlert = document.getElementById('globalAlert');

        // Función para mostrar/ocultar contraseña
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

        // Validaciones en tiempo real
        const usuario = document.getElementById('usuario');
        const password = document.getElementById('password');
        const errorUsuario = document.getElementById('errorUsuario');
        const errorPassword = document.getElementById('errorPassword');

        // Función para mostrar/ocultar errores
        function showError(element, show = true) {
            if (show) {
                element.classList.add('show');
            } else {
                element.classList.remove('show');
            }
        }

        // Función para mostrar alerta global
        function showAlert(message, type = 'error') {
            globalAlert.textContent = message;
            globalAlert.className = `alert ${type} show`;
            
            if (type === 'success') {
                setTimeout(() => {
                    globalAlert.classList.remove('show');
                }, 3000);
            }
        }

        // Validación del usuario
        usuario.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value === '') {
                showError(errorUsuario);
                errorUsuario.textContent = 'El usuario es requerido';
            } else if (value.length < 3) {
                showError(errorUsuario);
                errorUsuario.textContent = 'El usuario debe tener al menos 3 caracteres';
            } else {
                showError(errorUsuario, false);
            }
        });

        // Validación de la contraseña
        password.addEventListener('blur', function() {
            const value = this.value;
            if (value === '') {
                showError(errorPassword);
                errorPassword.textContent = 'La contraseña es requerida';
            } else {
                showError(errorPassword, false);
            }
        });

        // Limpiar errores al escribir
        usuario.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                showError(errorUsuario, false);
            }
            globalAlert.classList.remove('show');
        });

        password.addEventListener('input', function() {
            if (this.value !== '') {
                showError(errorPassword, false);
            }
            globalAlert.classList.remove('show');
        });

        // Envío del formulario
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validaciones finales
            let isValid = true;
            
            if (usuario.value.trim() === '') {
                showError(errorUsuario);
                errorUsuario.textContent = 'El usuario es requerido';
                isValid = false;
            } else if (usuario.value.trim().length < 3) {
                showError(errorUsuario);
                errorUsuario.textContent = 'El usuario debe tener al menos 3 caracteres';
                isValid = false;
            }

            if (password.value === '') {
                showError(errorPassword);
                errorPassword.textContent = 'La contraseña es requerida';
                isValid = false;
            }

            if (!isValid) return;

            // Mostrar loading
            submitBtn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.textContent = 'Verificando...';

            try {
                const formData = new FormData(this);
                
                const response = await fetch('../procesar_login.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showAlert(data.message, 'success');
                    btnText.textContent = 'Redirigiendo...';
                    
                    // Redireccionar después de un breve delay
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php';
                    }, 1000);
                } else {
                    showAlert(data.message);
                    
                    // Resetear botón
                    submitBtn.disabled = false;
                    spinner.style.display = 'none';
                    btnText.textContent = 'Iniciar Sesión';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error de conexión. Por favor, intenta nuevamente.');
                
                // Resetear botón
                submitBtn.disabled = false;
                spinner.style.display = 'none';
                btnText.textContent = 'Iniciar Sesión';
            }
        });

        // Efecto de enfoque automático
        window.addEventListener('load', function() {
            usuario.focus();
            
            // Ocultar automáticamente la alerta de logout después de 4 segundos
            const logoutAlert = document.getElementById('logoutAlert');
            if (logoutAlert) {
                setTimeout(function() {
                    logoutAlert.style.opacity = '0';
                    logoutAlert.style.transform = 'translateY(-20px)';
                    setTimeout(function() {
                        logoutAlert.style.display = 'none';
                        // Limpiar la URL removiendo el parámetro logout
                        if (window.location.search.includes('logout=1')) {
                            const url = new URL(window.location);
                            url.searchParams.delete('logout');
                            window.history.replaceState({}, document.title, url.pathname);
                        }
                    }, 300); // Esperar a que termine la transición
                }, 4000); // 4 segundos
            }
        });

        // Prevenir envío con Enter cuando hay errores
        form.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const hasErrors = errorUsuario.classList.contains('show') || 
                                errorPassword.classList.contains('show');
                if (hasErrors) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>