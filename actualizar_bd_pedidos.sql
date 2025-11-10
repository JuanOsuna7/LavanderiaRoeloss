-- Script para actualizar la base de datos para el nuevo flujo de pedidos
-- Ejecutar este script en phpMyAdmin para actualizar la estructura

-- 1. Crear tabla para tipos de prendas con precios
CREATE TABLE IF NOT EXISTS `tipos_prenda` (
  `pk_tipo_prenda` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tipo` varchar(100) NOT NULL,
  `precio_por_kg` decimal(10,2) NOT NULL,
  `descripcion` text,
  `estatus` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pk_tipo_prenda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar tipos de prendas básicos con precios
INSERT INTO `tipos_prenda` (`nombre_tipo`, `precio_por_kg`, `descripcion`) VALUES
('Ropa de casa', 15.00, 'Ropa casual, playeras, pantalones'),
('Cobijas', 25.00, 'Cobijas, edredones, ropa de cama'),
('Ropa delicada', 30.00, 'Ropa que requiere cuidado especial'),
('Ropa de trabajo', 18.00, 'Uniformes, ropa de trabajo');

-- 2. Crear tabla para ítems de pedidos (múltiples tipos de prenda por pedido)
CREATE TABLE IF NOT EXISTS `items_pedido` (
  `pk_item_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `fk_pedido` smallint(6) NOT NULL,
  `fk_tipo_prenda` int(11) NOT NULL,
  `peso_kg` decimal(8,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pk_item_pedido`),
  KEY `fk_pedido` (`fk_pedido`),
  KEY `fk_tipo_prenda` (`fk_tipo_prenda`),
  CONSTRAINT `items_pedido_ibfk_1` FOREIGN KEY (`fk_pedido`) REFERENCES `pedidos` (`pk_pedido`) ON DELETE CASCADE,
  CONSTRAINT `items_pedido_ibfk_2` FOREIGN KEY (`fk_tipo_prenda`) REFERENCES `tipos_prenda` (`pk_tipo_prenda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Modificar tabla pedidos para quitar campos innecesarios
-- Primero eliminamos las restricciones de clave foránea
SET foreign_key_checks = 0;

-- Verificar y eliminar la restricción de clave foránea si existe
ALTER TABLE `pedidos` 
DROP FOREIGN KEY IF EXISTS `pedidos_ibfk_2`;

-- Ahora eliminamos las columnas que ya no necesitamos
ALTER TABLE `pedidos` 
DROP COLUMN IF EXISTS `tipo_ropa`,
DROP COLUMN IF EXISTS `tipo_servicio`,
DROP COLUMN IF EXISTS `fk_servicioRopa`;

-- Reactivar las verificaciones de clave foránea
SET foreign_key_checks = 1;

-- 4. Agregar campo para peso total del pedido
ALTER TABLE `pedidos` 
ADD COLUMN IF NOT EXISTS `peso_total_kg` decimal(8,2) DEFAULT 0.00 AFTER `totalPedido`;

-- 5. Crear tabla temporal para manejar pedidos en proceso
CREATE TABLE IF NOT EXISTS `pedidos_temp` (
  `id_temp` varchar(50) NOT NULL,
  `fk_cliente` smallint(6),
  `tipo_entrega` varchar(50),
  `items_temp` longtext, -- JSON con los ítems temporales
  `total_temp` decimal(10,2) DEFAULT 0.00,
  `peso_total_temp` decimal(8,2) DEFAULT 0.00,
  `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_temp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;