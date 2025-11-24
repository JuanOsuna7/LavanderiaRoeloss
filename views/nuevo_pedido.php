<?php
require_once __DIR__ . '/../navbar.php';

// Obtener lista de clientes para búsqueda
try {
    $clientes = $pdo->query("SELECT c.pk_cliente, CONCAT(p.nombres, ' ', p.aPaterno, ' ', p.aMaterno) AS nombreCompleto
                              FROM clientes c
                              INNER JOIN personas p ON c.fk_persona = p.pk_persona
                              WHERE c.estatusCli = 1
                              ORDER BY p.nombres ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar clientes: " . $e->getMessage());
}

// Obtener tipos de prendas disponibles
try {
    $tipos_prendas = $pdo->query("SELECT pk_tipo_prenda, nombre_tipo, precio_por_kg, descripcion 
                                  FROM tipos_prenda 
                                  WHERE estatus = 1 
                                  ORDER BY nombre_tipo ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar tipos de prendas: " . $e->getMessage());
}

// Generar ID temporal para el pedido en sesión
if (!isset($_SESSION['pedido_temp_id'])) {
    $_SESSION['pedido_temp_id'] = 'temp_' . time() . '_' . uniqid();
    $_SESSION['pedido_items'] = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar nuevo pedido - Lavandería Roeloss</title>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        /* Estilos personalizados para el scroll del ticket */
        #ticketPrendas::-webkit-scrollbar {
            width: 6px;
        }
        
        #ticketPrendas::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 3px;
        }
        
        #ticketPrendas::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }
        
        #ticketPrendas::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
            border: 2px solid #3b82f6;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: #2563eb;
            border-color: #2563eb;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 2px solid #d1d5db;
        }
        
        .btn-secondary:hover:not(:disabled) {
            background: #e5e7eb;
            border-color: #9ca3af;
        }
        
        .btn-primary:disabled, .btn-secondary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="nuevo-pedido-container">
    <!-- CUADRO 2: Resumen de prendas (IZQUIERDA) -->
    <div class="cuadro-resumen">
        <h2 class="resumen-titulo">Prendas del pedido</h2>
        <div id="listaPrendas">
            <div class="mensaje-vacio">
                No hay prendas agregadas.<br>
                Haz clic en "Agregar prenda" para comenzar.
            </div>
        </div>
    </div>

    <!-- CUADRO 1: Formulario principal (DERECHA) -->
    <div class="cuadro-principal">
        <h1 class="titulo-principal">Registrar nuevo pedido</h1>

        <form id="formPedido">
            <!-- Búsqueda de cliente -->
            <div class="form-group">
                <label class="form-label">Cliente:</label>
                <div class="busqueda-cliente">
                    <svg class="busqueda-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="buscarCliente" class="busqueda-input" placeholder="Buscar cliente..." autocomplete="off">
                    <input type="hidden" id="clienteSeleccionado" name="cliente" required>
                    <div id="resultadosBusqueda" class="resultados-busqueda"></div>
                </div>
            </div>

            <!-- Tipo de entrega -->
            <div class="form-group">
                <label class="form-label" for="tipoEntrega">Tipo de entrega:</label>
                <select id="tipoEntrega" name="tipoEntrega" class="form-select" required>
                    <option value="">Seleccione tipo de entrega</option>
                    <option value="Entrega a domicilio">Entrega a domicilio</option>
                    <option value="Recoger en sucursal">Recoger en sucursal</option>
                </select>
            </div>

            <!-- Total del pedido -->
            <div class="total-display">
                <div class="total-label">Total del pedido:</div>
                <input type="text" class="total-input" id="totalPedido" value="$0.00 MXN" readonly>
            </div>

            <!-- Botón para agregar prendas -->
            <button type="button" class="btn-agregar-prenda" id="btnAgregarPrenda">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 8px; vertical-align: middle;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Agregar prenda al pedido
            </button>

            <!-- Botones de acción -->
            <div class="botones-accion">
                <button type="submit" class="btn-registrar" id="btnRegistrar" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 8px; vertical-align: middle;">
                        <path d="M20 6L9 17l-5-5"></path>
                    </svg>
                    Registrar pedido
                </button>
                <button type="button" class="btn-cancelar" onclick="cancelarPedido()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 8px; vertical-align: middle;">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmación del pedido -->
