<?php
require_once __DIR__ . '/../navbar.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Prendas Registradas</title>
</head>
<body>

<main>
    <h1>Prendas Registradas</h1>

    <!-- Buscador de tipos de prenda -->
    <div class="buscador-container">
        <div class="busqueda-cliente-lista">
            <svg class="busqueda-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input type="text" id="buscarPrendaLista" class="busqueda-input" placeholder="Buscar tipo de prenda..." autocomplete="off">
            <button type="button" id="limpiarBusqueda" class="btn-limpiar" title="Limpiar búsqueda" style="display: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>

    <div class="top-actions with-actions">
        <div class="filters-section">
            <button id="showAll" class="btn-filter active">Todos</button>
            <button id="showActive" class="btn-filter">Activos</button>
            <button id="showInactive" class="btn-filter">Inactivos</button>
        </div>
        
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>views/nueva_prenda.php" class="btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Agregar Nuevo Tipo
            </a>
        </div>
    </div>

    <div class="tabla-container">
        <table id="tablaPrendas">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Tipo</th>
                    <th>Precio por Kg</th>
                    <th>Descripción</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($prendas)): ?>
                    <?php foreach ($prendas as $prenda): ?>
                        <tr data-estatus="<?= $prenda['estatus'] ?>">
                            <td><?= htmlspecialchars($prenda['pk_tipo_prenda']) ?></td>
                            <td><?= htmlspecialchars($prenda['nombre_tipo']) ?></td>
                            <td>$<?= number_format($prenda['precio_por_kg'], 2) ?> / kg</td>
                            <td><?= htmlspecialchars($prenda['descripcion'] ?? 'Sin descripción') ?></td>
                            <td>
                                <?php if ($prenda['estatus'] === 1): ?>
                                    <span class="estatus-activo">Activa</span>
                                <?php elseif ($prenda['estatus'] === 0): ?>
                                    <span class="estatus-inactivo">Inactiva</span>
                                 <?php endif; ?>
                            </td>
                            <td class="acciones">
                                <button title="Editar" onclick="editarPedido(<?= $prenda['pk_tipo_prenda'] ?>)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <?php if ($prenda['estatus'] == 1): ?>
                                    <button class="action-btn destructive" title="Dar de baja prenda" onclick="toggleEstatus(<?= $prenda['pk_tipo_prenda'] ?>, <?= $prenda['estatus'] ?>)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                <?php else: ?>
                                    <button class="action-btn activate" title="Activar prenda" onclick="toggleEstatus(<?= $prenda['pk_tipo_prenda'] ?>, <?= $prenda['estatus'] ?>)">
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
                        <td colspan="8" class="sin-registros">No hay prendas registradas aún.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
// 🔹 Redirigir a editar pedido
function editarPedido(id) {
    // window.location.href = "editar_prenda.php?id=" + id;
    window.location.href = "<?= BASE_URL ?>views/editar_prenda.php?id=" + id;
}

// 🔹 Eliminar pedido
async function toggleEstatus(id, current) {
    console.log('toggleEstatus llamado con id:', id, 'y current:', current);
    
    const nueva = current == 1 ? 0 : 1;
    const confirmed = await customConfirm((nueva==0 ? '¿Desea dar de baja al usuario?' : '¿Desea activar al usuario?'), 'Confirmar');
    if (!confirmed) return;

    try {
        const resp = await fetch('<?= BASE_URL ?>cambiar_estatus_prenda.php', {
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
const buscarPrendaInput = document.getElementById('buscarPrendaLista');
const limpiarBusquedaBtn = document.getElementById('limpiarBusqueda');

// Variables para el filtrado
let filtroActual = null; // null=todos, 1=activos, 0=inactivos
let textoBusqueda = '';

// Función para buscar prendas
function buscarPrendas(texto) {
    textoBusqueda = texto.toLowerCase().trim();
    
    // Mostrar/ocultar botón limpiar
    if (textoBusqueda.length > 0) {
        limpiarBusquedaBtn.style.display = 'flex';
    } else {
        limpiarBusquedaBtn.style.display = 'none';
    }
    
    aplicarFiltros();
}

// Función para aplicar tanto filtros de estado como búsqueda
function aplicarFiltros() {
    const rows = document.querySelectorAll('#tablaPrendas tbody tr');
    
    rows.forEach(row => {
        let mostrar = true;
        
        // Aplicar filtro de estado
        if (filtroActual !== null) {
            const estatus = row.dataset.estatus;
            if (estatus != String(filtroActual)) {
                mostrar = false;
            }
        }
        
        // Aplicar filtro de búsqueda
        if (mostrar && textoBusqueda.length > 0) {
            const nombreTipo = row.cells[1].textContent.toLowerCase();
            
            // Buscar en el nombre del tipo de prenda
            if (!nombreTipo.includes(textoBusqueda)) {
                mostrar = false;
            }
        }
        
        row.style.display = mostrar ? '' : 'none';
    });
}

// Event listeners para el buscador
buscarPrendaInput.addEventListener('input', function() {
    buscarPrendas(this.value);
});

limpiarBusquedaBtn.addEventListener('click', function() {
    buscarPrendaInput.value = '';
    buscarPrendas('');
    buscarPrendaInput.focus();
});

// Event listeners para los botones de filtro
btnAll.addEventListener('click', () => { 
    setActiveFilter(btnAll); 
    filtroActual = null;
    aplicarFiltros();
});

btnActive.addEventListener('click', () => { 
    setActiveFilter(btnActive); 
    filtroActual = 1;
    aplicarFiltros();
});

btnInactive.addEventListener('click', () => { 
    setActiveFilter(btnInactive); 
    filtroActual = 0;
    aplicarFiltros();
});

function setActiveFilter(button) {
    [btnAll, btnActive, btnInactive].forEach(b => b.classList.remove('active'));
    button.classList.add('active');
}


async function cerrarSesion() {
    const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
    
    if (confirmed) {
        window.location.href = "<?= BASE_URL ?>views/logout.php";
    }
}
</script>

</body>
</html>