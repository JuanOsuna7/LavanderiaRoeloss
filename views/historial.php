<?php
require_once __DIR__ . '/../navbar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Historial de Pedidos</title>
</head>
<body>

<main>
    <h1>Historial de pedidos</h1>

    <div class="contenedor-acciones">
        <button onclick="window.location.href='<?= BASE_URL ?>views/nuevo_pedido.php'" class="btn-principal btn-agregar">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Pedido
        </button>
    </div>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Núm. Pedido</th>
                    <th>Cliente</th>
                    <th>Tipos de prendas</th>
                    <th>Peso total</th>
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
                            <td><?= htmlspecialchars($pedido['tipos_prenda'] ?? 'Sin prendas') ?></td>
                            <td><?= number_format($pedido['peso_total_kg'], 1) ?> kg</td>
                            <td><?= htmlspecialchars($pedido['tipoEntrega']) ?></td>
                            <td>
                                <?php if ($pedido['estatusPedido'] == 1): ?>
                                    <span class="estatus-pendiente">Pendiente</span>
                                <?php elseif ($pedido['estatusPedido'] == 2): ?>
                                    <span class="estatus-entregado">Entregado</span>
                                <?php else: ?>
                                    <span class="estatus-cancelado">Cancelado</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format($pedido['totalPedido'], 2) ?></td>
                            <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                            <td class="acciones">
                                <!-- Botón cambiar a pendiente -->
                                <?php if ($pedido['estatusPedido'] != 1): ?>
                                    <button title="Marcar como pendiente" onclick="cambiarEstatus(<?= $pedido['id_pedido'] ?>, 1)" class="btn-estado btn-pendiente">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <polyline points="12,6 12,12 16,14"/>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Botón cambiar a entregado -->
                                <?php if ($pedido['estatusPedido'] != 2): ?>
                                    <button title="Marcar como entregado" onclick="cambiarEstatus(<?= $pedido['id_pedido'] ?>, 2)" class="btn-estado btn-entregado">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 6L9 17l-5-5"/>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Botón cambiar a cancelado -->
                                <?php if ($pedido['estatusPedido'] != 0): ?>
                                    <button title="Marcar como cancelado" onclick="cambiarEstatus(<?= $pedido['id_pedido'] ?>, 0)" class="btn-estado btn-cancelado">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="15" y1="9" x2="9" y2="15"/>
                                            <line x1="9" y1="9" x2="15" y2="15"/>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Botón editar -->
                                <button title="Editar pedido" onclick="editarPedido(<?= $pedido['id_pedido'] ?>)" class="btn-accion btn-editar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                
                                <!-- Botón eliminar -->
                                <button title="Eliminar pedido" onclick="eliminarPedido(<?= $pedido['id_pedido'] ?>)" class="btn-accion btn-eliminar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                        <td colspan="9" class="sin-registros">No hay pedidos registrados aún.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function editarPedido(id) {
    window.location.href = "<?= BASE_URL ?>views/editar_pedido.php?id=" + id;
}

async function cambiarEstatus(id, nuevoEstatus) {
    const estados = {
        0: 'Cancelado',
        1: 'Pendiente', 
        2: 'Entregado'
    };
    
    const confirmed = await customConfirm(
        `¿Está seguro de que desea cambiar el estado del pedido a "${estados[nuevoEstatus]}"?`,
        'Confirmar cambio de estado'
    );
    
    if (confirmed) {
        try {
            const resp = await fetch('<?= BASE_URL ?>cambiar_estatus_pedido.php', {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${id}&estatus=${nuevoEstatus}`
            });
            const data = await resp.json();
            if (data.status === "ok") {
                showSuccess(`¡Estado cambiado a "${estados[nuevoEstatus]}" correctamente!`);
                setTimeout(() => location.reload(), 1500);
            } else {
                showError("Error: " + data.message);
            }
        } catch (error) {
            showError("Error de conexión. Por favor, intenta nuevamente.");
        }
    }
}

async function eliminarPedido(id) {
    const confirmed = await customConfirm("¿Desea eliminar el pedido?", "Confirmar eliminación");
    
    if (confirmed) {
        try {
            const resp = await fetch("eliminar_pedido.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + id
            });
            const data = await resp.json();
            if (data.status === "ok") {
                showSuccess("¡Pedido eliminado correctamente!");
                setTimeout(() => location.reload(), 1500);
            } else {
                showError("Error: " + data.message);
            }
        } catch (error) {
            showError("Error de conexión. Por favor, intenta nuevamente.");
        }
    }
}

async function cerrarSesion() {
    const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
    
    if (confirmed) {
        window.location.href = '<?= BASE_URL ?>views/logout.php';
    }
}
</script>

</body>
</html>