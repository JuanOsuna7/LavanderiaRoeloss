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
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $sexo = 'No especificado'; // Campo opcional

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

    // Transacción
    $pdo->beginTransaction();

    // 1️⃣ Insertar persona
    $sqlPersona = "INSERT INTO personas (nombres, aPaterno, aMaterno, sexo)
                   VALUES (:nombres, :aPaterno, :aMaterno, :sexo)";
    $stmt1 = $pdo->prepare($sqlPersona);
    $stmt1->execute([
        ':nombres' => $nombres,
        ':aPaterno' => $aPaterno,
        ':aMaterno' => $aMaterno,
        ':sexo' => $sexo
    ]);
    $id_persona = $pdo->lastInsertId();

    // 2️⃣ Insertar cliente (estatusCli = 'Activo')
    $sqlCliente = "INSERT INTO clientes (telefono, direccion, estatusCli, fk_persona)
                   VALUES (:telefono, :direccion, 'Activo', :fk_persona)";
    $stmt2 = $pdo->prepare($sqlCliente);
    $stmt2->execute([
        ':telefono' => $telefono,
        ':direccion' => $direccion,
        ':fk_persona' => $id_persona
    ]);

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Cliente registrado exitosamente']);
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar cliente: ' . $e->getMessage()]);
    exit;
}
?>
