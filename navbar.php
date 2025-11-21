<?php
require_once 'config.php';
require_once 'auth.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>estilos.css">
    <script src="<?= BASE_URL ?>custom-alerts.js"></script>

</head>

<body>
<div class="fondo-ilustrado"></div>

<header class="navbar">
<div class="nav-left">
    <a href="<?= BASE_URL ?>controllers/cliente_controller.php?action=list" class="nav-logo">
        <img src="<?= BASE_URL ?>img/logo.png" alt="Logo" class="logo">
    </a>

    <div class="nav-dropdown">
        <a href="#" class="nav-dropdown-toggle">Clientes</a>
        <div class="nav-dropdown-menu">
            <a href="<?= BASE_URL ?>controllers/cliente_controller.php?action=list">Ver todos los clientes</a>
            <!-- <a href="<?= BASE_URL ?>views/index.php">Ver todos los clientes</a> -->
            <a href="<?= BASE_URL ?>views/nuevo_cliente.php">Registrar nuevo cliente</a>
        </div>
    </div>

    <div class="nav-dropdown">
        <a href="#" class="nav-dropdown-toggle">Pedidos</a>
        <div class="nav-dropdown-menu">
            <a href="<?= BASE_URL ?>controllers/pedido_controller.php?action=list">Historial de pedidos</a>
            <!-- <a href="<?= BASE_URL ?>views/historial.php">Historial de pedidos</a> -->
            <a href="<?= BASE_URL ?>views/nuevo_pedido.php">Crear nuevo pedido</a>
        </div>
    </div>

    <div class="nav-dropdown">
        <a href="#" class="nav-dropdown-toggle">Prendas</a>
        <div class="nav-dropdown-menu">
            <a href="<?= BASE_URL ?>controllers/prenda_controller.php?action=list">Lista de prendas</a>
            <!-- <a href="<?= BASE_URL ?>listaPrendas.php">Lista de prendas</a> -->
            <a href="<?= BASE_URL ?>views/nueva_prenda.php">Registrar nueva prenda</a>
        </div>
    </div>

    <div class="nav-dropdown">
        <a href="#" class="nav-dropdown-toggle">Usuarios</a>
        <div class="nav-dropdown-menu">
            <a href="<?= BASE_URL ?>controllers/user_controller.php?action=list">Lista de usuarios</a>
            <a href="<?= BASE_URL ?>views/nuevo_usuario.php">Registrar nuevo usuario</a>
        </div>
    </div>
</div>
    <div class="nav-right">
        <div class="user-info">
            <div class="user-icon">
                <?= strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)) ?>
            </div>
            <span class="user-name">
                <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? $_SESSION['usuario_nombre']) ?>
            </span>
        </div>
        <button class="btn-cerrar" onclick="cerrarSesion()">
            <span class="logout-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16,17 21,12 16,7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </span>
            Cerrar sesión
        </button>
    </div>
</header>

<script>
// Mejorar funcionalidad del menú desplegable en móviles
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
    
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            // En dispositivos móviles, manejar el clic para mostrar/ocultar
            if (window.innerWidth <= 768) {
                const dropdown = this.parentElement;
                const menu = dropdown.querySelector('.nav-dropdown-menu');
                const isOpen = dropdown.classList.contains('active');
                
                // Cerrar todos los otros menús
                document.querySelectorAll('.nav-dropdown').forEach(function(d) {
                    d.classList.remove('active');
                });
                
                // Toggle del menú actual
                if (!isOpen) {
                    dropdown.classList.add('active');
                }
            }
        });
    });
    
    // Cerrar menús al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-dropdown')) {
            document.querySelectorAll('.nav-dropdown').forEach(function(dropdown) {
                dropdown.classList.remove('active');
            });
        }
    });
});

async function cerrarSesion() {
    // Use customConfirm from custom-alerts.js if available
    try {
        const confirmed = await customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Confirmar cierre de sesión');
        if (confirmed) {
            window.location.href = 'logout.php';
        }
    } catch (e) {
        // Fallback simple confirm
        if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            window.location.href = 'logout.php';
        }
    }
}
</script>