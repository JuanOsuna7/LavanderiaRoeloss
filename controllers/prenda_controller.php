<?php
// Controller: controllers/user_controller.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Prenda.php';

// Simple router for actions: 'create' (POST) and 'list' (GET)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

//Condicional para listar
if ($action === 'list') {
    try {
        $prendas = Prenda::listaPrendas();
        require_once __DIR__ . '/../views/listaPrendas.php';
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

    // Recibir y validar datos
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
        $pdo->beginTransaction();

        $filasPer = Prenda::actualizarPrenda($nombre, $costo, $id);

        $pdo->commit();

        echo json_encode([
            'status' => 'ok',
            'message' => 'Prenda actualizada correctamente.',
            'filas_afectadas' => $filasPer
        ]);
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error al actualizar prenda: ' . $e->getMessage()
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
        $tRopa = $_POST['nomPrenda'] ?? '';
        $costoRopa = $_POST['costoPrenda'] ?? '';

    if (empty($tRopa) || empty($costoRopa)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
        exit;
    }

        // Transaction: insert into personas then usuarios
        $pdo->beginTransaction();

        $id_persona = Prenda::createPrenda($tRopa, $costoRopa);

        $pdo->commit();

        echo json_encode(['status' => 'ok', 'message' => 'Prenda registrada correctamente.']);
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar prenda: ' . $e->getMessage()]);
        exit;
    }

}

// Unknown action
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'error', 'message' => 'Acción no permitida']);
exit;
