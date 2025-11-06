<?php
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cliente = $_POST['cliente'] ?? '';
    $tipoRopa = $_POST['tipoRopa'] ?? '';
    $tipoServicio = $_POST['tipoServicio'] ?? '';
    $tipoEntrega = $_POST['tipoEntrega'] ?? '';
    $total = $_POST['total'] ?? '';
    $estatus = $_POST['estatus'] ?? 'Pendiente';

    if (empty($cliente) || empty($tipoRopa) || empty($tipoServicio) || empty($tipoEntrega) || empty($total)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
        exit;
    }

    try {
        $sql = "INSERT INTO pedidos (fechaDeRecibo, totalPedido, tipoEntrega, estatusPedido, fk_cliente, fk_servicioRopa, tipo_ropa, tipo_servicio)
                VALUES (NOW(), :total, :tipoEntrega, :estatus, :cliente, :servicio, :tipoRopa, :tipoServicio)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':total' => $total,
            ':tipoEntrega' => $tipoEntrega,
            ':estatus' => $estatus,
            ':cliente' => $cliente,
            ':servicio' => 1, // por si fk_servicioRopa es obligatorio, pero ya no se usa en este caso
            ':tipoRopa' => $tipoRopa,
            ':tipoServicio' => $tipoServicio
        ]);

        echo json_encode(['status' => 'ok', 'message' => 'Pedido registrado correctamente.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar pedido: ' . $e->getMessage()]);
    }
}
?>
