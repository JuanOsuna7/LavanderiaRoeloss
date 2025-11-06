<?php
require_once 'config.php';
require_once 'auth.php';

// Obtener lista de clientes
try {
    $clientes = $pdo->query("SELECT c.pk_cliente, CONCAT(p.nombres, ' ', p.aPaterno, ' ', p.aMaterno) AS nombreCompleto
                              FROM clientes c
                              INNER JOIN personas p ON c.fk_persona = p.pk_persona
                              ORDER BY p.nombres ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar clientes: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrar nuevo pedido</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f3f4f6;
        margin: 0;
        padding: 0;
    }
    .navbar {
        background-color: #1e3a8a;
        padding: 15px 40px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .nav-left a {
        color: white;
        text-decoration: none;
        margin-right: 20px;
        font-weight: 500;
    }
    .nav-left a.active {
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
    }
    main {
        max-width: 500px;
        margin: 50px auto;
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    h1 {
        text-align: center;
        color: #1e3a8a;
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-top: 15px;
        font-weight: bold;
    }
    select, input {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 15px;
    }
    .botones {
        display: flex;
        justify-content: space-between;
        margin-top: 25px;
    }
    .btn {
        padding: 10px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn-aceptar {
        background: #2563eb;
        color: white;
    }
    .btn-aceptar:hover { background: #1d4ed8; }
    .btn-cancelar {
        background: #dc2626;
        color: white;
        text-decoration: none;
        text-align: center;
        padding: 10px 25px;
        border-radius: 8px;
    }
    .btn-cancelar:hover { background: #b91c1c; }
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
</style>
</head>
<body>

<header class="navbar">
    <div class="nav-left">
        <a href="index.php"><img src="img/logo.png" alt="Logo" class="logo"></a>
        <a href="nuevo_cliente.php">Registrar nuevo cliente</a>
        <a href="historial.php">Historial de registros</a>
        <a href="nuevo_pedido.php" class="active">Crear nuevo pedido</a>
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
    <h1>Registrar nuevo pedido</h1>

    <form id="formPedido">
        <label>Cliente</label>
        <select name="cliente" required>
            <option value="">Seleccione un cliente</option>
            <?php foreach ($clientes as $cli): ?>
                <option value="<?= $cli['pk_cliente'] ?>"><?= htmlspecialchars($cli['nombreCompleto']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Tipo de ropa</label>
        <select name="tipoRopa" required>
            <option value="">Seleccione el tipo de ropa</option>
            <option value="Mezclilla">Mezclilla</option>
            <option value="Color">Color</option>
            <option value="Negra">Negra</option>
            <option value="Colchas/Cobijas">Colchas/Cobijas</option>
            <option value="Edredón">Edredón</option>
            <option value="Cobertor">Cobertor</option>
        </select>

        <label>Tipo de servicio</label>
        <select name="tipoServicio" required>
            <option value="">Seleccione el tipo de servicio</option>
            <option value="Express">Express</option>
            <option value="Normal">Normal</option>
            <option value="Delicado">Delicado</option>
        </select>

        <label>Tipo de entrega</label>
        <select name="tipoEntrega" required>
            <option value="">Seleccione</option>
            <option value="Entrega a domicilio">Entrega a domicilio</option>
            <option value="Recoger en sucursal">Recoger en sucursal</option>
        </select>

        <label>Total (MXN)</label>
        <input type="number" name="total" step="0.01" min="0" required>

        <div class="botones">
            <button type="submit" class="btn btn-aceptar">Aceptar</button>
            <a href="index.php" class="btn-cancelar">Cancelar</a>
        </div>
    </form>
</main>

<!-- Modal -->
<div class="modal" id="modalExito">
    <div class="modal-content">
        <h2>¡Pedido registrado correctamente!</h2>
        <button class="btn-modal" id="btnCerrarModal">Aceptar</button>
    </div>
</div>

<script>
document.getElementById("formPedido").addEventListener("submit", async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const resp = await fetch("guardar_pedido.php", {
        method: "POST",
        body: formData
    });
    const data = await resp.json();

    if (data.status === "ok") {
        document.getElementById("modalExito").style.display = "flex";
        this.reset();
    } else {
        alert(data.message);
    }
});

document.getElementById("btnCerrarModal").addEventListener("click", () => {
    document.getElementById("modalExito").style.display = "none";
    window.location.href = "historial.php";
});

function cerrarSesion() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        window.location.href = 'logout.php';
    }
}
</script>

</body>
</html>
