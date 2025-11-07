<?php
require_once 'config.php';
require_once 'navbar.php';
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
                                <?php if ($pedido['estatusPedido'] === 0): ?>
                                    <span class="estatus-pendiente"><?= $prenda['estatusPedido'] = 'Pendiente' ?></span>
                                <?php elseif ($pedido['estatusPedido'] === 'entregado'): ?>
                                    <span class="estatus-entregado">Entregado</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format($pedido['totalPedido'], 2) ?></td>
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
                    <tr>
                        <td colspan="8" class="sin-registros">No hay pedidos registrados aún.</td>
                    </tr>
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