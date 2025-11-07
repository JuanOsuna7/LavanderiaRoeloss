<?php
require_once 'navbar.php';
?>

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
