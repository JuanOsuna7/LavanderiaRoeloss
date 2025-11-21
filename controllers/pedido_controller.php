<?php
// Controller: controllers/user_controller.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Pedido.php';

// Simple router for actions: 'create' (POST) and 'list' (GET)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

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
