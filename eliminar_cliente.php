<?php
require_once 'config.php'; // debe exponer $pdo
// require_once 'auth.php'; // opcional: incluye solo si no imprime salida

// detecta columna PK probable en tabla 'clientes'
function detectarColumnaId(PDO $pdo) {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM clientes")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            $f = $c['Field'];
            if (preg_match('/^(pk_|id_|.*_cliente$)/i', $f)) return $f;
        }
        return $cols[0]['Field'] ?? 'id';
    } catch (Exception $e) {
        return 'id';
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// aceptar tanto "id" como "id_cliente" por compatibilidad
$idInput = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$idInput) {
    $idInput = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
}

if (!$idInput) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$idCol = detectarColumnaId($pdo);

try {
    $sql = "DELETE FROM clientes WHERE `$idCol` = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idInput]);

    header('Location: index.php?msg=deleted');
    exit;
} catch (Exception $e) {
    // opcional: error_log($e->getMessage());
    header('Location: index.php?error=delete_failed');
    exit;
}