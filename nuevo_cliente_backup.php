<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar nuevo cliente</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background: #1e3a8a;
            color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar .nav-left a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            font-weight: 500;
        }
        .navbar .nav-left a.active {
            border-bottom: 2px solid #93c5fd;
            padding-bottom: 4px;
        }
        .logo {
            height: 50px;
            margin-right: 20px;
            vertical-align: middle;
        }
        
        /* Área de usuario mejorada */
        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .user-info:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .user-icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(45deg, #3b82f6, #60a5fa);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .user-name {
            color: white;
            font-size: 14px;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .btn-cerrar {
            background: linear-gradient(45deg, #dc2626, #dc2626);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 25px;
            padding: 10px 20px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-cerrar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-cerrar:hover::before {
            left: 100%;
        }

        .btn-cerrar:hover {
            background: linear-gradient(45deg, #b91c1c, #991b1b);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
        }

        .btn-cerrar:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

        .logout-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .logout-icon svg {
            transition: transform 0.3s ease;
        }

        .btn-cerrar:hover .logout-icon svg {
            transform: translateX(2px);
        }
        .contenedor-form {
            max-width: 550px;
            background: white;
            margin: 60px auto;
            padding: 35px 50px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        h1 {
            text-align: center;
            color: #1e3a8a;
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-top: 12px;
            font-weight: 600;
            color: #374151;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            margin-top: 4px;
        }
        .botones-form {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
        }
        .btn-aceptar {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
        }
        .btn-aceptar:hover {
            background: #1e40af;
        }
        .btn-cancelar {
            text-decoration: none;
            color: #1e3a8a;
            font-weight: bold;
            padding: 10px 25px;
            border: 2px solid #1e3a8a;
            border-radius: 8px;
            transition: 0.3s;
        }
        .btn-cancelar:hover {
            background: #1e3a8a;
            color: white;
        }
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .modal-content {
            background: white;
            padding: 40px 60px;
            border-radius: 15px;
            text-align: center;
            animation: aparecer 0.4s ease;
        }
        @keyframes aparecer {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-content h2 {
            color: #16a34a;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .btn-modal {
            background: #16a34a;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }
        .btn-modal:hover { background: #15803d; }
        /* Errores */
        .error {
            color: #dc2626;
            font-size: 13px;
            display: none;
            margin-top: 3px;
        }
    </style>
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

<main class="contenedor-form">
    <form id="formCliente" method="POST">
        <h1>Registrar nuevo cliente</h1>

        <label>Nombre</label>
        <input type="text" id="nombre" name="nombre" required>
        <div id="errorNombre" class="error">Solo se permiten letras.</div>

        <label>Apellido paterno</label>
        <input type="text" id="apellido_paterno" name="apellido_paterno" required>
        <div id="errorPaterno" class="error">Solo se permiten letras.</div>

        <label>Apellido materno</label>
        <input type="text" id="apellido_materno" name="apellido_materno">
        <div id="errorMaterno" class="error">Solo se permiten letras.</div>

        <label>Teléfono</label>
        <input type="tel" id="telefono" name="telefono" maxlength="10" required>
        <div id="errorTelefono" class="error">Debe contener solo 10 dígitos numéricos.</div>

        <label>Dirección</label>
        <input type="text" id="direccion" name="direccion" required>

        <div class="botones-form">
            <button type="submit" class="btn-aceptar">Aceptar</button>
            <a href="index.php" class="btn-cancelar">Cancelar</a>
        </div>
    </form>
</main>

<!-- Modal de éxito -->
<div class="modal" id="modalExito">
    <div class="modal-content">
        <h2>¡Cliente creado correctamente!</h2>
        <button class="btn-modal" id="btnCerrarModal">Aceptar</button>
    </div>
</div>

<script>
const form = document.getElementById("formCliente");
const modal = document.getElementById("modalExito");

// Validaciones
const soloLetras = /^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/;
const soloNumeros = /^[0-9]{10}$/;

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    let valido = true;

    const nombre = document.getElementById("nombre");
    const paterno = document.getElementById("apellido_paterno");
    const materno = document.getElementById("apellido_materno");
    const telefono = document.getElementById("telefono");

    // Validaciones básicas
    if (!soloLetras.test(nombre.value.trim())) {
        document.getElementById("errorNombre").style.display = "block";
        valido = false;
    } else document.getElementById("errorNombre").style.display = "none";

    if (!soloLetras.test(paterno.value.trim())) {
        document.getElementById("errorPaterno").style.display = "block";
        valido = false;
    } else document.getElementById("errorPaterno").style.display = "none";

    if (materno.value.trim() && !soloLetras.test(materno.value.trim())) {
        document.getElementById("errorMaterno").style.display = "block";
        valido = false;
    } else document.getElementById("errorMaterno").style.display = "none";

    if (!soloNumeros.test(telefono.value.trim())) {
        document.getElementById("errorTelefono").style.display = "block";
        valido = false;
    } else document.getElementById("errorTelefono").style.display = "none";

    if (!valido) return;

    // Enviar al servidor
    const formData = new FormData(form);

    try {
        const resp = await fetch("guardar_cliente.php", {
            method: "POST",
            body: formData
        });

        const data = await resp.json();

        if (data.status === "ok") {
            modal.style.display = "flex";
            form.reset();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error(error);
        alert("Error al conectar con el servidor.");
    }
});

// Solo números en teléfono
document.getElementById("telefono").addEventListener("input", function() {
    this.value = this.value.replace(/[^0-9]/g, "");
});

// Cerrar modal
document.getElementById("btnCerrarModal").addEventListener("click", () => {
    modal.style.display = "none";
});

function cerrarSesion() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        window.location.href = 'logout.php';
    }
}
</script>
</body>
</html>
