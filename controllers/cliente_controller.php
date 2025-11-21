<?php
// Controller: controllers/user_controller.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Person.php';
require_once __DIR__ . '/../models/Cliente.php';

// Simple router for actions: 'create' (POST) and 'list' (GET)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'list') {
    try {
        $clientes = Cliente::listaCli();
        require_once __DIR__ . '/../views/index.php';
        exit;
    } catch (PDOException $e) {
        echo '<p>Error al listar clientes: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}

if ($action === 'update') {
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        exit;
    }

    // Recibir y validar datos
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT); // pk_cliente
    $personaId = filter_input(INPUT_POST, 'personaId', FILTER_VALIDATE_INT); // pk_persona
    $nombres = trim($_POST['nombres'] ?? '');
    $aPaterno = trim($_POST['aPaterno'] ?? '');
    $aMaterno = trim($_POST['aMaterno'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
        exit;
    }

    // Validación básica
        if ($nombres === '' || $aPaterno === '' || $aMaterno === '' || $telefono === '' || $direccion === '') {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
            exit;
        }

        // Validar longitud de campos
        if (strlen($nombres) < 2 || strlen($nombres) > 50) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre debe tener entre 2 y 50 caracteres.']);
            exit;
        }

        if (strlen($aPaterno) < 2 || strlen($aPaterno) > 30) {
            echo json_encode(['status' => 'error', 'message' => 'El apellido paterno debe tener entre 2 y 30 caracteres.']);
            exit;
        }

        if (strlen($aMaterno) < 2 || strlen($aMaterno) > 30) {
            echo json_encode(['status' => 'error', 'message' => 'El apellido materno debe tener entre 2 y 30 caracteres.']);
            exit;
        }

        if (!preg_match('/^[0-9]{10}$/', $telefono)) {
            echo json_encode(['status' => 'error', 'message' => 'El teléfono debe contener exactamente 10 dígitos.']);
            exit;
        }

        if (strlen($direccion) < 10 || strlen($direccion) > 200) {
            echo json_encode(['status' => 'error', 'message' => 'La dirección debe tener entre 10 y 200 caracteres.']);
            exit;
        }

    try {
        $pdo->beginTransaction();

        $filasPer = Person::actualizarPer($nombres, $aPaterno, $aMaterno, $personaId);
        $filasCli = Cliente::actualizarCli($telefono, $direccion, $id);
       
        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Cliente actualizado correctamente.',
            'filas_afectadas' => $filasCli
        ]);
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error al actualizar cliente: ' . $e->getMessage()
        ]);
        exit;
    }
}
// Condicional Create
if ($action === 'create') {
    header('Content-Type: application/json; charset=utf-8');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        exit;
    }

    try {
        // Recibir datos del formulario
        $nombres = trim($_POST['nombres'] ?? '');
        $aPaterno = trim($_POST['aPaterno'] ?? '');
        $aMaterno = trim($_POST['aMaterno'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');

        // Validación básica
        if ($nombres === '' || $aPaterno === '' || $aMaterno === '' || $telefono === '' || $direccion === '') {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
            exit;
        }

        // Validar longitud de campos
        if (strlen($nombres) < 2 || strlen($nombres) > 50) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre debe tener entre 2 y 50 caracteres.']);
            exit;
        }

        if (strlen($aPaterno) < 2 || strlen($aPaterno) > 30) {
            echo json_encode(['status' => 'error', 'message' => 'El apellido paterno debe tener entre 2 y 30 caracteres.']);
            exit;
        }

        if (strlen($aMaterno) < 2 || strlen($aMaterno) > 30) {
            echo json_encode(['status' => 'error', 'message' => 'El apellido materno debe tener entre 2 y 30 caracteres.']);
            exit;
        }

        if (!preg_match('/^[0-9]{10}$/', $telefono)) {
            echo json_encode(['status' => 'error', 'message' => 'El teléfono debe contener exactamente 10 dígitos.']);
            exit;
        }

        if (strlen($direccion) < 10 || strlen($direccion) > 200) {
            echo json_encode(['status' => 'error', 'message' => 'La dirección debe tener entre 10 y 200 caracteres.']);
            exit;
        }

        // Transaction: insert into personas then usuarios
        $pdo->beginTransaction();

        $id_persona = Person::create($nombres, $aPaterno, $aMaterno);

        $id_cliente = Cliente::crearCliente($telefono, $direccion, $id_persona);

        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Cliente registrado exitosamente']);
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar cliente: ' . $e->getMessage()]);
        exit;
    }
}

// Unknown action
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'error', 'message' => 'Acción no permitida']);
exit;
