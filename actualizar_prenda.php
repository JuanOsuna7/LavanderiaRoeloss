<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nombre = trim($_POST['nomPrenda'] ?? '');
$costoRaw = $_POST['costoPrenda'] ?? '';

// Validaciones básicas
if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID de prenda inválido.']);
    exit;
}

if ($nombre === '') {
    echo json_encode(['status' => 'error', 'message' => 'El nombre de la prenda es obligatorio.']);
    exit;
}

// Validar longitud y caracteres (letras y espacios, incluyendo acentos)
if (mb_strlen($nombre) > 50 || !preg_match('/^[A-Za-zÀ-ÿñÑ\s]+$/u', $nombre)) {
    echo json_encode(['status' => 'error', 'message' => 'Nombre de prenda inválido. Solo letras y espacios, máximo 50 caracteres.']);
    exit;
}

// Validar costo
if ($costoRaw === '' || !is_numeric($costoRaw)) {
    echo json_encode(['status' => 'error', 'message' => 'El costo de la prenda es obligatorio y debe ser numérico.']);
    exit;
}

$costo = floatval($costoRaw);
if ($costo < 0) {
    echo json_encode(['status' => 'error', 'message' => 'El costo no puede ser negativo.']);
    exit;
}

try {
    $sql = "UPDATE prendas SET nombrePrenda = :nombre, costoPrenda = :costo WHERE pk_prenda = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':costo' => $costo,
        ':id' => $id
    ]);

    if ($stmt->rowCount() === 0) {
        // No rows updated: puede ser que el ID no exista o que no se hayan modificado valores.
        echo json_encode(['status' => 'ok', 'message' => 'No se realizaron cambios o la prenda no existe.']);
        exit;
    }

    echo json_encode(['status' => 'ok', 'message' => 'Prenda actualizada correctamente.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la prenda: ' . $e->getMessage()]);
}

?>
