<?php
require_once 'config.php'; // Conexión PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1️⃣ Recibimos datos del formulario
    $fk_cliente      = $_POST['cliente'] ?? null;
    $fk_servicioRopa = $_POST['servicio'] ?? null;
    $totalPedido     = $_POST['total'] ?? null;
    $tipoEntrega     = $_POST['tipo_entrega'] ?? null;

    // Validar campos básicos
    if (empty($fk_cliente) || empty($fk_servicioRopa) || empty($totalPedido) || empty($tipoEntrega)) {
        echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios."]);
        exit;
    }

    try {
        // 2️⃣ Insertar el nuevo pedido
        $sql = "INSERT INTO pedidos 
                    (fechaDeRecibo, totalPedido, tipoEntrega, estatusPedido, fk_cliente, fk_servicioRopa)
                VALUES 
                    (NOW(), :totalPedido, :tipoEntrega, 'Pendiente', :fk_cliente, :fk_servicioRopa)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':totalPedido'     => $totalPedido,
            ':tipoEntrega'     => $tipoEntrega,
            ':fk_cliente'      => $fk_cliente,
            ':fk_servicioRopa' => $fk_servicioRopa
        ]);

        echo json_encode(["status" => "ok", "message" => "Pedido registrado correctamente"]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error al guardar el pedido: " . $e->getMessage()]);
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Historial de registros</title>
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
    <h1>Historial de registros</h1>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Cliente</th>
                    <th>Tipo de ropa</th>
                    <th>Peso</th>
                    <th>Estatus</th>
                    <th>Tipo de servicio</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pedidos)): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= htmlspecialchars($pedido['id']) ?></td>
                            <td><?= htmlspecialchars($pedido['cliente']) ?></td>
                            <td><?= htmlspecialchars($pedido['tipo_ropa']) ?></td>
                            <td><?= htmlspecialchars($pedido['peso']) ?></td>
                            <td><?= htmlspecialchars($pedido['estatus']) ?></td>
                            <td><?= htmlspecialchars($pedido['servicio']) ?></td>
                            <td><?= htmlspecialchars($pedido['total']) ?></td>
                            <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                            <td class="acciones">
                                <button class="icon-btn" title="Pendiente">⏰</button>
                                <button class="icon-btn" title="Completar">✔️</button>
                                <button class="icon-btn" title="Eliminar">🗑️</button>
                                <button class="icon-btn" title="Editar">✏️</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9">No hay pedidos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="volver">
        <a href="index.php" class="btn-aceptar">Volver</a>
    </div>
</main>

</body>
</html>
ZS