<?php
require_once 'config.php';
require_once 'auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

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
    echo json_encode(['status' => 'error', 'message' => 'Nombre de usuario inválido.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Actualizar persona
    $sqlP = "UPDATE personas SET nombres = :nombres, aPaterno = :aPaterno, aMaterno = :aMaterno WHERE pk_persona = :fk";
    $stmt = $pdo->prepare($sqlP);
    $stmt->execute([
        ':nombres' => $nombres,
        ':aPaterno' => $aPaterno,
        ':aMaterno' => $aMaterno,
        ':fk' => $fk_persona
    ]);

    // Actualizar usuario (correo y opcionalmente contraseña)
    if ($password !== '') {
        if (strlen($password) < 6 || strlen($password) > 50) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener entre 6 y 50 caracteres.']);
            exit;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sqlU = "UPDATE usuarios SET correoUsu = :correo, contrasUsu = :pass WHERE pk_usuario = :id";
        $stmt2 = $pdo->prepare($sqlU);
        $stmt2->execute([':correo' => $correo, ':pass' => $hash, ':id' => $id]);
    } else {
        $sqlU = "UPDATE usuarios SET correoUsu = :correo WHERE pk_usuario = :id";
        $stmt2 = $pdo->prepare($sqlU);
        $stmt2->execute([':correo' => $correo, ':id' => $id]);
    }

    $pdo->commit();

    echo json_encode(['status' => 'ok', 'message' => 'Usuario actualizado correctamente.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar usuario: ' . $e->getMessage()]);
}

?>
