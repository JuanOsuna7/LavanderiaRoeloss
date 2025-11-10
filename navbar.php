<?php
require_once 'config.php';
require_once 'auth.php';

try {
    $sql = "SELECT 
                c.pk_cliente AS id_cliente,
                p.nombres,
                p.aPaterno,
                p.aMaterno, 
                c.telefono,
                c.estatusCli,
                DATE_FORMAT(cast(cast(c.pk_cliente as unsigned) as datetime), '%d/%m/%Y %H:%i') as fecha_registro
            FROM clientes c
            INNER JOIN personas p ON c.fk_persona = p.pk_persona
            ORDER BY c.pk_cliente DESC
            LIMIT 5";
    $stmt = $pdo->query($sql);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar los clientes: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="estilos.css">
    <script src="custom-alerts.js"></script>
</head>

<body>
<div class="fondo-ilustrado"></div>

<header class="navbar">
    <div class="nav-left">
        <a href="index.php"><img src="img/logo.png" alt="Logo" class="logo"></a>
        
        <!-- Sección Clientes -->
        <div class="nav-dropdown">
            <a href="#" class="nav-dropdown-toggle">
                Clientes
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            </a>
            <div class="nav-dropdown-menu">
                <a href="index.php">Ver todos los clientes</a>
                <a href="nuevo_cliente.php">Registrar nuevo cliente</a>
                <a href="nuevo_cliente.php">Registrar nuevo clientesssss</a>

            </div>
        </div>

        <!-- Sección Pedidos -->
        <div class="nav-dropdown">
            <a href="#" class="nav-dropdown-toggle">
                Pedidos
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            </a>
            <div class="nav-dropdown-menu">
                <a href="historial.php">Historial de pedidos</a>
                <a href="nuevo_pedido.php">Crear nuevo pedido</a>
            </div>
        </div>

        <!-- Sección Prendas -->
        <div class="nav-dropdown">
            <a href="#" class="nav-dropdown-toggle">
                Prendas
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            </a>
            <div class="nav-dropdown-menu">
                <a href="listaPrendas.php">Lista de prendas</a>
                <a href="nueva_prenda.php">Registrar nueva prenda</a>
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
</script>