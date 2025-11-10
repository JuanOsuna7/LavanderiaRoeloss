<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Headers de seguridad
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido'
    ]);
    exit;
}

try {
    // Obtener y validar datos
    $pedidoId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nuevoEstatus = filter_input(INPUT_POST, 'estatus', FILTER_VALIDATE_INT);

    // Validaciones básicas
    if (!$pedidoId) {
        throw new Exception('ID de pedido inválido.');
    }

    if ($nuevoEstatus === false || !in_array($nuevoEstatus, [0, 1, 2])) {
        throw new Exception('Estado inválido. Los valores permitidos son: 0 (Cancelado), 1 (Pendiente), 2 (Entregado).');
    }

    // Verificar que el pedido existe
    $stmt = $pdo->prepare("SELECT pk_pedido, estatusPedido FROM pedidos WHERE pk_pedido = ?");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new Exception('El pedido especificado no existe.');
    }

    // Verificar si el estado ya es el mismo
    if ($pedido['estatusPedido'] == $nuevoEstatus) {
        throw new Exception('El pedido ya tiene ese estado.');
    }

    // Actualizar el estado del pedido
    $stmt = $pdo->prepare("UPDATE pedidos SET estatusPedido = ? WHERE pk_pedido = ?");
    $stmt->execute([$nuevoEstatus, $pedidoId]);

    // Verificar que se actualizó correctamente
    if ($stmt->rowCount() === 0) {
        throw new Exception('No se pudo actualizar el estado del pedido.');
    }

    // Obtener nombre del estado para el log
    $estados = [
        0 => 'Cancelado',
        1 => 'Pendiente',
        2 => 'Entregado'
    ];

    // Registrar actividad
    error_log("Estado de pedido cambiado - ID: $pedidoId, Nuevo estado: {$estados[$nuevoEstatus]}, Usuario: " . ($_SESSION['usuario_id'] ?? 'N/A'));

    echo json_encode([
        'status' => 'ok',
        'message' => "Estado del pedido actualizado a {$estados[$nuevoEstatus]}",
        'pedido_id' => $pedidoId,
        'nuevo_estatus' => $nuevoEstatus,
        'nombre_estatus' => $estados[$nuevoEstatus]
    ]);

} catch (Exception $e) {
    error_log("Error al cambiar estado del pedido: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    
} catch (PDOException $e) {
    error_log("Error de base de datos al cambiar estado del pedido: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor. Por favor, intenta nuevamente.'
    ]);
}
?>