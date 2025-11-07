<?php
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tRopa = $_POST['nomPrenda'] ?? '';
    $costoRopa = $_POST['costoPrenda'] ?? '';

    if (empty($tRopa) || empty($costoRopa)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
        exit;
    }

    try {
        $sql = "INSERT INTO prendas (nombrePrenda, costoPrenda, estatusPrenda)
                VALUES (:nombreP, :costoP, :estatusP)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombreP' => $tRopa,
            ':costoP' => $costoRopa,
            ':estatusP' => 1
        ]);

        echo json_encode(['status' => 'ok', 'message' => 'Prenda registrada correctamente.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar prenda: ' . $e->getMessage()]);
    }
}
?>
