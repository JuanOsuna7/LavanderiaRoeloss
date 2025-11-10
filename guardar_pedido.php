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
    $clienteId = filter_input(INPUT_POST, 'cliente', FILTER_VALIDATE_INT);
    $tipoEntrega = filter_input(INPUT_POST, 'tipoEntrega', FILTER_SANITIZE_STRING);
    $total = filter_input(INPUT_POST, 'total', FILTER_VALIDATE_FLOAT);
    $prendasJson = $_POST['prendas'] ?? null;

    // Validaciones básicas
    if (!$clienteId || !$tipoEntrega || !$total || !$prendasJson) {
        throw new Exception('Datos incompletos. Todos los campos son requeridos.');
    }

    if ($total <= 0) {
        throw new Exception('El total del pedido debe ser mayor a cero.');
    }

    // Decodificar prendas
    $prendas = json_decode($prendasJson, true);
    if (!$prendas || !is_array($prendas) || count($prendas) === 0) {
        throw new Exception('Debe agregar al menos una prenda al pedido.');
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
        
        // Insertar pedido principal
        $stmt = $pdo->prepare("
            INSERT INTO pedidos (fechaDeRecibo, totalPedido, peso_total_kg, tipoEntrega, estatusPedido, fk_cliente) 
            VALUES (NOW(), ?, ?, ?, 1, ?)
        ");
        
        $stmt->execute([$total, $pesoTotal, $tipoEntrega, $clienteId]);
        $pedidoId = $pdo->lastInsertId();

        // Insertar cada prenda del pedido
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

        // Limpiar datos temporales de sesión
        unset($_SESSION['pedido_temp_id']);
        unset($_SESSION['pedido_items']);

        // Registrar actividad (opcional)
        error_log("Pedido creado exitosamente - ID: $pedidoId, Cliente: $clienteId, Total: $total, Usuario: " . ($_SESSION['usuario_id'] ?? 'N/A'));

        echo json_encode([
            'status' => 'ok',
            'message' => 'Pedido registrado correctamente',
            'pedido_id' => $pedidoId
        ]);

    } catch (Exception $e) {
        // Revertir transacción
        $pdo->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error al guardar pedido: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    
} catch (PDOException $e) {
    error_log("Error de base de datos al guardar pedido: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor. Por favor, intenta nuevamente.'
    ]);
}
?>
