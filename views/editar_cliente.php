<?php 
    require_once __DIR__ . '/../navbar.php';

    $clienteId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$clienteId) {
    header('Location: listaClientes.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT * FROM clientes c
        INNER JOIN personas p ON c.fk_persona = p.pk_persona
         WHERE c.pk_cliente = ?
    ");
    $stmt->execute([$clienteId]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        header('Location: listaClientes.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error al consultar el cliente: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar cliente</title>
</head>
<body>
    <main>
        <div class="form-container">
            <h1>Editar cliente</h1>
            
            <!-- Mensajes de éxito/error -->
            <div id="mensaje" class="mensaje"></div>

            <form id="clienteForm">
                <input type="hidden" id="clienteId" name="id" value="<?= $cliente['pk_cliente'] ?>">
                <input type="hidden" id="personaId" name="personaId" value="<?= $cliente['pk_persona'] ?>">
                <div class="form-group">
                    <label for="nombres">Nombres:</label>
                    <input type="text" id="nombres" name="nombres" placeholder="Ingresa el nombre" 
                           pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+" 
                           title="Solo se permiten letras y espacios"
                           maxlength="50"
                           value="<?= $cliente['nombres'] ?>"
                           required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="aPaterno">Apellido paterno:</label>
                        <input type="text" id="aPaterno" name="aPaterno" placeholder="Ingresa el apellido paterno" 
                               pattern="[A-Za-zÀ-ÿ\u00f1\u00d1]+" 
                               value="<?=$cliente['aPaterno']?>"
                               title="Solo se permiten letras"
                               maxlength="30" 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="aMaterno">Apellido materno:</label>
                        <input type="text" id="aMaterno" name="aMaterno" placeholder="Ingresa el apellido materno" 
                               pattern="[A-Za-zÀ-ÿ\u00f1\u00d1]+" 
                               title="Solo se permiten letras"
                               maxlength="30"
                               value="<?= $cliente['aMaterno'] ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="10 dígitos" 
                           pattern="[0-9]{10}" 
                           title="Debe contener exactamente 10 dígitos"
                           maxlength="10" 
                           minlength="10"
                           value="<?= $cliente['telefono'] ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <textarea id="direccion" name="direccion" placeholder="Ingresa la dirección completa" 
                              rows="3" 
                              maxlength="200" 
                              title="Máximo 200 caracteres"     
                              required><?= $cliente['direccion'] ?></textarea>
                </div>

                <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                    Editar Cliente
                </button>
                <a href="<?= BASE_URL ?>controllers/cliente_controller.php?action=list" class="btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12,19 5,12 12,5"/>
                    </svg>
                    Cancelar    
                </a>
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
                const response = await fetch('<?= BASE_URL ?>controllers/cliente_controller.php?action=update', {
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
                        window.location.href = '<?= BASE_URL ?>controllers/cliente_controller.php?action=list';
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

        async function cerrarSesion() {
            const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
            
            if (confirmed) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>