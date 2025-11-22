<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM pedidos WHERE pk_pedido = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'ok', 'message' => 'Pedido eliminado correctamente']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontró el pedido o ya fue eliminado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar pedido: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
?>
