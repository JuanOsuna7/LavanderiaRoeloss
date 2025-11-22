<?php
require_once __DIR__ . '/../navbar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Historial de Pedidos</title>
</head>
<body>

<main>
    <h1>Historial de pedidos</h1>

    <!-- Botones de filtro por estatus -->
    <div class="top-actions">
        <button id="showAll" class="btn-filter active">Todos</button>
        <button id="showPendientes" class="btn-filter" data-estatus="1">Pendientes</button>
        <button id="showEntregados" class="btn-filter" data-estatus="2">Entregados</button>
        <button id="showCancelados" class="btn-filter" data-estatus="3">Cancelados</button>
    </div>

    <div class="contenedor-acciones">
        <button onclick="window.location.href='<?= BASE_URL ?>views/nuevo_pedido.php'" class="btn-principal btn-agregar">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Pedido
        </button>
    </div>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Núm. Pedido</th>
                    <th>Cliente</th>
                    <th>Tipos de prendas</th>
                    <th>Peso total</th>
                    <th>Tipo de entrega</th>
                    <th>Estatus</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pedidos)): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr data-estatus="<?= $pedido['estatusPedido'] ?>">
                            <td><?= htmlspecialchars($pedido['id_pedido']) ?></td>
                            <td><?= htmlspecialchars($pedido['nombres'] . ' ' . $pedido['aPaterno'] . ' ' . $pedido['aMaterno']) ?></td>
                            <td class="tipos-prenda-lista">
                                <?php if (!empty($pedido['tipos_prenda'])): ?>
                                    <?php 
                                    $tipos = explode('|', $pedido['tipos_prenda']);
                                    foreach ($tipos as $tipo): 
                                        // Separar nombre y peso usando regex
                                        if (preg_match('/^(.+?)\s*\(([^)]+)\)$/', trim($tipo), $matches)) {
                                            $nombrePrenda = trim($matches[1]);
                                            $peso = trim($matches[2]);
                                        } else {
                                            $nombrePrenda = trim($tipo);
                                            $peso = '';
                                        }
                                    ?>
                                        <div class="tipos-prenda-item" title="<?= htmlspecialchars($nombrePrenda . ($peso ? ' (' . $peso . ')' : '')) ?>">
                                            <span class="prenda-nombre"><?= htmlspecialchars($nombrePrenda) ?></span>
                                            <?php if ($peso): ?>
                                                <span class="prenda-peso">(<?= htmlspecialchars($peso) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div style="color: #9ca3af; font-style: italic;">Sin prendas</div>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($pedido['peso_total_kg'], 1) ?> kg</td>
                            <td><?= htmlspecialchars($pedido['tipoEntrega']) ?></td>
                            <td>
                                <?php if ($pedido['estatusPedido'] == 1): ?>
                                    <span class="estatus-pendiente">Pendiente</span>
                                <?php elseif ($pedido['estatusPedido'] == 2): ?>
                                    <span class="estatus-entregado">Entregado</span>
                                <?php else: ?>
                                    <span class="estatus-cancelado">Cancelado</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format($pedido['totalPedido'], 2) ?></td>
                            <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                            <td class="acciones">
                                <!-- Botón ver ticket -->
                                <button title="Ver ticket" onclick="verTicket(<?= $pedido['id_pedido'] ?>)" class="btn-accion btn-ticket">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14,2 14,8 20,8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                        <polyline points="10,9 9,9 8,9"/>
                                    </svg>
                                </button>
                                
                                <!-- Botón cambiar a pendiente -->
                                <?php if ($pedido['estatusPedido'] != 1): ?>
                                    <button title="Marcar como pendiente" onclick="cambiarEstatus(<?= $pedido['id_pedido'] ?>, 1)" class="btn-estado btn-pendiente">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <polyline points="12,6 12,12 16,14"/>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Botón cambiar a entregado -->
                                <?php if ($pedido['estatusPedido'] != 2): ?>
                                    <button title="Marcar como entregado" onclick="cambiarEstatus(<?= $pedido['id_pedido'] ?>, 2)" class="btn-estado btn-entregado">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 6L9 17l-5-5"/>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Botón cambiar a cancelado -->
                                <?php if ($pedido['estatusPedido'] != 0): ?>
                                    <button title="Marcar como cancelado" onclick="cambiarEstatus(<?= $pedido['id_pedido'] ?>, 0)" class="btn-estado btn-cancelado">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="15" y1="9" x2="9" y2="15"/>
                                            <line x1="9" y1="9" x2="15" y2="15"/>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Botón editar -->
                                <button title="Editar pedido" onclick="editarPedido(<?= $pedido['id_pedido'] ?>)" class="btn-accion btn-editar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                
                                <!-- Botón eliminar -->
                                <button title="Eliminar pedido" onclick="eliminarPedido(<?= $pedido['id_pedido'] ?>)" class="btn-accion btn-eliminar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3,6 5,6 21,6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        <line x1="10" y1="11" x2="10" y2="17"/>
                                        <line x1="14" y1="11" x2="14" y2="17"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="sin-registros">No hay pedidos registrados aún.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal para mostrar ticket -->
<div class="modal" id="modalTicket">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header" style="text-align: center; padding: 20px 20px 0; position: relative;">
            <h2 style="margin: 0; color: #1f2937; font-size: 1.5rem;">Ticket de Pedido</h2>
            <button type="button" onclick="cerrarModalTicket()" style="position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
        </div>
        
        <div class="modal-body" style="padding: 20px;">
            <!-- Ticket/Resumen del pedido -->
            <div class="ticket-container" style="background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; font-family: 'Courier New', monospace; font-size: 14px;">
                <div class="ticket-header" style="text-align: center; border-bottom: 1px dashed #9ca3af; padding-bottom: 10px; margin-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: bold;">LAVANDERÍA ROELOSS</h3>
                    <p style="margin: 5px 0 0; font-size: 12px; color: #6b7280;">Ticket de Pedido</p>
                </div>
                
                <div class="ticket-info" style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Núm. Pedido:</span>
                        <span id="ticketNumeroPedido" style="font-weight: bold;"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Fecha:</span>
                        <span id="ticketFechaHistorial"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Cliente:</span>
                        <span id="ticketClienteHistorial" style="font-weight: bold;"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Teléfono:</span>
                        <span id="ticketTelefono"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Entrega:</span>
                        <span id="ticketEntregaHistorial"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Estado:</span>
                        <span id="ticketEstado" style="font-weight: bold;"></span>
                    </div>
                </div>
                
                <div style="border-top: 1px dashed #9ca3af; border-bottom: 1px dashed #9ca3af; padding: 10px 0; margin: 15px 0;">
                    <h4 style="margin: 0 0 10px; font-size: 14px; text-align: center;">PRENDAS</h4>
                    <div id="ticketPrendasHistorial" style="max-height: 200px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #d1d5db #f3f4f6;"></div>
                </div>
                
                <div class="ticket-total" style="text-align: right; margin-top: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Peso Total:</span>
                        <span id="ticketPesoTotalHistorial" style="font-weight: bold;"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; border-top: 1px dashed #9ca3af; padding-top: 5px;">
                        <span>TOTAL:</span>
                        <span id="ticketTotalHistorial" style="color: #059669;"></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-actions" style="padding: 0 20px 20px; display: flex; gap: 10px; justify-content: center;">
            <button onclick="imprimirTicket()" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; min-width: 120px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                    <polyline points="6,9 6,2 18,2 18,9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Imprimir
            </button>
            <button onclick="cerrarModalTicket()" style="background: #f3f4f6; color: #374151; border: 2px solid #d1d5db; padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; min-width: 120px;">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
// Función para ver el ticket del pedido
async function verTicket(pedidoId) {
    try {
        const response = await fetch(`<?= BASE_URL ?>controllers/pedido_controller.php?action=get&id=${pedidoId}`);
        const data = await response.json();
        
        if (data.status === 'ok') {
            mostrarTicketPedido(data.data);
        } else {
            showError('Error al cargar el ticket: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error de conexión al cargar el ticket.');
    }
}

function mostrarTicketPedido(pedido) {
    // Formatear fecha
    const fecha = new Date(pedido.fechaDeRecibo);
    const fechaFormateada = fecha.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Estados del pedido
    const estados = {
        0: { texto: 'Cancelado', color: '#ef4444' },
        1: { texto: 'Pendiente', color: '#f59e0b' },
        2: { texto: 'Entregado', color: '#10b981' }
    };
    
    // Llenar datos del ticket
    document.getElementById('ticketNumeroPedido').textContent = pedido.pk_pedido;
    document.getElementById('ticketFechaHistorial').textContent = fechaFormateada;
    document.getElementById('ticketClienteHistorial').textContent = pedido.nombreCliente;
    document.getElementById('ticketTelefono').textContent = pedido.telefono || 'No registrado';
    document.getElementById('ticketEntregaHistorial').textContent = pedido.tipoEntrega;
    
    const estadoSpan = document.getElementById('ticketEstado');
    const estado = estados[pedido.estatusPedido];
    estadoSpan.textContent = estado.texto;
    estadoSpan.style.color = estado.color;
    
    // Mostrar prendas
    const ticketPrendasDiv = document.getElementById('ticketPrendasHistorial');
    ticketPrendasDiv.innerHTML = '';
    
    pedido.prendas.forEach((prenda) => {
        const prendaDiv = document.createElement('div');
        prendaDiv.style.cssText = 'display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px;';
        prendaDiv.innerHTML = `
            <div style="flex: 1;">
                <div style="font-weight: bold;">${prenda.nombre_tipo}</div>
                <div style="color: #6b7280; font-size: 11px;">${prenda.peso_kg} kg × $${parseFloat(prenda.precio_unitario).toFixed(2)}</div>
            </div>
            <div style="text-align: right; font-weight: bold;">$${parseFloat(prenda.subtotal).toFixed(2)}</div>
        `;
        ticketPrendasDiv.appendChild(prendaDiv);
    });
    
    // Actualizar totales
    document.getElementById('ticketPesoTotalHistorial').textContent = `${parseFloat(pedido.peso_total_kg).toFixed(1)} kg`;
    document.getElementById('ticketTotalHistorial').textContent = `$${parseFloat(pedido.totalPedido).toFixed(2)} MXN`;
    
    // Mostrar modal
    document.getElementById('modalTicket').classList.add('show');
}

function cerrarModalTicket() {
    document.getElementById('modalTicket').classList.remove('show');
}

function imprimirTicket() {
    const ticketContent = document.querySelector('#modalTicket .ticket-container').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Ticket de Pedido</title>
                <style>
                    body { font-family: 'Courier New', monospace; margin: 20px; }
                    .ticket-container { background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; font-size: 14px; }
                    @media print {
                        body { margin: 0; }
                        .ticket-container { border: none; background: white; }
                    }
                </style>
            </head>
            <body>
                <div class="ticket-container">${ticketContent}</div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalTicket')?.addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalTicket();
    }
});

