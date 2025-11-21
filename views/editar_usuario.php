<?php
// require_once 'config.php';
// require_once 'auth.php';
require_once __DIR__ . '/../navbar.php';

$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$userId) {
    header('Location: listaUsuarios.php');
    exit;
}

try {
    $sql = "SELECT u.pk_usuario, u.correoUsu, u.estatusUsu, u.fk_persona, p.nombres, p.aPaterno, p.aMaterno
            FROM usuarios u
            INNER JOIN personas p ON u.fk_persona = p.pk_persona
            WHERE u.pk_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: listaUsuarios.php');
        exit;
    }
} catch (PDOException $e) {
    die('Error al obtener usuario: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Editar usuario</title>
    <link rel="stylesheet" href="estilos.css">
    <script src="custom-alerts.js"></script>
    <style>
        .form-edit { max-width: 720px; margin: 0 auto; }
        .field-row { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
        .nota { font-size:0.9rem; color:#6b7280; margin-top:6px; }
        .actions { display:flex; gap:12px; justify-content:flex-end; margin-top:16px; }
    </style>
</head>
<body>
<main>
    <h1>Editar usuario #<?= htmlspecialchars($user['pk_usuario']) ?></h1>

    <div class="form-container form-edit">
        <form id="formEditarUsuario">
            <input type="hidden" name="id" value="<?= htmlspecialchars($user['pk_usuario']) ?>">
            <input type="hidden" name="fk_persona" value="<?= htmlspecialchars($user['fk_persona']) ?>">

            <div class="field-row">
                <div>
                    <label for="nombres">Nombres</label>
                    <input id="nombres" name="nombres" required value="<?= htmlspecialchars($user['nombres']) ?>">
                </div>
                <div>
                    <label for="aPaterno">Apellido paterno</label>
                    <input id="aPaterno" name="aPaterno" required value="<?= htmlspecialchars($user['aPaterno']) ?>">
                </div>
            </div>

            <div class="field-row" style="margin-top:12px;">
                <div>
                    <label for="aMaterno">Apellido materno</label>
                    <input id="aMaterno" name="aMaterno" value="<?= htmlspecialchars($user['aMaterno']) ?>">
                </div>
                <div>
                    <label for="correo">Nombre de Usuario</label>
                    <input id="correo" name="correo" type="text" required value="<?= htmlspecialchars($user['correoUsu']) ?>">
                </div>
            </div>

            <div style="margin-top:12px;">
                <label for="password">Contraseña (dejar vacío para mantener la actual)</label>
                <div style="display:flex; gap:8px; align-items:center;">
                    <input id="password" name="password" type="text" placeholder="Ingrese nueva contraseña o genere una temporal" style="flex:1;">
                    <button type="button" id="btnGenerar" class="btn">Generar</button>
                </div>
                <div class="nota">Nota: por seguridad las contraseñas se almacenan hasheadas; no es posible recuperar la contraseña original. Puedes generar una contraseña temporal que se mostrará aquí.</div>
            </div>

            <div class="actions">
                <a href="<?= BASE_URL ?>controllers/user_controller.php?action=list" class="btn btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</main>

<script>
function generarPassword(length = 10) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*()-_';
    let out = '';
    for (let i=0;i<length;i++) out += chars.charAt(Math.floor(Math.random()*chars.length));
    return out;
}

document.getElementById('btnGenerar').addEventListener('click', () => {
    const pwd = generarPassword(12);
    document.getElementById('password').value = pwd;
    showSuccess('Se generó una contraseña temporal. Revísala antes de guardar.');
});

document.getElementById('formEditarUsuario').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = new FormData(this);

    try {
         const resp = await fetch('<?= BASE_URL ?>controllers/user_controller.php?action=update', {
        method: 'POST',
        body: form
    });
        const data = await resp.json();
        if (data.status === 'ok') {
            showSuccess(data.message || 'Usuario actualizado');
            setTimeout(() => window.location.href = "<?= BASE_URL ?>controllers/user_controller.php?action=list", 1200);
        } else {
            showError(data.message || 'Error al actualizar');
        }
    } catch (err) {
        console.error(err);
        showError('Error de conexión');
    }
});

async function cerrarSesion() {
    const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
    
    if (confirmed) {
        window.location.href = 'logout.php';
    }
}
</script>
</body>
</html>