<div class="modal" id="modalConfirmarPedido">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header" style="text-align: center; padding: 20px 20px 0;">
            <h2 style="margin: 0; color: #1f2937; font-size: 1.5rem;">¿Guardar pedido?</h2>
        </div>
        
        <div class="modal-body" style="padding: 20px;">
            <!-- Ticket/Resumen del pedido -->
            <div class="ticket-container" style="background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; font-family: 'Courier New', monospace; font-size: 14px;">
                <div class="ticket-header" style="text-align: center; border-bottom: 1px dashed #9ca3af; padding-bottom: 10px; margin-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: bold;">LAVANDERÍA ROELOSS</h3>
                    <p style="margin: 5px 0 0; font-size: 12px; color: #6b7280;">Resumen del Pedido</p>
                </div>
                
                <div class="ticket-info" style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Fecha:</span>
                        <span id="ticketFecha"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Cliente:</span>
                        <span id="ticketCliente" style="font-weight: bold;"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Entrega:</span>
                        <span id="ticketEntrega"></span>
                    </div>
                </div>
                
                <div style="border-top: 1px dashed #9ca3af; border-bottom: 1px dashed #9ca3af; padding: 10px 0; margin: 15px 0;">
                    <h4 style="margin: 0 0 10px; font-size: 14px; text-align: center;">PRENDAS</h4>
                    <div id="ticketPrendas" style="max-height: 200px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #d1d5db #f3f4f6;"></div>
                </div>
                
                <div class="ticket-total" style="text-align: right; margin-top: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Peso Total:</span>
                        <span id="ticketPesoTotal" style="font-weight: bold;"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; border-top: 1px dashed #9ca3af; padding-top: 5px;">
                        <span>TOTAL:</span>
                        <span id="ticketTotal" style="color: #059669;"></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-actions" style="padding: 0 20px 20px; display: flex; gap: 10px; justify-content: center;">
            <button class="btn-primary" id="btnConfirmarGuardar" style="background: #059669; border-color: #059669; min-width: 120px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
                Guardar Pedido
            </button>
            <button class="btn-secondary" id="btnSeguirEditando" style="min-width: 120px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="m18.5 2.5 1.4 1.4L12 12v3h3l7.9-7.9 1.4 1.4L16.5 16.5"/>
                </svg>
                Seguir Editando
            </button>
        </div>
    </div>
</div>

<!-- Modal de éxito -->
<div class="modal" id="modalExito">
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <div class="modal-header">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
        </div>
        <div class="modal-body">
            <h2>¡Pedido registrado correctamente!</h2>
            <p>El pedido ha sido guardado exitosamente en el sistema.</p>
        </div>
        <div class="modal-actions">
            <button class="btn-primary" id="btnCerrarModal">Aceptar</button>
        </div>
    </div>
</div>

<!-- Modal para agregar prenda -->
<div class="modal-overlay" id="modalAgregarPrenda">
    <div class="modal-content-prenda">
        <div class="modal-header-prenda">
            <h2>Agregar prenda al pedido</h2>
            <button type="button" class="btn-cerrar-modal" onclick="cerrarModalPrenda()">×</button>
        </div>
        
        <form id="formPrenda">
            <!-- Tipo de prenda -->
            <div class="form-group">
                <label class="form-label" for="tipoPrenda">Tipo de prenda:</label>
                <select id="tipoPrenda" name="tipoPrenda" class="form-select-modal" required>
                    <option value="">Seleccione tipo de prenda</option>
                    <!-- Las opciones se cargarán dinámicamente -->
                </select>
                <div id="prendaInfo" class="precio-info" style="display: none;"></div>
            </div>

            <!-- Peso -->
            <div class="form-group">
                <label class="form-label" for="pesoModal">Peso (kg):</label>
                <input type="number" id="pesoModal" name="peso" class="form-input-modal" 
                       step="0.1" min="0.1" placeholder="0.0" required>
                <small style="color: #6b7280; font-size: 0.8rem;">KG</small>
            </div>

            <!-- Precio unitario y subtotal -->
            <div class="form-row-modal">
                <div class="form-group">
                    <label class="form-label" for="precioUnitario">Precio unitario:</label>
                    <input type="number" id="precioUnitario" name="precioUnitario" class="form-input-modal" 
                           step="0.01" readonly>
                    <small style="color: #6b7280; font-size: 0.8rem;">MXN</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="subtotalModal">Subtotal:</label>
                    <input type="number" id="subtotalModal" name="subtotal" class="form-input-modal" 
                           step="0.01" readonly>
                    <small style="color: #6b7280; font-size: 0.8rem;">MXN</small>
                </div>
            </div>

            <!-- Botones -->
            <div class="botones-modal">
                <button type="submit" class="btn-agregar-modal" id="btnAgregarModal">
                    Agregar prenda
                </button>
                <button type="button" class="btn-cancelar-modal" onclick="cerrarModalPrenda()">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Datos desde PHP
