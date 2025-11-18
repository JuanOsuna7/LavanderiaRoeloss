<?php
require_once 'config.php';
require_once 'auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$estatus = filter_input(INPUT_POST, 'estatus', FILTER_VALIDATE_INT);

if (!$id || ($estatus !== 0 && $estatus !== 1)) {
    echo json_encode(['status' => 'error', 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE usuarios SET estatusUsu = :estatus WHERE pk_usuario = :id");
    $stmt->execute([':estatus' => $estatus, ':id' => $id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado o sin cambios.']);
        exit;
    }

    echo json_encode(['status' => 'ok', 'message' => 'Estatus actualizado correctamente.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar estatus: ' . $e->getMessage()]);
}

?>
