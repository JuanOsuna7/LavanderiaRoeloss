<?php
require_once 'config.php';
$pagina_actual = basename($_SERVER['PHP_SELF']);

// Traer clientes y servicios de la BD para llenar los selects
$clientes = $pdo->query("
    SELECT c.pk_cliente, CONCAT(p.nombres, ' ', p.aPaterno, ' ', IFNULL(p.aMaterno,'')) AS nombre_completo
    FROM clientes c
    INNER JOIN personas p ON c.fk_persona = p.pk_persona
    WHERE c.estatusCli = 'Activo'
")->fetchAll(PDO::FETCH_ASSOC);

$servicios = $pdo->query("
    SELECT pk_servicioRopa, nombreServicioRopa 
    FROM serviciosropa
    WHERE estatusServicioRopa = 'Activo'
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear nuevo pedido</title>
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
        .btn-modal:hover {
            background-color: #1e4ed8;
        }
    </style>
</head>
<body>

<header class="navbar">
    <div class="nav-left">
        <a href="index.php"><img src="img/logo.png" alt="Logo" class="logo"></a>
        <a href="nuevo_cliente.php" class="<?= $pagina_actual == 'nuevo_cliente.php' ? 'active' : '' ?>">Registrar nuevo cliente</a>
        <a href="historial.php" class="<?= $pagina_actual == 'historial.php' ? 'active' : '' ?>">Historial de registros</a>
        <a href="nuevo_pedido.php" class="<?= $pagina_actual == 'nuevo_pedido.php' ? 'active' : '' ?>">Crear nuevo pedido</a>
    </div>
    <div class="nav-right">
        <button class="btn-cerrar">Cerrar sesión</button>
    </div>
</header>

<main class="contenedor-form">
    <form id="formPedido" class="formulario-cliente">
        <h1>Crear nuevo pedido</h1>

        <label for="cliente">Cliente</label>
        <select id="cliente" name="cliente" required>
            <option value="">Selecciona un cliente</option>
            <?php foreach ($clientes as $cli): ?>
                <option value="<?= $cli['pk_cliente'] ?>"><?= htmlspecialchars($cli['nombre_completo']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="servicio">Tipo de servicio</label>
        <select id="servicio" name="servicio" required>
            <option value="">Selecciona un servicio</option>
            <?php foreach ($servicios as $srv): ?>
                <option value="<?= $srv['pk_servicioRopa'] ?>"><?= htmlspecialchars($srv['nombreServicioRopa']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="tipo_entrega">Tipo de entrega</label>
        <select id="tipo_entrega" name="tipo_entrega" required>
            <option value="">Selecciona tipo de entrega</option>
            <option value="Domicilio">Domicilio</option>
            <option value="Recoger en local">Recoger en local</option>
        </select>

        <label for="total">Total</label>
        <input type="number" id="total" name="total" placeholder="Monto total" required>

        <div class="botones-form">
            <button type="submit" class="btn-aceptar">Aceptar</button>
            <a href="index.php" class="btn-cancelar">Cancelar</a>
        </div>
    </form>
</main>

<!-- Modal de éxito -->
<div class="modal" id="modalExito">
    <div class="modal-content">
        <h2>¡Pedido registrado correctamente!</h2>
        <button class="btn-modal" id="btnCerrarModal">Aceptar</button>
    </div>
</div>

<script>
document.getElementById('formPedido').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    const respuesta = await fetch('guardar_pedido.php', {
        method: 'POST',
        body: formData
    });
    const data = await respuesta.json();

    if (data.status === "ok") {
        document.getElementById('modalExito').style.display = 'flex';
        this.reset();
    } else {
        alert(data.message);
    }
});

document.getElementById('btnCerrarModal').addEventListener('click', () => {
    document.getElementById('modalExito').style.display = 'none';
});
</script>

</body>
</html>
