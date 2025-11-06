<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar nuevo cliente</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-left">
            <a href="index.php"><img src="img/logo.png" alt="Logo" class="logo"></a>
            <a href="nuevo_cliente.php" class="active">Registrar nuevo cliente</a>
            <a href="historial.php">Historial de registros</a>
            <a href="nuevo_pedido.php">Crear nuevo pedido</a>
        </div>
        <div class="nav-right">
            <div class="user-info">
                <div class="user-icon">
                    <?= strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)) ?>
                </div>
                <span class="user-name">
                    <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? $_SESSION['usuario_nombre']) ?>
                </span>
            </div>
            <button class="btn-cerrar" onclick="cerrarSesion()">
                <span class="logout-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16,17 21,12 16,7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </span>
                Cerrar sesión
            </button>
        </div>
    </header>

    <main>
        <div class="form-container">
            <h1>Registrar nuevo cliente</h1>
            
            <!-- Mensajes de éxito/error -->
            <div id="mensaje" class="mensaje"></div>

            <form id="clienteForm">
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
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="10 dígitos" 
                           pattern="[0-9]{10}" 
                           title="Debe contener exactamente 10 dígitos"
                           maxlength="10" 
                           minlength="10" required>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <textarea id="direccion" name="direccion" placeholder="Ingresa la dirección completa" 
                              rows="3" 
                              maxlength="200" 
                              title="Máximo 200 caracteres" required></textarea>
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
            const telefono = document.getElementById('telefono');
            const nombres = document.getElementById('nombres');
            const aPaterno = document.getElementById('aPaterno');
            const aMaterno = document.getElementById('aMaterno');
            const direccion = document.getElementById('direccion');

            // Validación para teléfono - solo números
            telefono.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 10) {
                    this.value = this.value.slice(0, 10);
                }
            });

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

            // Validación para dirección - límite de caracteres
            direccion.addEventListener('input', function(e) {
                if (this.value.length > 200) {
                    this.value = this.value.slice(0, 200);
                }
            });

            // Mostrar contador de caracteres para dirección
            const contadorDireccion = document.createElement('small');
            contadorDireccion.style.color = '#6b7280';
            contadorDireccion.style.fontSize = '12px';
            contadorDireccion.textContent = '0/200 caracteres';
            direccion.parentNode.appendChild(contadorDireccion);

            direccion.addEventListener('input', function() {
                const length = this.value.length;
                contadorDireccion.textContent = `${length}/200 caracteres`;
                contadorDireccion.style.color = length > 180 ? '#ef4444' : '#6b7280';
            });
        });

        document.getElementById('clienteForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validaciones adicionales antes del envío
            const telefono = document.getElementById('telefono').value;
            const nombres = document.getElementById('nombres').value.trim();
            const aPaterno = document.getElementById('aPaterno').value.trim();
            const aMaterno = document.getElementById('aMaterno').value.trim();
            const direccion = document.getElementById('direccion').value.trim();

            // Validar que el teléfono tenga exactamente 10 dígitos
            if (telefono.length !== 10 || !/^[0-9]{10}$/.test(telefono)) {
                mostrarMensaje('El teléfono debe contener exactamente 10 dígitos', 'error');
                return;
            }

            // Validar que los nombres no estén vacíos después de trim
            if (nombres.length < 2) {
                mostrarMensaje('El nombre debe tener al menos 2 caracteres', 'error');
                return;
            }

            if (aPaterno.length < 2) {
                mostrarMensaje('El apellido paterno debe tener al menos 2 caracteres', 'error');
                return;
            }

            if (aMaterno.length < 2) {
                mostrarMensaje('El apellido materno debe tener al menos 2 caracteres', 'error');
                return;
            }

            if (direccion.length < 10) {
                mostrarMensaje('La dirección debe tener al menos 10 caracteres', 'error');
                return;
            }
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('guardar_cliente.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    mostrarMensaje(data.message, 'exito');
                    this.reset();
                    // Resetear contador
                    const contador = this.querySelector('small');
                    if (contador) contador.textContent = '0/200 caracteres';
                    
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
                }, 5000);
            }
        }

        function limpiarFormulario() {
            document.getElementById('clienteForm').reset();
            document.getElementById('mensaje').style.display = 'none';
            // Resetear contador
            const contador = document.querySelector('small');
            if (contador) contador.textContent = '0/200 caracteres';
        }

        function cerrarSesion() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>