const clientes = <?= json_encode($clientes) ?>;
const tiposPrendas = <?= json_encode($tipos_prendas) ?>;

let prendaItems = [];
let clienteSeleccionado = null;

// Elementos del formulario
const buscarClienteInput = document.getElementById('buscarCliente');
const clienteSeleccionadoInput = document.getElementById('clienteSeleccionado');
const resultadosBusqueda = document.getElementById('resultadosBusqueda');
const listaPrendas = document.getElementById('listaPrendas');
const totalPedido = document.getElementById('totalPedido');
const btnRegistrar = document.getElementById('btnRegistrar');
const btnAgregarPrenda = document.getElementById('btnAgregarPrenda');

// Modal agregar prenda
const modalAgregarPrenda = document.getElementById('modalAgregarPrenda');
const tipoPrendaSelect = document.getElementById('tipoPrenda');
const pesoModalInput = document.getElementById('pesoModal');
const precioUnitarioInput = document.getElementById('precioUnitario');
const subtotalModalInput = document.getElementById('subtotalModal');
const btnAgregarModal = document.getElementById('btnAgregarModal');
const prendaInfo = document.getElementById('prendaInfo');

// Buscar clientes
function buscarClientes(texto) {
    const resultados = clientes.filter(cliente => {
        // Extraer solo el primer nombre (antes del primer espacio)
        const primerNombre = cliente.nombreCompleto.split(' ')[0].toLowerCase();
        const textoBusqueda = texto.toLowerCase();
        
        // Verificar si el primer nombre empieza con el texto buscado
        return primerNombre.startsWith(textoBusqueda);
    });
    
    mostrarResultados(resultados);
}

function mostrarResultados(resultados) {
    resultadosBusqueda.innerHTML = '';
    
    if (resultados.length === 0) {
        resultadosBusqueda.style.display = 'none';
        return;
    }
    
    resultados.forEach(cliente => {
        const item = document.createElement('div');
        item.className = 'resultado-item';
        item.textContent = cliente.nombreCompleto;
        item.onclick = () => seleccionarCliente(cliente);
        resultadosBusqueda.appendChild(item);
    });
    
    resultadosBusqueda.style.display = 'block';
}

function seleccionarCliente(cliente) {
    clienteSeleccionado = cliente;
    buscarClienteInput.value = cliente.nombreCompleto;
    clienteSeleccionadoInput.value = cliente.pk_cliente;
    resultadosBusqueda.style.display = 'none';
    validarFormulario();
}

function agregarPrendaAlPedido(prenda) {
    const nuevaPrenda = {
        ...prenda,
        id: Date.now() // ID único temporal
    };
    
    prendaItems.push(nuevaPrenda);
    actualizarListaPrendas();
    calcularTotal();
    validarFormulario();
}

// Modal functions
function abrirModalPrenda() {
    cargarTiposPrendas();
    limpiarFormularioModal();
    modalAgregarPrenda.classList.add('show');
}

function cerrarModalPrenda() {
    modalAgregarPrenda.classList.remove('show');
    limpiarFormularioModal();
}

function cargarTiposPrendas() {
    tipoPrendaSelect.innerHTML = '<option value="">Seleccione tipo de prenda</option>';
    
    tiposPrendas.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.pk_tipo_prenda;
        option.textContent = `${tipo.nombre_tipo} - $${parseFloat(tipo.precio_por_kg).toFixed(2)} por kg`;
        option.dataset.precio = tipo.precio_por_kg;
        option.dataset.descripcion = tipo.descripcion;
        tipoPrendaSelect.appendChild(option);
    });
}

function limpiarFormularioModal() {
    document.getElementById('formPrenda').reset();
    precioUnitarioInput.value = '';
    subtotalModalInput.value = '';
    prendaInfo.style.display = 'none';
    btnAgregarModal.disabled = true;
}

function actualizarInfoPrenda() {
    const selectedOption = tipoPrendaSelect.selectedOptions[0];
    if (selectedOption && selectedOption.value) {
        const precio = parseFloat(selectedOption.dataset.precio);
        const descripcion = selectedOption.dataset.descripcion;
        
        precioUnitarioInput.value = precio.toFixed(2);
        prendaInfo.innerHTML = `<strong>Descripción:</strong> ${descripcion}<br><strong>Precio:</strong> $${precio.toFixed(2)} por kg`;
        prendaInfo.style.display = 'block';
        
        calcularSubtotalModal();
    } else {
        precioUnitarioInput.value = '';
        subtotalModalInput.value = '';
        prendaInfo.style.display = 'none';
    }
    validarFormularioModal();
}

