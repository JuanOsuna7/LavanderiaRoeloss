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
    // Obtener y validar datos del formulario
    $pedidoId = filter_input(INPUT_POST, 'pedidoId', FILTER_VALIDATE_INT);
    $clienteId = filter_input(INPUT_POST, 'cliente', FILTER_VALIDATE_INT);
    $tipoEntrega = filter_input(INPUT_POST, 'tipoEntrega', FILTER_SANITIZE_STRING);
    $estatusPedido = filter_input(INPUT_POST, 'estatusPedido', FILTER_VALIDATE_INT);
    $total = filter_input(INPUT_POST, 'total', FILTER_VALIDATE_FLOAT);
    $prendasJson = $_POST['prendas'] ?? null;

    // Validaciones básicas
    if (!$pedidoId || !$clienteId || !$tipoEntrega || ($estatusPedido === false) || !$total || !$prendasJson) {
        throw new Exception('Datos incompletos. Todos los campos son requeridos.');
    }

    if ($total <= 0) {
        throw new Exception('El total del pedido debe ser mayor a cero.');
    }

    if (!in_array($estatusPedido, [0, 1, 2])) {
        throw new Exception('Estado del pedido inválido.');
    }

    // Decodificar prendas
    $prendas = json_decode($prendasJson, true);
    if (!$prendas || !is_array($prendas) || count($prendas) === 0) {
        throw new Exception('Debe tener al menos una prenda en el pedido.');
    }

    // Verificar que el pedido existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE pk_pedido = ?");
    $stmt->execute([$pedidoId]);
    if ($stmt->fetchColumn() === 0) {
        throw new Exception('El pedido especificado no existe.');
    }

    // Validar que el cliente exista
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE pk_cliente = ? AND estatusCli = 'Activo'");
    $stmt->execute([$clienteId]);
    if ($stmt->fetchColumn() === 0) {
        throw new Exception('El cliente seleccionado no existe o está inactivo.');
    }

    // Validar cada prenda
    foreach ($prendas as $index => $prenda) {
        if (empty($prenda['tipoPrenda']) || empty($prenda['peso']) || empty($prenda['precioUnitario'])) {
            throw new Exception("Datos incompletos en la prenda " . ($index + 1));
        }
        
        if ($prenda['peso'] <= 0 || $prenda['precioUnitario'] <= 0) {
            throw new Exception("Peso y precio deben ser mayores a cero en la prenda " . ($index + 1));
        }
        
        // Verificar que el tipo de prenda existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tipos_prenda WHERE pk_tipo_prenda = ? AND estatus = 1");
        $stmt->execute([$prenda['tipoPrenda']]);
        if ($stmt->fetchColumn() === 0) {
            throw new Exception("Tipo de prenda inválido en la prenda " . ($index + 1));
        }
    }

    // Iniciar transacción
    $pdo->beginTransaction();

    try {
        // Calcular peso total
        $pesoTotal = array_sum(array_column($prendas, 'peso'));
        
        // Actualizar pedido principal
        $stmt = $pdo->prepare("
            UPDATE pedidos 
            SET totalPedido = ?, peso_total_kg = ?, tipoEntrega = ?, estatusPedido = ?, fk_cliente = ?
            WHERE pk_pedido = ?
        ");
        
        $stmt->execute([$total, $pesoTotal, $tipoEntrega, $estatusPedido, $clienteId, $pedidoId]);

        // Eliminar todos los items anteriores del pedido
        $stmt = $pdo->prepare("DELETE FROM items_pedido WHERE fk_pedido = ?");
        $stmt->execute([$pedidoId]);

        // Insertar los nuevos items del pedido
        $stmt = $pdo->prepare("
            INSERT INTO items_pedido (fk_pedido, fk_tipo_prenda, peso_kg, precio_unitario, subtotal) 
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($prendas as $prenda) {
            $subtotal = $prenda['peso'] * $prenda['precioUnitario'];
            $stmt->execute([
                $pedidoId,
                $prenda['tipoPrenda'],
                $prenda['peso'],
                $prenda['precioUnitario'],
                $subtotal
            ]);
        }

        // Confirmar transacción
        $pdo->commit();

        // Registrar actividad (opcional)
        error_log("Pedido actualizado exitosamente - ID: $pedidoId, Cliente: $clienteId, Total: $total, Estado: $estatusPedido, Usuario: " . ($_SESSION['usuario_id'] ?? 'N/A'));

        echo json_encode([
            'status' => 'ok',
            'message' => 'Pedido actualizado correctamente',
            'pedido_id' => $pedidoId
        ]);

    } catch (Exception $e) {
        // Revertir transacción
        $pdo->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error al actualizar pedido: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    
} catch (PDOException $e) {
    error_log("Error de base de datos al actualizar pedido: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor. Por favor, intenta nuevamente.'
    ]);
}
?>