<?php
require_once 'config.php';
$pagina_actual = basename($_SERVER['PHP_SELF']);

try {
    // Consulta: últimos 5 pedidos
    $sql = "SELECT 
                p.pk_pedido AS id,
                CONCAT(per.nombres, ' ', per.aPaterno, ' ', per.aMaterno) AS cliente,
                s.nombreServicioRopa AS tipo_ropa,
                CONCAT(p.totalPedido, ' kg') AS peso,
                p.estatusPedido AS estatus,
                p.tipoEntrega AS tipo_entrega,
                CONCAT('$', p.totalPedido) AS total,
                DATE_FORMAT(p.fechaDeRecibo, '%d/%m/%Y') AS fecha
            FROM pedidos p
            INNER JOIN clientes c ON p.fk_cliente = c.pk_cliente
            INNER JOIN personas per ON c.fk_persona = per.pk_persona
            INNER JOIN serviciosropa s ON p.fk_servicioRopa = s.pk_servicioRopa
            ORDER BY p.pk_pedido DESC
            LIMIT 5";
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
    <title>Inicio - Últimos pedidos</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

<header class="navbar">
    <div class="nav-left">
        <a href="index.php">
            <img src="img/logo.png" alt="Logo" class="logo">
        </a>
        <a href="nuevo_cliente.php" class="<?= $pagina_actual == 'nuevo_cliente.php' ? 'active' : '' ?>">Registrar nuevo cliente</a>
        <a href="historial.php" class="<?= $pagina_actual == 'historial.php' ? 'active' : '' ?>">Historial de registros</a>
        <a href="nuevo_pedido.php" class="<?= $pagina_actual == 'nuevo_pedido.php' ? 'active' : '' ?>">Crear nuevo pedido</a>
    </div>
    <div class="nav-right">
        <button class="btn-cerrar">Cerrar sesión</button>
    </div>
</header>

<main class="contenedor-historial">
    <h1>Últimos pedidos registrados</h1>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Tipo de ropa</th>
                    <th>Peso</th>
                    <th>Estatus</th>
                    <th>Entrega</th>
                    <th>Total</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pedidos): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= htmlspecialchars($pedido['cliente']) ?></td>
                            <td><?= htmlspecialchars($pedido['tipo_ropa']) ?></td>
                            <td><?= htmlspecialchars($pedido['peso']) ?></td>
                            <td><?= htmlspecialchars($pedido['estatus']) ?></td>
                            <td><?= htmlspecialchars($pedido['tipo_entrega']) ?></td>
                            <td><?= htmlspecialchars($pedido['total']) ?></td>
                            <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No hay pedidos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