function calcularSubtotalModal() {
    const peso = parseFloat(pesoModalInput.value) || 0;
    const precioUnitario = parseFloat(precioUnitarioInput.value) || 0;
    const subtotal = peso * precioUnitario;
    
    subtotalModalInput.value = subtotal.toFixed(2);
    validarFormularioModal();
}

function validarFormularioModal() {
    const tipoPrendaValido = tipoPrendaSelect.value !== '';
    const pesoValido = pesoModalInput.value !== '' && parseFloat(pesoModalInput.value) > 0;
    
    btnAgregarModal.disabled = !(tipoPrendaValido && pesoValido);
}

function actualizarListaPrendas() {
    if (prendaItems.length === 0) {
        listaPrendas.innerHTML = `
            <div class="mensaje-vacio">
                No hay prendas agregadas.<br>
                Haz clic en "Agregar prenda" para comenzar.
            </div>
        `;
        return;
    }
    
    listaPrendas.innerHTML = '';
    
    prendaItems.forEach((prenda, index) => {
        const itemHTML = `
            <div class="item-prenda">
                <div class="item-header">
                    <div class="item-numero">Prenda ${index + 1}</div>
                    <button type="button" class="btn-eliminar-item" onclick="eliminarPrenda(${prenda.id})">
                        ×
                    </button>
                </div>
                <div class="item-detalle">
                    <strong>Tipo de prenda:</strong> ${prenda.nombreTipoPrenda}
                </div>
                <div class="item-detalle">
                    <strong>Peso:</strong> ${prenda.peso} kg
                </div>
                <div class="item-detalle">
                    <strong>Precio unitario:</strong> $${prenda.precioUnitario.toFixed(2)} MXN
                </div>
                <div class="item-subtotal">
                    Subtotal: $${prenda.subtotal.toFixed(2)} MXN
                </div>
            </div>
        `;
        listaPrendas.insertAdjacentHTML('beforeend', itemHTML);
    });
}

function eliminarPrenda(id) {
    prendaItems = prendaItems.filter(prenda => prenda.id !== id);
    actualizarListaPrendas();
    calcularTotal();
    validarFormulario();
}

function calcularTotal() {
    const total = prendaItems.reduce((sum, prenda) => sum + prenda.subtotal, 0);
    totalPedido.value = `$${total.toFixed(2)} MXN`;
}

function validarFormulario() {
    const clienteValido = clienteSeleccionado !== null;
    const tipoEntregaValido = document.getElementById('tipoEntrega').value !== '';
    const prendasValidas = prendaItems.length > 0;
    
    btnRegistrar.disabled = !(clienteValido && tipoEntregaValido && prendasValidas);
}

function mostrarResumenPedido() {
    // Actualizar información del ticket
    const ahora = new Date();
    const fechaFormateada = ahora.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    document.getElementById('ticketFecha').textContent = fechaFormateada;
    document.getElementById('ticketCliente').textContent = clienteSeleccionado.nombreCompleto;
    document.getElementById('ticketEntrega').textContent = document.getElementById('tipoEntrega').value;
    
    // Mostrar prendas
    const ticketPrendasDiv = document.getElementById('ticketPrendas');
    ticketPrendasDiv.innerHTML = '';
    
    let pesoTotalCalculado = 0;
    
    prendaItems.forEach((prenda, index) => {
        pesoTotalCalculado += prenda.peso;
        
        const prendaDiv = document.createElement('div');
        prendaDiv.style.cssText = 'display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px;';
        prendaDiv.innerHTML = `
            <div style="flex: 1;">
                <div style="font-weight: bold;">${prenda.nombreTipoPrenda}</div>
                <div style="color: #6b7280; font-size: 11px;">${prenda.peso} kg × $${prenda.precioUnitario.toFixed(2)}</div>
            </div>
            <div style="text-align: right; font-weight: bold;">$${prenda.subtotal.toFixed(2)}</div>
        `;
        ticketPrendasDiv.appendChild(prendaDiv);
    });
    
    // Actualizar totales
    document.getElementById('ticketPesoTotal').textContent = `${pesoTotalCalculado.toFixed(1)} kg`;
    
    const totalCalculado = prendaItems.reduce((sum, prenda) => sum + prenda.subtotal, 0);
    document.getElementById('ticketTotal').textContent = `$${totalCalculado.toFixed(2)} MXN`;
    
    // Mostrar modal
    document.getElementById('modalConfirmarPedido').classList.add('show');
}

function cerrarModalConfirmacion() {
    document.getElementById('modalConfirmarPedido').classList.remove('show');
}

