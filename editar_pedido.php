<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'navbar.php';

// Verificar que se reciba un ID válido
$pedidoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$pedidoId) {
    header('Location: historial.php');
    exit;
}

// Obtener datos del pedido
try {
    $stmt = $pdo->prepare("
        SELECT 
            p.pk_pedido,
            p.fk_cliente,
            p.tipoEntrega,
            p.estatusPedido,
            p.totalPedido,
            p.peso_total_kg,
            p.fechaDeRecibo,
            CONCAT(per.nombres, ' ', per.aPaterno, ' ', per.aMaterno) AS nombreCliente
        FROM pedidos p
        INNER JOIN clientes c ON p.fk_cliente = c.pk_cliente
        INNER JOIN personas per ON c.fk_persona = per.pk_persona
        WHERE p.pk_pedido = ?
    ");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        header('Location: historial.php');
        exit;
    }
    
    // Obtener items del pedido
    $stmt = $pdo->prepare("
        SELECT 
            ip.pk_item_pedido,
            ip.fk_tipo_prenda,
            ip.peso_kg,
            ip.precio_unitario,
            ip.subtotal,
            tp.nombre_tipo,
            tp.descripcion
        FROM items_pedido ip
        INNER JOIN tipos_prenda tp ON ip.fk_tipo_prenda = tp.pk_tipo_prenda
        WHERE ip.fk_pedido = ?
        ORDER BY tp.nombre_tipo
    ");
    $stmt->execute([$pedidoId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error al consultar el pedido: " . $e->getMessage());
}

// Obtener lista de clientes para búsqueda
try {
    $clientes = $pdo->query("SELECT c.pk_cliente, CONCAT(p.nombres, ' ', p.aPaterno, ' ', p.aMaterno) AS nombreCompleto
                              FROM clientes c
                              INNER JOIN personas p ON c.fk_persona = p.pk_persona
                              WHERE c.estatusCli = 'Activo'
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar pedido #<?= $pedido['pk_pedido'] ?> - Lavandería Roeloss</title>
    <link rel="stylesheet" href="estilos.css">
    <script src="custom-alerts.js"></script>
</head>
<body>

<div class="nuevo-pedido-container">
    <!-- CUADRO 2: Resumen de prendas (IZQUIERDA) -->
    <div class="cuadro-resumen">
        <h2 class="resumen-titulo">Prendas del pedido #<?= $pedido['pk_pedido'] ?></h2>
        <div id="listaPrendas">
            <!-- Se llenará con JavaScript -->
        </div>
    </div>

    <!-- CUADRO 1: Formulario principal (DERECHA) -->
    <div class="cuadro-principal">
        <h1 class="titulo-principal">Editar pedido #<?= $pedido['pk_pedido'] ?></h1>

        <form id="formPedido">
            <input type="hidden" id="pedidoId" value="<?= $pedido['pk_pedido'] ?>">
            
            <!-- Búsqueda de cliente -->
            <div class="form-group">
                <label class="form-label">Cliente:</label>
                <div class="busqueda-cliente">
                    <svg class="busqueda-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="buscarCliente" class="busqueda-input" 
                           value="<?= htmlspecialchars($pedido['nombreCliente']) ?>" autocomplete="off">
                    <input type="hidden" id="clienteSeleccionado" name="cliente" 
                           value="<?= $pedido['fk_cliente'] ?>" required>
                    <div id="resultadosBusqueda" class="resultados-busqueda"></div>
                </div>
            </div>

            <!-- Tipo de entrega -->
            <div class="form-group">
                <label class="form-label" for="tipoEntrega">Tipo de entrega:</label>
                <select id="tipoEntrega" name="tipoEntrega" class="form-select" required>
                    <option value="">Seleccione tipo de entrega</option>
                    <option value="Entrega a domicilio" <?= $pedido['tipoEntrega'] === 'Entrega a domicilio' ? 'selected' : '' ?>>
                        Entrega a domicilio
                    </option>
                    <option value="Recoger en sucursal" <?= $pedido['tipoEntrega'] === 'Recoger en sucursal' ? 'selected' : '' ?>>
                        Recoger en sucursal
                    </option>
                </select>
            </div>

            <!-- Estado del pedido -->
            <div class="form-group">
                <label class="form-label" for="estatusPedido">Estado del pedido:</label>
                <select id="estatusPedido" name="estatusPedido" class="form-select" required>
                    <option value="1" <?= $pedido['estatusPedido'] == 1 ? 'selected' : '' ?>>Pendiente</option>
                    <option value="2" <?= $pedido['estatusPedido'] == 2 ? 'selected' : '' ?>>Entregado</option>
                    <option value="0" <?= $pedido['estatusPedido'] == 0 ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>

            <!-- Total del pedido -->
            <div class="total-display">
                <div class="total-label">Total del pedido:</div>
                <input type="text" class="total-input" id="totalPedido" value="$<?= number_format($pedido['totalPedido'], 2) ?> MXN" readonly>
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
                <button type="submit" class="btn-registrar" id="btnActualizar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 8px; vertical-align: middle;">
                        <path d="M20 6L9 17l-5-5"></path>
                    </svg>
                    Actualizar pedido
                </button>
                <button type="button" class="btn-cancelar" onclick="cancelarEdicion()">
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

<!-- Modal para agregar prenda -->
<div class="modal-overlay" id="modalAgregarPrenda" style="display: none;">
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
const itemsOriginales = <?= json_encode($items) ?>;
let prendaItems = [];
let clienteSeleccionado = {
    pk_cliente: <?= $pedido['fk_cliente'] ?>,
    nombreCompleto: "<?= htmlspecialchars($pedido['nombreCliente']) ?>"
};

// Elementos del formulario
const buscarClienteInput = document.getElementById('buscarCliente');
const clienteSeleccionadoInput = document.getElementById('clienteSeleccionado');
const resultadosBusqueda = document.getElementById('resultadosBusqueda');
const listaPrendas = document.getElementById('listaPrendas');
const totalPedido = document.getElementById('totalPedido');
const btnActualizar = document.getElementById('btnActualizar');
const btnAgregarPrenda = document.getElementById('btnAgregarPrenda');

// Modal agregar prenda
const modalAgregarPrenda = document.getElementById('modalAgregarPrenda');
const tipoPrendaSelect = document.getElementById('tipoPrenda');
const pesoModalInput = document.getElementById('pesoModal');
const precioUnitarioInput = document.getElementById('precioUnitario');
const subtotalModalInput = document.getElementById('subtotalModal');
const btnAgregarModal = document.getElementById('btnAgregarModal');
const prendaInfo = document.getElementById('prendaInfo');

// Cargar datos existentes
function cargarDatosExistentes() {
    itemsOriginales.forEach((item, index) => {
        const prenda = {
            id: Date.now() + index,
            itemId: item.pk_item_pedido,
            tipoPrenda: item.fk_tipo_prenda,
            nombreTipoPrenda: item.nombre_tipo,
            peso: parseFloat(item.peso_kg),
            precioUnitario: parseFloat(item.precio_unitario),
            subtotal: parseFloat(item.subtotal),
            esExistente: true
        };
        prendaItems.push(prenda);
    });
    
    actualizarListaPrendas();
    calcularTotal();
    validarFormulario();
}

// Buscar clientes
function buscarClientes(texto) {
    const resultados = clientes.filter(cliente => 
        cliente.nombreCompleto.toLowerCase().includes(texto.toLowerCase())
    );
    
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
        id: Date.now(),
        esExistente: false
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
    modalAgregarPrenda.style.display = 'flex';
}

function cerrarModalPrenda() {
    modalAgregarPrenda.style.display = 'none';
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
                No hay prendas en el pedido.
            </div>
        `;
        return;
    }
    
    listaPrendas.innerHTML = '';
    
    prendaItems.forEach((prenda, index) => {
        const itemHTML = `
            <div class="item-prenda">
                <div class="item-header">
                    <div class="item-numero">
                        Prenda ${index + 1} 
                        ${prenda.esExistente ? '<span style="color: #6b7280; font-size: 0.8rem;">(Existente)</span>' : '<span style="color: #10b981; font-size: 0.8rem;">(Nueva)</span>'}
                    </div>
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
    
    btnActualizar.disabled = !(clienteValido && tipoEntregaValido && prendasValidas);
}

async function cancelarEdicion() {
    const confirmed = await customConfirm('¿Está seguro de que desea cancelar la edición? Se perderán los cambios no guardados.', 'Confirmar cancelación');
    
    if (confirmed) {
        window.location.href = 'historial.php';
    }
}

// Event listeners
buscarClienteInput.addEventListener('input', function() {
    const texto = this.value.trim();
    if (texto.length >= 2) {
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
document.getElementById('estatusPedido').addEventListener('change', validarFormulario);

btnAgregarPrenda.addEventListener('click', function() {
    abrirModalPrenda();
});

// Actualizar pedido
document.getElementById('formPedido').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (prendaItems.length === 0) {
        showError('Debe tener al menos una prenda en el pedido.');
        return;
    }
    
    const formData = new FormData();
    formData.append('pedidoId', document.getElementById('pedidoId').value);
    formData.append('cliente', clienteSeleccionadoInput.value);
    formData.append('tipoEntrega', document.getElementById('tipoEntrega').value);
    formData.append('estatusPedido', document.getElementById('estatusPedido').value);
    formData.append('total', prendaItems.reduce((sum, prenda) => sum + prenda.subtotal, 0).toFixed(2));
    formData.append('prendas', JSON.stringify(prendaItems));
    
    try {
        btnActualizar.disabled = true;
        btnActualizar.textContent = 'Actualizando...';
        
        const response = await fetch('actualizar_pedido.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'ok') {
            showSuccess('¡Pedido actualizado correctamente!');
            setTimeout(() => {
                window.location.href = 'historial.php';
            }, 1500);
        } else {
            showError('Error al actualizar el pedido: ' + data.message);
            btnActualizar.disabled = false;
            btnActualizar.textContent = 'Actualizar pedido';
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error de conexión. Por favor, intenta nuevamente.');
        btnActualizar.disabled = false;
        btnActualizar.textContent = 'Actualizar pedido';
    }
});

async function cerrarSesion() {
    const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
    
    if (confirmed) {
        window.location.href = 'logout.php';
    }
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosExistentes();
});

// Event listeners del modal
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