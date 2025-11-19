<?php
require_once 'config.php'; // debe exponer $pdo

// Detectar columna PK probable en tabla 'clientes'
function detectarColumnaId(PDO $pdo): string {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM clientes")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            $f = $c['Field'] ?? '';
            if (preg_match('/^(pk_|id_|.*_cliente$)/i', $f)) return $f;
        }
        return $cols[0]['Field'] ?? 'id';
    } catch (Exception $e) {
        return 'id';
    }
}

$idCol = detectarColumnaId($pdo);

// POST: actualizar estatus (debe ejecutarse antes de cualquier salida)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // aceptar tanto 'id' como 'id_cliente'
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
    $estatus = filter_input(INPUT_POST, 'estatusCli', FILTER_SANITIZE_STRING);

    if (!$id || !$estatus) {
        header('Location: index.php?error=invalid_input');
        exit;
    }

    $estatusNorm = ucfirst(strtolower(trim($estatus)));
    if (!in_array(strtolower($estatusNorm), ['activo','inactivo'], true)) {
        header('Location: index.php?error=invalid_status');
        exit;
    }

    try {
        $sql = "UPDATE clientes SET estatusCli = ? WHERE `$idCol` = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$estatusNorm, $id]);
        header('Location: index.php?msg=updated');
        exit;
    } catch (Exception $e) {
        header('Location: index.php?error=update_failed');
        exit;
    }
}

// GET: mostrar formulario
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php?error=invalid_id');
    exit;
}

try {
    $sql = "SELECT * FROM clientes WHERE `$idCol` = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    header('Location: index.php?error=query_failed');
    exit;
}

if (!$cliente) {
    header('Location: index.php?error=not_found');
    exit;
}

// Construir nombre para mostrar
$displayName = trim(
    ($cliente['nombres'] ?? $cliente['nombre'] ?? '') . ' ' .
    ($cliente['aPaterno'] ?? '') . ' ' . ($cliente['aMaterno'] ?? '')
);
if ($displayName === '') {
    $displayName = 'Cliente #' . ($cliente[$idCol] ?? $id);
}

