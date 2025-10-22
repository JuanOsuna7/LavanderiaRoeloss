<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recibir datos del formulario
    $nombre   = trim($_POST['nombre'] ?? '');
    $paterno  = trim($_POST['apellido_paterno'] ?? '');
    $materno  = trim($_POST['apellido_materno'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $sexo = $_POST['sexo'] ?? 'No especificado'; // por si luego agregas el campo

    // Validar campos mínimos
    if (empty($nombre) || empty($paterno) || empty($telefono)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1️⃣ Insertar en personas
        $sqlPersona = "INSERT INTO personas (nombres, aPaterno, aMaterno, sexo)
                       VALUES (:nombres, :aPaterno, :aMaterno, :sexo)";
        $stmt1 = $pdo->prepare($sqlPersona);
        $stmt1->execute([
            ':nombres' => $nombre,
            ':aPaterno' => $paterno,
            ':aMaterno' => $materno,
            ':sexo' => $sexo
        ]);

        // Obtener ID de persona creada
        $id_persona = $pdo->lastInsertId();

        // 2️⃣ Insertar en clientes
        $sqlCliente = "INSERT INTO clientes (telefono, estatusCli, fk_persona)
                       VALUES (:telefono, 'Activo', :fk_persona)";
        $stmt2 = $pdo->prepare($sqlCliente);
        $stmt2->execute([
            ':telefono' => $telefono,
            ':fk_persona' => $id_persona
        ]);

        $pdo->commit();

        echo json_encode(['status' => 'ok', 'message' => 'Cliente registrado correctamente']);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar cliente: ' . $e->getMessage()]);
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado del registro</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

<header class="navbar">
    <div class="nav-left">
        <img src="img/logo.png" alt="Logo" class="logo">
        <a href="nuevo_cliente.php">Registrar nuevo cliente</a>
        <a href="index.php">Historial de registros</a>
        <a href="#">Crear nuevo pedido</a>
    </div>
    <div class="nav-right">
        <button class="btn-cerrar">Cerrar sesión</button>
    </div>
</header>

<main class="contenedor-form">
    <div class="mensaje-confirmacion">
        <?php if ($clienteCreado): ?>
            <h2>¡Cliente creado correctamente!</h2>
            <p>El cliente <strong><?= htmlspecialchars($nombre) ?> <?= htmlspecialchars($apellido_paterno) ?></strong> ha sido registrado con éxito.</p>
            <a href="index.php" class="btn-aceptar">Aceptar</a>
        <?php else: ?>
            <h2>⚠️ Error al crear el cliente</h2>
            <p>Por favor, completa todos los campos requeridos.</p>
            <a href="nuevo_cliente.php" class="btn-cancelar">Regresar</a>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