function editarPedido(id) {
    window.location.href = "<?= BASE_URL ?>views/editar_pedido.php?id=" + id;
}

async function cambiarEstatus(id, nuevoEstatus) {
    const estados = {
        0: 'Cancelado',
        1: 'Pendiente', 
        2: 'Entregado'
    };
    
    const confirmed = await customConfirm(
        `¿Está seguro de que desea cambiar el estado del pedido a "${estados[nuevoEstatus]}"?`,
        'Confirmar cambio de estado'
    );
    
    if (confirmed) {
        try {
            const resp = await fetch('<?= BASE_URL ?>cambiar_estatus_pedido.php', {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${id}&estatus=${nuevoEstatus}`
            });
            const data = await resp.json();
            if (data.status === "ok") {
                showSuccess(`¡Estado cambiado a "${estados[nuevoEstatus]}" correctamente!`);
                setTimeout(() => location.reload(), 1500);
            } else {
                showError("Error: " + data.message);
            }
        } catch (error) {
            showError("Error de conexión. Por favor, intenta nuevamente.");
        }
    }
}

async function eliminarPedido(id) {
    const confirmed = await customConfirm("¿Desea eliminar el pedido?", "Confirmar eliminación");
    
    if (confirmed) {
        try {
            const resp = await fetch("<?= BASE_URL ?>eliminar_pedido.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + id
            });
            const data = await resp.json();
            if (data.status === "ok") {
                showSuccess("¡Pedido eliminado correctamente!");
                setTimeout(() => location.reload(), 1500);
            } else {
                showError("Error: " + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showError("Error de conexión. Por favor, intenta nuevamente.");
        }
    }
}

async function cerrarSesion() {
    const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
    
    if (confirmed) {
        window.location.href = '<?= BASE_URL ?>views/logout.php';
    }
}

// Funcionalidad de filtros por estatus
const btnAll = document.getElementById('showAll');
const btnPendientes = document.getElementById('showPendientes');
const btnEntregados = document.getElementById('showEntregados');
const btnCancelados = document.getElementById('showCancelados');

// Event listeners para los botones de filtro
btnAll.addEventListener('click', () => { 
    setActiveFilter(btnAll); 
    filterByEstatus(null);
});

btnPendientes.addEventListener('click', () => { 
    setActiveFilter(btnPendientes); 
    filterByEstatus(1);
});

btnEntregados.addEventListener('click', () => { 
    setActiveFilter(btnEntregados); 
    filterByEstatus(2);
});

btnCancelados.addEventListener('click', () => { 
    setActiveFilter(btnCancelados); 
    filterByEstatus(3);
});

function setActiveFilter(button) {
    [btnAll, btnPendientes, btnEntregados, btnCancelados].forEach(b => b.classList.remove('active'));
    button.classList.add('active');
}

function filterByEstatus(estatus) {
    const rows = document.querySelectorAll('table tbody tr');
    rows.forEach(row => {
        if (estatus === null) { 
            row.style.display = ''; 
            return; 
        }
        
        const rowEstatus = parseInt(row.dataset.estatus);
        let mostrar = false;
        
        if (estatus === 1) {
            // Pendientes
            mostrar = (rowEstatus === 1);
        } else if (estatus === 2) {
            // Entregados
            mostrar = (rowEstatus === 2);
        } else if (estatus === 3) {
            // Cancelados (cualquier valor que no sea 1 o 2)
            mostrar = (rowEstatus !== 1 && rowEstatus !== 2);
        }
        
        row.style.display = mostrar ? '' : 'none';
    });
}
</script>

</body>
</html>