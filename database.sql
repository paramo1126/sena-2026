-- ==============================================================================
-- BASE DE DATOS: construccionesmym
-- PROYECTO SENA 2026: M&M Construcciones y Servicios S.A.S.
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `construccionesmym` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `construccionesmym`;

-- ------------------------------------------------------------------------------
-- 1. Estructura de la tabla `usuarios`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `clave` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos iniciales de usuarios administradores
INSERT INTO `usuarios` (`id`, `usuario`, `clave`) VALUES
(1, 'josephparamo@gmail.com', 'josephparamo2008.'),
(2, 'administrador@gmail.com', '12345678');

-- ------------------------------------------------------------------------------
-- 2. Estructura de la tabla `maquinarias`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `maquinarias`;
CREATE TABLE `maquinarias` (
  `id_maquina` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` enum('Maquinaria','Vehiculo') NOT NULL,
  `placa` char(6) NOT NULL,
  `modelo` varchar(60) NOT NULL,
  `caracteristicas` varchar(100) DEFAULT NULL,
  `ubicacion_actual` varchar(80) NOT NULL DEFAULT 'Base Principal',
  `estado` enum('Disponible','Trabajando','En mantenimiento') NOT NULL DEFAULT 'Disponible',
  `fecha_entrada` date DEFAULT NULL,
  `hora_entrada` time DEFAULT NULL,
  `fecha_salida` date DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  PRIMARY KEY (`id_maquina`),
  UNIQUE KEY `placa` (`placa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------------------------
-- 3. Estructura de la tabla `historial_movimientos`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `historial_movimientos`;
CREATE TABLE `historial_movimientos` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_maquina` int(11) NOT NULL,
  `accion` varchar(80) NOT NULL,
  `ubicacion` varchar(80) NOT NULL,
  `estado` enum('Disponible','Trabajando','En mantenimiento') NOT NULL,
  `fecha_entrada` date DEFAULT NULL,
  `hora_entrada` time DEFAULT NULL,
  `fecha_salida` date DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_movimiento`),
  KEY `id_maquina` (`id_maquina`),
  CONSTRAINT `historial_movimientos_ibfk_1` FOREIGN KEY (`id_maquina`) REFERENCES `maquinarias` (`id_maquina`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
