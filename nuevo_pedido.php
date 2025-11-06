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
<link rel="stylesheet" href="estilos.css">
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
    <div class="form-container">
        <h1>Registrar nuevo pedido</h1>

        <form id="formPedido">
            <div class="form-group">
                <label for="cliente">Cliente:</label>
                <select name="cliente" id="cliente" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['pk_cliente'] ?>">
                            <?= htmlspecialchars($cliente['nombreCompleto']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="servicio">Tipo de ropa:</label>
                <select name="servicio" id="servicio" required>
                    <option value="">Seleccione</option>
                    <option value="1">Ropa de casa</option>
                    <option value="2">Cobijas</option>
                </select>
            </div>

            <div class="form-group">
                <label for="peso">Peso (kg):</label>
                <input type="number" name="peso" id="peso" step="0.1" min="0.1" required>
            </div>

            <div class="form-group">
                <label for="tipoEntrega">Tipo de entrega:</label>
                <select name="tipoEntrega" id="tipoEntrega" required>
                    <option value="">Seleccione</option>
                    <option value="Entrega a domicilio">Entrega a domicilio</option>
                    <option value="Recoger en sucursal">Recoger en sucursal</option>
                </select>
            </div>

            <div class="form-group">
                <label for="total">Total (MXN):</label>
                <input type="number" name="total" id="total" step="0.01" min="0" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                    Registrar pedido
                </button>
                <a href="index.php" class="btn-secondary">
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

<!-- Modal de éxito -->
<div class="modal" id="modalExito">
    <div class="modal-content">
        <div class="modal-header">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
        </div>
        <div class="modal-body">
            <h2>¡Pedido registrado correctamente!</h2>
            <p>El pedido ha sido guardado exitosamente en el sistema.</p>
        </div>
        <div class="modal-actions">
            <button class="btn-primary" id="btnCerrarModal">Aceptar</button>
        </div>
    </div>
</div>

<script>
document.getElementById("formPedido").addEventListener("submit", async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    
    try {
        const resp = await fetch("guardar_pedido.php", {
            method: "POST",
            body: formData
        });
        const data = await resp.json();

        if (data.status === "ok") {
            document.getElementById("modalExito").style.display = "flex";
            this.reset();
        } else {
            alert("Error: " + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert("Error de conexión");
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