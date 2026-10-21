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

-- ------------------------------------------------------------------------------
-- 4. Estructura de la tabla `contenido_inicio`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `contenido_inicio`;
CREATE TABLE `contenido_inicio` (
  `id` int(11) NOT NULL PRIMARY KEY,
  `b1_imagen` varchar(255) NOT NULL DEFAULT 'imagen1.png',
  `b1_titulo` varchar(255) NOT NULL,
  `b1_subtitulo` text NOT NULL,
  `b2_titulo` varchar(255) NOT NULL,
  `b2_subtitulo` text NOT NULL,
  `t1_titulo` varchar(255) NOT NULL,
  `t1_texto` text NOT NULL,
  `t1_vinetas` text NOT NULL,
  `t2_titulo` varchar(255) NOT NULL,
  `t2_texto` text NOT NULL,
  `t2_vinetas` text NOT NULL,
  `carrusel_img1` varchar(255) NOT NULL DEFAULT 'imagen 22.png',
  `carrusel_img2` varchar(255) NOT NULL DEFAULT 'imagen 33.png',
  `carrusel_img3` varchar(255) NOT NULL DEFAULT 'imagen 44.png',
  `b3_titulo` varchar(255) NOT NULL,
  `b3_subtitulo` text NOT NULL,
  `m1_titulo` varchar(255) NOT NULL,
  `m1_texto` text NOT NULL,
  `m2_titulo` varchar(255) NOT NULL,
  `m2_texto` text NOT NULL,
  `m3_titulo` varchar(255) NOT NULL,
  `m3_texto` text NOT NULL,
  `footer_vinetas` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------------------------
-- 5. Estructura de la tabla `contenido_proyectos`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `contenido_proyectos`;
CREATE TABLE `contenido_proyectos` (
  `id` int(11) NOT NULL PRIMARY KEY,
  `carrusel_img1` varchar(255) NOT NULL DEFAULT 'imagen 44.png',
  `carrusel_img2` varchar(255) NOT NULL DEFAULT 'imagen 22.png',
  `carrusel_img3` varchar(255) NOT NULL DEFAULT 'imagen 33.png',
  `btn1_nombre` varchar(100) NOT NULL DEFAULT 'Calles pavimentadas',
  `btn2_nombre` varchar(100) NOT NULL DEFAULT 'Tuberias y redes',
  `btn3_nombre` varchar(100) NOT NULL DEFAULT 'Evidencias',
  `calles_titulo` varchar(200) NOT NULL DEFAULT 'Calles pavimentadas',
  `calles_texto` text NOT NULL,
  `calles_badge` varchar(150) NOT NULL DEFAULT 'Evidencia con direccion de referencia',
  `c1_img` varchar(255) NOT NULL DEFAULT 'imagen1.png',
  `c1_badge` varchar(100) NOT NULL DEFAULT 'Pavimentacion',
  `c1_titulo` varchar(200) NOT NULL DEFAULT 'Mejoramiento vial barrio Las Palmas',
  `c1_vinetas` text NOT NULL,
  `c2_img` varchar(255) NOT NULL DEFAULT 'imagen2.png',
  `c2_badge` varchar(100) NOT NULL DEFAULT 'Andenes',
  `c2_titulo` varchar(200) NOT NULL DEFAULT 'Andenes y sardineles sector Centro',
  `c2_vinetas` text NOT NULL,
  `c3_img` varchar(255) NOT NULL DEFAULT 'imagen3.png',
  `c3_badge` varchar(100) NOT NULL DEFAULT 'Via urbana',
  `c3_titulo` varchar(200) NOT NULL DEFAULT 'Adecuacion vial barrio El Jardin',
  `c3_vinetas` text NOT NULL,
  `tuberias_titulo` varchar(200) NOT NULL DEFAULT 'Tuberias y redes',
  `tuberias_texto` text NOT NULL,
  `tuberias_badge` varchar(150) NOT NULL DEFAULT 'Redes instaladas y verificadas',
  `t1_img` varchar(255) NOT NULL DEFAULT 'imagen4.png',
  `t1_badge` varchar(100) NOT NULL DEFAULT 'Redes de gas',
  `t1_titulo` varchar(200) NOT NULL DEFAULT 'Instalacion de red interna residencial',
  `t1_vinetas` text NOT NULL,
  `t2_img` varchar(255) NOT NULL DEFAULT 'imagen2.png',
  `t2_badge` varchar(100) NOT NULL DEFAULT 'Red hidraulica',
  `t2_titulo` varchar(200) NOT NULL DEFAULT 'Rehabilitacion de tuberia principal',
  `t2_vinetas` text NOT NULL,
  `evidencias_badge1` varchar(100) NOT NULL DEFAULT 'Galería Técnica',
  `evidencias_badge2` varchar(100) NOT NULL DEFAULT 'Control de Calidad',
  `evidencias_titulo` varchar(200) NOT NULL DEFAULT 'Evidencias y Registro Fotográfico',
  `evidencias_texto` text NOT NULL,
  `evidencias_badge3` varchar(150) NOT NULL DEFAULT 'Auditoría y trazabilidad en cada fase del proyecto',
  `e1_img` varchar(255) NOT NULL DEFAULT 'imagen 44.png',
  `e1_badge` varchar(100) NOT NULL DEFAULT 'Obra Civil',
  `e1_titulo` varchar(200) NOT NULL DEFAULT 'Pavimentación y Control de Asfalto',
  `e1_vinetas` text NOT NULL,
  `e2_img` varchar(255) NOT NULL DEFAULT 'imagen3.png',
  `e2_badge` varchar(100) NOT NULL DEFAULT 'Red Hidráulica',
  `e2_titulo` varchar(200) NOT NULL DEFAULT 'Canalización e Instalación Subterránea',
  `e2_vinetas` text NOT NULL,
  `e3_img` varchar(255) NOT NULL DEFAULT 'imagen 22.png',
  `e3_badge` varchar(100) NOT NULL DEFAULT 'Redes de Gas',
  `e3_titulo` varchar(200) NOT NULL DEFAULT 'Empalme y Válvulas de Seguridad',
  `e3_vinetas` text NOT NULL,
  `e4_img` varchar(255) NOT NULL DEFAULT 'imagen 33.png',
  `e4_badge` varchar(100) NOT NULL DEFAULT 'Pavimentación',
  `e4_titulo` varchar(200) NOT NULL DEFAULT 'Entrega Final y Señalización Vial',
  `e4_vinetas` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------------------------
-- 6. Estructura de la tabla `contenido_nosotros`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `contenido_nosotros`;
CREATE TABLE `contenido_nosotros` (
  `id` int(11) NOT NULL PRIMARY KEY,
  `carrusel_img1` varchar(255) NOT NULL DEFAULT 'imagen 22.png',
  `carrusel_img2` varchar(255) NOT NULL DEFAULT 'imagen 44.png',
  `carrusel_img3` varchar(255) NOT NULL DEFAULT 'imagen 33.png',
  `qs_imagen` varchar(255) NOT NULL DEFAULT 'imagen4.png',
  `qs_titulo` varchar(200) NOT NULL DEFAULT '¿Quiénes somos?',
  `qs_texto` text NOT NULL,
  `qs_vinetas` text NOT NULL,
  `mision_titulo` varchar(200) NOT NULL DEFAULT 'Misión',
  `mision_texto` text NOT NULL,
  `vision_titulo` varchar(200) NOT NULL DEFAULT 'Visión',
  `vision_texto` text NOT NULL,
  `valores_titulo` varchar(200) NOT NULL DEFAULT 'Nuestros Valores',
  `valores_subtitulo` text NOT NULL,
  `v1_titulo` varchar(100) NOT NULL DEFAULT 'Seguridad',
  `v1_texto` text NOT NULL,
  `v2_titulo` varchar(100) NOT NULL DEFAULT 'Calidad',
  `v2_texto` text NOT NULL,
  `v3_titulo` varchar(100) NOT NULL DEFAULT 'Cumplimiento',
  `v3_texto` text NOT NULL,
  `v4_titulo` varchar(100) NOT NULL DEFAULT 'Sostenibilidad',
  `v4_texto` text NOT NULL,
  `m1_valor` varchar(50) NOT NULL DEFAULT '+150',
  `m1_texto` varchar(150) NOT NULL DEFAULT 'Proyectos Ejecutados',
  `m2_valor` varchar(50) NOT NULL DEFAULT '+50 Km',
  `m2_texto` varchar(150) NOT NULL DEFAULT 'Redes de Gas Instaladas',
  `m3_valor` varchar(50) NOT NULL DEFAULT '100%',
  `m3_texto` varchar(150) NOT NULL DEFAULT 'Certificación Normativa',
  `m4_valor` varchar(50) NOT NULL DEFAULT '+15',
  `m4_texto` varchar(150) NOT NULL DEFAULT 'Años de Trayectoria'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------------------------
-- 7. Estructura de la tabla `contenido_maquinaria`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `contenido_maquinaria`;
CREATE TABLE `contenido_maquinaria` (
  `id` int(11) NOT NULL PRIMARY KEY,
  `hero_imagen` varchar(255) NOT NULL DEFAULT 'imagen3.png',
  `hero_badge` varchar(150) NOT NULL DEFAULT 'FLOTA PESADA Y EQUIPOS CERTIFICADOS',
  `hero_titulo` varchar(200) NOT NULL DEFAULT 'FLOTA DE MAQUINARIA Y EQUIPOS',
  `hero_texto` text NOT NULL,
  `tab1_nombre` varchar(100) NOT NULL DEFAULT 'Todas las Máquinas',
  `tab2_nombre` varchar(100) NOT NULL DEFAULT 'Excavación y Tierras',
  `tab3_nombre` varchar(100) NOT NULL DEFAULT 'Tuberías y Redes de Gas',
  `tab4_nombre` varchar(100) NOT NULL DEFAULT 'Carga y Transporte',
  `garantia_titulo` varchar(200) NOT NULL DEFAULT 'Garantía y Respaldo en Obra',
  `garantia_texto` text NOT NULL,
  `g1_titulo` varchar(150) NOT NULL DEFAULT 'Mantenimiento Preventivo',
  `g1_texto` text NOT NULL,
  `g2_titulo` varchar(150) NOT NULL DEFAULT 'Operadores Certificados',
  `g2_texto` text NOT NULL,
  `g3_titulo` varchar(150) NOT NULL DEFAULT 'Asistencia 24/7',
  `g3_texto` text NOT NULL,
  `g4_titulo` varchar(150) NOT NULL DEFAULT 'Certificaciones Vigentes',
  `g4_texto` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------------------------
-- 8. Estructura de la tabla `contenido_contacto`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `contenido_contacto`;
CREATE TABLE `contenido_contacto` (
  `id` int(11) NOT NULL PRIMARY KEY,
  `hero_titulo` varchar(150) NOT NULL DEFAULT 'Hablemos de su próximo proyecto',
  `hero_texto` text NOT NULL,
  `wpp_numero` varchar(50) NOT NULL DEFAULT '573026043945',
  `wpp_mensaje_hero` varchar(250) NOT NULL DEFAULT 'Hola, quiero información sobre sus servicios',
  `wpp_mensaje_flotante` varchar(250) NOT NULL DEFAULT 'Hola, cómo estás información sobre sus servicios',
  `c1_dato` varchar(100) NOT NULL DEFAULT '+57 310 291 6140',
  `c1_desc` varchar(100) NOT NULL DEFAULT 'Atención comercial',
  `c2_dato` varchar(100) NOT NULL DEFAULT '+57 302 604 3945',
  `c2_desc` varchar(100) NOT NULL DEFAULT 'Respuesta rápida',
  `c3_dato` varchar(100) NOT NULL DEFAULT 'contacto@constructoresmym.com',
  `c3_desc` varchar(100) NOT NULL DEFAULT 'Cotizaciones y soporte',
  `c4_dato` varchar(100) NOT NULL DEFAULT 'Neiva, Huila, Colombia',
  `c4_desc` varchar(100) NOT NULL DEFAULT 'Área de servicio',
  `mapa_busqueda` text NOT NULL,
  `mapa_titulo` varchar(150) NOT NULL DEFAULT 'Estamos en Neiva, Huila',
  `mapa_texto` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `contenido_contacto` (`id`, `hero_titulo`, `hero_texto`, `wpp_numero`, `wpp_mensaje_hero`, `wpp_mensaje_flotante`, `c1_dato`, `c1_desc`, `c2_dato`, `c2_desc`, `c3_dato`, `c3_desc`, `c4_dato`, `c4_desc`, `mapa_busqueda`, `mapa_titulo`, `mapa_texto`) VALUES
(1, 'Hablemos de su próximo proyecto', 'Estamos listos para asesorarle en construcción civil, redes de gas, mantenimiento e infraestructura. Cuéntenos qué necesita y nuestro equipo le contactará con una respuesta clara y oportuna.', '573026043945', 'Hola, quiero información sobre sus servicios', 'Hola, cómo estás información sobre sus servicios', '+57 310 291 6140', 'Atención comercial', '+57 302 604 3945', 'Respuesta rápida', 'contacto@constructoresmym.com', 'Cotizaciones y soporte', 'Neiva, Huila, Colombia', 'Área de servicio', 'Neiva, Huila, Colombia', 'Estamos en Neiva, Huila', 'Atendemos proyectos en la ciudad y zonas cercanas con la mejor calidad técnica y cumplimiento normativo.');