// Incluir navbar (salida a partir de aquí)
require_once 'navbar.php';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Editar estatus - <?= htmlspecialchars($displayName, ENT_QUOTES) ?></title>
<link rel="stylesheet" href="estilos.css">
<style>
/* Estilos modernos y limpios para cambiar estatus */
.edit-wrapper { max-width:640px; margin:36px auto; padding:28px; box-sizing:border-box; }
.card-edit {
    background: #fff;
    border-radius:12px;
    padding:22px;
    box-shadow: 0 8px 30px rgba(2,6,23,0.06);
    border:1px solid rgba(0,0,0,0.04);
    color:#0b3b5a;
}
.card-edit h1 { margin:0 0 8px; font-size:1.15rem; }
.client-meta { color: #345; margin-bottom:18px; font-weight:600; }
.status-row { display:flex; align-items:center; gap:16px; flex-wrap:wrap; }

/* Toggle accesible: input invisible pero clicable */
.toggle { --w:56px; --h:30px; width:var(--w); height:var(--h); position:relative; cursor:pointer; }
.toggle input {
    position:absolute; top:0; left:0; width:var(--w); height:var(--h);
    margin:0; padding:0; opacity:0; z-index:9999; cursor:pointer; pointer-events:auto;
}
.toggle .switch {
    width:100%; height:100%; background: rgba(11,59,90,0.06);
    border-radius:999px; position:relative; transition:background .18s ease;
    box-shadow: inset 0 -2px 6px rgba(0,0,0,0.03);
}
.toggle .knob {
    width: calc(var(--h) - 6px); height: calc(var(--h) - 6px);
    background:#fff; border-radius:50%; position:absolute; top:3px; left:3px;
    transition: transform .18s cubic-bezier(.2,.9,.3,1);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}
.toggle input:checked + .switch { background: linear-gradient(90deg,#4fd1a5,#38b173); }
.toggle input:checked + .switch .knob { transform: translateX(calc(var(--w) - var(--h))); }

/* Status label */
.status-label { font-weight:600; padding:6px 10px; border-radius:8px; background: rgba(11,59,90,0.04); color:#0b3b5a; }
.status-label.inactivo { background: rgba(255,80,80,0.06); color:#d23f3f; }

.form-actions { margin-top:20px; display:flex; gap:12px; align-items:center; }
.btn-primary {
    background: linear-gradient(90deg,#2aa6ff,#0077cc);
    color:#fff; border:0; padding:10px 14px; border-radius:8px; cursor:pointer; font-weight:600;
    box-shadow: 0 6px 18px rgba(0,119,204,0.12);
}
.btn-cancel { background:transparent; color:#0b3b5a; border:1px solid rgba(11,59,90,0.06); padding:9px 12px; border-radius:8px; text-decoration:none; }

@media (max-width:520px) {
    .edit-wrapper { padding:16px; }
}
</style>
</head>
<body>
<main class="edit-wrapper">
    <div class="card-edit" role="region" aria-labelledby="editarTitulo">
        <h1 id="editarTitulo">Editar estatus de cliente</h1>
        <div class="client-meta"><?= htmlspecialchars($displayName, ENT_QUOTES) ?> · ID: <?= htmlspecialchars($cliente[$idCol], ENT_QUOTES) ?></div>

        <form id="formEstatus" method="post" action="edit_cliente.php" novalidate>
            <input type="hidden" name="id" value="<?= htmlspecialchars($cliente[$idCol], ENT_QUOTES) ?>">
            <input type="hidden" id="estatusHidden" name="estatusCli" value="<?= isset($cliente['estatusCli']) ? htmlspecialchars($cliente['estatusCli'], ENT_QUOTES) : 'Inactivo' ?>">

            <div class="status-row" aria-hidden="false">
                <label class="toggle" title="Cambiar estatus" aria-label="Interruptor de estatus" role="switch" aria-checked="<?= (isset($cliente['estatusCli']) && strtolower($cliente['estatusCli']) === 'activo') ? 'true' : 'false' ?>">
                    <input id="estatusToggle" type="checkbox" <?= (isset($cliente['estatusCli']) && strtolower($cliente['estatusCli']) === 'activo') ? 'checked' : '' ?>>
                    <span class="switch" aria-hidden="true">
                        <span class="knob"></span>
                    </span>
                </label>

                <div id="statusPreview" class="status-label"><?= (isset($cliente['estatusCli']) && strtolower($cliente['estatusCli']) === 'activo') ? 'Activo' : 'Inactivo' ?></div>

                <div style="margin-left:auto; color:#567; font-size:0.92rem;">
                    Cambia el estatus del cliente entre Activo e Inactivo
                </div>
            </div>

            <div class="form-actions">
                <button id="guardarBtn" type="submit" class="btn-primary">Guardar cambios</button>
                <a class="btn-cancel" href="index.php">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<script>
(function(){
    const toggle = document.getElementById('estatusToggle');
    const preview = document.getElementById('statusPreview');
    const hidden = document.getElementById('estatusHidden');
    const form = document.getElementById('formEstatus');
    const toggleLabel = document.querySelector('.toggle');

    function refresh() {
        const activo = !!toggle.checked;
        preview.textContent = activo ? 'Activo' : 'Inactivo';
        preview.classList.toggle('inactivo', !activo);
        hidden.value = activo ? 'Activo' : 'Inactivo';
        // actualizar atributo aria-checked para accesibilidad
        if (toggleLabel) toggleLabel.setAttribute('aria-checked', activo ? 'true' : 'false');
    }

    // Inicializar
    refresh();

    // Toggle change
    toggle.addEventListener('change', refresh);

    // Fallback: si algo impide al input recibir el clic, permitir toggle al hacer click en el label
    if (toggleLabel) {
        toggleLabel.addEventListener('click', function(e){
            // si el clic vino directamente del input, no togglear doble
            if (e.target === toggle) return;
            // evitar que el click en el botón de guardado/cancel afecte
            if (e.target.closest('button') || e.target.closest('a')) return;
            toggle.checked = !toggle.checked;
            refresh();
        }, { passive: true });
    }

    // Submit con confirmación bonita si existe customConfirm
    form.addEventListener('submit', async function(e){
        e.preventDefault();
        const nuevo = hidden.value;
        const nombre = "<?= addslashes($displayName) ?>";
        const mensaje = '¿Confirma que desea cambiar el estatus a "' + nuevo + '" para ' + nombre + '?';
        const titulo = 'Confirmar cambio de estatus';

        try {
            if (typeof customConfirm === 'function') {
                const ok = await customConfirm(mensaje, titulo);
                if (ok) form.submit();
            } else {
                if (confirm(mensaje)) form.submit();
            }
        } catch (err) {
            if (confirm(mensaje)) form.submit();
        }
    });
})();
</script>
</body>
</html>