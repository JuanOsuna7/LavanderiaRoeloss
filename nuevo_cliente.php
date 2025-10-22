<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar nuevo cliente</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }
        .btn-modal {
            background-color: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-modal:hover { background-color: #1e4ed8; }
        .error { color: red; font-size: 14px; display: none; }
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
        <button class="btn-cerrar">Cerrar sesión</button>
    </div>
</header>

<main class="contenedor-form">
    <form id="formCliente" class="formulario-cliente" method="POST">
        <h1>Crear nuevo cliente</h1>

        <label>Nombre</label>
        <input type="text" id="nombre" name="nombre" required>

        <label>Apellido paterno</label>
        <input type="text" id="apellido_paterno" name="apellido_paterno" required>

        <label>Apellido materno</label>
        <input type="text" id="apellido_materno" name="apellido_materno">

        <label>Teléfono</label>
        <input type="tel" id="telefono" name="telefono" maxlength="10" required>

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
document.getElementById("formCliente").addEventListener("submit", async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    const respuesta = await fetch('guardar_cliente.php', {
        method: 'POST',
        body: formData
    });

    const data = await respuesta.json();

    if (data.status === 'ok') {
        document.getElementById("modalExito").style.display = "flex";
        this.reset();
    } else {
        alert(data.message);
    }
});

document.getElementById("btnCerrarModal").addEventListener("click", () => {
    document.getElementById("modalExito").style.display = "none";
});
</script>

</body>
</html>
