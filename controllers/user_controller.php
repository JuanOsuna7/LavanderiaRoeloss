<?php
// Controller: controllers/user_controller.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Person.php';
require_once __DIR__ . '/../models/User.php';

// Simple router for actions: 'create' (POST) and 'list' (GET)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'list') {
    try {
        $usuarios = User::listaUsu();
        require_once __DIR__ . '/../views/listaUsuarios.php';
        exit;
    } catch (PDOException $e) {
        echo '<p>Error al listar usuarios: ' . htmlspecialchars($e->getMessage()) . '</p>';
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
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $fk_persona = filter_input(INPUT_POST, 'fk_persona', FILTER_VALIDATE_INT);
    $nombres = trim($_POST['nombres'] ?? '');
    $aPaterno = trim($_POST['aPaterno'] ?? '');
    $aMaterno = trim($_POST['aMaterno'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$id || !$fk_persona) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
        exit;
    }

    if ($nombres === '' || $aPaterno === '' || $correo === '') {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
        exit;
    }

    if (strlen($nombres) < 2 || strlen($nombres) > 50) {
        echo json_encode(['status' => 'error', 'message' => 'Nombre inválido.']);
        exit;
    }

    if (strlen($correo) < 2 || strlen($correo) > 50) {
        echo json_encode(['status' => 'error', 'message' => 'Correo inválido.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $filasPer = Person::actualizarPer($nombres, $aPaterno, $aMaterno, $fk_persona);
        $filasUsu = User::actualizarUsu($correo, $password, $id);

        $pdo->commit();

        echo json_encode([
            'status' => 'ok',
            'message' => 'Usuario actualizado correctamente.',
            'filas_afectadas' => $filasUsu
        ]);
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error al actualizar usuario: ' . $e->getMessage()
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
        $nombres = trim($_POST['nombres'] ?? '');
        $aPaterno = trim($_POST['aPaterno'] ?? '');
        $aMaterno = trim($_POST['aMaterno'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');

        if ($nombres === '' || $aPaterno === '' || $aMaterno === '' || $password === '' || $usuario === '') {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
            exit;
        }

        if (strlen($nombres) < 4 || strlen($nombres) > 50) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre debe tener entre 4 y 50 caracteres.']);
            exit;
        }

        if (strlen($aPaterno) < 4 || strlen($aPaterno) > 30) {
            echo json_encode(['status' => 'error', 'message' => 'El apellido paterno debe tener entre 4 y 30 caracteres.']);
            exit;
        }

        if (strlen($aMaterno) < 4 || strlen($aMaterno) > 30) {
            echo json_encode(['status' => 'error', 'message' => 'El apellido materno debe tener entre 4 y 30 caracteres.']);
            exit;
        }

        if(strlen($password) < 8 || strlen($password) > 50) {
            echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener entre 8 y 50 caracteres.']);
            exit;
        }

        if (strlen($usuario) < 4 || strlen($usuario) > 100) {
            echo json_encode(['status' => 'error', 'message' => 'El usuario debe tener entre 4 y 100 caracteres.']);
            exit;
        }

        // Transaction: insert into personas then usuarios
        $pdo->beginTransaction();

        $id_persona = Person::create($nombres, $aPaterno, $aMaterno);

        $passHash = password_hash($password, PASSWORD_DEFAULT);

        $id_usuario = User::create($usuario, $passHash, $id_persona);

        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Usuario registrado exitosamente']);
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar usuario: ' . $e->getMessage()]);
        exit;
    }
}

// Unknown action
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'error', 'message' => 'Acción no permitida']);
exit;
