<?php
require_once __DIR__ . '/../navbar.php';
?>

<main>
    <h1>Clientes registrados</h1>

    <div class="top-actions">
        <button id="showAll" class="btn-filter active">Todos</button>
        <button id="showActive" class="btn-filter">Activos</button>
        <button id="showInactive" class="btn-filter">Inactivos</button>
    </div>

    <div class="tabla-container">
        <table id="tablaCliente">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre completo</th>
                    <th>Teléfono</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($clientes)): ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr data-estatus="<?= $cliente['estatusCli'] ?>">
                            <td><?= htmlspecialchars($cliente['id_cliente']) ?></td>
                            <td><?= htmlspecialchars($cliente['nombres'] . ' ' . $cliente['aPaterno'] . ' ' . $cliente['aMaterno']) ?></td>
                            <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                            <td>
                                <?php if ($cliente['estatusCli'] == 1): ?>
                                    <span class="estatus-activo">Activo</span>
                                <?php else: ?>
                                    <span class="estatus-inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="acciones">
                                <button title="Editar cliente" onclick="editarPedido(<?= $cliente['id_cliente']?>)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <?php if ($cliente['estatusCli'] == 1): ?>
                                    <button class="action-btn destructive" title="Dar de baja cliente" onclick="toggleEstatus(<?= $cliente['id_cliente'] ?>, <?= $cliente['estatusCli'] ?>)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                <?php else: ?>
                                    <button class="action-btn activate" title="Activar cliente" onclick="toggleEstatus(<?= $cliente['id_cliente'] ?>, <?= $cliente['estatusCli'] ?>)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 6L9 17l-5-5"></path>
                                        </svg>
                                    </button>
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
    
    function editarPedido(id) {
    // window.location.href = "editar_prenda.php?id=" + id;
    window.location.href = "<?= BASE_URL ?>views/editar_cliente.php?id=" + id;
}

// 🔹 Eliminar pedido
async function toggleEstatus(id, current) {
    
    const nueva = current == 1 ? 0 : 1;
    const confirmed = await customConfirm((nueva==0 ? '¿Desea dar de baja al cliente?' : '¿Desea activar al cliente?'), 'Confirmar');
    if (!confirmed) return;

    try {
        const resp = await fetch('<?= BASE_URL ?>cambiar_estatus_cliente.php', {
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
    const rows = document.querySelectorAll('#tablaCliente tbody tr');
    rows.forEach(r => {
        if (est === null) { r.style.display = ''; return; }
        r.style.display = (r.dataset.estatus == String(est)) ? '' : 'none';
    });
}


async function cerrarSesion() {
    const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
    
    if (confirmed) {
        window.location.href = '<?= BASE_URL ?>views/logout.php';
    }
}
</script>

</body>
</html>
