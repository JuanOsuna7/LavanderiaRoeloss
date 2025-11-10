<?php
require_once 'config.php';
require_once 'navbar.php';
require_once 'auth.php';

// Consulta de todos los pedidos con los datos del cliente y servicio
try {
    $sql = "SELECT * FROM prendas";
    $stmt = $pdo->query($sql);
    $prendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar las prendas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Prendas Registradas</title>
<link rel="stylesheet" href="estilos.css">
<script src="custom-alerts.js"></script>
</head>
<body>

<main>
    <h1>Prendas Registradas</h1>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>ID Prenda</th>
                    <th>Nombre Prenda</th>
                    <th>Costo</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($prendas)): ?>
                    <?php foreach ($prendas as $prenda): ?>
                        <tr>
                            <td><?= htmlspecialchars($prenda['pk_prenda']) ?></td>
                            <td><?= htmlspecialchars($prenda['nombrePrenda']) ?></td>
                             <td>$<?= number_format($prenda['costoPrenda'], 2) ?></td>
                            <td>
                                <?php if ($prenda['estatusPrenda'] === 1): ?>
                                    <span class="estatus-entregado"><?= $prenda['estatusPrenda'] = 'Activa' ?></span>
                                <?php elseif ($prenda['estatusPrenda'] === 0): ?>
                                    <span class="estatus-entregado"><?= $prenda['estatusPrenda'] = 'Inactiva' ?></span>
                                 <?php endif; ?>
                            </td>
                            <td class="acciones">
                                <button title="Editar" onclick="editarPedido(<?= $prenda['pk_prenda'] ?>)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <button title="Eliminar" onclick="eliminarPedido(<?= $prenda['pk_prenda'] ?>)">
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
                        <td colspan="8" class="sin-registros">No hay prendas registradas aún.</td>
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
        window.location.href = 'logout.php';
    }
}
</script>

</body>
</html>