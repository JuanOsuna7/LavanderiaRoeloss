<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'navbar.php';

try {
    $sql = "SELECT u.pk_usuario, u.correoUsu, u.contrasUsu, u.estatusUsu, p.nombres, p.aPaterno, p.aMaterno
            FROM usuarios u
            LEFT JOIN personas p ON u.fk_persona = p.pk_persona
            ORDER BY p.nombres ASC";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar los usuarios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Lista de Usuarios</title>
    <link rel="stylesheet" href="estilos.css">
    <script src="custom-alerts.js"></script>
    <style>
        .top-actions { display:flex; gap:8px; justify-content:flex-end; margin-bottom:12px; }
        .btn-filter {
            padding:8px 12px;
            cursor:pointer;
            border-radius:6px;
            border:1px solid rgba(0,0,0,0.06);
            background: #fff;
            color: #111827;
            font-weight:600;
            box-shadow: 0 1px 0 rgba(0,0,0,0.02);
            transition: all .12s ease-in-out;
        }
        .btn-filter:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(2,6,23,0.04); }
        .btn-filter.active { background:#111827; color:#fff; border-color: rgba(0,0,0,0.12); }

        .masked-pass { font-family: monospace; letter-spacing: 2px; color:#6b7280; }

        /* .estatus-badge { display:inline-block; padding:6px 10px; border-radius:999px; font-weight:700; font-size:0.85rem; }
        /* Usar solo cambio de fondo como en historial; mantener color de texto inherente */
        /* .estatus-badge.estatus-activo { background-color: rgba(11, 201, 21, 0.12); }
        .estatus-badge.estatus-inactivo { background-color: rgba(250, 0, 0, 0.12); } */ */

        .acciones { display:flex; gap:8px; align-items:center; }
        .action-btn {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:34px;
            height:34px;
            border-radius:8px;
            border:1px solid transparent;
            background:transparent;
            cursor:pointer;
            transition:background .12s ease, transform .08s ease;
        }
        .action-btn svg { width:16px; height:16px; color: #ff0909ff; }
        .action-btn:hover { background:#f3f4f6; transform: translateY(-1px); }
        .action-btn.activate svg { color:#059669; }
        .action-btn.edit svg { color:#0ea5a4; }
    </style>
</head>
<body>

<main>
    <h1>Lista de Usuarios</h1>

    <div class="top-actions">
        <button id="showAll" class="btn-filter active">Todos</button>
        <button id="showActive" class="btn-filter">Activos</button>
        <button id="showInactive" class="btn-filter">Inactivos</button>
    </div>

    <div class="tabla-container">
        <table id="tablaUsuarios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre completo</th>
                    <th>Correo</th>
                    <th>Contraseña</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $u): ?>
                        <?php
                            $fullName = trim(($u['nombres'] ?? '') . ' ' . ($u['aPaterno'] ?? '') . ' ' . ($u['aMaterno'] ?? ''));
                            $estatus = ($u['estatusUsu'] == 1) ? 'Activo' : 'Inactivo';
                        ?>
                        <tr data-estatus="<?= $u['estatusUsu'] ?>">
                            <td><?= htmlspecialchars($u['pk_usuario']) ?></td>
                            <td><?= htmlspecialchars($fullName ?: 'Sin nombre') ?></td>
                            <td><?= htmlspecialchars($u['correoUsu']) ?></td>
                            <td class="masked-pass">●●●●●●●●</td>
                            <td>
                                <span class="estatus-badge <?= $u['estatusUsu'] == 1 ? 'estatus-activo' : 'estatus-inactivo' ?>"><?= $estatus ?></span>
                            </td>
                            <td class="acciones">
                                <button class="action-btn edit" title="Editar usuario" onclick="editarUsuario(<?= $u['pk_usuario'] ?>)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" >
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-5 1 1-5 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <?php if ($u['estatusUsu'] == 1): ?>
                                    <button class="action-btn" title="Dar de baja usuario" onclick="toggleEstatus(<?= $u['pk_usuario'] ?>, <?= $u['estatusUsu'] ?>)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                <?php else: ?>
                                    <button class="action-btn activate" title="Activar usuario" onclick="toggleEstatus(<?= $u['pk_usuario'] ?>, <?= $u['estatusUsu'] ?>)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 6L9 17l-5-5"></path>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="sin-registros">No hay usuarios registrados aún.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function editarUsuario(id) {
    window.location.href = 'editar_usuario.php?id=' + id;
}

async function toggleEstatus(id, current) {
    const nueva = current == 1 ? 0 : 1;
    const confirmed = await customConfirm((nueva==0 ? '¿Desea dar de baja al usuario?' : '¿Desea activar al usuario?'), 'Confirmar');
    if (!confirmed) return;

    try {
        const resp = await fetch('cambiar_estatus_usuario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(id) + '&estatus=' + encodeURIComponent(nueva)
        });
        const data = await resp.json();
        if (data.status === 'ok') {
            showSuccess(data.message || 'Estatus actualizado');
            setTimeout(() => location.reload(), 800);
        } else {
            showError('Error: ' + (data.message || 'Error al actualizar estatus'));
        }
    } catch (err) {
        console.error(err);
        showError('Error de conexión');
    }
}

// Filtros
const btnAll = document.getElementById('showAll');
const btnActive = document.getElementById('showActive');
const btnInactive = document.getElementById('showInactive');

btnAll.addEventListener('click', () => { setActiveFilter(btnAll); filterBy(null); });
btnActive.addEventListener('click', () => { setActiveFilter(btnActive); filterBy(1); });
btnInactive.addEventListener('click', () => { setActiveFilter(btnInactive); filterBy(0); });

function setActiveFilter(button) {
    [btnAll, btnActive, btnInactive].forEach(b => b.classList.remove('active'));
    button.classList.add('active');
}

function filterBy(est) {
    const rows = document.querySelectorAll('#tablaUsuarios tbody tr');
    rows.forEach(r => {
        if (est === null) { r.style.display = ''; return; }
        r.style.display = (r.dataset.estatus == String(est)) ? '' : 'none';
    });
}

async function cerrarSesion() {
    const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
    if (confirmed) window.location.href = 'logout.php';
}
</script>

</body>
</html>
