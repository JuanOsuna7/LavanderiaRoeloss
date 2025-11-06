-- Script SQL para crear la tabla de intentos de login
-- Ejecutar este script en phpMyAdmin o su gestor de base de datos preferido

CREATE TABLE IF NOT EXISTS `intentos_login` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `usuario` varchar(100) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `fecha_intento` datetime NOT NULL,
    `exitoso` tinyint(1) NOT NULL DEFAULT 0,
    `user_agent` text,
    PRIMARY KEY (`id`),
    KEY `idx_usuario` (`usuario`),
    KEY `idx_ip` (`ip_address`),
    KEY `idx_fecha` (`fecha_intento`),
    KEY `idx_exitoso` (`exitoso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar campo ultimo_login a la tabla usuarios si no existe
ALTER TABLE `usuarios` 
ADD COLUMN `ultimo_login` datetime NULL DEFAULT NULL AFTER `estatusUsu`;

-- Crear índices para mejorar el rendimiento
CREATE INDEX `idx_usuario_activo` ON `usuarios` (`correoUsu`, `estatusUsu`);
CREATE INDEX `idx_estatus` ON `usuarios` (`estatusUsu`);