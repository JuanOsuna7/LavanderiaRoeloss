<?php
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM pedidos WHERE pk_pedido = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode(['status' => 'ok']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar pedido: ' . $e->getMessage()]);
    }
}
?>
