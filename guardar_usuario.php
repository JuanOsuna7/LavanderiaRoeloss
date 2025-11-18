<?php
require_once 'config.php';
require_once 'auth.php';
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
    $password = trim($_POST['password'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');

    // Validación básica
    if ($nombres === '' || $aPaterno === '' || $aMaterno === '' || $password === '' || $usuario === '') {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
        exit;
    }

    // Validar longitud de campos
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

    if(strlen($password) < 8 || strlen($password) > 20) {
        echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener entre 8 y 20 caracteres.']);
        exit;
    }

    if (strlen($usuario) < 4 || strlen($usuario) > 15) {
        echo json_encode(['status' => 'error', 'message' => 'El usuario debe tener entre 4 y 15 caracteres.']);
        exit;
    }

    // Transacción
    $pdo->beginTransaction();

    // 1️⃣ Insertar persona
    $sqlPersona = "INSERT INTO personas (nombres, aPaterno, aMaterno)
                   VALUES (:nombres, :aPaterno, :aMaterno)";
    $stmt1 = $pdo->prepare($sqlPersona);
    $stmt1->execute([
        ':nombres' => $nombres,
        ':aPaterno' => $aPaterno,
        ':aMaterno' => $aMaterno
    ]);
    $id_persona = $pdo->lastInsertId();

    $passHash = password_hash($password, PASSWORD_DEFAULT);

    // 2️⃣ Insertar cliente (estatusCli = 'Activo')
    $sqlCliente = "INSERT INTO usuarios (correoUsu, contrasUsu, rolUsu, estatusUsu, fk_persona)
                   VALUES (:usuario, :password, 1, 1, :fk_persona)";
    $stmt2 = $pdo->prepare($sqlCliente);
    $stmt2->execute([
        ':usuario' => $usuario,
        ':password' => $passHash,
        ':fk_persona' => $id_persona
    ]);

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Usuario registrado exitosamente']);
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar usuario: ' . $e->getMessage()]);
    exit;
}
?>
