<?php
require_once __DIR__ . '/../navbar.php';

$prendaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$prendaId) {
    header('Location: listaPrendas.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT * FROM prendas WHERE pk_prenda = ?
    ");
    $stmt->execute([$prendaId]);
    $prenda = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prenda) {
        header('Location: listaPrendas.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error al consultar la prenda: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Tipo de Ropa</title>
<link rel="stylesheet" href="estilos.css">
<script src="custom-alerts.js"></script>
</head>
<body>

<main>
    <div class="form-container">
        <h1>Editar Tipo de Ropa</h1>
        <form id="formTipoRopa">
            <input type="hidden" id="prendaId" name="id" value="<?= htmlspecialchars($prendaId) ?>">
            <div class="form-group">
                <label for="servicio">Nombre de la Prenda:</label>
                <input type="text" id="nomPrenda" value="<?= htmlspecialchars($prenda['nombrePrenda']) ?>" name="nomPrenda" placeholder="Ingresa el nombre de la prenda" 
                           pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+" 
                           title="Solo se permiten letras y espacios"
                           maxlength="50" required>
            </div>

            <div class="form-group">
                <label for="total">Precio ($):</label>
                <input type="number" name="costoPrenda" value="<?= htmlspecialchars($prenda['costoPrenda']) ?>" id="costoPrenda" step="1" min="0" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                    Editar Prenda
                </button>
                <a href="<?= BASE_URL ?>controllers/prenda_controller.php?action=list" class="btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12,19 5,12 12,5"/>
                    </svg>
                    Cancelar    
                </a>
            </div>
        </form>
    </div>
</main>

<!-- Modal de éxito -->
<div class="modal" id="modalExito">
    <div class="modal-content">
        <div class="modal-header">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
        </div>
        <div class="modal-body">
            <h2>Prenda editada correctamente!</h2>
            <!-- <p>El pedido ha sido guardado exitosamente en el sistema.</p> -->
        </div>
        <div class="modal-actions">
            <button class="btn-primary" id="btnCerrarModal">Aceptar</button>
        </div>
    </div>
</div>

<script>
document.getElementById("formTipoRopa").addEventListener("submit", async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    
    try {
        const resp = await fetch('<?= BASE_URL ?>controllers/prenda_controller.php?action=update', {
            method: "POST",
            body: formData
        });
        const data = await resp.json();

        if (data.status === "ok") {
            document.getElementById("modalExito").style.display = "flex";
            this.reset();
        } else {
            showError("Error: " + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showError("Error de conexión");
    }
});

document.getElementById("btnCerrarModal").addEventListener("click", () => {
    document.getElementById("modalExito").style.display = "none";
    window.location.href = "<?= BASE_URL ?>controllers/prenda_controller.php?action=list";
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