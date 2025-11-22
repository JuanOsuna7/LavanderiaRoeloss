<?php
// Controller: controllers/user_controller.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Pedido.php';

// Simple router for actions: 'create' (POST) and 'list' (GET)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Endpoint para obtener datos del pedido para el ticket
if ($action === 'get') {
    header('Content-Type: application/json; charset=utf-8');
    
    $pedidoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$pedidoId) {
        echo json_encode(['status' => 'error', 'message' => 'ID de pedido inválido']);
        exit;
    }
    
    try {
        // Obtener datos del pedido con cliente y prendas
        $sql = "SELECT 
                    p.pk_pedido,
                    p.fechaDeRecibo,
                    p.totalPedido,
                    p.peso_total_kg,
                    p.tipoEntrega,
                    p.estatusPedido,
                    CONCAT(per.nombres, ' ', per.aPaterno, ' ', per.aMaterno) AS nombreCliente,
                    c.telefono,
                    c.direccion
                FROM pedidos p
                INNER JOIN clientes c ON p.fk_cliente = c.pk_cliente
                INNER JOIN personas per ON c.fk_persona = per.pk_persona
                WHERE p.pk_pedido = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pedidoId]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            echo json_encode(['status' => 'error', 'message' => 'Pedido no encontrado']);
            exit;
        }
        
        // Obtener prendas del pedido
        $sqlPrendas = "SELECT 
                           ip.peso_kg,
                           ip.precio_unitario,
                           ip.subtotal,
                           tp.nombre_tipo
                       FROM items_pedido ip
                       INNER JOIN tipos_prenda tp ON ip.fk_tipo_prenda = tp.pk_tipo_prenda
                       WHERE ip.fk_pedido = ?
                       ORDER BY tp.nombre_tipo";
        
        $stmtPrendas = $pdo->prepare($sqlPrendas);
        $stmtPrendas->execute([$pedidoId]);
        $prendas = $stmtPrendas->fetchAll(PDO::FETCH_ASSOC);
        
        $pedido['prendas'] = $prendas;
        
        echo json_encode([
            'status' => 'ok',
            'data' => $pedido
        ]);
        exit;
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener pedido: ' . $e->getMessage()]);
        exit;
    }
}

//Condicional para listar
if ($action === 'list') {
    try {
        $pedidos = Pedido::historialPedido();
        require_once __DIR__ . '/../views/historial.php';
        exit;
    } catch (PDOException $e) {
        echo '<p>Error al listar usuarios: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}

// -----------------------------------------------------------------------------------
//Condicional para actualizar
if ($action === 'update') {
    header('Content-Type: application/json; charset=utf-8');

     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        exit;
    }

    // Recibir datos
        $pedidoId = filter_input(INPUT_POST, 'pedidoId', FILTER_VALIDATE_INT);
        $clienteId = filter_input(INPUT_POST, 'cliente', FILTER_VALIDATE_INT);
        $tipoEntrega = $_POST['tipoEntrega'] ?? '';
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

    try {
        $pdo->beginTransaction();

        $filasPer = Pedido::actualizar($pedidoId, $clienteId, $tipoEntrega, $estatusPedido, $total, $prendas);

        $pdo->commit();

        echo json_encode([
            'status' => 'ok',
            'message' => 'Pedido actualizado correctamente.',
            'filas_afectadas' => $filasPer
        ]);
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error al actualizar pedido: ' . $e->getMessage()
        ]);
        exit;
    }
}
//--------------------------------------------------------------------------------
// Condicional Create
if ($action === 'create') {
    header('Content-Type: application/json; charset=utf-8');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
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

        // Transaction: insert into personas then usuarios
        $pdo->beginTransaction();

    // Llamada al modelo
    $pedidoId = Pedido::crearPedido(
        $total,
        $tipoEntrega,
        $clienteId,
        $prendas
    );

    $pdo->commit();

        // Limpiar sesión
    unset($_SESSION['pedido_temp_id'], $_SESSION['pedido_items']);

    echo json_encode([
        'status' => 'ok',
        'message' => 'Pedido registrado correctamente',
        'pedido_id' => $pedidoId
    ]);
    exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar pedido: ' . $e->getMessage()]);
        exit;
    }

}

// Unknown action
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'error', 'message' => 'Acción no permitida']);
exit;
