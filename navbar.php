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
    <!-- Botón hamburguesa para móviles -->
    <button class="nav-toggle" id="navToggle" aria-label="Menú">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
    </button>

    <div class="nav-left">
        <a href="<?= BASE_URL ?>controllers/cliente_controller.php?action=list" class="nav-logo">
            <img src="<?= BASE_URL ?>img/logo.png" alt="Logo" class="logo">
        </a>

        <!-- Menú de navegación para escritorio -->
        <div class="nav-links-desktop">
            <div class="nav-dropdown">
                <a href="#" class="nav-dropdown-toggle">Clientes</a>
                <div class="nav-dropdown-menu">
                    <a href="<?= BASE_URL ?>controllers/cliente_controller.php?action=list">Ver todos los clientes</a>
                    <a href="<?= BASE_URL ?>views/nuevo_cliente.php">Registrar nuevo cliente</a>
                </div>
            </div>

            <div class="nav-dropdown">
                <a href="#" class="nav-dropdown-toggle">Pedidos</a>
                <div class="nav-dropdown-menu">
                    <a href="<?= BASE_URL ?>controllers/pedido_controller.php?action=list">Historial de pedidos</a>
                    <a href="<?= BASE_URL ?>views/nuevo_pedido.php">Crear nuevo pedido</a>
                </div>
            </div>

            <div class="nav-dropdown">
                <a href="#" class="nav-dropdown-toggle">Prendas</a>
                <div class="nav-dropdown-menu">
                    <a href="<?= BASE_URL ?>controllers/prenda_controller.php?action=list">Lista de prendas</a>
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

            <a href="<?= BASE_URL ?>views/manual_usuario.php" class="nav-manual-btn">
                Manual de usuario
            </a>
        </div>
    </div>

    <!-- Menú móvil colapsable -->
    <div class="nav-menu-mobile" id="navMenu">
        <div class="nav-item">
            <div class="nav-dropdown">
                <a href="#" class="nav-dropdown-toggle">Clientes</a>
                <div class="nav-dropdown-menu">
                    <a href="<?= BASE_URL ?>controllers/cliente_controller.php?action=list">Ver todos los clientes</a>
                    <a href="<?= BASE_URL ?>views/nuevo_cliente.php">Registrar nuevo cliente</a>
                </div>
            </div>
        </div>

        <div class="nav-item">
            <div class="nav-dropdown">
                <a href="#" class="nav-dropdown-toggle">Pedidos</a>
                <div class="nav-dropdown-menu">
                    <a href="<?= BASE_URL ?>controllers/pedido_controller.php?action=list">Historial de pedidos</a>
                    <a href="<?= BASE_URL ?>views/nuevo_pedido.php">Crear nuevo pedido</a>
                </div>
            </div>
        </div>

        <div class="nav-item">
            <div class="nav-dropdown">
                <a href="#" class="nav-dropdown-toggle">Prendas</a>
                <div class="nav-dropdown-menu">
                    <a href="<?= BASE_URL ?>controllers/prenda_controller.php?action=list">Lista de prendas</a>
                    <a href="<?= BASE_URL ?>views/nueva_prenda.php">Registrar nueva prenda</a>
                </div>
            </div>
        </div>

        <div class="nav-item">
            <div class="nav-dropdown">
                <a href="#" class="nav-dropdown-toggle">Usuarios</a>
                <div class="nav-dropdown-menu">
                    <a href="<?= BASE_URL ?>controllers/user_controller.php?action=list">Lista de usuarios</a>
                    <a href="<?= BASE_URL ?>views/nuevo_usuario.php">Registrar nuevo usuario</a>
                </div>
            </div>
        </div>

        <div class="nav-item">
            <a href="<?= BASE_URL ?>views/manual_usuario.php" class="nav-manual-btn-mobile">
                Manual de usuario
            </a>
        </div>

        <div class="nav-right-mobile">
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
    </div>

    <!-- Usuario info para escritorio -->
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

<!-- Overlay para cerrar menú en móviles -->
<div class="nav-overlay" id="navOverlay"></div>

<script>
// Funcionalidad del menú hamburguesa y dropdowns
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    const navOverlay = document.getElementById('navOverlay');
    const dropdownTogglesMobile = document.querySelectorAll('#navMenu .nav-dropdown-toggle');
    const dropdownTogglesDesktop = document.querySelectorAll('.nav-links-desktop .nav-dropdown-toggle');

    console.log('NavToggle:', navToggle);
    console.log('NavMenu:', navMenu);
    console.log('NavOverlay:', navOverlay);

    // Toggle del menú principal en móviles
    if (navToggle && navMenu && navOverlay) {
        navToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Botón hamburguesa presionado');
            
            const isActive = navMenu.classList.contains('active');
            
            if (isActive) {
                closeMenu();
            } else {
                openMenu();
            }
        });
    } else {
        console.error('Elementos no encontrados:', {navToggle, navMenu, navOverlay});
    }
    
    function openMenu() {
        console.log('Abriendo menú');
        navMenu.classList.add('active');
        navToggle.classList.add('active');
        navOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeMenu() {
        console.log('Cerrando menú');
        navMenu.classList.remove('active');
        navToggle.classList.remove('active');
        navOverlay.classList.remove('active');
        document.body.style.overflow = '';
        
        // Cerrar todos los dropdowns móviles
        document.querySelectorAll('#navMenu .nav-dropdown').forEach(function(dropdown) {
            dropdown.classList.remove('active');
        });
    }

    // Cerrar menú al hacer clic en overlay
    if (navOverlay) {
        navOverlay.addEventListener('click', function() {
            console.log('Click en overlay');
            closeMenu();
        });
    }

    // Cerrar menú al hacer clic en un enlace móvil
    const navLinksMobile = document.querySelectorAll('#navMenu .nav-dropdown-menu a');
    navLinksMobile.forEach(function(link) {
        link.addEventListener('click', closeMenu);
    });

    // Manejo de dropdowns en móviles
    dropdownTogglesMobile.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Click en dropdown toggle móvil');
            
            const dropdown = this.parentElement;
            const isOpen = dropdown.classList.contains('active');
            
            console.log('Dropdown:', dropdown);
            console.log('Estado actual (isOpen):', isOpen);
            console.log('Clases antes:', dropdown.className);
            
            // Cerrar todos los otros menús (excepto el actual)
            document.querySelectorAll('#navMenu .nav-dropdown').forEach(function(d) {
                if (d !== dropdown) {
                    d.classList.remove('active');
                }
            });
            
            // Toggle del menú actual (abrir si está cerrado, cerrar si está abierto)
            dropdown.classList.toggle('active');
            
            console.log('Clases después:', dropdown.className);
        });
    });

    // Manejo de dropdowns en escritorio (solo prevenir navegación)
    dropdownTogglesDesktop.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
    
    // Cerrar menú automáticamente al cambiar el tamaño de ventana
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeMenu();
        }
    });
    
    // Prevenir scroll horizontal
    window.addEventListener('orientationchange', function() {
        setTimeout(function() {
            if (window.innerWidth <= 768 && navMenu && navMenu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            }
        }, 100);
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