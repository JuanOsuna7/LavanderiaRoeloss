<?php
require_once 'config.php';
require_once 'auth.php';

try {
    $sql = "SELECT 
                c.pk_cliente AS id_cliente,
                p.nombres,
                p.aPaterno,
                p.aMaterno,
                c.telefono,
                c.estatusCli,
                DATE_FORMAT(cast(cast(c.pk_cliente as unsigned) as datetime), '%d/%m/%Y %H:%i') as fecha_registro
            FROM clientes c
            INNER JOIN personas p ON c.fk_persona = p.pk_persona
            ORDER BY c.pk_cliente DESC
            LIMIT 5";
    $stmt = $pdo->query($sql);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar los clientes: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inicio - Últimos clientes</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
<div class="fondo-ilustrado"></div>

<header class="navbar">
    <div class="nav-left">
        <a href="index.php"><img src="img/logo.png" alt="Logo" class="logo"></a>
        <a href="nuevo_cliente.php">Registrar nuevo cliente</a>
        <a href="historial.php">Historial de registros</a>
        <a href="nuevo_pedido.php">Crear nuevo pedido</a>
    </div>
    <div class="nav-right">
        <div class="user-info">
            <div class="user-icon">
                <?= strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)) ?>
            </div>
            <span class="user-name">
                <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? $_SESSION['usuario_nombre']) ?>
            </span>
        </div>
        <button class="btn-cerrar" onclick="cerrarSesion()">
            <span class="logout-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16,17 21,12 16,7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </span>
            Cerrar sesión
        </button>
    </div>
</header>

<main>
    <h1>Clientes registrados</h1>
    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre completo</th>
                    <th>Teléfono</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($clientes)): ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?= htmlspecialchars($cliente['id_cliente']) ?></td>
                            <td><?= htmlspecialchars($cliente['nombres'] . ' ' . $cliente['aPaterno'] . ' ' . $cliente['aMaterno']) ?></td>
                            <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                            <td>
                                <?php if (strtolower($cliente['estatusCli']) === 'activo'): ?>
                                    <span class="estatus-activo">Activo</span>
                                <?php else: ?>
                                    <span class="estatus-inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="sin-registros">No hay clientes registrados aún.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function cerrarSesion() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        window.location.href = 'logout.php';
    }
}
</script>

</body>
</html>