async function guardarPedidoConfirmado() {
    const formData = new FormData();
    formData.append('cliente', clienteSeleccionadoInput.value);
    formData.append('tipoEntrega', document.getElementById('tipoEntrega').value);
    formData.append('total', prendaItems.reduce((sum, prenda) => sum + prenda.subtotal, 0).toFixed(2));
    formData.append('prendas', JSON.stringify(prendaItems));
    
    try {
        const btnConfirmar = document.getElementById('btnConfirmarGuardar');
        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; animation: spin 1s linear infinite;">
                <circle cx="12" cy="12" r="3"/>
            </svg>
            Guardando...
        `;
        
        const response = await fetch('<?= BASE_URL ?>controllers/pedido_controller.php?action=create', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'ok') {
            // Limpiar sesión
            prendaItems = [];
            clienteSeleccionado = null;
            
            // Cerrar modal de confirmación y mostrar éxito
            cerrarModalConfirmacion();
            document.getElementById('modalExito').classList.add('show');
        } else {
            showError('Error al guardar el pedido: ' + data.message);
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
                Guardar Pedido
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error de conexión. Por favor, intenta nuevamente.');
        const btnConfirmar = document.getElementById('btnConfirmarGuardar');
        btnConfirmar.disabled = false;
        btnConfirmar.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
            Guardar Pedido
        `;
        cerrarModalConfirmacion();
    }
}

async function cancelarPedido() {
    const confirmed = await customConfirm('¿Está seguro de que desea cancelar el pedido? Se perderán todos los datos ingresados.', 'Confirmar cancelación');
    
    if (confirmed) {
        // Limpiar todo
        prendaItems = [];
        clienteSeleccionado = null;
        
        document.getElementById('formPedido').reset();
        buscarClienteInput.value = '';
        clienteSeleccionadoInput.value = '';
        
        actualizarListaPrendas();
        calcularTotal();
        validarFormulario();
        
        window.location.href = '<?= BASE_URL ?>controllers/pedido_controller.php?action=list';
    }
}

// Event listeners
buscarClienteInput.addEventListener('input', function() {
    const texto = this.value.trim();
    if (texto.length >= 1) {
        buscarClientes(texto);
    } else {
        resultadosBusqueda.style.display = 'none';
        if (texto.length === 0) {
            clienteSeleccionado = null;
            clienteSeleccionadoInput.value = '';
            validarFormulario();
        }
    }
});

// Cerrar dropdown al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.busqueda-cliente')) {
        resultadosBusqueda.style.display = 'none';
    }
});

document.getElementById('tipoEntrega').addEventListener('change', validarFormulario);

btnAgregarPrenda.addEventListener('click', function() {
    abrirModalPrenda();
});

// Mostrar modal de confirmación del pedido
document.getElementById('formPedido').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (prendaItems.length === 0) {
        showError('Debe agregar al menos una prenda al pedido.');
        return;
    }
    
    // Mostrar modal de confirmación con el resumen
    mostrarResumenPedido();
});

// Modal de confirmación
document.getElementById('btnConfirmarGuardar').addEventListener('click', function() {
    guardarPedidoConfirmado();
});

document.getElementById('btnSeguirEditando').addEventListener('click', function() {
    cerrarModalConfirmacion();
});

// Cerrar modal de confirmación al hacer clic fuera
document.getElementById('modalConfirmarPedido').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalConfirmacion();
    }
});

// Modal success
document.getElementById('btnCerrarModal').addEventListener('click', function() {
    document.getElementById('modalExito').classList.remove('show');
    window.location.href = '<?= BASE_URL ?>controllers/pedido_controller.php?action=list';
});

async function cerrarSesion() {
    const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
    
    if (confirmed) {
        window.location.href = "<?= BASE_URL ?>views/logout.php";
    }
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    validarFormulario();
});

tipoPrendaSelect.addEventListener('change', actualizarInfoPrenda);
pesoModalInput.addEventListener('input', calcularSubtotalModal);

// Submit modal form
document.getElementById('formPrenda').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        tipoPrenda: tipoPrendaSelect.value,
        nombreTipoPrenda: tipoPrendaSelect.selectedOptions[0].text.split(' - ')[0],
        peso: parseFloat(pesoModalInput.value),
        precioUnitario: parseFloat(precioUnitarioInput.value),
        subtotal: parseFloat(subtotalModalInput.value)
    };
    
    agregarPrendaAlPedido(formData);
    cerrarModalPrenda();
});

// Cerrar modal al hacer clic fuera
modalAgregarPrenda.addEventListener('click', function(e) {
    if (e.target === modalAgregarPrenda) {
        cerrarModalPrenda();
    }
});
</script>

</body>
</html>