<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Lavandería Roeloss</title>
    <link rel="stylesheet" href="login.css">
        :root {
            --azul-oscuro: #1e3a8a;
            --azul-medio: #2563eb;
            --azul-claro: #93c5fd;
            --rojo: #dc2626;
            --verde: #16a34a;
            --gris-claro: #f3f4f6;
            --gris-texto: #4b5563;
            --sombra: 0 10px 30px rgba(0, 0, 0, 0.08);
            --radio: 14px;
        }

        body {
            font-family: "Poppins", "Segoe UI", sans-serif;
            background-color: var(--gris-claro);
            color: var(--gris-texto);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Fondo animado */
        .fondo-ilustrado {
            position: fixed;
            top: 0;
            left: 0;
            z-index: -1;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 20%, rgba(147,197,253,0.25), transparent 70%),
                        radial-gradient(circle at 80% 80%, rgba(30,58,138,0.15), transparent 60%);
        }

        /* Contenedor principal */
        .login-container {
            background: white;
            border-radius: var(--radio);
            padding: 40px 50px;
            box-shadow: var(--sombra);
            width: 100%;
            max-width: 420px;
            position: relative;
        }

        /* Logo y título */
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--azul-oscuro), var(--azul-medio));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        h1 {
            color: var(--azul-oscuro);
            font-size: 1.8rem;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        .subtitle {
            color: var(--gris-texto);
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        /* Formulario */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--gris-texto);
            font-size: 0.9rem;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fafafa;
            box-sizing: border-box;
        }

        /* Input con icono de contraseña */
        .password-container {
            position: relative;
        }

        .password-container input {
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gris-texto);
            user-select: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            border-radius: 4px;
        }

        .toggle-password:hover {
            color: var(--azul-medio);
            background: rgba(37, 99, 235, 0.05);
        }

        .toggle-password svg {
            transition: transform 0.2s ease;
        }

        .toggle-password:active svg {
            transform: scale(0.95);
        }

        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: var(--azul-medio);
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Mensajes de error */
        .error-message {
            color: var(--rojo);
            font-size: 13px;
            margin-top: 5px;
            display: none;
            padding: 8px 12px;
            background: #fef2f2;
            border-radius: 6px;
            border-left: 3px solid var(--rojo);
        }

        .error-message.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Botón de login */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--azul-oscuro), var(--azul-medio));
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Spinner de carga */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Recordar sesión - REMOVIDO */

        /* Mensaje de éxito/error global */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            display: none;
        }

        .alert.success {
            background: #f0fdf4;
            color: var(--verde);
            border: 1px solid #bbf7d0;
        }

        .alert.error {
            background: #fef2f2;
            color: var(--rojo);
            border: 1px solid #fecaca;
        }

        .alert.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 25px;
            }

            .logo {
                width: 60px;
                height: 60px;
                font-size: 20px;
            }

            h1 {
                font-size: 1.5rem;
            }
        }

        /* Efecto de particle background */
        .particle {
            position: absolute;
            background: var(--azul-claro);
            border-radius: 50%;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) { width: 4px; height: 4px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 6px; height: 6px; left: 20%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 3px; height: 3px; left: 80%; animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
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
        <div class="alert success show">
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
            } else if (value.length < 6) {
                showError(errorPassword);
                errorPassword.textContent = 'La contraseña debe tener al menos 6 caracteres';
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
            } else if (password.value.length < 6) {
                showError(errorPassword);
                errorPassword.textContent = 'La contraseña debe tener al menos 6 caracteres';
                isValid = false;
            }

            if (!isValid) return;

            // Mostrar loading
            submitBtn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.textContent = 'Verificando...';

            try {
                const formData = new FormData(this);
                
                const response = await fetch('procesar_login.php', {
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