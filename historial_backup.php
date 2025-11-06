<?php
require_once 'config.php';
require_once 'auth.php';

// Consulta de todos los pedidos con los datos del cliente y servicio
try {
    $sql = "SELECT 
                p.pk_pedido AS id_pedido,
                per.nombres,
                per.aPaterno,
                per.aMaterno,
                s.nombreServicioRopa AS tipo_ropa,
                p.tipoEntrega,
                p.estatusPedido,
                p.totalPedido,
                DATE_FORMAT(p.fechaDeRecibo, '%d/%m/%Y %H:%i') AS fecha
            FROM pedidos p
            INNER JOIN clientes c ON p.fk_cliente = c.pk_cliente
            INNER JOIN personas per ON c.fk_persona = per.pk_persona
            INNER JOIN serviciosropa s ON p.fk_servicioRopa = s.pk_servicioRopa
            ORDER BY p.pk_pedido DESC";
    $stmt = $pdo->query($sql);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar los pedidos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Historial de Pedidos</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
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
    h1 {
        text-align: center;
        color: #1e3a8a;
        margin-top: 40px;
    }
    .tabla-container {
        max-width: 1100px;
        margin: 40px auto;
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        background: #2563eb;
        color: white;
        text-align: center;
        padding: 10px;
    }
    td {
        text-align: center;
        padding: 10px;
        border-bottom: 1px solid #e5e7eb;
    }
    tr:hover {
        background-color: #f9fafb;
    }
    .estatus-pendiente {
        background-color: #f59e0b;
        color: white;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: bold;
    }
    .estatus-entregado {
        background-color: #16a34a;
        color: white;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: bold;
    }
    .acciones button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
        margin: 0 3px;
        border-radius: 6px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .acciones button:hover {
        background: rgba(0, 0, 0, 0.05);
        transform: scale(1.1);
    }
    .acciones button:first-child {
        color: #2563eb;
    }
    .acciones button:first-child:hover {
        background: rgba(37, 99, 235, 0.1);
    }
    .acciones button:last-child {
        color: #dc2626;
    }
    .acciones button:last-child:hover {
        background: rgba(220, 38, 38, 0.1);
    }
    .sin-registros {
        text-align: center;
        color: #6b7280;
        font-style: italic;
        padding: 20px;
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
*/
</head>
<body>

<header class="navbar">
    <div class="nav-left">
        <a href="index.php"><img src="img/logo.png" alt="Logo" class="logo"></a>
        <a href="nuevo_cliente.php">Registrar nuevo cliente</a>
        <a href="historial.php" class="active">Historial de registros</a>
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
    <h1>Historial de pedidos</h1>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Cliente</th>
                    <th>Tipo de ropa</th>
                    <th>Tipo de entrega</th>
                    <th>Estatus</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pedidos)): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= htmlspecialchars($pedido['id_pedido']) ?></td>
                            <td><?= htmlspecialchars($pedido['nombres'] . ' ' . $pedido['aPaterno'] . ' ' . $pedido['aMaterno']) ?></td>
                            <td><?= htmlspecialchars($pedido['tipo_ropa']) ?></td>
                            <td><?= htmlspecialchars($pedido['tipoEntrega']) ?></td>
                            <td>
                                <?php if (strtolower($pedido['estatusPedido']) === 'pendiente'): ?>
                                    <span class="estatus-pendiente">Pendiente</span>
                                <?php else: ?>
                                    <span class="estatus-entregado">Entregado</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?= htmlspecialchars(number_format($pedido['totalPedido'], 2)) ?></td>
                            <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                            <td class="acciones">
                                <button title="Editar" onclick="editarPedido(<?= $pedido['id_pedido'] ?>)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <button title="Eliminar" onclick="eliminarPedido(<?= $pedido['id_pedido'] ?>)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3,6 5,6 21,6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        <line x1="10" y1="11" x2="10" y2="17"/>
                                        <line x1="14" y1="11" x2="14" y2="17"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="sin-registros">No hay pedidos registrados aún.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
// 🔹 Redirigir a editar pedido
function editarPedido(id) {
    window.location.href = "nuevo_pedido.php?id=" + id;
}

// 🔹 Eliminar pedido
async function eliminarPedido(id) {
    if (confirm("¿Desea eliminar el pedido?")) {
        const resp = await fetch("eliminar_pedido.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + id
        });
        const data = await resp.json();
        if (data.status === "ok") {
            alert("¡Pedido eliminado correctamente!");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    }
}

function cerrarSesion() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        window.location.href = 'logout.php';
    }
}
</script>

</body>
</html>
