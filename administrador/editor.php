<?php
/**
 * Panel de Administración - Editor de Contenido Web
 * M&M CONSTRUCCIONES Y SERVICIOS S.A.S.
 * 
 * PHP 8 + MySQLi + Bootstrap 5.3
 * Tema Claro Corporativo (#0A192F Navy + #FFC107 Amarillo Corporativo)
 */

// ==============================================================================
// 1. SEGURIDAD Y CONTROL DE SESIÓN
// ==============================================================================
session_start();

// Conexión a la base de datos MySQL
require_once __DIR__ . '/../config/conexion.php';

$conexion_ok = isset($conexion) && !$conexion->connect_error;

// 1. SEGURIDAD Y CONTROL DE SESIÓN DINÁMICO
$sesion_usuario_actual = $_SESSION['admin_activo'] ?? $_SESSION['usuario'] ?? $_SESSION['admin'] ?? '';
$admin_nombre = !empty($sesion_usuario_actual) ? $sesion_usuario_actual : 'Administrador';
$admin_email  = 'admin@construccionesmym.com';
$admin_rol    = $_SESSION['rol'] ?? 'Administrador';

// Consulta preparada a la tabla usuarios (columna usuario) usando la sesión activa
if ($conexion_ok) {
    if (!empty($sesion_usuario_actual)) {
        $stmt_user = $conexion->prepare("SELECT usuario FROM usuarios WHERE usuario = ? LIMIT 1");
        if ($stmt_user) {
            $stmt_user->bind_param("s", $sesion_usuario_actual);
            $stmt_user->execute();
            $res_user = $stmt_user->get_result();
            if ($res_user && $row_u = $res_user->fetch_assoc()) {
                if (!empty($row_u['usuario'])) {
                    $admin_email  = $row_u['usuario'];
                    $admin_nombre = $row_u['usuario'];
                }
            }
            $stmt_user->close();
        }
    } else {
        // En caso de ingreso directo o prueba, consultar el usuario administrador activo en la BD
        $res_u = @$conexion->query("SELECT usuario FROM usuarios LIMIT 1");
        if ($res_u && $row_u = $res_u->fetch_assoc()) {
            if (!empty($row_u['usuario'])) {
                $admin_email  = $row_u['usuario'];
                $admin_nombre = $row_u['usuario'];
                $_SESSION['admin_activo'] = $row_u['usuario'];
            }
        }
    }
}

// ==============================================================================
// GESTIÓN DIRECTA DE CONTENIDO: SECCIÓN INICIO (SELECT / UPDATE CON $conexion)
// ==============================================================================
$contenido = [];

if ($conexion_ok) {
    // 1. SELECT directo sobre la tabla contenido_inicio (fila id = 1)
    $res_inicio = $conexion->query("SELECT * FROM `contenido_inicio` WHERE `id` = 1 LIMIT 1");
    if ($res_inicio && $res_inicio->num_rows > 0) {
        $contenido = $res_inicio->fetch_assoc();
    } else {
        // En caso de tabla recién creada o vacía, insertar registro inicial
        $v1_def = "Edificaciones residenciales y multifamiliares\nObras comerciales e industriales\nRemodelaciones y ampliaciones";
        $v2_def = "Instalación de redes internas y externas\nMantenimiento y rehabilitación\nPruebas de hermeticidad y certificaciones";
        $vf_def = "Neiva, Huila, Colombia\n+57 310 291 6140\ncontacto@constructoresmym.com\nLunes a viernes: 8:00 a.m. - 5:00 p.m.";

        $stmt_ins = $conexion->prepare("INSERT INTO `contenido_inicio` (
            `id`, `b1_imagen`, `b1_titulo`, `b1_subtitulo`,
            `b2_titulo`, `b2_subtitulo`,
            `t1_titulo`, `t1_texto`, `t1_vinetas`,
            `t2_titulo`, `t2_texto`, `t2_vinetas`,
            `carrusel_img1`, `carrusel_img2`, `carrusel_img3`,
            `b3_titulo`, `b3_subtitulo`,
            `m1_titulo`, `m1_texto`,
            `m2_titulo`, `m2_texto`,
            `m3_titulo`, `m3_texto`,
            `footer_vinetas`
        ) VALUES (
            1, 'imagen1.png', 'BIENVENIDO A CONSTRUCCIONES M&M', 'Soluciones integrales de alta ingeniería en construcción civil y redes de gas natural. Proyectos seguros, modernos y con los más altos estándares de calidad.',
            'Nuestros Servicios', 'Ofrecemos soluciones completas para el sector de la construcción y la infraestructura energética.',
            'Construcción Civil', 'Ejecutamos obras residenciales, comerciales e industriales con procesos certificados, control de calidad en cada etapa y entrega puntual. Desde cimentación hasta acabados finales.', ?,
            'Redes de Gas', 'Diseñamos, construimos y mantenemos redes de gas natural para proyectos urbanos e industriales, garantizando seguridad, eficiencia y cumplimiento de la normativa técnica.', ?,
            'imagen 22.png', 'imagen 33.png', 'imagen 44.png',
            '¿Por qué elegirnos?', 'Nuestro compromiso es convertir cada proyecto en una obra segura, eficiente y duradera.',
            'Seguridad garantizada', 'Protocolos estrictos de seguridad industrial y personal capacitado para minimizar riesgos en obra.',
            'Experiencia comprobada', 'Más de 15 años desarrollando proyectos de construcción e infraestructura de gas',
            'Cumplimiento normativo', 'Trabajamos bajo los estándares técnicos y legales vigentes en cada fase del proyecto.',
            ?
        )");
        if ($stmt_ins) {
            $stmt_ins->bind_param('sss', $v1_def, $v2_def, $vf_def);
            $stmt_ins->execute();
            $stmt_ins->close();
        }
        $res_inicio = $conexion->query("SELECT * FROM `contenido_inicio` WHERE `id` = 1 LIMIT 1");
        $contenido = $res_inicio ? $res_inicio->fetch_assoc() : [];
    }

    // Asegurar mapeo de campos bidireccional
    if (is_array($contenido)) {
        $contenido['hero_imagen'] = $contenido['b1_imagen'] ?? $contenido['hero_imagen'] ?? 'imagen1.png';
        $contenido['hero_titulo'] = $contenido['b1_titulo'] ?? $contenido['hero_titulo'] ?? 'BIENVENIDO A CONSTRUCCIONES M&M';
        $contenido['hero_subtitulo'] = $contenido['b1_subtitulo'] ?? $contenido['hero_subtitulo'] ?? '';
        $contenido['servicios_titulo'] = $contenido['b2_titulo'] ?? $contenido['servicios_titulo'] ?? 'Nuestros Servicios';
        $contenido['servicios_subtitulo'] = $contenido['b2_subtitulo'] ?? $contenido['servicios_subtitulo'] ?? '';
        $contenido['servicio1_titulo'] = $contenido['t1_titulo'] ?? $contenido['servicio1_titulo'] ?? 'Construcción Civil';
        $contenido['servicio1_texto'] = $contenido['t1_texto'] ?? $contenido['servicio1_texto'] ?? '';
        $contenido['servicio1_vinetas'] = $contenido['t1_vinetas'] ?? $contenido['servicio1_vinetas'] ?? '';
        $contenido['servicio2_titulo'] = $contenido['t2_titulo'] ?? $contenido['servicio2_titulo'] ?? 'Redes de Gas';
        $contenido['servicio2_texto'] = $contenido['t2_texto'] ?? $contenido['servicio2_texto'] ?? '';
        $contenido['servicio2_vinetas'] = $contenido['t2_vinetas'] ?? $contenido['servicio2_vinetas'] ?? '';
        $contenido['elegirnos_titulo'] = $contenido['b3_titulo'] ?? $contenido['elegirnos_titulo'] ?? '¿Por qué elegirnos?';
        $contenido['elegirnos_subtitulo'] = $contenido['b3_subtitulo'] ?? $contenido['elegirnos_subtitulo'] ?? '';
        $contenido['elegirnos_item1_titulo'] = $contenido['m1_titulo'] ?? $contenido['elegirnos_item1_titulo'] ?? 'Seguridad garantizada';
        $contenido['elegirnos_item1_texto'] = $contenido['m1_texto'] ?? $contenido['elegirnos_item1_texto'] ?? '';
        $contenido['elegirnos_item2_titulo'] = $contenido['m2_titulo'] ?? $contenido['elegirnos_item2_titulo'] ?? 'Experiencia comprobada';
        $contenido['elegirnos_item2_texto'] = $contenido['m2_texto'] ?? $contenido['elegirnos_item2_texto'] ?? '';
        $contenido['elegirnos_item3_titulo'] = $contenido['m3_titulo'] ?? $contenido['elegirnos_item3_titulo'] ?? 'Cumplimiento normativo';
        $contenido['elegirnos_item3_texto'] = $contenido['m3_texto'] ?? $contenido['elegirnos_item3_texto'] ?? '';
        $contenido['footer_contacto_vinetas'] = $contenido['footer_vinetas'] ?? $contenido['footer_contacto_vinetas'] ?? '';
    }

    // ==============================================================================
    // GESTIÓN DIRECTA DE CONTENIDO: SECCIÓN PROYECTOS (SELECT / UPDATE CON $conexion)
    // ==============================================================================
    $contenido_proyectos = [];

    // Valores por defecto para proyectos (para inserción o fallback si existen campos nulos)
    $calles_texto_def = "Obras de pavimentacion, mejoramiento de vias, conformacion de andenes y adecuacion de espacio publico.";
    $c1_v_def = "Calle 8 # 21-45, Neiva, Huila\nEntrega de carpeta asfaltica y nivelacion de via\nRegistro fotografico antes, durante y despues";
    $c2_v_def = "Carrera 5 # 10-32, Neiva, Huila\nConstruccion de andenes, bordillos y accesos\nAcabados seguros para transito peatonal";
    $c3_v_def = "Calle 18 Sur # 7-80, Neiva, Huila\nCompactacion, base granular y acabado final\nControl de calidad por etapas de obra";
    $tuberias_texto_def = "Instalacion, rehabilitacion y mantenimiento de redes de gas e infraestructura hidraulica.";
    $t1_v_def = "Carrera 16 # 22-18, Neiva, Huila\nTrazado, instalacion y prueba de hermeticidad\nSoporte tecnico de cumplimiento normativo";
    $t2_v_def = "Avenida La Toma # 3-60, Neiva, Huila\nExcavacion tecnica, cambio de tramo y relleno compactado\nInspeccion y reporte final de funcionamiento";
    $evidencias_texto_def = "Documentación visual de procesos constructivos, inspecciones técnicas y control de calidad en cada fase del proyecto.";
    $e1_v_def = "Calle 10 con Carrera 15, Neiva, Huila\nControl densimétrico y verificación de espesores\nRegistro fotográfico de tendido y compactación";
    $e2_v_def = "Zona Industrial Sur, Neiva, Huila\nExcavación con zanjadora y entibado de seguridad\nPrueba hidrostática a 150 PSI aprobada";
    $e3_v_def = "Sector Oriente, Neiva, Huila\nTermofusión de polietileno de alta densidad\nInspección radiográfica y sellado certificado";
    $e4_v_def = "Avenida Circunvalar # 14-20, Neiva, Huila\nDemarcación termoplástica y señalización vertical\nActa de entrega y conformidad ciudadana";

    $valores_defecto_proy = [
        'carrusel_img1' => 'imagen 44.png',
        'carrusel_img2' => 'imagen 22.png',
        'carrusel_img3' => 'imagen 33.png',
        'btn1_nombre' => 'Calles pavimentadas',
        'btn2_nombre' => 'Tuberias y redes',
        'btn3_nombre' => 'Evidencias',
        'calles_titulo' => 'Calles pavimentadas',
        'calles_texto' => $calles_texto_def,
        'calles_badge' => 'Evidencia con direccion de referencia',
        'c1_img' => 'imagen1.png',
        'c1_badge' => 'Pavimentacion',
        'c1_titulo' => 'Mejoramiento vial barrio Las Palmas',
        'c1_vinetas' => $c1_v_def,
        'c2_img' => 'imagen2.png',
        'c2_badge' => 'Andenes',
        'c2_titulo' => 'Andenes y sardineles sector Centro',
        'c2_vinetas' => $c2_v_def,
        'c3_img' => 'imagen3.png',
        'c3_badge' => 'Via urbana',
        'c3_titulo' => 'Adecuacion vial barrio El Jardin',
        'c3_vinetas' => $c3_v_def,
        'tuberias_titulo' => 'Tuberias y redes',
        'tuberias_texto' => $tuberias_texto_def,
        'tuberias_badge' => 'Redes instaladas y verificadas',
        't1_img' => 'imagen4.png',
        't1_badge' => 'Redes de gas',
        't1_titulo' => 'Instalacion de red interna residencial',
        't1_vinetas' => $t1_v_def,
        't2_img' => 'imagen2.png',
        't2_badge' => 'Red hidraulica',
        't2_titulo' => 'Rehabilitacion de tuberia principal',
        't2_vinetas' => $t2_v_def,
        'evidencias_badge1' => 'Galería Técnica',
        'evidencias_badge2' => 'Control de Calidad',
        'evidencias_titulo' => 'Evidencias y Registro Fotográfico',
        'evidencias_texto' => $evidencias_texto_def,
        'evidencias_badge3' => 'Auditoría y trazabilidad en cada fase del proyecto',
        'e1_img' => 'imagen 44.png',
        'e1_badge' => 'Obra Civil',
        'e1_titulo' => 'Pavimentación y Control de Asfalto',
        'e1_vinetas' => $e1_v_def,
        'e2_img' => 'imagen3.png',
        'e2_badge' => 'Red Hidráulica',
        'e2_titulo' => 'Canalización e Instalación Subterránea',
        'e2_vinetas' => $e2_v_def,
        'e3_img' => 'imagen 22.png',
        'e3_badge' => 'Redes de Gas',
        'e3_titulo' => 'Empalme y Válvulas de Seguridad',
        'e3_vinetas' => $e3_v_def,
        'e4_img' => 'imagen 33.png',
        'e4_badge' => 'Pavimentación',
        'e4_titulo' => 'Entrega Final y Señalización Vial',
        'e4_vinetas' => $e4_v_def
    ];

    try {
        // 1. Verificación automática de la tabla contenido_proyectos
        $conexion->query("CREATE TABLE IF NOT EXISTS `contenido_proyectos` (
          `id` int(11) NOT NULL PRIMARY KEY
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        // 2. Comprobación y migración de columnas existentes para evitar errores de columnas faltantes
        $columnas_existentes = [];
        $res_cols = $conexion->query("SHOW COLUMNS FROM `contenido_proyectos`");
        if ($res_cols) {
            while ($col_row = $res_cols->fetch_assoc()) {
                $columnas_existentes[$col_row['Field']] = true;
            }
        }

        $columnas_requeridas = [
            'carrusel_img1' => "VARCHAR(255) NOT NULL DEFAULT 'imagen 44.png'",
            'carrusel_img2' => "VARCHAR(255) NOT NULL DEFAULT 'imagen 22.png'",
            'carrusel_img3' => "VARCHAR(255) NOT NULL DEFAULT 'imagen 33.png'",
            'btn1_nombre' => "VARCHAR(100) NOT NULL DEFAULT 'Calles pavimentadas'",
            'btn2_nombre' => "VARCHAR(100) NOT NULL DEFAULT 'Tuberias y redes'",
            'btn3_nombre' => "VARCHAR(100) NOT NULL DEFAULT 'Evidencias'",
            'calles_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Calles pavimentadas'",
            'calles_texto' => "TEXT NULL",
            'calles_badge' => "VARCHAR(150) NOT NULL DEFAULT 'Evidencia con direccion de referencia'",
            'c1_img' => "VARCHAR(255) NOT NULL DEFAULT 'imagen1.png'",
            'c1_badge' => "VARCHAR(100) NOT NULL DEFAULT 'Pavimentacion'",
            'c1_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Mejoramiento vial barrio Las Palmas'",
            'c1_vinetas' => "TEXT NULL",
            'c2_img' => "VARCHAR(255) NOT NULL DEFAULT 'imagen2.png'",
            'c2_badge' => "VARCHAR(100) NOT NULL DEFAULT 'Andenes'",
            'c2_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Andenes y sardineles sector Centro'",
            'c2_vinetas' => "TEXT NULL",
            'c3_img' => "VARCHAR(255) NOT NULL DEFAULT 'imagen3.png'",
            'c3_badge' => "VARCHAR(100) NOT NULL DEFAULT 'Via urbana'",
            'c3_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Adecuacion vial barrio El Jardin'",
            'c3_vinetas' => "TEXT NULL",
            'tuberias_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Tuberias y redes'",
            'tuberias_texto' => "TEXT NULL",
            'tuberias_badge' => "VARCHAR(150) NOT NULL DEFAULT 'Redes instaladas y verificadas'",
            't1_img' => "VARCHAR(255) NOT NULL DEFAULT 'imagen4.png'",
            't1_badge' => "VARCHAR(100) NOT NULL DEFAULT 'Redes de gas'",
            't1_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Instalacion de red interna residencial'",
            't1_vinetas' => "TEXT NULL",
            't2_img' => "VARCHAR(255) NOT NULL DEFAULT 'imagen2.png'",
            't2_badge' => "VARCHAR(100) NOT NULL DEFAULT 'Red hidraulica'",
            't2_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Rehabilitacion de tuberia principal'",
            't2_vinetas' => "TEXT NULL",
            'evidencias_badge1' => "VARCHAR(100) NOT NULL DEFAULT 'Galería Técnica'",
            'evidencias_badge2' => "VARCHAR(100) NOT NULL DEFAULT 'Control de Calidad'",
            'evidencias_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Evidencias y Registro Fotográfico'",
            'evidencias_texto' => "TEXT NULL",
            'evidencias_badge3' => "VARCHAR(150) NOT NULL DEFAULT 'Auditoría y trazabilidad en cada fase del proyecto'",
            'e1_img' => "VARCHAR(255) NOT NULL DEFAULT 'imagen 44.png'",
            'e1_badge' => "VARCHAR(100) NOT NULL DEFAULT 'Obra Civil'",
            'e1_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Pavimentación y Control de Asfalto'",
            'e1_vinetas' => "TEXT NULL",
            'e2_img' => "VARCHAR(255) NOT NULL DEFAULT 'imagen3.png'",
            'e2_badge' => "VARCHAR(100) NOT NULL DEFAULT 'Red Hidráulica'",
            'e2_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Canalización e Instalación Subterránea'",
            'e2_vinetas' => "TEXT NULL",
            'e3_img' => "VARCHAR(255) NOT NULL DEFAULT 'imagen 22.png'",
            'e3_badge' => "VARCHAR(100) NOT NULL DEFAULT 'Redes de Gas'",
            'e3_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Empalme y Válvulas de Seguridad'",
            'e3_vinetas' => "TEXT NULL",
            'e4_img' => "VARCHAR(255) NOT NULL DEFAULT 'imagen 33.png'",
            'e4_badge' => "VARCHAR(100) NOT NULL DEFAULT 'Pavimentación'",
            'e4_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Entrega Final y Señalización Vial'",
            'e4_vinetas' => "TEXT NULL"
        ];

        foreach ($columnas_requeridas as $col_nombre => $col_def) {
            if (!isset($columnas_existentes[$col_nombre])) {
                $conexion->query("ALTER TABLE `contenido_proyectos` ADD COLUMN `$col_nombre` $col_def");
            }
        }

        // SELECT directo sobre la tabla contenido_proyectos (fila id = 1)
        $res_proy = $conexion->query("SELECT * FROM `contenido_proyectos` WHERE `id` = 1 LIMIT 1");
        if ($res_proy && $res_proy->num_rows > 0) {
            $contenido_proyectos = $res_proy->fetch_assoc();
        } else {
            // En caso de tabla recién creada o vacía, insertar registro inicial
            $stmt_p_ins = $conexion->prepare("INSERT INTO `contenido_proyectos` (
                `id`, `carrusel_img1`, `carrusel_img2`, `carrusel_img3`,
                `btn1_nombre`, `btn2_nombre`, `btn3_nombre`,
                `calles_titulo`, `calles_texto`, `calles_badge`,
                `c1_img`, `c1_badge`, `c1_titulo`, `c1_vinetas`,
                `c2_img`, `c2_badge`, `c2_titulo`, `c2_vinetas`,
                `c3_img`, `c3_badge`, `c3_titulo`, `c3_vinetas`,
                `tuberias_titulo`, `tuberias_texto`, `tuberias_badge`,
                `t1_img`, `t1_badge`, `t1_titulo`, `t1_vinetas`,
                `t2_img`, `t2_badge`, `t2_titulo`, `t2_vinetas`,
                `evidencias_badge1`, `evidencias_badge2`, `evidencias_titulo`, `evidencias_texto`, `evidencias_badge3`,
                `e1_img`, `e1_badge`, `e1_titulo`, `e1_vinetas`,
                `e2_img`, `e2_badge`, `e2_titulo`, `e2_vinetas`,
                `e3_img`, `e3_badge`, `e3_titulo`, `e3_vinetas`,
                `e4_img`, `e4_badge`, `e4_titulo`, `e4_vinetas`
            ) VALUES (
                1, 'imagen 44.png', 'imagen 22.png', 'imagen 33.png',
                'Calles pavimentadas', 'Tuberias y redes', 'Evidencias',
                'Calles pavimentadas', ?, 'Evidencia con direccion de referencia',
                'imagen1.png', 'Pavimentacion', 'Mejoramiento vial barrio Las Palmas', ?,
                'imagen2.png', 'Andenes', 'Andenes y sardineles sector Centro', ?,
                'imagen3.png', 'Via urbana', 'Adecuacion vial barrio El Jardin', ?,
                'Tuberias y redes', ?, 'Redes instaladas y verificadas',
                'imagen4.png', 'Redes de gas', 'Instalacion de red interna residencial', ?,
                'imagen2.png', 'Red hidraulica', 'Rehabilitacion de tuberia principal', ?,
                'Galería Técnica', 'Control de Calidad', 'Evidencias y Registro Fotográfico', ?, 'Auditoría y trazabilidad en cada fase del proyecto',
                'imagen 44.png', 'Obra Civil', 'Pavimentación y Control de Asfalto', ?,
                'imagen3.png', 'Red Hidráulica', 'Canalización e Instalación Subterránea', ?,
                'imagen 22.png', 'Redes de Gas', 'Empalme y Válvulas de Seguridad', ?,
                'imagen 33.png', 'Pavimentación', 'Entrega Final y Señalización Vial', ?
            )");
            if ($stmt_p_ins) {
                $stmt_p_ins->bind_param(
                    'ssssssssssss',
                    $calles_texto_def, $c1_v_def, $c2_v_def, $c3_v_def,
                    $tuberias_texto_def, $t1_v_def, $t2_v_def,
                    $evidencias_texto_def, $e1_v_def, $e2_v_def, $e3_v_def, $e4_v_def
                );
                $stmt_p_ins->execute();
                $stmt_p_ins->close();
            }
            $res_proy = $conexion->query("SELECT * FROM `contenido_proyectos` WHERE `id` = 1 LIMIT 1");
            $contenido_proyectos = $res_proy ? $res_proy->fetch_assoc() : [];
        }

        // Asegurar que ningún campo quede vacío o nulo si la tabla preexistía con datos parciales
        if (!is_array($contenido_proyectos)) {
            $contenido_proyectos = [];
        }
        foreach ($valores_defecto_proy as $clave => $val_def) {
            if (!isset($contenido_proyectos[$clave]) || trim((string)$contenido_proyectos[$clave]) === '') {
                $contenido_proyectos[$clave] = $val_def;
            }
        }
    } catch (\Throwable $e_proy) {
        // En caso de cualquier error durante la migración o consulta, inicializar defaults
        if (!is_array($contenido_proyectos)) {
            $contenido_proyectos = [];
        }
        foreach ($valores_defecto_proy as $clave => $val_def) {
            if (!isset($contenido_proyectos[$clave]) || trim((string)$contenido_proyectos[$clave]) === '') {
                $contenido_proyectos[$clave] = $val_def;
            }
        }
    }

    // ==============================================================================
    // GESTIÓN DIRECTA DE CONTENIDO: SECCIÓN NOSOTROS (SELECT / UPDATE CON $conexion)
    // ==============================================================================
    $contenido_nosotros = [];

    // Valores por defecto para nosotros (para inserción o fallback si existen campos nulos)
    $qs_texto_def = "Constructores M&M es una empresa líder especializada en construcción civil y soluciones integrales de infraestructura para redes de gas natural. Contamos con un equipo multidisciplinario de ingenieros, arquitectos y técnicos altamente certificados que desarrollan proyectos ejecutando estándares rigurosos desde el diseño preliminar hasta la entrega final.";
    $qs_vinetas_def = "+15 años de experiencia\nCumplimiento normativo\nPersonal certificado\nProyectos a tiempo";
    $mision_texto_def = "Proveer soluciones integrales e innovadoras en construcción civil e infraestructura de redes de gas, garantizando altos estándares de calidad, seguridad industrial y cumplimiento normativo. Buscamos impactar positivamente en las comunidades a través de obras sostenibles, eficientes y confiables.";
    $vision_texto_def = "Ser reconocidos para el 2030 como la empresa referente a nivel regional y nacional en el desarrollo de proyectos de ingeniería civil y redes de infraestructura energética, destacándonos por la excelencia operativa, el desarrollo de nuestro talento humano y el compromiso medioambiental.";
    $valores_subtitulo_def = "Los principios que guían la ejecución de cada uno de nuestros proyectos.";
    $v1_texto_def = "Protegemos la vida de nuestros colaboradores y de la comunidad en cada obra.";
    $v2_texto_def = "Uso de materiales certificados y procesos con estándares técnicos rigurosos.";
    $v3_texto_def = "Entregamos los proyectos respetando cronogramas y presupuestos pactados.";
    $v4_texto_def = "Respeto por el medio ambiente y mitigación del impacto ambiental.";

    $valores_defecto_nos = [
        'carrusel_img1' => 'imagen 22.png',
        'carrusel_img2' => 'imagen 44.png',
        'carrusel_img3' => 'imagen 33.png',
        'qs_imagen' => 'imagen4.png',
        'qs_titulo' => '¿Quiénes somos?',
        'qs_texto' => $qs_texto_def,
        'qs_vinetas' => $qs_vinetas_def,
        'mision_titulo' => 'Misión',
        'mision_texto' => $mision_texto_def,
        'vision_titulo' => 'Visión',
        'vision_texto' => $vision_texto_def,
        'valores_titulo' => 'Nuestros Valores',
        'valores_subtitulo' => $valores_subtitulo_def,
        'v1_titulo' => 'Seguridad',
        'v1_texto' => $v1_texto_def,
        'v2_titulo' => 'Calidad',
        'v2_texto' => $v2_texto_def,
        'v3_titulo' => 'Cumplimiento',
        'v3_texto' => $v3_texto_def,
        'v4_titulo' => 'Sostenibilidad',
        'v4_texto' => $v4_texto_def,
        'm1_valor' => '+150',
        'm1_texto' => 'Proyectos Ejecutados',
        'm2_valor' => '+50 Km',
        'm2_texto' => 'Redes de Gas Instaladas',
        'm3_valor' => '100%',
        'm3_texto' => 'Certificación Normativa',
        'm4_valor' => '+15',
        'm4_texto' => 'Años de Trayectoria'
    ];

    try {
        // 1. Verificación automática de la tabla contenido_nosotros
        $conexion->query("CREATE TABLE IF NOT EXISTS `contenido_nosotros` (
          `id` int(11) NOT NULL PRIMARY KEY
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        // 2. Comprobación y migración de columnas existentes
        $columnas_existentes_nos = [];
        $res_cols_nos = $conexion->query("SHOW COLUMNS FROM `contenido_nosotros`");
        if ($res_cols_nos) {
            while ($col_row_n = $res_cols_nos->fetch_assoc()) {
                $columnas_existentes_nos[$col_row_n['Field']] = true;
            }
        }

        $columnas_requeridas_nos = [
            'carrusel_img1' => "VARCHAR(255) NOT NULL DEFAULT 'imagen 22.png'",
            'carrusel_img2' => "VARCHAR(255) NOT NULL DEFAULT 'imagen 44.png'",
            'carrusel_img3' => "VARCHAR(255) NOT NULL DEFAULT 'imagen 33.png'",
            'qs_imagen' => "VARCHAR(255) NOT NULL DEFAULT 'imagen4.png'",
            'qs_titulo' => "VARCHAR(200) NOT NULL DEFAULT '¿Quiénes somos?'",
            'qs_texto' => "TEXT NULL",
            'qs_vinetas' => "TEXT NULL",
            'mision_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Misión'",
            'mision_texto' => "TEXT NULL",
            'vision_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Visión'",
            'vision_texto' => "TEXT NULL",
            'valores_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Nuestros Valores'",
            'valores_subtitulo' => "TEXT NULL",
            'v1_titulo' => "VARCHAR(100) NOT NULL DEFAULT 'Seguridad'",
            'v1_texto' => "TEXT NULL",
            'v2_titulo' => "VARCHAR(100) NOT NULL DEFAULT 'Calidad'",
            'v2_texto' => "TEXT NULL",
            'v3_titulo' => "VARCHAR(100) NOT NULL DEFAULT 'Cumplimiento'",
            'v3_texto' => "TEXT NULL",
            'v4_titulo' => "VARCHAR(100) NOT NULL DEFAULT 'Sostenibilidad'",
            'v4_texto' => "TEXT NULL",
            'm1_valor' => "VARCHAR(50) NOT NULL DEFAULT '+150'",
            'm1_texto' => "VARCHAR(150) NOT NULL DEFAULT 'Proyectos Ejecutados'",
            'm2_valor' => "VARCHAR(50) NOT NULL DEFAULT '+50 Km'",
            'm2_texto' => "VARCHAR(150) NOT NULL DEFAULT 'Redes de Gas Instaladas'",
            'm3_valor' => "VARCHAR(50) NOT NULL DEFAULT '100%'",
            'm3_texto' => "VARCHAR(150) NOT NULL DEFAULT 'Certificación Normativa'",
            'm4_valor' => "VARCHAR(50) NOT NULL DEFAULT '+15'",
            'm4_texto' => "VARCHAR(150) NOT NULL DEFAULT 'Años de Trayectoria'"
        ];

        foreach ($columnas_requeridas_nos as $col_nombre => $col_def) {
            if (!isset($columnas_existentes_nos[$col_nombre])) {
                $conexion->query("ALTER TABLE `contenido_nosotros` ADD COLUMN `$col_nombre` $col_def");
            }
        }

        // SELECT directo sobre la tabla contenido_nosotros (fila id = 1)
        $res_nos = $conexion->query("SELECT * FROM `contenido_nosotros` WHERE `id` = 1 LIMIT 1");
        if ($res_nos && $res_nos->num_rows > 0) {
            $contenido_nosotros = $res_nos->fetch_assoc();
        } else {
            // En caso de tabla recién creada o vacía, insertar registro inicial
            $stmt_n_ins = $conexion->prepare("INSERT INTO `contenido_nosotros` (
                `id`, `carrusel_img1`, `carrusel_img2`, `carrusel_img3`,
                `qs_imagen`, `qs_titulo`, `qs_texto`, `qs_vinetas`,
                `mision_titulo`, `mision_texto`, `vision_titulo`, `vision_texto`,
                `valores_titulo`, `valores_subtitulo`,
                `v1_titulo`, `v1_texto`, `v2_titulo`, `v2_texto`,
                `v3_titulo`, `v3_texto`, `v4_titulo`, `v4_texto`,
                `m1_valor`, `m1_texto`, `m2_valor`, `m2_texto`,
                `m3_valor`, `m3_texto`, `m4_valor`, `m4_texto`
            ) VALUES (
                1, 'imagen 22.png', 'imagen 44.png', 'imagen 33.png',
                'imagen4.png', '¿Quiénes somos?', ?, ?,
                'Misión', ?, 'Visión', ?,
                'Nuestros Valores', ?,
                'Seguridad', ?, 'Calidad', ?,
                'Cumplimiento', ?, 'Sostenibilidad', ?,
                '+150', 'Proyectos Ejecutados', '+50 Km', 'Redes de Gas Instaladas',
                '100%', 'Certificación Normativa', '+15', 'Años de Trayectoria'
            )");
            if ($stmt_n_ins) {
                $stmt_n_ins->bind_param(
                    'sssssssss',
                    $qs_texto_def, $qs_vinetas_def,
                    $mision_texto_def, $vision_texto_def,
                    $valores_subtitulo_def,
                    $v1_texto_def, $v2_texto_def, $v3_texto_def, $v4_texto_def
                );
                $stmt_n_ins->execute();
                $stmt_n_ins->close();
            }
            $res_nos = $conexion->query("SELECT * FROM `contenido_nosotros` WHERE `id` = 1 LIMIT 1");
            $contenido_nosotros = $res_nos ? $res_nos->fetch_assoc() : [];
        }

        if (!is_array($contenido_nosotros)) {
            $contenido_nosotros = [];
        }
        foreach ($valores_defecto_nos as $clave => $val_def) {
            if (!isset($contenido_nosotros[$clave]) || trim((string)$contenido_nosotros[$clave]) === '') {
                $contenido_nosotros[$clave] = $val_def;
            }
        }
    } catch (\Throwable $e_nos) {
        if (!is_array($contenido_nosotros)) {
            $contenido_nosotros = [];
        }
        foreach ($valores_defecto_nos as $clave => $val_def) {
            if (!isset($contenido_nosotros[$clave]) || trim((string)$contenido_nosotros[$clave]) === '') {
                $contenido_nosotros[$clave] = $val_def;
            }
        }
    }

    // ==============================================================================
    // GESTIÓN DIRECTA DE CONTENIDO: SECCIÓN MAQUINARIA (SELECT / UPDATE CON $conexion)
    // ==============================================================================
    $contenido_maquinaria = [];

    $hero_texto_maq_def = "Contamos con maquinaria pesada, equipos certificados para redes de gas natural y transporte especializado para ejecutar obras civiles e infraestructura de alta exigencia.";
    $garantia_texto_maq_def = "Aseguramos la máxima productividad de nuestros equipos en sus proyectos civiles e hidrosanitarios.";
    $g1_texto_maq_def = "Equipos revisados minuciosamente antes de cada despacho a obra.";
    $g2_texto_maq_def = "Personal técnico capacitado en normas de seguridad industrial y SST.";
    $g3_texto_maq_def = "Soporte mecánico prioritario en sitio para evitar retrasos en el cronograma.";
    $g4_texto_maq_def = "Documentación al día para ingreso a proyectos públicos y privados.";

    $valores_defecto_maq = [
        'hero_imagen' => 'imagen3.png',
        'hero_badge' => 'FLOTA PESADA Y EQUIPOS CERTIFICADOS',
        'hero_titulo' => 'FLOTA DE MAQUINARIA Y EQUIPOS',
        'hero_texto' => $hero_texto_maq_def,
        'tab1_nombre' => 'Todas las Máquinas',
        'tab2_nombre' => 'Excavación y Tierras',
        'tab3_nombre' => 'Tuberías y Redes de Gas',
        'tab4_nombre' => 'Carga y Transporte',
        'garantia_titulo' => 'Garantía y Respaldo en Obra',
        'garantia_texto' => $garantia_texto_maq_def,
        'g1_titulo' => 'Mantenimiento Preventivo',
        'g1_texto' => $g1_texto_maq_def,
        'g2_titulo' => 'Operadores Certificados',
        'g2_texto' => $g2_texto_maq_def,
        'g3_titulo' => 'Asistencia 24/7',
        'g3_texto' => $g3_texto_maq_def,
        'g4_titulo' => 'Certificaciones Vigentes',
        'g4_texto' => $g4_texto_maq_def
    ];

    try {
        // 1. Verificación automática de la tabla contenido_maquinaria
        $conexion->query("CREATE TABLE IF NOT EXISTS `contenido_maquinaria` (
          `id` int(11) NOT NULL PRIMARY KEY
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        // 2. Comprobación y migración de columnas existentes
        $columnas_existentes_maq = [];
        $res_cols_maq = $conexion->query("SHOW COLUMNS FROM `contenido_maquinaria`");
        if ($res_cols_maq) {
            while ($col_row_m = $res_cols_maq->fetch_assoc()) {
                $columnas_existentes_maq[$col_row_m['Field']] = true;
            }
        }

        $columnas_requeridas_maq = [
            'hero_imagen' => "VARCHAR(255) NOT NULL DEFAULT 'imagen3.png'",
            'hero_badge' => "VARCHAR(150) NOT NULL DEFAULT 'FLOTA PESADA Y EQUIPOS CERTIFICADOS'",
            'hero_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'FLOTA DE MAQUINARIA Y EQUIPOS'",
            'hero_texto' => "TEXT NULL",
            'tab1_nombre' => "VARCHAR(100) NOT NULL DEFAULT 'Todas las Máquinas'",
            'tab2_nombre' => "VARCHAR(100) NOT NULL DEFAULT 'Excavación y Tierras'",
            'tab3_nombre' => "VARCHAR(100) NOT NULL DEFAULT 'Tuberías y Redes de Gas'",
            'tab4_nombre' => "VARCHAR(100) NOT NULL DEFAULT 'Carga y Transporte'",
            'garantia_titulo' => "VARCHAR(200) NOT NULL DEFAULT 'Garantía y Respaldo en Obra'",
            'garantia_texto' => "TEXT NULL",
            'g1_titulo' => "VARCHAR(150) NOT NULL DEFAULT 'Mantenimiento Preventivo'",
            'g1_texto' => "TEXT NULL",
            'g2_titulo' => "VARCHAR(150) NOT NULL DEFAULT 'Operadores Certificados'",
            'g2_texto' => "TEXT NULL",
            'g3_titulo' => "VARCHAR(150) NOT NULL DEFAULT 'Asistencia 24/7'",
            'g3_texto' => "TEXT NULL",
            'g4_titulo' => "VARCHAR(150) NOT NULL DEFAULT 'Certificaciones Vigentes'",
            'g4_texto' => "TEXT NULL"
        ];

        foreach ($columnas_requeridas_maq as $col_nombre => $col_def) {
            if (!isset($columnas_existentes_maq[$col_nombre])) {
                $conexion->query("ALTER TABLE `contenido_maquinaria` ADD COLUMN `$col_nombre` $col_def");
            }
        }

        // SELECT directo sobre la tabla contenido_maquinaria (fila id = 1)
        $res_maq = $conexion->query("SELECT * FROM `contenido_maquinaria` WHERE `id` = 1 LIMIT 1");
        if ($res_maq && $res_maq->num_rows > 0) {
            $contenido_maquinaria = $res_maq->fetch_assoc();
        } else {
            // En caso de tabla recién creada o vacía, insertar registro inicial
            $stmt_m_ins = $conexion->prepare("INSERT INTO `contenido_maquinaria` (
                `id`, `hero_imagen`, `hero_badge`, `hero_titulo`, `hero_texto`,
                `tab1_nombre`, `tab2_nombre`, `tab3_nombre`, `tab4_nombre`,
                `garantia_titulo`, `garantia_texto`,
                `g1_titulo`, `g1_texto`, `g2_titulo`, `g2_texto`,
                `g3_titulo`, `g3_texto`, `g4_titulo`, `g4_texto`
            ) VALUES (
                1, 'imagen3.png', 'FLOTA PESADA Y EQUIPOS CERTIFICADOS', 'FLOTA DE MAQUINARIA Y EQUIPOS', ?,
                'Todas las Máquinas', 'Excavación y Tierras', 'Tuberías y Redes de Gas', 'Carga y Transporte',
                'Garantía y Respaldo en Obra', ?,
                'Mantenimiento Preventivo', ?, 'Operadores Certificados', ?,
                'Asistencia 24/7', ?, 'Certificaciones Vigentes', ?
            )");
            if ($stmt_m_ins) {
                $stmt_m_ins->bind_param(
                    'ssssss',
                    $hero_texto_maq_def, $garantia_texto_maq_def,
                    $g1_texto_maq_def, $g2_texto_maq_def, $g3_texto_maq_def, $g4_texto_maq_def
                );
                $stmt_m_ins->execute();
                $stmt_m_ins->close();
            }
            $res_maq = $conexion->query("SELECT * FROM `contenido_maquinaria` WHERE `id` = 1 LIMIT 1");
            $contenido_maquinaria = $res_maq ? $res_maq->fetch_assoc() : [];
        }

        if (!is_array($contenido_maquinaria)) {
            $contenido_maquinaria = [];
        }
        foreach ($valores_defecto_maq as $clave => $val_def) {
            if (!isset($contenido_maquinaria[$clave]) || trim((string)$contenido_maquinaria[$clave]) === '') {
                $contenido_maquinaria[$clave] = $val_def;
            }
        }
    } catch (\Throwable $e_maq) {
        if (!is_array($contenido_maquinaria)) {
            $contenido_maquinaria = [];
        }
        foreach ($valores_defecto_maq as $clave => $val_def) {
            if (!isset($contenido_maquinaria[$clave]) || trim((string)$contenido_maquinaria[$clave]) === '') {
                $contenido_maquinaria[$clave] = $val_def;
            }
        }
    }

    // ==============================================================================
    // GESTIÓN DIRECTA DE CONTENIDO: SECCIÓN CONTACTO (SELECT / UPDATE CON $conexion)
    // ==============================================================================
    $contenido_contacto = [];

    $hero_texto_cont_def = "Estamos listos para asesorarle en construccion civil, redes de gas, mantenimiento e infraestructura. Cuentenos que necesita y nuestro equipo le contactara con una respuesta clara y oportuna.";
    $mapa_texto_cont_def = "Atendemos proyectos en la ciudad y zonas cercanas con la mejor calidad tecnica y cumplimiento normativo.";

    $valores_defecto_cont = [
        'hero_titulo' => 'Hablemos de su proximo proyecto',
        'hero_texto' => $hero_texto_cont_def,
        'wpp_numero' => '573026043945',
        'wpp_mensaje_hero' => 'Hola, quiero informacion sobre sus servicios',
        'wpp_mensaje_flotante' => 'Hola, como estas información sobre sus servicios',
        'c1_dato' => '+57 310 291 6140',
        'c1_desc' => 'Atencion comercial',
        'c2_dato' => '+57 302 604 3945',
        'c2_desc' => 'Respuesta rapida',
        'c3_dato' => 'contacto@constructoresmym.com',
        'c3_desc' => 'Cotizaciones y soporte',
        'c4_dato' => 'Neiva, Huila, Colombia',
        'c4_desc' => 'Area de servicio',
        'mapa_busqueda' => 'Neiva, Huila, Colombia',
        'mapa_titulo' => 'Estamos en Neiva, Huila',
        'mapa_texto' => $mapa_texto_cont_def
    ];

    try {
        // 1. Verificación automática de la tabla contenido_contacto
        $conexion->query("CREATE TABLE IF NOT EXISTS `contenido_contacto` (
          `id` int(11) NOT NULL PRIMARY KEY
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        // 2. Comprobación y migración de columnas existentes
        $columnas_existentes_cont = [];
        $res_cols_cont = $conexion->query("SHOW COLUMNS FROM `contenido_contacto`");
        if ($res_cols_cont) {
            while ($col_row_c = $res_cols_cont->fetch_assoc()) {
                $columnas_existentes_cont[$col_row_c['Field']] = true;
            }
        }

        $columnas_necesarias_cont = [
            'hero_titulo' => 'VARCHAR(150) NOT NULL DEFAULT "Hablemos de su proximo proyecto"',
            'hero_texto' => 'TEXT NOT NULL',
            'wpp_numero' => 'VARCHAR(50) NOT NULL DEFAULT "573026043945"',
            'wpp_mensaje_hero' => 'VARCHAR(250) NOT NULL DEFAULT "Hola, quiero informacion sobre sus servicios"',
            'wpp_mensaje_flotante' => 'VARCHAR(250) NOT NULL DEFAULT "Hola, como estas información sobre sus servicios"',
            'c1_dato' => 'VARCHAR(100) NOT NULL DEFAULT "+57 310 291 6140"',
            'c1_desc' => 'VARCHAR(100) NOT NULL DEFAULT "Atencion comercial"',
            'c2_dato' => 'VARCHAR(100) NOT NULL DEFAULT "+57 302 604 3945"',
            'c2_desc' => 'VARCHAR(100) NOT NULL DEFAULT "Respuesta rapida"',
            'c3_dato' => 'VARCHAR(100) NOT NULL DEFAULT "contacto@constructoresmym.com"',
            'c3_desc' => 'VARCHAR(100) NOT NULL DEFAULT "Cotizaciones y soporte"',
            'c4_dato' => 'VARCHAR(100) NOT NULL DEFAULT "Neiva, Huila, Colombia"',
            'c4_desc' => 'VARCHAR(100) NOT NULL DEFAULT "Area de servicio"',
            'mapa_busqueda' => 'VARCHAR(150) NOT NULL DEFAULT "Neiva, Huila, Colombia"',
            'mapa_titulo' => 'VARCHAR(150) NOT NULL DEFAULT "Estamos en Neiva, Huila"',
            'mapa_texto' => 'TEXT NOT NULL'
        ];

        foreach ($columnas_necesarias_cont as $col_c => $tipo_c) {
            if (!isset($columnas_existentes_cont[$col_c])) {
                $conexion->query("ALTER TABLE `contenido_contacto` ADD COLUMN `{$col_c}` {$tipo_c};");
            }
        }

        // 3. Verificación del registro inicial (id = 1)
        $res_cont = $conexion->query("SELECT * FROM `contenido_contacto` WHERE `id` = 1 LIMIT 1");
        if ($res_cont && $res_cont->num_rows > 0) {
            $contenido_contacto = $res_cont->fetch_assoc();
        } else {
            $stmt_c_ins = $conexion->prepare("INSERT INTO `contenido_contacto` (
                `id`, `hero_titulo`, `hero_texto`, `wpp_numero`, `wpp_mensaje_hero`, `wpp_mensaje_flotante`,
                `c1_dato`, `c1_desc`, `c2_dato`, `c2_desc`, `c3_dato`, `c3_desc`, `c4_dato`, `c4_desc`,
                `mapa_busqueda`, `mapa_titulo`, `mapa_texto`
            ) VALUES (
                1, 'Hablemos de su proximo proyecto', ?, '573026043945', 'Hola, quiero informacion sobre sus servicios', 'Hola, como estas información sobre sus servicios',
                '+57 310 291 6140', 'Atencion comercial', '+57 302 604 3945', 'Respuesta rapida', 'contacto@constructoresmym.com', 'Cotizaciones y soporte', 'Neiva, Huila, Colombia', 'Area de servicio',
                'Neiva, Huila, Colombia', 'Estamos en Neiva, Huila', ?
            )");
            if ($stmt_c_ins) {
                $stmt_c_ins->bind_param('ss', $hero_texto_cont_def, $mapa_texto_cont_def);
                $stmt_c_ins->execute();
                $stmt_c_ins->close();
            }
            $res_cont = $conexion->query("SELECT * FROM `contenido_contacto` WHERE `id` = 1 LIMIT 1");
            $contenido_contacto = $res_cont ? $res_cont->fetch_assoc() : [];
        }

        if (!is_array($contenido_contacto)) {
            $contenido_contacto = [];
        }
        foreach ($valores_defecto_cont as $clave => $val_def) {
            if (!isset($contenido_contacto[$clave]) || trim((string)$contenido_contacto[$clave]) === '') {
                $contenido_contacto[$clave] = $val_def;
            }
        }
    } catch (\Throwable $e_cont) {
        if (!is_array($contenido_contacto)) {
            $contenido_contacto = [];
        }
        foreach ($valores_defecto_cont as $clave => $val_def) {
            if (!isset($contenido_contacto[$clave]) || trim((string)$contenido_contacto[$clave]) === '') {
                $contenido_contacto[$clave] = $val_def;
            }
        }
    }
}

// Variables para mensajes de estado y alertas
$mensaje_alerta = '';
$tipo_alerta = '';

// Procesamiento de formulario de edición de la sección INICIO
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['guardar_inicio']) && $conexion_ok) {
    $dir_imagenes = dirname(__DIR__) . '/imagenes/';

    // Función interna para procesar subida de archivo o conservar el existente/manual
    $procesar_img = function($file_key, $nombre_manual, $valor_actual) use ($dir_imagenes) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES[$file_key]['tmp_name'])) {
            $info = pathinfo($_FILES[$file_key]['name']);
            $ext = strtolower($info['extension'] ?? '');
            $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
            if (in_array($ext, $permitidas, true)) {
                $nombre_base = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $info['filename']);
                $nuevo_nombre = time() . '_' . $nombre_base . '.' . $ext;
                $destino = rtrim($dir_imagenes, '/\\') . DIRECTORY_SEPARATOR . $nuevo_nombre;
                if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $destino)) {
                    return $nuevo_nombre;
                }
            }
        }
        $nombre_manual = trim((string)$nombre_manual);
        if ($nombre_manual !== '') {
            return $nombre_manual;
        }
        return $valor_actual;
    };

    // 1. Procesar imágenes del Hero y del Carrusel (3 imágenes)
    $b1_imagen     = $procesar_img('hero_imagen_file', $_POST['hero_imagen_nombre'] ?? '', $contenido['b1_imagen'] ?? $contenido['hero_imagen'] ?? 'imagen1.png');
    $carrusel_img1 = $procesar_img('carrusel_img1_file', $_POST['carrusel_img1_nombre'] ?? '', $contenido['carrusel_img1'] ?? 'imagen 22.png');
    $carrusel_img2 = $procesar_img('carrusel_img2_file', $_POST['carrusel_img2_nombre'] ?? '', $contenido['carrusel_img2'] ?? 'imagen 33.png');
    $carrusel_img3 = $procesar_img('carrusel_img3_file', $_POST['carrusel_img3_nombre'] ?? '', $contenido['carrusel_img3'] ?? 'imagen 44.png');

    // 2. Procesar textos de Bloque 1 (Hero)
    $b1_titulo    = trim($_POST['hero_titulo'] ?? '');
    $b1_subtitulo = trim($_POST['hero_subtitulo'] ?? '');

    // 3. Procesar textos de Bloque 2 (Servicios)
    $b2_titulo    = trim($_POST['servicios_titulo'] ?? '');
    $b2_subtitulo = trim($_POST['servicios_subtitulo'] ?? '');
    $t1_titulo    = trim($_POST['servicio1_titulo'] ?? '');
    $t1_texto     = trim($_POST['servicio1_texto'] ?? '');
    $t1_vinetas   = trim($_POST['servicio1_vinetas'] ?? '');
    $t2_titulo    = trim($_POST['servicio2_titulo'] ?? '');
    $t2_texto     = trim($_POST['servicio2_texto'] ?? '');
    $t2_vinetas   = trim($_POST['servicio2_vinetas'] ?? '');

    // 4. Procesar textos de Bloque 3 (¿Por qué elegirnos?)
    $b3_titulo    = trim($_POST['elegirnos_titulo'] ?? '');
    $b3_subtitulo = trim($_POST['elegirnos_subtitulo'] ?? '');
    $m1_titulo    = trim($_POST['elegirnos_item1_titulo'] ?? '');
    $m1_texto     = trim($_POST['elegirnos_item1_texto'] ?? '');
    $m2_titulo    = trim($_POST['elegirnos_item2_titulo'] ?? '');
    $m2_texto     = trim($_POST['elegirnos_item2_texto'] ?? '');
    $m3_titulo    = trim($_POST['elegirnos_item3_titulo'] ?? '');
    $m3_texto     = trim($_POST['elegirnos_item3_texto'] ?? '');

    // 5. Procesar Footer
    $footer_vinetas = trim($_POST['footer_contacto_vinetas'] ?? '');

    // 6. UPDATE preparado directamente sobre contenido_inicio con $conexion
    try {
        $stmt_up = $conexion->prepare("UPDATE `contenido_inicio` SET 
            `b1_imagen` = ?, 
            `b1_titulo` = ?, 
            `b1_subtitulo` = ?, 
            `b2_titulo` = ?, 
            `b2_subtitulo` = ?, 
            `t1_titulo` = ?, 
            `t1_texto` = ?, 
            `t1_vinetas` = ?, 
            `t2_titulo` = ?, 
            `t2_texto` = ?, 
            `t2_vinetas` = ?, 
            `carrusel_img1` = ?, 
            `carrusel_img2` = ?, 
            `carrusel_img3` = ?, 
            `b3_titulo` = ?, 
            `b3_subtitulo` = ?, 
            `m1_titulo` = ?, 
            `m1_texto` = ?, 
            `m2_titulo` = ?, 
            `m2_texto` = ?, 
            `m3_titulo` = ?, 
            `m3_texto` = ?, 
            `footer_vinetas` = ? 
        WHERE `id` = 1");

        if ($stmt_up) {
            $stmt_up->bind_param(
                'sssssssssssssssssssssss',
                $b1_imagen, $b1_titulo, $b1_subtitulo,
                $b2_titulo, $b2_subtitulo,
                $t1_titulo, $t1_texto, $t1_vinetas,
                $t2_titulo, $t2_texto, $t2_vinetas,
                $carrusel_img1, $carrusel_img2, $carrusel_img3,
                $b3_titulo, $b3_subtitulo,
                $m1_titulo, $m1_texto,
                $m2_titulo, $m2_texto,
                $m3_titulo, $m3_texto,
                $footer_vinetas
            );

            if ($stmt_up->execute()) {
                $mensaje_alerta = "¡Los cambios de la sección INICIO fueron guardados correctamente en la base de datos!";
                $tipo_alerta = "success";
                
                // SELECT directo para recargar los datos actualizados
                $res_act = $conexion->query("SELECT * FROM `contenido_inicio` WHERE `id` = 1 LIMIT 1");
                if ($res_act && $res_act->num_rows > 0) {
                    $contenido = $res_act->fetch_assoc();
                    $contenido['hero_imagen'] = $contenido['b1_imagen'] ?? $contenido['hero_imagen'] ?? 'imagen1.png';
                    $contenido['hero_titulo'] = $contenido['b1_titulo'] ?? $contenido['hero_titulo'] ?? 'BIENVENIDO A CONSTRUCCIONES M&M';
                    $contenido['hero_subtitulo'] = $contenido['b1_subtitulo'] ?? $contenido['hero_subtitulo'] ?? '';
                    $contenido['servicios_titulo'] = $contenido['b2_titulo'] ?? $contenido['servicios_titulo'] ?? 'Nuestros Servicios';
                    $contenido['servicios_subtitulo'] = $contenido['b2_subtitulo'] ?? $contenido['servicios_subtitulo'] ?? '';
                    $contenido['servicio1_titulo'] = $contenido['t1_titulo'] ?? $contenido['servicio1_titulo'] ?? 'Construcción Civil';
                    $contenido['servicio1_texto'] = $contenido['t1_texto'] ?? $contenido['servicio1_texto'] ?? '';
                    $contenido['servicio1_vinetas'] = $contenido['t1_vinetas'] ?? $contenido['servicio1_vinetas'] ?? '';
                    $contenido['servicio2_titulo'] = $contenido['t2_titulo'] ?? $contenido['servicio2_titulo'] ?? 'Redes de Gas';
                    $contenido['servicio2_texto'] = $contenido['t2_texto'] ?? $contenido['servicio2_texto'] ?? '';
                    $contenido['servicio2_vinetas'] = $contenido['t2_vinetas'] ?? $contenido['servicio2_vinetas'] ?? '';
                    $contenido['elegirnos_titulo'] = $contenido['b3_titulo'] ?? $contenido['elegirnos_titulo'] ?? '¿Por qué elegirnos?';
                    $contenido['elegirnos_subtitulo'] = $contenido['b3_subtitulo'] ?? $contenido['elegirnos_subtitulo'] ?? '';
                    $contenido['elegirnos_item1_titulo'] = $contenido['m1_titulo'] ?? $contenido['elegirnos_item1_titulo'] ?? 'Seguridad garantizada';
                    $contenido['elegirnos_item1_texto'] = $contenido['m1_texto'] ?? $contenido['elegirnos_item1_texto'] ?? '';
                    $contenido['elegirnos_item2_titulo'] = $contenido['m2_titulo'] ?? $contenido['elegirnos_item2_titulo'] ?? 'Experiencia comprobada';
                    $contenido['elegirnos_item2_texto'] = $contenido['m2_texto'] ?? $contenido['elegirnos_item2_texto'] ?? '';
                    $contenido['elegirnos_item3_titulo'] = $contenido['m3_titulo'] ?? $contenido['elegirnos_item3_titulo'] ?? 'Cumplimiento normativo';
                    $contenido['elegirnos_item3_texto'] = $contenido['m3_texto'] ?? $contenido['elegirnos_item3_texto'] ?? '';
                    $contenido['footer_contacto_vinetas'] = $contenido['footer_vinetas'] ?? $contenido['footer_contacto_vinetas'] ?? '';
                }
            } else {
                $mensaje_alerta = "Error al guardar en la base de datos: " . $stmt_up->error;
                $tipo_alerta = "danger";
            }
            $stmt_up->close();
        } else {
            $mensaje_alerta = "Error de preparación SQL: " . $conexion->error;
            $tipo_alerta = "danger";
        }
    } catch (\Throwable $e_ini) {
        $mensaje_alerta = "Error en la base de datos al guardar INICIO: " . $e_ini->getMessage();
        $tipo_alerta = "danger";
    }
}

// Procesamiento de formulario de edición de la sección PROYECTOS
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['guardar_proyectos']) && $conexion_ok) {
    $dir_imagenes = dirname(__DIR__) . '/imagenes/';

    // Función interna para procesar subida de archivo o conservar el existente/manual
    $procesar_img_proy = function($file_key, $nombre_manual, $valor_actual) use ($dir_imagenes) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES[$file_key]['tmp_name'])) {
            $info = pathinfo($_FILES[$file_key]['name']);
            $ext = strtolower($info['extension'] ?? '');
            $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
            if (in_array($ext, $permitidas, true)) {
                $nombre_base = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $info['filename']);
                $nuevo_nombre = time() . '_' . $nombre_base . '.' . $ext;
                $destino = rtrim($dir_imagenes, '/\\') . DIRECTORY_SEPARATOR . $nuevo_nombre;
                if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $destino)) {
                    return $nuevo_nombre;
                }
            }
        }
        $nombre_manual = trim((string)$nombre_manual);
        if ($nombre_manual !== '') {
            return $nombre_manual;
        }
        return $valor_actual;
    };

    // 1. Carrusel
    $carrusel_img1 = $procesar_img_proy('proy_carrusel_img1_file', $_POST['proy_carrusel_img1_nombre'] ?? '', $contenido_proyectos['carrusel_img1'] ?? 'imagen 44.png');
    $carrusel_img2 = $procesar_img_proy('proy_carrusel_img2_file', $_POST['proy_carrusel_img2_nombre'] ?? '', $contenido_proyectos['carrusel_img2'] ?? 'imagen 22.png');
    $carrusel_img3 = $procesar_img_proy('proy_carrusel_img3_file', $_POST['proy_carrusel_img3_nombre'] ?? '', $contenido_proyectos['carrusel_img3'] ?? 'imagen 33.png');

    // 2. Filtros
    $btn1_nombre = trim($_POST['proy_btn1_nombre'] ?? 'Calles pavimentadas');
    $btn2_nombre = trim($_POST['proy_btn2_nombre'] ?? 'Tuberias y redes');
    $btn3_nombre = trim($_POST['proy_btn3_nombre'] ?? 'Evidencias');

    // 3. Calles Pavimentadas
    $calles_titulo = trim($_POST['proy_calles_titulo'] ?? 'Calles pavimentadas');
    $calles_texto  = trim($_POST['proy_calles_texto'] ?? '');
    $calles_badge  = trim($_POST['proy_calles_badge'] ?? '');

    $c1_img     = $procesar_img_proy('proy_c1_img_file', $_POST['proy_c1_img_nombre'] ?? '', $contenido_proyectos['c1_img'] ?? 'imagen1.png');
    $c1_badge   = trim($_POST['proy_c1_badge'] ?? '');
    $c1_titulo  = trim($_POST['proy_c1_titulo'] ?? '');
    $c1_vinetas = trim($_POST['proy_c1_vinetas'] ?? '');

    $c2_img     = $procesar_img_proy('proy_c2_img_file', $_POST['proy_c2_img_nombre'] ?? '', $contenido_proyectos['c2_img'] ?? 'imagen2.png');
    $c2_badge   = trim($_POST['proy_c2_badge'] ?? '');
    $c2_titulo  = trim($_POST['proy_c2_titulo'] ?? '');
    $c2_vinetas = trim($_POST['proy_c2_vinetas'] ?? '');

    $c3_img     = $procesar_img_proy('proy_c3_img_file', $_POST['proy_c3_img_nombre'] ?? '', $contenido_proyectos['c3_img'] ?? 'imagen3.png');
    $c3_badge   = trim($_POST['proy_c3_badge'] ?? '');
    $c3_titulo  = trim($_POST['proy_c3_titulo'] ?? '');
    $c3_vinetas = trim($_POST['proy_c3_vinetas'] ?? '');

    // 4. Tuberías y Redes
    $tuberias_titulo = trim($_POST['proy_tuberias_titulo'] ?? 'Tuberias y redes');
    $tuberias_texto  = trim($_POST['proy_tuberias_texto'] ?? '');
    $tuberias_badge  = trim($_POST['proy_tuberias_badge'] ?? '');

    $t1_img     = $procesar_img_proy('proy_t1_img_file', $_POST['proy_t1_img_nombre'] ?? '', $contenido_proyectos['t1_img'] ?? 'imagen4.png');
    $t1_badge   = trim($_POST['proy_t1_badge'] ?? '');
    $t1_titulo  = trim($_POST['proy_t1_titulo'] ?? '');
    $t1_vinetas = trim($_POST['proy_t1_vinetas'] ?? '');

    $t2_img     = $procesar_img_proy('proy_t2_img_file', $_POST['proy_t2_img_nombre'] ?? '', $contenido_proyectos['t2_img'] ?? 'imagen2.png');
    $t2_badge   = trim($_POST['proy_t2_badge'] ?? '');
    $t2_titulo  = trim($_POST['proy_t2_titulo'] ?? '');
    $t2_vinetas = trim($_POST['proy_t2_vinetas'] ?? '');

    // 5. Evidencias
    $evidencias_badge1 = trim($_POST['proy_evidencias_badge1'] ?? '');
    $evidencias_badge2 = trim($_POST['proy_evidencias_badge2'] ?? '');
    $evidencias_titulo = trim($_POST['proy_evidencias_titulo'] ?? '');
    $evidencias_texto  = trim($_POST['proy_evidencias_texto'] ?? '');
    $evidencias_badge3 = trim($_POST['proy_evidencias_badge3'] ?? '');

    $e1_img     = $procesar_img_proy('proy_e1_img_file', $_POST['proy_e1_img_nombre'] ?? '', $contenido_proyectos['e1_img'] ?? 'imagen 44.png');
    $e1_badge   = trim($_POST['proy_e1_badge'] ?? '');
    $e1_titulo  = trim($_POST['proy_e1_titulo'] ?? '');
    $e1_vinetas = trim($_POST['proy_e1_vinetas'] ?? '');

    $e2_img     = $procesar_img_proy('proy_e2_img_file', $_POST['proy_e2_img_nombre'] ?? '', $contenido_proyectos['e2_img'] ?? 'imagen3.png');
    $e2_badge   = trim($_POST['proy_e2_badge'] ?? '');
    $e2_titulo  = trim($_POST['proy_e2_titulo'] ?? '');
    $e2_vinetas = trim($_POST['proy_e2_vinetas'] ?? '');

    $e3_img     = $procesar_img_proy('proy_e3_img_file', $_POST['proy_e3_img_nombre'] ?? '', $contenido_proyectos['e3_img'] ?? 'imagen 22.png');
    $e3_badge   = trim($_POST['proy_e3_badge'] ?? '');
    $e3_titulo  = trim($_POST['proy_e3_titulo'] ?? '');
    $e3_vinetas = trim($_POST['proy_e3_vinetas'] ?? '');

    $e4_img     = $procesar_img_proy('proy_e4_img_file', $_POST['proy_e4_img_nombre'] ?? '', $contenido_proyectos['e4_img'] ?? 'imagen 33.png');
    $e4_badge   = trim($_POST['proy_e4_badge'] ?? '');
    $e4_titulo  = trim($_POST['proy_e4_titulo'] ?? '');
    $e4_vinetas = trim($_POST['proy_e4_vinetas'] ?? '');

    try {
        $stmt_up_p = $conexion->prepare("UPDATE `contenido_proyectos` SET
            `carrusel_img1` = ?, `carrusel_img2` = ?, `carrusel_img3` = ?,
            `btn1_nombre` = ?, `btn2_nombre` = ?, `btn3_nombre` = ?,
            `calles_titulo` = ?, `calles_texto` = ?, `calles_badge` = ?,
            `c1_img` = ?, `c1_badge` = ?, `c1_titulo` = ?, `c1_vinetas` = ?,
            `c2_img` = ?, `c2_badge` = ?, `c2_titulo` = ?, `c2_vinetas` = ?,
            `c3_img` = ?, `c3_badge` = ?, `c3_titulo` = ?, `c3_vinetas` = ?,
            `tuberias_titulo` = ?, `tuberias_texto` = ?, `tuberias_badge` = ?,
            `t1_img` = ?, `t1_badge` = ?, `t1_titulo` = ?, `t1_vinetas` = ?,
            `t2_img` = ?, `t2_badge` = ?, `t2_titulo` = ?, `t2_vinetas` = ?,
            `evidencias_badge1` = ?, `evidencias_badge2` = ?, `evidencias_titulo` = ?, `evidencias_texto` = ?, `evidencias_badge3` = ?,
            `e1_img` = ?, `e1_badge` = ?, `e1_titulo` = ?, `e1_vinetas` = ?,
            `e2_img` = ?, `e2_badge` = ?, `e2_titulo` = ?, `e2_vinetas` = ?,
            `e3_img` = ?, `e3_badge` = ?, `e3_titulo` = ?, `e3_vinetas` = ?,
            `e4_img` = ?, `e4_badge` = ?, `e4_titulo` = ?, `e4_vinetas` = ?
        WHERE `id` = 1");

        if ($stmt_up_p) {
            $stmt_up_p->bind_param(
                'sssssssssssssssssssssssssssssssssssssssssssssssssssss',
                $carrusel_img1, $carrusel_img2, $carrusel_img3,
                $btn1_nombre, $btn2_nombre, $btn3_nombre,
                $calles_titulo, $calles_texto, $calles_badge,
                $c1_img, $c1_badge, $c1_titulo, $c1_vinetas,
                $c2_img, $c2_badge, $c2_titulo, $c2_vinetas,
                $c3_img, $c3_badge, $c3_titulo, $c3_vinetas,
                $tuberias_titulo, $tuberias_texto, $tuberias_badge,
                $t1_img, $t1_badge, $t1_titulo, $t1_vinetas,
                $t2_img, $t2_badge, $t2_titulo, $t2_vinetas,
                $evidencias_badge1, $evidencias_badge2, $evidencias_titulo, $evidencias_texto, $evidencias_badge3,
                $e1_img, $e1_badge, $e1_titulo, $e1_vinetas,
                $e2_img, $e2_badge, $e2_titulo, $e2_vinetas,
                $e3_img, $e3_badge, $e3_titulo, $e3_vinetas,
                $e4_img, $e4_badge, $e4_titulo, $e4_vinetas
            );
            if ($stmt_up_p->execute()) {
                $mensaje_alerta = "¡Los cambios de la sección PROYECTOS fueron guardados correctamente en la base de datos!";
                $tipo_alerta = "success";

                // Recargar datos actualizados
                $res_act_p = $conexion->query("SELECT * FROM `contenido_proyectos` WHERE `id` = 1 LIMIT 1");
                if ($res_act_p && $res_act_p->num_rows > 0) {
                    $contenido_proyectos = $res_act_p->fetch_assoc();
                }
            } else {
                $mensaje_alerta = "Error al actualizar la sección PROYECTOS: " . $stmt_up_p->error;
                $tipo_alerta = "danger";
            }
            $stmt_up_p->close();
        } else {
            $mensaje_alerta = "Error de preparación SQL en PROYECTOS: " . $conexion->error;
            $tipo_alerta = "danger";
        }
    } catch (\Throwable $e_proy_up) {
        $mensaje_alerta = "Error en la base de datos al guardar PROYECTOS: " . $e_proy_up->getMessage();
        $tipo_alerta = "danger";
    }
}

// Procesamiento de formulario de edición de la sección NOSOTROS
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['guardar_nosotros']) && $conexion_ok) {
    $dir_imagenes = dirname(__DIR__) . '/imagenes/';

    // Función interna para procesar subida de archivo o conservar el existente/manual
    $procesar_img_nos = function($file_key, $nombre_manual, $valor_actual) use ($dir_imagenes) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES[$file_key]['tmp_name'])) {
            $info = pathinfo($_FILES[$file_key]['name']);
            $ext = strtolower($info['extension'] ?? '');
            $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
            if (in_array($ext, $permitidas, true)) {
                $nombre_base = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $info['filename']);
                $nuevo_nombre = time() . '_' . $nombre_base . '.' . $ext;
                $destino = rtrim($dir_imagenes, '/\\') . DIRECTORY_SEPARATOR . $nuevo_nombre;
                if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $destino)) {
                    return $nuevo_nombre;
                }
            }
        }
        $nombre_manual = trim((string)$nombre_manual);
        if ($nombre_manual !== '') {
            return $nombre_manual;
        }
        return $valor_actual;
    };

    // 1. Carrusel
    $carrusel_img1 = $procesar_img_nos('nos_carrusel_img1_file', $_POST['nos_carrusel_img1_nombre'] ?? '', $contenido_nosotros['carrusel_img1'] ?? 'imagen 22.png');
    $carrusel_img2 = $procesar_img_nos('nos_carrusel_img2_file', $_POST['nos_carrusel_img2_nombre'] ?? '', $contenido_nosotros['carrusel_img2'] ?? 'imagen 44.png');
    $carrusel_img3 = $procesar_img_nos('nos_carrusel_img3_file', $_POST['nos_carrusel_img3_nombre'] ?? '', $contenido_nosotros['carrusel_img3'] ?? 'imagen 33.png');

    // 2. ¿Quiénes Somos?
    $qs_imagen  = $procesar_img_nos('nos_qs_imagen_file', $_POST['nos_qs_imagen_nombre'] ?? '', $contenido_nosotros['qs_imagen'] ?? 'imagen4.png');
    $qs_titulo  = trim($_POST['nos_qs_titulo'] ?? '¿Quiénes somos?');
    $qs_texto   = trim($_POST['nos_qs_texto'] ?? '');
    $qs_vinetas = trim($_POST['nos_qs_vinetas'] ?? '');

    // 3. Misión y Visión
    $mision_titulo = trim($_POST['nos_mision_titulo'] ?? 'Misión');
    $mision_texto  = trim($_POST['nos_mision_texto'] ?? '');
    $vision_titulo = trim($_POST['nos_vision_titulo'] ?? 'Visión');
    $vision_texto  = trim($_POST['nos_vision_texto'] ?? '');

    // 4. Nuestros Valores
    $valores_titulo    = trim($_POST['nos_valores_titulo'] ?? 'Nuestros Valores');
    $valores_subtitulo = trim($_POST['nos_valores_subtitulo'] ?? '');
    $v1_titulo         = trim($_POST['nos_v1_titulo'] ?? 'Seguridad');
    $v1_texto          = trim($_POST['nos_v1_texto'] ?? '');
    $v2_titulo         = trim($_POST['nos_v2_titulo'] ?? 'Calidad');
    $v2_texto          = trim($_POST['nos_v2_texto'] ?? '');
    $v3_titulo         = trim($_POST['nos_v3_titulo'] ?? 'Cumplimiento');
    $v3_texto          = trim($_POST['nos_v3_texto'] ?? '');
    $v4_titulo         = trim($_POST['nos_v4_titulo'] ?? 'Sostenibilidad');
    $v4_texto          = trim($_POST['nos_v4_texto'] ?? '');

    // 5. Métricas y Logros
    $m1_valor = trim($_POST['nos_m1_valor'] ?? '+150');
    $m1_texto = trim($_POST['nos_m1_texto'] ?? 'Proyectos Ejecutados');
    $m2_valor = trim($_POST['nos_m2_valor'] ?? '+50 Km');
    $m2_texto = trim($_POST['nos_m2_texto'] ?? 'Redes de Gas Instaladas');
    $m3_valor = trim($_POST['nos_m3_valor'] ?? '100%');
    $m3_texto = trim($_POST['nos_m3_texto'] ?? 'Certificación Normativa');
    $m4_valor = trim($_POST['nos_m4_valor'] ?? '+15');
    $m4_texto = trim($_POST['nos_m4_texto'] ?? 'Años de Trayectoria');

    try {
        $stmt_up_n = $conexion->prepare("UPDATE `contenido_nosotros` SET
            `carrusel_img1` = ?, `carrusel_img2` = ?, `carrusel_img3` = ?,
            `qs_imagen` = ?, `qs_titulo` = ?, `qs_texto` = ?, `qs_vinetas` = ?,
            `mision_titulo` = ?, `mision_texto` = ?, `vision_titulo` = ?, `vision_texto` = ?,
            `valores_titulo` = ?, `valores_subtitulo` = ?,
            `v1_titulo` = ?, `v1_texto` = ?, `v2_titulo` = ?, `v2_texto` = ?,
            `v3_titulo` = ?, `v3_texto` = ?, `v4_titulo` = ?, `v4_texto` = ?,
            `m1_valor` = ?, `m1_texto` = ?, `m2_valor` = ?, `m2_texto` = ?,
            `m3_valor` = ?, `m3_texto` = ?, `m4_valor` = ?, `m4_texto` = ?
        WHERE `id` = 1");

        if ($stmt_up_n) {
            $stmt_up_n->bind_param(
                'sssssssssssssssssssssssssssss',
                $carrusel_img1, $carrusel_img2, $carrusel_img3,
                $qs_imagen, $qs_titulo, $qs_texto, $qs_vinetas,
                $mision_titulo, $mision_texto, $vision_titulo, $vision_texto,
                $valores_titulo, $valores_subtitulo,
                $v1_titulo, $v1_texto, $v2_titulo, $v2_texto,
                $v3_titulo, $v3_texto, $v4_titulo, $v4_texto,
                $m1_valor, $m1_texto, $m2_valor, $m2_texto,
                $m3_valor, $m3_texto, $m4_valor, $m4_texto
            );

            if ($stmt_up_n->execute()) {
                $mensaje_alerta = "¡Los cambios de la sección NOSOTROS fueron guardados correctamente en la base de datos!";
                $tipo_alerta = "success";

                // Recargar datos actualizados
                $res_act_n = $conexion->query("SELECT * FROM `contenido_nosotros` WHERE `id` = 1 LIMIT 1");
                if ($res_act_n && $res_act_n->num_rows > 0) {
                    $contenido_nosotros = $res_act_n->fetch_assoc();
                }
            } else {
                $mensaje_alerta = "Error al actualizar la sección NOSOTROS: " . $stmt_up_n->error;
                $tipo_alerta = "danger";
            }
            $stmt_up_n->close();
        } else {
            $mensaje_alerta = "Error de preparación SQL en NOSOTROS: " . $conexion->error;
            $tipo_alerta = "danger";
        }
    } catch (\Throwable $e_nos_up) {
        $mensaje_alerta = "Error en la base de datos al guardar NOSOTROS: " . $e_nos_up->getMessage();
        $tipo_alerta = "danger";
    }
}

// Procesamiento de formulario de edición de la sección MAQUINARIA
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['guardar_maquinaria']) && $conexion_ok) {
    $dir_imagenes = dirname(__DIR__) . '/imagenes/';

    // Función interna para procesar subida de archivo o conservar el existente/manual
    $procesar_img_maq = function($file_key, $nombre_manual, $valor_actual) use ($dir_imagenes) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES[$file_key]['tmp_name'])) {
            $info = pathinfo($_FILES[$file_key]['name']);
            $ext = strtolower($info['extension'] ?? '');
            $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
            if (in_array($ext, $permitidas, true)) {
                $nombre_base = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $info['filename']);
                $nuevo_nombre = time() . '_' . $nombre_base . '.' . $ext;
                $destino = rtrim($dir_imagenes, '/\\') . DIRECTORY_SEPARATOR . $nuevo_nombre;
                if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $destino)) {
                    return $nuevo_nombre;
                }
            }
        }
        $nombre_manual = trim((string)$nombre_manual);
        if ($nombre_manual !== '') {
            return $nombre_manual;
        }
        return $valor_actual;
    };

    // 1. Hero
    $hero_imagen = $procesar_img_maq('maq_hero_imagen_file', $_POST['maq_hero_imagen_nombre'] ?? '', $contenido_maquinaria['hero_imagen'] ?? 'imagen3.png');
    $hero_badge  = mb_substr(trim($_POST['maq_hero_badge'] ?? 'FLOTA PESADA Y EQUIPOS CERTIFICADOS'), 0, 100);
    $hero_titulo = mb_substr(trim($_POST['maq_hero_titulo'] ?? 'FLOTA DE MAQUINARIA Y EQUIPOS'), 0, 150);
    $hero_texto  = mb_substr(trim($_POST['maq_hero_texto'] ?? ''), 0, 300);

    // 2. Filtros del Catálogo (Pestañas)
    $tab1_nombre = mb_substr(trim($_POST['maq_tab1_nombre'] ?? 'Todas las Máquinas'), 0, 50);
    $tab2_nombre = mb_substr(trim($_POST['maq_tab2_nombre'] ?? 'Excavación y Tierras'), 0, 50);
    $tab3_nombre = mb_substr(trim($_POST['maq_tab3_nombre'] ?? 'Tuberías y Redes de Gas'), 0, 50);
    $tab4_nombre = mb_substr(trim($_POST['maq_tab4_nombre'] ?? 'Carga y Transporte'), 0, 50);

    // 3. Garantía y Respaldo
    $garantia_titulo = mb_substr(trim($_POST['maq_garantia_titulo'] ?? 'Garantía y Respaldo en Obra'), 0, 100);
    $garantia_texto  = mb_substr(trim($_POST['maq_garantia_texto'] ?? ''), 0, 250);

    $g1_titulo = mb_substr(trim($_POST['maq_g1_titulo'] ?? 'Mantenimiento Preventivo'), 0, 100);
    $g1_texto  = mb_substr(trim($_POST['maq_g1_texto'] ?? ''), 0, 200);

    $g2_titulo = mb_substr(trim($_POST['maq_g2_titulo'] ?? 'Operadores Certificados'), 0, 100);
    $g2_texto  = mb_substr(trim($_POST['maq_g2_texto'] ?? ''), 0, 200);

    $g3_titulo = mb_substr(trim($_POST['maq_g3_titulo'] ?? 'Asistencia 24/7'), 0, 100);
    $g3_texto  = mb_substr(trim($_POST['maq_g3_texto'] ?? ''), 0, 200);

    $g4_titulo = mb_substr(trim($_POST['maq_g4_titulo'] ?? 'Certificaciones Vigentes'), 0, 100);
    $g4_texto  = mb_substr(trim($_POST['maq_g4_texto'] ?? ''), 0, 200);

    try {
        $stmt_up_m = $conexion->prepare("UPDATE `contenido_maquinaria` SET
            `hero_imagen` = ?, `hero_badge` = ?, `hero_titulo` = ?, `hero_texto` = ?,
            `tab1_nombre` = ?, `tab2_nombre` = ?, `tab3_nombre` = ?, `tab4_nombre` = ?,
            `garantia_titulo` = ?, `garantia_texto` = ?,
            `g1_titulo` = ?, `g1_texto` = ?, `g2_titulo` = ?, `g2_texto` = ?,
            `g3_titulo` = ?, `g3_texto` = ?, `g4_titulo` = ?, `g4_texto` = ?
        WHERE `id` = 1");

        if ($stmt_up_m) {
            $stmt_up_m->bind_param(
                'ssssssssssssssssss',
                $hero_imagen, $hero_badge, $hero_titulo, $hero_texto,
                $tab1_nombre, $tab2_nombre, $tab3_nombre, $tab4_nombre,
                $garantia_titulo, $garantia_texto,
                $g1_titulo, $g1_texto, $g2_titulo, $g2_texto,
                $g3_titulo, $g3_texto, $g4_titulo, $g4_texto
            );

            if ($stmt_up_m->execute()) {
                $mensaje_alerta = "¡Los cambios de la sección MAQUINARIA fueron guardados correctamente en la base de datos!";
                $tipo_alerta = "success";

                // Recargar datos actualizados
                $res_act_m = $conexion->query("SELECT * FROM `contenido_maquinaria` WHERE `id` = 1 LIMIT 1");
                if ($res_act_m && $res_act_m->num_rows > 0) {
                    $contenido_maquinaria = $res_act_m->fetch_assoc();
                }
            } else {
                $mensaje_alerta = "Error al actualizar la sección MAQUINARIA: " . $stmt_up_m->error;
                $tipo_alerta = "danger";
            }
            $stmt_up_m->close();
        } else {
            $mensaje_alerta = "Error de preparación SQL en MAQUINARIA: " . $conexion->error;
            $tipo_alerta = "danger";
        }
    } catch (\Throwable $e_maq_up) {
        $mensaje_alerta = "Error en la base de datos al guardar MAQUINARIA: " . $e_maq_up->getMessage();
        $tipo_alerta = "danger";
    }
}

// Procesamiento de formulario de edición de la sección CONTACTO
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['guardar_contacto']) && $conexion_ok) {
    // Bloque 1: Encabezado y WhatsApp
    $hero_titulo          = mb_substr(trim($_POST['cont_hero_titulo'] ?? 'Hablemos de su proximo proyecto'), 0, 150);
    $hero_texto           = mb_substr(trim($_POST['cont_hero_texto'] ?? ''), 0, 300);
    $raw_wpp              = trim($_POST['cont_wpp_numero'] ?? '573026043945');
    $wpp_numero           = mb_substr(ltrim(preg_replace('/[^0-9]/', '', $raw_wpp), '+'), 0, 50);
    if ($wpp_numero === '') {
        $wpp_numero = '573026043945';
    }
    $wpp_mensaje_hero     = mb_substr(trim($_POST['cont_wpp_mensaje_hero'] ?? 'Hola, quiero informacion sobre sus servicios'), 0, 250);
    $wpp_mensaje_flotante = mb_substr(trim($_POST['cont_wpp_mensaje_flotante'] ?? 'Hola, como estas información sobre sus servicios'), 0, 250);

    // Bloque 2: Tarjetas de Información
    $c1_dato = mb_substr(trim($_POST['cont_c1_dato'] ?? '+57 310 291 6140'), 0, 100);
    $c1_desc = mb_substr(trim($_POST['cont_c1_desc'] ?? 'Atencion comercial'), 0, 100);

    $c2_dato = mb_substr(trim($_POST['cont_c2_dato'] ?? '+57 302 604 3945'), 0, 100);
    $c2_desc = mb_substr(trim($_POST['cont_c2_desc'] ?? 'Respuesta rapida'), 0, 100);

    $c3_dato = mb_substr(trim($_POST['cont_c3_dato'] ?? 'contacto@constructoresmym.com'), 0, 100);
    $c3_desc = mb_substr(trim($_POST['cont_c3_desc'] ?? 'Cotizaciones y soporte'), 0, 100);

    $c4_dato = mb_substr(trim($_POST['cont_c4_dato'] ?? 'Neiva, Huila, Colombia'), 0, 100);
    $c4_desc = mb_substr(trim($_POST['cont_c4_desc'] ?? 'Area de servicio'), 0, 100);

    // Bloque 3: Mapa Interactivo
    $mapa_busqueda = mb_substr(trim($_POST['cont_mapa_busqueda'] ?? 'Neiva, Huila, Colombia'), 0, 150);
    $mapa_titulo   = mb_substr(trim($_POST['cont_mapa_titulo'] ?? 'Estamos en Neiva, Huila'), 0, 150);
    $mapa_texto    = mb_substr(trim($_POST['cont_mapa_texto'] ?? ''), 0, 350);

    try {
        $stmt_up_c = $conexion->prepare("UPDATE `contenido_contacto` SET
            `hero_titulo` = ?, `hero_texto` = ?, `wpp_numero` = ?,
            `wpp_mensaje_hero` = ?, `wpp_mensaje_flotante` = ?,
            `c1_dato` = ?, `c1_desc` = ?,
            `c2_dato` = ?, `c2_desc` = ?,
            `c3_dato` = ?, `c3_desc` = ?,
            `c4_dato` = ?, `c4_desc` = ?,
            `mapa_busqueda` = ?, `mapa_titulo` = ?, `mapa_texto` = ?
        WHERE `id` = 1");

        if ($stmt_up_c) {
            $stmt_up_c->bind_param(
                'ssssssssssssssss',
                $hero_titulo, $hero_texto, $wpp_numero,
                $wpp_mensaje_hero, $wpp_mensaje_flotante,
                $c1_dato, $c1_desc,
                $c2_dato, $c2_desc,
                $c3_dato, $c3_desc,
                $c4_dato, $c4_desc,
                $mapa_busqueda, $mapa_titulo, $mapa_texto
            );

            if ($stmt_up_c->execute()) {
                $mensaje_alerta = "¡Los canales y datos de la sección CONTACTO fueron guardados correctamente en la base de datos!";
                $tipo_alerta = "success";

                // Recargar datos actualizados
                $res_act_c = $conexion->query("SELECT * FROM `contenido_contacto` WHERE `id` = 1 LIMIT 1");
                if ($res_act_c && $res_act_c->num_rows > 0) {
                    $contenido_contacto = $res_act_c->fetch_assoc();
                }
            } else {
                $mensaje_alerta = "Error al actualizar la sección CONTACTO: " . $stmt_up_c->error;
                $tipo_alerta = "danger";
            }
            $stmt_up_c->close();
        } else {
            $mensaje_alerta = "Error de preparación SQL en CONTACTO: " . $conexion->error;
            $tipo_alerta = "danger";
        }
    } catch (\Throwable $e_cont_up) {
        $mensaje_alerta = "Error en la base de datos al guardar CONTACTO: " . $e_cont_up->getMessage();
        $tipo_alerta = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contenido Web | M&M Construcciones y Servicios S.A.S.</title>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons 1.11.3 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    /* ==========================================================================
       PALETA CORPORATIVA LUMINOSA (TEMA CLARO)
       ========================================================================== */
    :root {
      --navy-deep: #0A192F;           /* Azul marino profundo corporativo */
      --navy-surface: #0f2341;        /* Azul marino superficie */
      --yellow-corp: #FFC107;         /* Amarillo corporativo */
      --yellow-hover: #ffcd39;        /* Amarillo hover */
      --yellow-dim: rgba(255, 193, 7, 0.15);

      --bg-page: #f8f9fa;             /* Fondo general gris muy claro */
      --card-bg: #ffffff;             /* Tarjetas blancas limpias */
      --border-subtle: #e2e8f0;       /* Bordes claros suaves */

      --text-main: #1e293b;           /* Texto oscuro de alta legibilidad */
      --text-muted: #64748b;          /* Texto secundario */
      
      --status-disp: #10b981;         /* Verde disponible */
      --status-trab: #0284c7;         /* Azul trabajando */
      --status-mant: #f59e0b;         /* Ámbar mantenimiento */
      --status-inact: #94a3b8;        /* Gris inactivo */

      --sidebar-width: 280px;
      --topbar-height: 70px;
      --transition-smooth: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background-color: var(--bg-page);
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      overflow-x: hidden;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }

    /* Scrollbar personalizada */
    ::-webkit-scrollbar {
      width: 7px;
      height: 7px;
    }
    ::-webkit-scrollbar-track {
      background: #f1f5f9;
    }
    ::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    /* ==========================================================================
       LOGO FLOTANTE CON PALETA CORPORATIVA (#0A192F AZUL MARINO + #FFC107 AMARILLO)
       ========================================================================== */
    .logo-floating-trigger {
      position: fixed;
      top: 11px;
      left: 18px;
      z-index: 1040;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background-color: var(--navy-deep);
      padding: 5px 14px 5px 5px;
      border-radius: 12px;
      box-shadow: 0 4px 18px rgba(10, 25, 47, 0.35), 0 0 0 1px rgba(255, 193, 7, 0.25);
      border: 1px solid rgba(255, 193, 7, 0.35);
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      user-select: none;
      -webkit-tap-highlight-color: transparent;
    }

    .logo-floating-trigger:hover,
    .logo-floating-trigger:active {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 8px 24px rgba(255, 193, 7, 0.4), 0 0 0 2px var(--yellow-corp);
      background-color: var(--navy-surface);
    }

    .logo-floating-trigger .brand-icon-box {
      width: 44px;
      height: 44px;
      background: var(--yellow-corp);
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--navy-deep);
      font-weight: 900;
      font-size: 1.1rem;
      letter-spacing: -0.5px;
      box-shadow: 0 3px 10px rgba(255, 193, 7, 0.45);
      flex-shrink: 0;
    }

    .logo-floating-trigger .trigger-label {
      display: flex;
      flex-direction: column;
      line-height: 1.15;
    }

    .logo-floating-trigger .trigger-brand {
      font-size: 0.9rem;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: 0.4px;
    }

    .logo-floating-trigger .trigger-hint {
      font-size: 0.68rem;
      font-weight: 700;
      color: var(--yellow-corp);
      text-transform: uppercase;
      letter-spacing: 0.6px;
      display: flex;
      align-items: center;
      gap: 3px;
    }

    /* ==========================================================================
       SIDEBAR AZUL MARINO (#0A192F) QUE SE DESPLIEGA CON HOVER Y TOQUE
       ========================================================================== */
    .admin-sidebar {
      width: var(--sidebar-width);
      background-color: var(--navy-deep);
      border-right: 1px solid rgba(255, 255, 255, 0.08);
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1050;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transform: translateX(-100%); /* Oculto por defecto hasta pasar el mouse o tocar */
      transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 14px 0 38px rgba(10, 25, 47, 0.35);
    }

    .admin-sidebar.sidebar-open {
      transform: translateX(0);
    }

    .sidebar-brand {
      padding: 22px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      text-decoration: none;
      color: inherit;
    }

    .btn-close-sidebar {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: var(--yellow-corp);
      width: 38px;
      height: 38px;
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
      cursor: pointer;
      transition: var(--transition-smooth);
      flex-shrink: 0;
      -webkit-tap-highlight-color: transparent;
    }

    .btn-close-sidebar:hover,
    .btn-close-sidebar:active {
      background-color: var(--yellow-corp);
      color: var(--navy-deep);
      border-color: var(--yellow-corp);
    }

    .sidebar-brand .brand-icon-box {
      width: 44px;
      height: 44px;
      background: var(--yellow-corp);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--navy-deep);
      font-weight: 900;
      font-size: 1.05rem;
      letter-spacing: -0.5px;
      box-shadow: 0 4px 12px rgba(255, 193, 7, 0.35);
      flex-shrink: 0;
    }

    .brand-text {
      display: flex;
      flex-direction: column;
      line-height: 1.2;
    }

    .brand-title {
      font-size: 0.95rem;
      font-weight: 800;
      letter-spacing: 0.5px;
      color: #ffffff;
      text-transform: uppercase;
    }

    .brand-subtitle {
      font-size: 0.74rem;
      font-weight: 700;
      letter-spacing: 0.8px;
      color: var(--yellow-corp);
      text-transform: uppercase;
      margin-top: 2px;
    }

    .brand-subtext {
      font-size: 0.68rem;
      font-weight: 500;
      letter-spacing: 0.4px;
      color: rgba(255, 255, 255, 0.65);
      text-transform: uppercase;
      margin-top: 1px;
    }

    .sidebar-menu {
      padding: 24px 14px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      flex-grow: 1;
    }

    .menu-label {
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 1px;
      color: rgba(255, 255, 255, 0.45);
      text-transform: uppercase;
      padding: 0 12px 10px 12px;
    }

    .nav-item-btn {
      position: relative;
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 12px 16px;
      border-radius: 8px;
      color: rgba(255, 255, 255, 0.85);
      text-decoration: none;
      font-size: 0.92rem;
      font-weight: 500;
      background: transparent;
      border: 1px solid transparent;
      cursor: pointer;
      width: 100%;
      text-align: left;
      transition: var(--transition-smooth);
    }

    .nav-item-btn i {
      font-size: 1.15rem;
      transition: var(--transition-smooth);
      color: rgba(255, 255, 255, 0.6);
    }

    .nav-item-btn:hover {
      background-color: rgba(255, 255, 255, 0.1);
      color: #ffffff;
    }

    .nav-item-btn:hover i {
      color: var(--yellow-corp);
    }

    .nav-item-btn.active {
      background-color: var(--yellow-dim);
      color: var(--yellow-corp);
      font-weight: 700;
      border-color: rgba(255, 193, 7, 0.25);
    }

    .nav-item-btn.active i {
      color: var(--yellow-corp);
    }

    .nav-item-btn.active::before {
      content: '';
      position: absolute;
      left: 0;
      top: 15%;
      bottom: 15%;
      width: 4px;
      border-radius: 0 4px 4px 0;
      background-color: var(--yellow-corp);
      box-shadow: 0 0 10px rgba(255, 193, 7, 0.6);
    }

    .sidebar-footer {
      padding: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 0.76rem;
      color: rgba(255, 255, 255, 0.6);
      line-height: 1.4;
    }

    .footer-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      background-color: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 20px;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--yellow-corp);
    }

    .badge-dot {
      width: 7px;
      height: 7px;
      background-color: var(--yellow-corp);
      border-radius: 50%;
      box-shadow: 0 0 6px var(--yellow-corp);
    }

    /* ==========================================================================
       BARRA SUPERIOR Y CONTENIDO PRINCIPAL (100% ANCHO LIMPIO)
       ========================================================================== */
    .admin-main-wrapper {
      margin-left: 0;
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background-color: var(--bg-page);
      width: 100%;
    }

    .admin-topbar {
      height: var(--topbar-height);
      background-color: var(--navy-deep);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 32px 0 180px; /* Deja espacio al logo flotante a la izquierda */
      position: sticky;
      top: 0;
      z-index: 900;
      box-shadow: 0 2px 8px rgba(10, 25, 47, 0.1);
      transition: padding 0.3s ease;
    }

    .topbar-left {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .topbar-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 5px 12px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      color: rgba(255, 255, 255, 0.9);
      font-size: 0.8rem;
      font-weight: 500;
    }

    .topbar-badge .status-live-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: var(--status-disp);
      box-shadow: 0 0 8px var(--status-disp);
    }

    .user-profile-wrapper {
      position: relative;
    }

    .user-avatar-btn {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.1);
      border: 2px solid var(--yellow-corp);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--yellow-corp);
      font-size: 1.25rem;
      cursor: pointer;
      transition: var(--transition-smooth);
      position: relative;
    }

    .user-avatar-btn:hover,
    .user-avatar-btn.active {
      background-color: var(--yellow-corp);
      color: var(--navy-deep);
      box-shadow: 0 0 12px rgba(255, 193, 7, 0.5);
    }

    .user-avatar-status {
      position: absolute;
      bottom: 2px;
      right: 2px;
      width: 10px;
      height: 10px;
      background-color: var(--status-disp);
      border: 2px solid var(--navy-deep);
      border-radius: 50%;
    }

    .user-dropdown {
      position: absolute;
      top: calc(100% + 12px);
      right: 0;
      width: 270px;
      background-color: #ffffff;
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      box-shadow: 0 16px 36px rgba(10, 25, 47, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.05);
      padding: 10px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-8px);
      transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
      z-index: 1100;
    }

    .user-dropdown.show {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .dropdown-header-custom {
      padding: 12px 14px;
      border-bottom: 1px solid var(--border-subtle);
      margin-bottom: 6px;
    }

    .dropdown-user-name {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--navy-deep);
      line-height: 1.2;
    }

    .dropdown-user-email {
      font-size: 0.8rem;
      color: var(--text-muted);
      margin-top: 3px;
      word-break: break-all;
    }

    .dropdown-item-custom {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      color: var(--text-main);
      font-size: 0.88rem;
      font-weight: 500;
      text-decoration: none;
      border-radius: 8px;
      transition: var(--transition-smooth);
      background: transparent;
      border: none;
      width: 100%;
      text-align: left;
      cursor: pointer;
    }

    .dropdown-item-custom i {
      font-size: 1.1rem;
      color: var(--text-muted);
      transition: var(--transition-smooth);
    }

    .dropdown-item-custom:hover {
      background-color: #f1f5f9;
      color: var(--navy-deep);
    }

    .dropdown-item-custom:hover i {
      color: var(--navy-deep);
    }

    .dropdown-item-custom.text-logout:hover {
      background-color: #fee2e2;
      color: #dc2626;
    }

    .dropdown-item-custom.text-logout:hover i {
      color: #dc2626;
    }

    /* ==========================================================================
       ÁREA DE CONTENIDO Y GRID DE TARJETAS WEB
       ========================================================================== */
    .admin-content {
      padding: 36px 36px 60px 36px;
      max-width: 1440px;
      width: 100%;
      margin: 0 auto;
    }

    .section-header {
      margin-bottom: 28px;
    }

    .section-title {
      font-size: 1.85rem;
      font-weight: 800;
      color: var(--navy-deep);
      letter-spacing: -0.5px;
    }

    .section-desc {
      font-size: 0.95rem;
      color: var(--text-muted);
      margin-top: 4px;
    }

    .web-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 22px;
    }

    .web-card {
      background-color: #ffffff;
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      padding: 24px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: var(--transition-smooth);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .web-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(10, 25, 47, 0.08);
      border-color: #cbd5e1;
    }

    .web-card-icon {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      background-color: rgba(10, 25, 47, 0.06);
      border: 1px solid rgba(10, 25, 47, 0.12);
      color: var(--navy-deep);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
    }

    /* Fondo tenue al desplegar el menú */
    .sidebar-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background-color: rgba(10, 25, 47, 0.4);
      backdrop-filter: blur(2px);
      -webkit-backdrop-filter: blur(2px);
      z-index: 1045;
      opacity: 0;
      transition: opacity 0.3s ease;
      pointer-events: none;
    }

    .sidebar-backdrop.show {
      display: block;
      opacity: 1;
    }

    /* PLACA VEHICULAR AMARILLA (ESTILO COLOMBIANA / M&M) */
    .placa-badge {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      background: #fef08a !important; /* Amarillo claro vehicular luminoso (estilo placa colombiana) */
      color: #0f172a !important;      /* Letras oscuras negro/navy de máxima legibilidad y contraste */
      border: 1.5px solid #d4a017 !important; /* Borde ámbar/dorado de placa metálica */
      border-radius: 6px !important;
      padding: 3px 9px !important;
      font-family: 'Consolas', 'Roboto Mono', 'SFMono-Regular', monospace !important;
      font-size: 13px !important;
      font-weight: 800 !important;
      letter-spacing: 0.12em !important;
      line-height: 1.15 !important;
      text-transform: uppercase !important;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    }

    /* BOTÓN CORPORATIVO AMARILLO */
    .btn-corp-yellow {
      background-color: var(--yellow-corp);
      color: var(--navy-deep) !important;
      font-weight: 700;
      border: 1px solid var(--yellow-corp);
      border-radius: 8px;
      padding: 10px 22px;
      font-size: 0.92rem;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: var(--transition-smooth);
      box-shadow: 0 2px 6px rgba(255, 193, 7, 0.3);
      cursor: pointer;
      text-decoration: none;
    }

    .btn-corp-yellow:hover {
      background-color: var(--yellow-hover);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(255, 193, 7, 0.45);
      color: var(--navy-deep) !important;
    }

    .btn-corp-yellow:active {
      transform: translateY(0);
    }

    /* ESTILOS DEL MODAL CORPORATIVO */
    .modal-header-corp {
      background: linear-gradient(135deg, var(--navy-deep) 0%, #172a45 100%);
      color: #ffffff;
      border-bottom: 3px solid var(--yellow-corp);
    }

    .modal-section-card {
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }

    .modal-section-card .card-header {
      background-color: #f8fafc;
      border-bottom: 1px solid var(--border-subtle);
      font-weight: 700;
      color: var(--navy-deep);
    }

    /* ESTILOS DEL ACORDEÓN CORPORATIVO EN MODAL */
    .accordion-corp .accordion-item {
      border: 1px solid var(--border-subtle);
      border-radius: 12px !important;
      margin-bottom: 16px;
      overflow: hidden;
      background-color: #ffffff;
      box-shadow: 0 2px 8px rgba(10, 25, 47, 0.04);
    }

    .accordion-corp .accordion-button {
      background-color: #ffffff;
      color: var(--navy-deep);
      font-weight: 700;
      font-size: 0.95rem;
      padding: 16px 20px;
      box-shadow: none;
      transition: background-color 0.2s ease, color 0.2s ease;
    }

    .accordion-corp .accordion-button:not(.collapsed) {
      background-color: #f8fafc;
      color: var(--navy-deep);
      box-shadow: inset 0 -1px 0 var(--border-subtle);
    }

    .accordion-corp .accordion-button:focus {
      box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.35);
      border-color: var(--yellow-corp);
    }

    .compact-file-box {
      background-color: #f8fafc;
      border: 1px solid var(--border-subtle);
      border-radius: 8px;
      padding: 10px 14px;
    }

    /* ==========================================================================
       ESTILOS DE DESLIZAMIENTO DEL FORMULARIO (SCROLL SUAVE Y TRANSICIONES)
       ========================================================================== */
    .form-scroll-container {
      overflow-y: auto !important;
      max-height: calc(88vh - 145px) !important;
      scroll-behavior: smooth !important;
      -webkit-overflow-scrolling: touch;
      overscroll-behavior: contain;
    }

    .form-scroll-container::-webkit-scrollbar {
      width: 8px;
    }

    .form-scroll-container::-webkit-scrollbar-track {
      background: #e2e8f0;
      border-radius: 8px;
    }

    .form-scroll-container::-webkit-scrollbar-thumb {
      background: #94a3b8;
      border-radius: 8px;
      border: 2px solid #e2e8f0;
    }

    .form-scroll-container::-webkit-scrollbar-thumb:hover {
      background: var(--navy-deep);
    }

    /* Animación fluida de deslizamiento en el despliegue del acordeón */
    .accordion-corp .collapsing {
      transition: height 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    /* Botón flotante para deslizar rápidamente al tope */
    .btn-slide-top {
      position: sticky;
      bottom: 12px;
      float: right;
      z-index: 10;
      background: var(--navy-deep);
      color: #ffffff;
      border: 1px solid var(--yellow-corp);
      width: 38px;
      height: 38px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(10, 25, 47, 0.3);
      cursor: pointer;
      opacity: 0.85;
      transition: var(--transition-smooth);
    }

    .btn-slide-top:hover {
      opacity: 1;
      transform: translateY(-2px);
      background: var(--yellow-corp);
      color: var(--navy-deep);
    }

    @media (max-width: 768px) {
      .admin-topbar {
        padding: 0 16px 0 80px;
      }
      .logo-floating-trigger {
        padding: 4px;
      }
      .admin-content {
        padding: 24px 16px 50px 16px;
      }
    }
  </style>
</head>
<body>

  <!-- Backdrop tenue cuando la barra azul se abre -->
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- ==========================================================================
       1. GATILLO CON EL LOGO M&M EN FONDO BLANCO (VISUAL CUANDO LA BARRA DESAPARECE)
       ========================================================================== -->
  <div class="logo-floating-trigger" id="sidebarTrigger" title="Pasa el mouse para abrir el menú">
    <div class="brand-icon-box">M&M</div>
    <div class="trigger-label d-none d-sm-flex">
      <span class="trigger-brand">M&M</span>
      <span class="trigger-hint">Menú <i class="bi bi-chevron-right"></i></span>
    </div>
  </div>

  <!-- ==========================================================================
       2. SIDEBAR AZUL MARINO (#0A192F) QUE APARECE CON HOVER
       ========================================================================== -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div>
      <!-- Marca Institucional -->
      <div class="sidebar-brand">
        <a href="desboard.php" class="d-flex align-items-center gap-3 text-decoration-none text-white" aria-label="M&M Construcciones">
          <div class="brand-icon-box">M&M</div>
          <div class="brand-text">
            <span class="brand-title">M&M</span>
            <span class="brand-subtitle">Construcciones</span>
            <span class="brand-subtext">y Servicios S.A.S.</span>
          </div>
        </a>
        <button type="button" class="btn-close-sidebar d-md-none" id="closeSidebarBtn" aria-label="Cerrar menú" title="Cerrar menú">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <!-- Menú de Navegación Vertical con los 3 Hipervínculos -->
      <nav class="sidebar-menu" aria-label="Navegación del panel">
        <span class="menu-label">Panel Principal</span>

        <!-- Opción 1: Dashboard (Enlace a desboard.php) -->
        <a href="desboard.php" class="nav-item-btn">
          <i class="bi bi-grid-1x2-fill"></i>
          <span>Dashboard</span>
        </a>

        <!-- Opción 2: Maquinarias (Enlace a registrarmaquina.php) -->
        <a href="registrarmaquina.php" class="nav-item-btn">
          <i class="bi bi-truck"></i>
          <span>Maquinarias</span>
        </a>

        <!-- Opción 3: Contenido de web (Página Actual Activa) -->
        <a href="editor.php" class="nav-item-btn active">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Contenido de web</span>
        </a>
      </nav>
    </div>

    <!-- Pie del Sidebar -->
    <div class="sidebar-footer">
      <div class="footer-badge">
        <span class="badge-dot"></span>
        <span>Panel Administrativo</span>
      </div>
      <div>Sistema PHP 8 &bull; Conexión MySQL Activa</div>
    </div>
  </aside>

  <!-- ==========================================================================
       3. CONTENIDO PRINCIPAL Y BARRA SUPERIOR (#0A192F)
       ========================================================================== -->
  <div class="admin-main-wrapper">
    
    <!-- Topbar Azul Marino (#0A192F) -->
    <header class="admin-topbar">
      <div class="topbar-left">
        <div class="topbar-badge d-none d-md-inline-flex">
          <span class="status-live-dot"></span>
          <span>Sistema en Línea</span>
        </div>
      </div>

      <!-- Perfil del Administrador (Esquina Superior Derecha) -->
      <div class="user-profile-wrapper">
        <button type="button" class="user-avatar-btn" id="userAvatarBtn" aria-label="Menú del usuario">
          <i class="bi bi-person-fill"></i>
          <span class="user-avatar-status" title="En línea"></span>
        </button>

        <!-- Menú Desplegable -->
        <div class="user-dropdown" id="userDropdown">
          <div class="dropdown-header-custom">
            <div class="dropdown-user-name"><?php echo htmlspecialchars($admin_nombre); ?></div>
            <div class="dropdown-user-email"><?php echo htmlspecialchars($admin_email); ?></div>
            <div class="badge bg-warning text-dark mt-2" style="font-size: 0.7rem; font-weight: 700;"><?php echo $admin_rol; ?></div>
          </div>
          
          <button type="button" class="dropdown-item-custom">
            <i class="bi bi-gear"></i>
            <span>Configuración</span>
          </button>

          <a href="logout.php" class="dropdown-item-custom text-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Cerrar sesión</span>
          </a>
        </div>
      </div>
    </header>

    <!-- Área de Contenido Principal: CONTENIDO DE LA WEB -->
    <main class="admin-content">

      <div class="section-header">
        <h1 class="section-title">Contenido del Sitio Web</h1>
        <p class="section-desc">Módulos para la gestión y actualización del portal público de M&M Construcciones.</p>
      </div>

      <?php if (!empty($mensaje_alerta)): ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
          <i class="bi bi-check-circle-fill fs-5 text-success"></i>
          <div class="fw-medium"><?php echo htmlspecialchars($mensaje_alerta, ENT_QUOTES, 'UTF-8'); ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
      <?php endif; ?>

      <div class="web-grid">
        
        <!-- Bloque 1: Información de la empresa -->
        <div class="web-card">
          <div>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="web-card-icon"><i class="bi bi-building"></i></div>
              <div>
                <h3 class="fs-5 fw-bold mb-0 text-dark">INICIO</h3>
                <small class="text-primary fw-semibold">Sección Institucional</small>
              </div>
            </div>
            <p class="text-muted small mb-3">Gestión de la misión, visión, trayectoria de la empresa, directivos y valores organizacionales.</p>
            
            <ul class="list-unstyled small text-secondary bg-light p-3 rounded-3 mb-4">
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Misión y Visión corporativa</li>
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Reseña histórica y certificaciones</li>
              <li><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Políticas de calidad y seguridad</li>
            </ul>
          </div>

          <button type="button" class="btn btn-outline-dark fw-bold w-100 d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#modalEditarInicio">
            <span>Gestionar Sección</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>

        <!-- Bloque 2: Servicios -->
        <div class="web-card">
          <div>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="web-card-icon"><i class="bi bi-briefcase"></i></div>
              <div>
                <h3 class="fs-5 fw-bold mb-0 text-dark">PROYECTOS</h3>
                <small class="text-primary fw-semibold">Portafolio Técnico</small>
              </div>
            </div>
            <p class="text-muted small mb-3">Catálogo público de servicios: movimiento de tierras, alquiler de maquinaria y pavimentación.</p>
            
            <ul class="list-unstyled small text-secondary bg-light p-3 rounded-3 mb-4">
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Movimiento masivo de tierras</li>
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Alquiler de maquinaria pesada</li>
              <li><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Obras civiles y pavimentación</li>
            </ul>
          </div>

          <button type="button" class="btn btn-outline-dark fw-bold w-100 d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#modalEditarProyectos">
            <span>Gestionar Sección</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>

        <!-- Bloque 3: Obras -->
        <div class="web-card">
          <div>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="web-card-icon"><i class="bi bi-people-fill"></i></div>
              <div>
                <h3 class="fs-5 fw-bold mb-0 text-dark">NOSOTROS</h3>
                <small class="text-primary fw-semibold">Identidad Corporativa</small>
              </div>
            </div>
            <p class="text-muted small mb-3">Gestión de quiénes somos, misión, visión, valores corporativos y métricas de trayectoria.</p>
            
            <ul class="list-unstyled small text-secondary bg-light p-3 rounded-3 mb-4">
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Quiénes somos y características</li>
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Misión y Visión organizacional</li>
              <li><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Valores corporativos y métricas</li>
            </ul>
          </div>

          <button type="button" class="btn btn-outline-dark fw-bold w-100 d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#modalEditarNosotros">
            <span>Gestionar Sección</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>

        <!-- Bloque 4: Galería -->
        <div class="web-card">
          <div>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="web-card-icon"><i class="bi bi-truck"></i></div>
              <div>
                <h3 class="fs-5 fw-bold mb-0 text-dark">MAQUINARIA</h3>
                <small class="text-primary fw-semibold">Flota y Equipos</small>
              </div>
            </div>
            <p class="text-muted small mb-3">Gestión de la flota pesada, filtros del catálogo de equipos y sección de respaldo técnico.</p>
            
            <ul class="list-unstyled small text-secondary bg-light p-3 rounded-3 mb-4">
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Encabezado Hero y badge destacado</li>
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Pestañas de filtros del catálogo</li>
              <li><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Garantías y respaldo en obra</li>
            </ul>
          </div>

          <button type="button" class="btn btn-outline-dark fw-bold w-100 d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#modalEditarMaquinaria">
            <span>Gestionar Sección</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>

        <!-- Bloque 5: Contacto -->
        <div class="web-card">
          <div>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="web-card-icon"><i class="bi bi-envelope-paper"></i></div>
              <div>
                <h3 class="fs-5 fw-bold mb-0 text-dark">CONTACTO</h3>
                <small class="text-primary fw-semibold">Canales de Atención</small>
              </div>
            </div>
            <p class="text-muted small mb-3">Canales de contacto: líneas telefónicas, buzones de correo y ubicación en mapa.</p>
            
            <ul class="list-unstyled small text-secondary bg-light p-3 rounded-3 mb-4">
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Teléfonos y líneas de atención</li>
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Correos institucionales</li>
              <li><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Mapa y dirección de sede central</li>
            </ul>
          </div>

          <button type="button" class="btn btn-outline-dark fw-bold w-100 d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#modalEditarContacto">
            <span>Gestionar Sección</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>

      </div>

      <!-- ==========================================================================
           MODAL EXTRA GRANDE (modal-xl): EDICIÓN DE SECCIÓN INICIO
           ========================================================================== -->
      <div class="modal fade" id="modalEditarInicio" tabindex="-1" aria-labelledby="modalEditarInicioLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-height: 92vh;">
          <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; max-height: 92vh; display: flex; flex-direction: column;">
            
            <!-- Header del Modal -->
            <div class="modal-header modal-header-corp py-3 px-4 flex-shrink-0">
              <div class="d-flex align-items-center gap-3">
                <div class="brand-icon-box" style="width: 40px; height: 40px; font-size: 13px;">M&M</div>
                <div>
                  <h5 class="modal-title fw-bold text-white mb-0" id="modalEditarInicioLabel">
                    <i class="bi bi-pencil-square text-warning me-2"></i>Gestión de Contenido: Sección INICIO
                  </h5>
                  <small class="text-white-50">Edita en tiempo real el banner principal (Hero) y los bloques de servicios destacados</small>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark text-warning border border-warning-subtle small d-none d-md-inline-flex align-items-center gap-1 py-2 px-3">
                  <i class="bi bi-arrows-vertical"></i> Deslizamiento Activo
                </span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
            </div>

            <!-- Formulario con soporte para subida de archivos y deslizamiento garantizado -->
            <form method="POST" action="" enctype="multipart/form-data" class="d-flex flex-column h-100 overflow-hidden m-0 p-0" style="min-height: 0; flex: 1 1 auto;">
              
              <div class="modal-body p-4 bg-light form-scroll-container">
                
                <!-- Barra de Navegación Rápida con Deslizamiento Suave (Quick Slide) -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 px-3 bg-white rounded-3 border shadow-sm">
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                      <i class="bi bi-arrows-vertical me-1"></i> Deslizar a:
                    </span>
                    <span class="small text-muted fw-semibold d-none d-sm-inline">Navegación rápida de secciones</span>
                  </div>
                  <div class="d-flex align-items-center gap-1 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccion('headingHero', '#collapseHero')">
                      <i class="bi bi-house-door me-1 text-warning"></i>1. Hero
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccion('headingServicios', '#collapseServicios')">
                      <i class="bi bi-briefcase me-1 text-warning"></i>2. Servicios
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccion('headingElegirnos', '#collapseElegirnos')">
                      <i class="bi bi-shield-check me-1 text-warning"></i>3. Elegirnos
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccion('headingFooter', '#collapseFooter')">
                      <i class="bi bi-layout-text-window me-1 text-warning"></i>4. Footer
                    </button>
                  </div>
                </div>

                <div class="accordion accordion-corp" id="accordionEditorInicio">
                  
                  <!-- ================================================================
                       ACORDEÓN 1 - BLOQUE 1 (HERO / INICIO)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingHero">
                      <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero" aria-expanded="true" aria-controls="collapseHero">
                        <i class="bi bi-house-door-fill text-warning me-2 fs-5"></i>
                        <span>Acordeón 1 - Bloque 1 (Hero / Inicio)</span>
                      </button>
                    </h2>
                    <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero" data-bs-parent="#accordionEditorInicio">
                      <div class="accordion-body p-4 bg-white">
                        
                        <!-- Imagen de Fondo (Compacta con Selector) -->
                        <div class="mb-4">
                          <label for="hero_imagen_texto" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-image text-warning"></i>
                            <span>Imagen de Fondo (Hero):</span>
                          </label>
                          <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-file-earmark-image"></i></span>
                            <input type="text" class="form-control" id="hero_imagen_texto" name="hero_imagen_nombre" value="<?php echo htmlspecialchars($contenido['hero_imagen'] ?? 'imagen1.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nombre de archivo o link de imagen...">
                            <input type="file" id="hero_imagen_file" name="hero_imagen_file" class="d-none" accept="image/*">
                            <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" onclick="document.getElementById('hero_imagen_file').click();">
                              <i class="bi bi-folder2-open"></i>
                              <span>Seleccionar</span>
                            </button>
                          </div>
                          <div class="form-text text-muted small mt-1">
                            <i class="bi bi-info-circle me-1"></i>Selector compacto (sin previsualización gigante). Puedes hacer clic en <strong>Seleccionar</strong> para cargar un nuevo archivo desde tu equipo o ingresar el nombre/enlace directamente.
                          </div>
                        </div>

                        <!-- Título Hero -->
                        <div class="mb-4">
                          <label for="hero_titulo" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-type-h1 text-warning"></i>
                            <span>Título:</span>
                          </label>
                          <input type="text" class="form-control" id="hero_titulo" name="hero_titulo" value="<?php echo htmlspecialchars($contenido['hero_titulo'] ?? 'BIENVENIDO A CONSTRUCCIONES M&M', ENT_QUOTES, 'UTF-8'); ?>" placeholder="BIENVENIDO A CONSTRUCCIONES M&M">
                        </div>

                        <!-- Subtítulo Hero -->
                        <div class="mb-2">
                          <label for="hero_subtitulo" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-text-paragraph text-warning"></i>
                            <span>Subtítulo:</span>
                          </label>
                          <textarea class="form-control" id="hero_subtitulo" name="hero_subtitulo" rows="3" placeholder="Soluciones integrales de alta ingeniería..."><?php echo htmlspecialchars($contenido['hero_subtitulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 2 - BLOQUE 2 (NUESTROS SERVICIOS Y CARRUSEL)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingServicios">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseServicios" aria-expanded="false" aria-controls="collapseServicios">
                        <i class="bi bi-briefcase-fill text-warning me-2 fs-5"></i>
                        <span>Acordeón 2 - Bloque 2 (Nuestros Servicios y Carrusel)</span>
                      </button>
                    </h2>
                    <div id="collapseServicios" class="accordion-collapse collapse" aria-labelledby="headingServicios" data-bs-parent="#accordionEditorInicio">
                      <div class="accordion-body p-4 bg-white">
                        
                        <!-- Título Principal de Servicios -->
                        <div class="mb-3">
                          <label for="servicios_titulo" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-fonts text-warning"></i>
                            <span>Título Principal:</span>
                          </label>
                          <input type="text" class="form-control" id="servicios_titulo" name="servicios_titulo" value="<?php echo htmlspecialchars($contenido['servicios_titulo'] ?? 'Nuestros Servicios', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nuestros Servicios">
                        </div>

                        <!-- Subtítulo Principal de Servicios -->
                        <div class="mb-4">
                          <label for="servicios_subtitulo" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-card-text text-warning"></i>
                            <span>Subtítulo Principal:</span>
                          </label>
                          <textarea class="form-control" id="servicios_subtitulo" name="servicios_subtitulo" rows="2" placeholder="Ofrecemos soluciones completas..."><?php echo htmlspecialchars($contenido['servicios_subtitulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <!-- Tarjetas de Servicios: Construcción Civil y Redes de Gas -->
                        <div class="row g-4 mb-4">
                          
                          <!-- Tarjeta 1 (Construcción Civil) -->
                          <div class="col-md-6">
                            <div class="card h-100 border rounded-3 p-3 bg-light-subtle shadow-none">
                              <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                  <div class="rounded-2 p-1 bg-white border text-primary">
                                    <i class="bi bi-building"></i>
                                  </div>
                                  <span class="fw-bold text-dark">Tarjeta 1 (Construcción Civil)</span>
                                </div>
                                <span class="badge bg-primary text-white">Servicio 01</span>
                              </div>

                              <div class="mb-3">
                                <label for="servicio1_titulo" class="form-label small fw-bold text-secondary">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="servicio1_titulo" name="servicio1_titulo" value="<?php echo htmlspecialchars($contenido['servicio1_titulo'] ?? 'Construcción Civil', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>

                              <div class="mb-3">
                                <label for="servicio1_texto" class="form-label small fw-bold text-secondary">Texto:</label>
                                <textarea class="form-control form-control-sm" id="servicio1_texto" name="servicio1_texto" rows="3"><?php echo htmlspecialchars($contenido['servicio1_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>

                              <div class="mb-1">
                                <label for="servicio1_vinetas" class="form-label small fw-bold text-secondary">Lista de viñetas (una por línea):</label>
                                <textarea class="form-control form-control-sm font-monospace" id="servicio1_vinetas" name="servicio1_vinetas" rows="4"><?php echo htmlspecialchars($contenido['servicio1_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <div class="form-text text-muted" style="font-size: 11px;">
                                  Escribe cada viñeta en un renglón nuevo.
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- Tarjeta 2 (Redes de Gas) -->
                          <div class="col-md-6">
                            <div class="card h-100 border rounded-3 p-3 bg-light-subtle shadow-none">
                              <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                  <div class="rounded-2 p-1 bg-white border text-danger">
                                    <i class="bi bi-fire"></i>
                                  </div>
                                  <span class="fw-bold text-dark">Tarjeta 2 (Redes de Gas)</span>
                                </div>
                                <span class="badge bg-danger text-white">Servicio 02</span>
                              </div>

                              <div class="mb-3">
                                <label for="servicio2_titulo" class="form-label small fw-bold text-secondary">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="servicio2_titulo" name="servicio2_titulo" value="<?php echo htmlspecialchars($contenido['servicio2_titulo'] ?? 'Redes de Gas', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>

                              <div class="mb-3">
                                <label for="servicio2_texto" class="form-label small fw-bold text-secondary">Texto:</label>
                                <textarea class="form-control form-control-sm" id="servicio2_texto" name="servicio2_texto" rows="3"><?php echo htmlspecialchars($contenido['servicio2_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>

                              <div class="mb-1">
                                <label for="servicio2_vinetas" class="form-label small fw-bold text-secondary">Lista de viñetas (una por línea):</label>
                                <textarea class="form-control form-control-sm font-monospace" id="servicio2_vinetas" name="servicio2_vinetas" rows="4"><?php echo htmlspecialchars($contenido['servicio2_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <div class="form-text text-muted" style="font-size: 11px;">
                                  Escribe cada viñeta en un renglón nuevo.
                                </div>
                              </div>
                            </div>
                          </div>

                        </div>

                        <!-- Carrusel 1 (3 imágenes compactas) -->
                        <div class="border rounded-3 p-3 bg-light-subtle">
                          <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-images text-warning"></i>
                            <span>Carrusel 1 (3 imágenes compactas):</span>
                          </h6>

                          <!-- Imagen 1 -->
                          <div class="mb-3">
                            <label for="carrusel_img1_texto" class="form-label small fw-bold text-secondary">Imagen 1 del Carrusel:</label>
                            <div class="input-group input-group-sm">
                              <span class="input-group-text bg-white text-muted"><i class="bi bi-1-circle"></i></span>
                              <input type="text" class="form-control" id="carrusel_img1_texto" name="carrusel_img1_nombre" value="<?php echo htmlspecialchars($contenido['carrusel_img1'] ?? 'imagen 22.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nombre de archivo o link...">
                              <input type="file" id="carrusel_img1_file" name="carrusel_img1_file" class="d-none" accept="image/*">
                              <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" onclick="document.getElementById('carrusel_img1_file').click();">
                                <i class="bi bi-folder2-open"></i> Seleccionar
                              </button>
                            </div>
                          </div>

                          <!-- Imagen 2 -->
                          <div class="mb-3">
                            <label for="carrusel_img2_texto" class="form-label small fw-bold text-secondary">Imagen 2 del Carrusel:</label>
                            <div class="input-group input-group-sm">
                              <span class="input-group-text bg-white text-muted"><i class="bi bi-2-circle"></i></span>
                              <input type="text" class="form-control" id="carrusel_img2_texto" name="carrusel_img2_nombre" value="<?php echo htmlspecialchars($contenido['carrusel_img2'] ?? 'imagen 33.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nombre de archivo o link...">
                              <input type="file" id="carrusel_img2_file" name="carrusel_img2_file" class="d-none" accept="image/*">
                              <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" onclick="document.getElementById('carrusel_img2_file').click();">
                                <i class="bi bi-folder2-open"></i> Seleccionar
                              </button>
                            </div>
                          </div>

                          <!-- Imagen 3 -->
                          <div class="mb-1">
                            <label for="carrusel_img3_texto" class="form-label small fw-bold text-secondary">Imagen 3 del Carrusel:</label>
                            <div class="input-group input-group-sm">
                              <span class="input-group-text bg-white text-muted"><i class="bi bi-3-circle"></i></span>
                              <input type="text" class="form-control" id="carrusel_img3_texto" name="carrusel_img3_nombre" value="<?php echo htmlspecialchars($contenido['carrusel_img3'] ?? 'imagen 44.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nombre de archivo o link...">
                              <input type="file" id="carrusel_img3_file" name="carrusel_img3_file" class="d-none" accept="image/*">
                              <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" onclick="document.getElementById('carrusel_img3_file').click();">
                                <i class="bi bi-folder2-open"></i> Seleccionar
                              </button>
                            </div>
                          </div>

                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 3 - BLOQUE 3 (¿POR QUÉ ELEGIRNOS?)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingElegirnos">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseElegirnos" aria-expanded="false" aria-controls="collapseElegirnos">
                        <i class="bi bi-shield-check text-warning me-2 fs-5"></i>
                        <span>Acordeón 3 - Bloque 3 (¿Por qué elegirnos?)</span>
                      </button>
                    </h2>
                    <div id="collapseElegirnos" class="accordion-collapse collapse" aria-labelledby="headingElegirnos" data-bs-parent="#accordionEditorInicio">
                      <div class="accordion-body p-4 bg-white">
                        
                        <!-- Título y Subtítulo -->
                        <div class="mb-3">
                          <label for="elegirnos_titulo" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-fonts text-warning"></i>
                            <span>Título:</span>
                          </label>
                          <input type="text" class="form-control" id="elegirnos_titulo" name="elegirnos_titulo" value="<?php echo htmlspecialchars($contenido['elegirnos_titulo'] ?? '¿Por qué elegirnos?', ENT_QUOTES, 'UTF-8'); ?>" placeholder="¿Por qué elegirnos?">
                        </div>

                        <div class="mb-4">
                          <label for="elegirnos_subtitulo" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-card-text text-warning"></i>
                            <span>Subtítulo:</span>
                          </label>
                          <textarea class="form-control" id="elegirnos_subtitulo" name="elegirnos_subtitulo" rows="2" placeholder="Nuestro compromiso es convertir cada proyecto..."><?php echo htmlspecialchars($contenido['elegirnos_subtitulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <!-- 3 Mini Tarjetas -->
                        <div class="row g-3">
                          
                          <!-- Mini Tarjeta 1 -->
                          <div class="col-md-4">
                            <div class="card h-100 border rounded-3 p-3 bg-light-subtle shadow-none">
                              <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                                <i class="bi bi-shield-check text-primary fs-5"></i>
                                <span class="fw-bold small text-dark">Mini Tarjeta 1</span>
                              </div>
                              <div class="mb-2">
                                <label for="elegirnos_item1_titulo" class="form-label small fw-bold text-secondary">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="elegirnos_item1_titulo" name="elegirnos_item1_titulo" value="<?php echo htmlspecialchars($contenido['elegirnos_item1_titulo'] ?? 'Seguridad garantizada', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>
                              <div>
                                <label for="elegirnos_item1_texto" class="form-label small fw-bold text-secondary">Texto:</label>
                                <textarea class="form-control form-control-sm" id="elegirnos_item1_texto" name="elegirnos_item1_texto" rows="3"><?php echo htmlspecialchars($contenido['elegirnos_item1_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>
                            </div>
                          </div>

                          <!-- Mini Tarjeta 2 -->
                          <div class="col-md-4">
                            <div class="card h-100 border rounded-3 p-3 bg-light-subtle shadow-none">
                              <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                                <i class="bi bi-award text-warning fs-5"></i>
                                <span class="fw-bold small text-dark">Mini Tarjeta 2</span>
                              </div>
                              <div class="mb-2">
                                <label for="elegirnos_item2_titulo" class="form-label small fw-bold text-secondary">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="elegirnos_item2_titulo" name="elegirnos_item2_titulo" value="<?php echo htmlspecialchars($contenido['elegirnos_item2_titulo'] ?? 'Experiencia comprobada', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>
                              <div>
                                <label for="elegirnos_item2_texto" class="form-label small fw-bold text-secondary">Texto:</label>
                                <textarea class="form-control form-control-sm" id="elegirnos_item2_texto" name="elegirnos_item2_texto" rows="3"><?php echo htmlspecialchars($contenido['elegirnos_item2_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>
                            </div>
                          </div>

                          <!-- Mini Tarjeta 3 -->
                          <div class="col-md-4">
                            <div class="card h-100 border rounded-3 p-3 bg-light-subtle shadow-none">
                              <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                                <i class="bi bi-file-check text-success fs-5"></i>
                                <span class="fw-bold small text-dark">Mini Tarjeta 3</span>
                              </div>
                              <div class="mb-2">
                                <label for="elegirnos_item3_titulo" class="form-label small fw-bold text-secondary">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="elegirnos_item3_titulo" name="elegirnos_item3_titulo" value="<?php echo htmlspecialchars($contenido['elegirnos_item3_titulo'] ?? 'Cumplimiento normativo', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>
                              <div>
                                <label for="elegirnos_item3_texto" class="form-label small fw-bold text-secondary">Texto:</label>
                                <textarea class="form-control form-control-sm" id="elegirnos_item3_texto" name="elegirnos_item3_texto" rows="3"><?php echo htmlspecialchars($contenido['elegirnos_item3_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>
                            </div>
                          </div>

                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 4 - FOOTER (PIE DE PÁGINA)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingFooter">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFooter" aria-expanded="false" aria-controls="collapseFooter">
                        <i class="bi bi-layout-text-window text-warning me-2 fs-5"></i>
                        <span>Acordeón 4 - Footer (Pie de página)</span>
                      </button>
                    </h2>
                    <div id="collapseFooter" class="accordion-collapse collapse" aria-labelledby="headingFooter" data-bs-parent="#accordionEditorInicio">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="mb-2">
                          <label for="footer_contacto_vinetas" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-card-checklist text-warning"></i>
                            <span>Lista de viñetas de Contacto (una por línea):</span>
                          </label>
                          <textarea class="form-control font-monospace" id="footer_contacto_vinetas" name="footer_contacto_vinetas" rows="5" placeholder="Neiva, Huila, Colombia&#10;+57 310 291 6140&#10;contacto@constructoresmym.com&#10;Lunes a viernes: 8:00 a.m. - 5:00 p.m."><?php echo htmlspecialchars($contenido['footer_contacto_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                          <div class="form-text text-muted small mt-1">
                            <i class="bi bi-info-circle me-1"></i>Escribe cada dato de contacto en un renglón nuevo (Ciudad, Teléfono, Correo, Horario de atención).
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                </div>

                <!-- Botón flotante para deslizar rápidamente al inicio del formulario -->
                <button type="button" class="btn-slide-top shadow" title="Deslizar arriba" onclick="document.querySelector('.form-scroll-container').scrollTo({top: 0, behavior: 'smooth'});">
                  <i class="bi bi-chevron-up"></i>
                </button>

              </div>

              <!-- Footer del Modal (Fijo en la base con flex-shrink-0) -->
              <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center border-top flex-shrink-0">
                <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal">
                  <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>
                <button type="submit" name="guardar_inicio" class="btn-corp-yellow px-4 py-2">
                  <i class="bi bi-check2-circle me-1 fs-5"></i>
                  <span>Guardar Cambios de Inicio</span>
                </button>
              </div>

            </form>

          </div>
        </div>
      </div>

      <!-- ==========================================================================
           MODAL EXTRA GRANDE (modal-xl): EDICIÓN DE SECCIÓN PROYECTOS
           ========================================================================== -->
      <div class="modal fade" id="modalEditarProyectos" tabindex="-1" aria-labelledby="modalEditarProyectosLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-height: 92vh;">
          <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; max-height: 92vh; display: flex; flex-direction: column;">
            
            <!-- Header del Modal -->
            <div class="modal-header modal-header-corp py-3 px-4 flex-shrink-0">
              <div class="d-flex align-items-center gap-3">
                <div class="brand-icon-box" style="width: 40px; height: 40px; font-size: 13px;">M&M</div>
                <div>
                  <h5 class="modal-title fw-bold text-white mb-0" id="modalEditarProyectosLabel">
                    <i class="bi bi-briefcase-fill text-warning me-2"></i>Gestión de Contenido: Sección PROYECTOS
                  </h5>
                  <small class="text-white-50">Edita en tiempo real el carrusel, botones de filtro y el catálogo técnico de proyectos y evidencias</small>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark text-warning border border-warning-subtle small d-none d-md-inline-flex align-items-center gap-1 py-2 px-3">
                  <i class="bi bi-arrows-vertical"></i> Deslizamiento Activo
                </span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
            </div>

            <!-- Formulario con soporte para subida de archivos y enlaces directos -->
            <form method="POST" action="" enctype="multipart/form-data" class="d-flex flex-column h-100 overflow-hidden m-0 p-0" style="min-height: 0; flex: 1 1 auto;">
              
              <div class="modal-body p-4 bg-light form-scroll-container">
                
                <!-- Barra de Navegación Rápida con Deslizamiento Suave (Quick Slide) -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 px-3 bg-white rounded-3 border shadow-sm">
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                      <i class="bi bi-arrows-vertical me-1"></i> Deslizar a:
                    </span>
                    <span class="small text-muted fw-semibold d-none d-sm-inline">Navegación rápida de proyectos</span>
                  </div>
                  <div class="d-flex align-items-center gap-1 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionProy('headingProyCarrusel', '#collapseProyCarrusel')">
                      <i class="bi bi-images me-1 text-warning"></i>1. Carrusel
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionProy('headingProyFiltros', '#collapseProyFiltros')">
                      <i class="bi bi-sliders me-1 text-warning"></i>2. Filtros
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionProy('headingProyCalles', '#collapseProyCalles')">
                      <i class="bi bi-signpost-split me-1 text-warning"></i>3. Calles
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionProy('headingProyTuberias', '#collapseProyTuberias')">
                      <i class="bi bi-fire me-1 text-warning"></i>4. Tuberías
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionProy('headingProyEvidencias', '#collapseProyEvidencias')">
                      <i class="bi bi-camera-fill me-1 text-warning"></i>5. Evidencias
                    </button>
                  </div>
                </div>

                <div class="accordion accordion-corp" id="accordionEditorProyectos">
                  
                  <!-- ================================================================
                       ACORDEÓN 1 - BLOQUE 1 (CARRUSEL)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingProyCarrusel">
                      <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProyCarrusel" aria-expanded="true" aria-controls="collapseProyCarrusel">
                        <i class="bi bi-images text-warning me-2 fs-5"></i>
                        <span>Bloque 1: Carrusel Principal (3 Imágenes)</span>
                      </button>
                    </h2>
                    <div id="collapseProyCarrusel" class="accordion-collapse collapse show" aria-labelledby="headingProyCarrusel" data-bs-parent="#accordionEditorProyectos">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="alert alert-light border small text-muted mb-4 d-flex align-items-center gap-2">
                          <i class="bi bi-info-circle-fill text-warning fs-5"></i>
                          <span><strong>Imágenes Híbridas:</strong> Puedes pegar una URL directa (ej. enlace de internet o IA) o hacer clic en <strong>Subir</strong> para guardar un archivo en la carpeta <code>sena-2026/imagenes/</code>.</span>
                        </div>

                        <!-- Imagen 1 Carrusel -->
                        <div class="mb-4">
                          <label for="proy_carrusel_img1_texto" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-image text-warning"></i>
                            <span>Imagen 1 del Carrusel:</span>
                          </label>
                          <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" class="form-control" id="proy_carrusel_img1_texto" name="proy_carrusel_img1_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['carrusel_img1'] ?? 'imagen 44.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL directa o nombre de archivo...">
                            <input type="file" id="proy_carrusel_img1_file" name="proy_carrusel_img1_file" class="d-none" accept="image/*">
                            <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" onclick="document.getElementById('proy_carrusel_img1_file').click();">
                              <i class="bi bi-upload"></i>
                              <span>Subir</span>
                            </button>
                          </div>
                        </div>

                        <!-- Imagen 2 Carrusel -->
                        <div class="mb-4">
                          <label for="proy_carrusel_img2_texto" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-image text-warning"></i>
                            <span>Imagen 2 del Carrusel:</span>
                          </label>
                          <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" class="form-control" id="proy_carrusel_img2_texto" name="proy_carrusel_img2_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['carrusel_img2'] ?? 'imagen 22.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL directa o nombre de archivo...">
                            <input type="file" id="proy_carrusel_img2_file" name="proy_carrusel_img2_file" class="d-none" accept="image/*">
                            <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" onclick="document.getElementById('proy_carrusel_img2_file').click();">
                              <i class="bi bi-upload"></i>
                              <span>Subir</span>
                            </button>
                          </div>
                        </div>

                        <!-- Imagen 3 Carrusel -->
                        <div class="mb-2">
                          <label for="proy_carrusel_img3_texto" class="form-label fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-image text-warning"></i>
                            <span>Imagen 3 del Carrusel:</span>
                          </label>
                          <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" class="form-control" id="proy_carrusel_img3_texto" name="proy_carrusel_img3_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['carrusel_img3'] ?? 'imagen 33.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL directa o nombre de archivo...">
                            <input type="file" id="proy_carrusel_img3_file" name="proy_carrusel_img3_file" class="d-none" accept="image/*">
                            <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" onclick="document.getElementById('proy_carrusel_img3_file').click();">
                              <i class="bi bi-upload"></i>
                              <span>Subir</span>
                            </button>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 2 - BLOQUE 2 (BOTONES FUNCIONALES / FILTROS)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingProyFiltros">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProyFiltros" aria-expanded="false" aria-controls="collapseProyFiltros">
                        <i class="bi bi-sliders text-warning me-2 fs-5"></i>
                        <span>Bloque 2: Botones Funcionales (Filtros y Menú)</span>
                      </button>
                    </h2>
                    <div id="collapseProyFiltros" class="accordion-collapse collapse" aria-labelledby="headingProyFiltros" data-bs-parent="#accordionEditorProyectos">
                      <div class="accordion-body p-4 bg-white">
                        <div class="row g-3">
                          <div class="col-md-4">
                            <label for="proy_btn1_nombre" class="form-label fw-bold text-dark">Filtro 1 (Calles):</label>
                            <input type="text" class="form-control" id="proy_btn1_nombre" name="proy_btn1_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['btn1_nombre'] ?? 'Calles pavimentadas', ENT_QUOTES, 'UTF-8'); ?>">
                            <small class="text-muted">Se sincroniza con el Navbar y la botonera</small>
                          </div>
                          <div class="col-md-4">
                            <label for="proy_btn2_nombre" class="form-label fw-bold text-dark">Filtro 2 (Tuberías):</label>
                            <input type="text" class="form-control" id="proy_btn2_nombre" name="proy_btn2_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['btn2_nombre'] ?? 'Tuberias y redes', ENT_QUOTES, 'UTF-8'); ?>">
                            <small class="text-muted">Se sincroniza con el Navbar y la botonera</small>
                          </div>
                          <div class="col-md-4">
                            <label for="proy_btn3_nombre" class="form-label fw-bold text-dark">Filtro 3 (Evidencias):</label>
                            <input type="text" class="form-control" id="proy_btn3_nombre" name="proy_btn3_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['btn3_nombre'] ?? 'Evidencias', ENT_QUOTES, 'UTF-8'); ?>">
                            <small class="text-muted">Se sincroniza con el Navbar y la botonera</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 3 - BLOQUE 3 (CALLES PAVIMENTADAS)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingProyCalles">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProyCalles" aria-expanded="false" aria-controls="collapseProyCalles">
                        <i class="bi bi-signpost-split-fill text-warning me-2 fs-5"></i>
                        <span>Bloque 3: Calles Pavimentadas (3 Tarjetas)</span>
                      </button>
                    </h2>
                    <div id="collapseProyCalles" class="accordion-collapse collapse" aria-labelledby="headingProyCalles" data-bs-parent="#accordionEditorProyectos">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="card p-3 bg-light border-0 mb-4 rounded-3">
                          <h6 class="fw-bold text-dark mb-3"><i class="bi bi-card-heading text-warning me-2"></i>Cabecera de la Sección</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label for="proy_calles_titulo" class="form-label fw-bold small text-dark">Título de Sección:</label>
                              <input type="text" class="form-control" id="proy_calles_titulo" name="proy_calles_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['calles_titulo'] ?? 'Calles pavimentadas', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-6">
                              <label for="proy_calles_badge" class="form-label fw-bold small text-dark">Botón Decorativo (Badge de Cabecera):</label>
                              <input type="text" class="form-control" id="proy_calles_badge" name="proy_calles_badge" value="<?php echo htmlspecialchars($contenido_proyectos['calles_badge'] ?? 'Evidencia con direccion de referencia', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_calles_texto" class="form-label fw-bold small text-dark">Texto General:</label>
                              <textarea class="form-control" id="proy_calles_texto" name="proy_calles_texto" rows="2"><?php echo htmlspecialchars($contenido_proyectos['calles_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                        <!-- Tarjeta 1 Calles -->
                        <div class="card p-3 border mb-3 rounded-3">
                          <h6 class="fw-bold text-primary mb-3"><i class="bi bi-1-circle-fill me-1"></i> Tarjeta 1 (Calles)</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-bold small text-dark">Imagen Tarjeta 1 (Híbrida):</label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="proy_c1_img_texto" name="proy_c1_img_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['c1_img'] ?? 'imagen1.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre...">
                                <input type="file" id="proy_c1_img_file" name="proy_c1_img_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('proy_c1_img_file').click();"><i class="bi bi-upload"></i> Subir</button>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <label for="proy_c1_badge" class="form-label fw-bold small text-dark">Botón Decorativo:</label>
                              <input type="text" class="form-control" id="proy_c1_badge" name="proy_c1_badge" value="<?php echo htmlspecialchars($contenido_proyectos['c1_badge'] ?? 'Pavimentacion', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_c1_titulo" class="form-label fw-bold small text-dark">Título:</label>
                              <input type="text" class="form-control" id="proy_c1_titulo" name="proy_c1_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['c1_titulo'] ?? 'Mejoramiento vial barrio Las Palmas', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_c1_vinetas" class="form-label fw-bold small text-dark">Viñetas de evidencia (una por línea):</label>
                              <textarea class="form-control font-monospace" id="proy_c1_vinetas" name="proy_c1_vinetas" rows="3"><?php echo htmlspecialchars($contenido_proyectos['c1_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                        <!-- Tarjeta 2 Calles -->
                        <div class="card p-3 border mb-3 rounded-3">
                          <h6 class="fw-bold text-primary mb-3"><i class="bi bi-2-circle-fill me-1"></i> Tarjeta 2 (Calles)</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-bold small text-dark">Imagen Tarjeta 2 (Híbrida):</label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="proy_c2_img_texto" name="proy_c2_img_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['c2_img'] ?? 'imagen2.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre...">
                                <input type="file" id="proy_c2_img_file" name="proy_c2_img_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('proy_c2_img_file').click();"><i class="bi bi-upload"></i> Subir</button>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <label for="proy_c2_badge" class="form-label fw-bold small text-dark">Botón Decorativo:</label>
                              <input type="text" class="form-control" id="proy_c2_badge" name="proy_c2_badge" value="<?php echo htmlspecialchars($contenido_proyectos['c2_badge'] ?? 'Andenes', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_c2_titulo" class="form-label fw-bold small text-dark">Título:</label>
                              <input type="text" class="form-control" id="proy_c2_titulo" name="proy_c2_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['c2_titulo'] ?? 'Andenes y sardineles sector Centro', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_c2_vinetas" class="form-label fw-bold small text-dark">Viñetas de evidencia (una por línea):</label>
                              <textarea class="form-control font-monospace" id="proy_c2_vinetas" name="proy_c2_vinetas" rows="3"><?php echo htmlspecialchars($contenido_proyectos['c2_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                        <!-- Tarjeta 3 Calles -->
                        <div class="card p-3 border mb-2 rounded-3">
                          <h6 class="fw-bold text-primary mb-3"><i class="bi bi-3-circle-fill me-1"></i> Tarjeta 3 (Calles)</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-bold small text-dark">Imagen Tarjeta 3 (Híbrida):</label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="proy_c3_img_texto" name="proy_c3_img_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['c3_img'] ?? 'imagen3.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre...">
                                <input type="file" id="proy_c3_img_file" name="proy_c3_img_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('proy_c3_img_file').click();"><i class="bi bi-upload"></i> Subir</button>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <label for="proy_c3_badge" class="form-label fw-bold small text-dark">Botón Decorativo:</label>
                              <input type="text" class="form-control" id="proy_c3_badge" name="proy_c3_badge" value="<?php echo htmlspecialchars($contenido_proyectos['c3_badge'] ?? 'Via urbana', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_c3_titulo" class="form-label fw-bold small text-dark">Título:</label>
                              <input type="text" class="form-control" id="proy_c3_titulo" name="proy_c3_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['c3_titulo'] ?? 'Adecuacion vial barrio El Jardin', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_c3_vinetas" class="form-label fw-bold small text-dark">Viñetas de evidencia (una por línea):</label>
                              <textarea class="form-control font-monospace" id="proy_c3_vinetas" name="proy_c3_vinetas" rows="3"><?php echo htmlspecialchars($contenido_proyectos['c3_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 4 - BLOQUE 4 (TUBERÍAS Y REDES)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingProyTuberias">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProyTuberias" aria-expanded="false" aria-controls="collapseProyTuberias">
                        <i class="bi bi-fire text-warning me-2 fs-5"></i>
                        <span>Bloque 4: Tuberías y Redes (2 Tarjetas)</span>
                      </button>
                    </h2>
                    <div id="collapseProyTuberias" class="accordion-collapse collapse" aria-labelledby="headingProyTuberias" data-bs-parent="#accordionEditorProyectos">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="card p-3 bg-light border-0 mb-4 rounded-3">
                          <h6 class="fw-bold text-dark mb-3"><i class="bi bi-card-heading text-warning me-2"></i>Cabecera de la Sección</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label for="proy_tuberias_titulo" class="form-label fw-bold small text-dark">Título de Sección:</label>
                              <input type="text" class="form-control" id="proy_tuberias_titulo" name="proy_tuberias_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['tuberias_titulo'] ?? 'Tuberias y redes', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-6">
                              <label for="proy_tuberias_badge" class="form-label fw-bold small text-dark">Botón Decorativo (Badge de Cabecera):</label>
                              <input type="text" class="form-control" id="proy_tuberias_badge" name="proy_tuberias_badge" value="<?php echo htmlspecialchars($contenido_proyectos['tuberias_badge'] ?? 'Redes instaladas y verificadas', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_tuberias_texto" class="form-label fw-bold small text-dark">Texto General:</label>
                              <textarea class="form-control" id="proy_tuberias_texto" name="proy_tuberias_texto" rows="2"><?php echo htmlspecialchars($contenido_proyectos['tuberias_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                        <!-- Tarjeta 1 Tuberías -->
                        <div class="card p-3 border mb-3 rounded-3">
                          <h6 class="fw-bold text-primary mb-3"><i class="bi bi-1-circle-fill me-1"></i> Tarjeta 1 (Tuberías)</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-bold small text-dark">Imagen Tarjeta 1 (Híbrida):</label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="proy_t1_img_texto" name="proy_t1_img_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['t1_img'] ?? 'imagen4.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre...">
                                <input type="file" id="proy_t1_img_file" name="proy_t1_img_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('proy_t1_img_file').click();"><i class="bi bi-upload"></i> Subir</button>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <label for="proy_t1_badge" class="form-label fw-bold small text-dark">Botón Decorativo:</label>
                              <input type="text" class="form-control" id="proy_t1_badge" name="proy_t1_badge" value="<?php echo htmlspecialchars($contenido_proyectos['t1_badge'] ?? 'Redes de gas', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_t1_titulo" class="form-label fw-bold small text-dark">Título:</label>
                              <input type="text" class="form-control" id="proy_t1_titulo" name="proy_t1_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['t1_titulo'] ?? 'Instalacion de red interna residencial', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_t1_vinetas" class="form-label fw-bold small text-dark">Viñetas de evidencia (una por línea):</label>
                              <textarea class="form-control font-monospace" id="proy_t1_vinetas" name="proy_t1_vinetas" rows="3"><?php echo htmlspecialchars($contenido_proyectos['t1_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                        <!-- Tarjeta 2 Tuberías -->
                        <div class="card p-3 border mb-2 rounded-3">
                          <h6 class="fw-bold text-primary mb-3"><i class="bi bi-2-circle-fill me-1"></i> Tarjeta 2 (Tuberías)</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-bold small text-dark">Imagen Tarjeta 2 (Híbrida):</label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="proy_t2_img_texto" name="proy_t2_img_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['t2_img'] ?? 'imagen2.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre...">
                                <input type="file" id="proy_t2_img_file" name="proy_t2_img_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('proy_t2_img_file').click();"><i class="bi bi-upload"></i> Subir</button>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <label for="proy_t2_badge" class="form-label fw-bold small text-dark">Botón Decorativo:</label>
                              <input type="text" class="form-control" id="proy_t2_badge" name="proy_t2_badge" value="<?php echo htmlspecialchars($contenido_proyectos['t2_badge'] ?? 'Red hidraulica', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_t2_titulo" class="form-label fw-bold small text-dark">Título:</label>
                              <input type="text" class="form-control" id="proy_t2_titulo" name="proy_t2_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['t2_titulo'] ?? 'Rehabilitacion de tuberia principal', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_t2_vinetas" class="form-label fw-bold small text-dark">Viñetas de evidencia (una por línea):</label>
                              <textarea class="form-control font-monospace" id="proy_t2_vinetas" name="proy_t2_vinetas" rows="3"><?php echo htmlspecialchars($contenido_proyectos['t2_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 5 - BLOQUE 5 (EVIDENCIAS Y REGISTRO FOTOGRÁFICO)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingProyEvidencias">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProyEvidencias" aria-expanded="false" aria-controls="collapseProyEvidencias">
                        <i class="bi bi-camera-fill text-warning me-2 fs-5"></i>
                        <span>Bloque 5: Evidencias y Registro Fotográfico (4 Tarjetas)</span>
                      </button>
                    </h2>
                    <div id="collapseProyEvidencias" class="accordion-collapse collapse" aria-labelledby="headingProyEvidencias" data-bs-parent="#accordionEditorProyectos">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="card p-3 bg-light border-0 mb-4 rounded-3">
                          <h6 class="fw-bold text-dark mb-3"><i class="bi bi-card-heading text-warning me-2"></i>Cabecera y Botones de Evidencias</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label for="proy_evidencias_badge1" class="form-label fw-bold small text-dark">Botón Decorativo Superior 1:</label>
                              <input type="text" class="form-control" id="proy_evidencias_badge1" name="proy_evidencias_badge1" value="<?php echo htmlspecialchars($contenido_proyectos['evidencias_badge1'] ?? 'Galería Técnica', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-6">
                              <label for="proy_evidencias_badge2" class="form-label fw-bold small text-dark">Botón Decorativo Superior 2:</label>
                              <input type="text" class="form-control" id="proy_evidencias_badge2" name="proy_evidencias_badge2" value="<?php echo htmlspecialchars($contenido_proyectos['evidencias_badge2'] ?? 'Control de Calidad', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-6">
                              <label for="proy_evidencias_titulo" class="form-label fw-bold small text-dark">Título de Sección:</label>
                              <input type="text" class="form-control" id="proy_evidencias_titulo" name="proy_evidencias_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['evidencias_titulo'] ?? 'Evidencias y Registro Fotográfico', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-6">
                              <label for="proy_evidencias_badge3" class="form-label fw-bold small text-dark">Botón Decorativo Inferior (Badge):</label>
                              <input type="text" class="form-control" id="proy_evidencias_badge3" name="proy_evidencias_badge3" value="<?php echo htmlspecialchars($contenido_proyectos['evidencias_badge3'] ?? 'Auditoría y trazabilidad en cada fase del proyecto', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_evidencias_texto" class="form-label fw-bold small text-dark">Texto General:</label>
                              <textarea class="form-control" id="proy_evidencias_texto" name="proy_evidencias_texto" rows="2"><?php echo htmlspecialchars($contenido_proyectos['evidencias_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                        <!-- Tarjeta 1 Evidencias -->
                        <div class="card p-3 border mb-3 rounded-3">
                          <h6 class="fw-bold text-primary mb-3"><i class="bi bi-1-circle-fill me-1"></i> Tarjeta 1 (Evidencias)</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-bold small text-dark">Imagen Tarjeta 1 (Híbrida):</label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="proy_e1_img_texto" name="proy_e1_img_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['e1_img'] ?? 'imagen 44.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre...">
                                <input type="file" id="proy_e1_img_file" name="proy_e1_img_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('proy_e1_img_file').click();"><i class="bi bi-upload"></i> Subir</button>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <label for="proy_e1_badge" class="form-label fw-bold small text-dark">Botón Decorativo:</label>
                              <input type="text" class="form-control" id="proy_e1_badge" name="proy_e1_badge" value="<?php echo htmlspecialchars($contenido_proyectos['e1_badge'] ?? 'Obra Civil', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_e1_titulo" class="form-label fw-bold small text-dark">Título:</label>
                              <input type="text" class="form-control" id="proy_e1_titulo" name="proy_e1_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['e1_titulo'] ?? 'Pavimentación y Control de Asfalto', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_e1_vinetas" class="form-label fw-bold small text-dark">Viñetas de evidencia (una por línea):</label>
                              <textarea class="form-control font-monospace" id="proy_e1_vinetas" name="proy_e1_vinetas" rows="3"><?php echo htmlspecialchars($contenido_proyectos['e1_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                        <!-- Tarjeta 2 Evidencias -->
                        <div class="card p-3 border mb-3 rounded-3">
                          <h6 class="fw-bold text-primary mb-3"><i class="bi bi-2-circle-fill me-1"></i> Tarjeta 2 (Evidencias)</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-bold small text-dark">Imagen Tarjeta 2 (Híbrida):</label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="proy_e2_img_texto" name="proy_e2_img_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['e2_img'] ?? 'imagen3.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre...">
                                <input type="file" id="proy_e2_img_file" name="proy_e2_img_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('proy_e2_img_file').click();"><i class="bi bi-upload"></i> Subir</button>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <label for="proy_e2_badge" class="form-label fw-bold small text-dark">Botón Decorativo:</label>
                              <input type="text" class="form-control" id="proy_e2_badge" name="proy_e2_badge" value="<?php echo htmlspecialchars($contenido_proyectos['e2_badge'] ?? 'Red Hidráulica', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_e2_titulo" class="form-label fw-bold small text-dark">Título:</label>
                              <input type="text" class="form-control" id="proy_e2_titulo" name="proy_e2_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['e2_titulo'] ?? 'Canalización e Instalación Subterránea', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_e2_vinetas" class="form-label fw-bold small text-dark">Viñetas de evidencia (una por línea):</label>
                              <textarea class="form-control font-monospace" id="proy_e2_vinetas" name="proy_e2_vinetas" rows="3"><?php echo htmlspecialchars($contenido_proyectos['e2_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                        <!-- Tarjeta 3 Evidencias -->
                        <div class="card p-3 border mb-3 rounded-3">
                          <h6 class="fw-bold text-primary mb-3"><i class="bi bi-3-circle-fill me-1"></i> Tarjeta 3 (Evidencias)</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-bold small text-dark">Imagen Tarjeta 3 (Híbrida):</label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="proy_e3_img_texto" name="proy_e3_img_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['e3_img'] ?? 'imagen 22.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre...">
                                <input type="file" id="proy_e3_img_file" name="proy_e3_img_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('proy_e3_img_file').click();"><i class="bi bi-upload"></i> Subir</button>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <label for="proy_e3_badge" class="form-label fw-bold small text-dark">Botón Decorativo:</label>
                              <input type="text" class="form-control" id="proy_e3_badge" name="proy_e3_badge" value="<?php echo htmlspecialchars($contenido_proyectos['e3_badge'] ?? 'Redes de Gas', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_e3_titulo" class="form-label fw-bold small text-dark">Título:</label>
                              <input type="text" class="form-control" id="proy_e3_titulo" name="proy_e3_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['e3_titulo'] ?? 'Empalme y Válvulas de Seguridad', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_e3_vinetas" class="form-label fw-bold small text-dark">Viñetas de evidencia (una por línea):</label>
                              <textarea class="form-control font-monospace" id="proy_e3_vinetas" name="proy_e3_vinetas" rows="3"><?php echo htmlspecialchars($contenido_proyectos['e3_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                        <!-- Tarjeta 4 Evidencias -->
                        <div class="card p-3 border mb-2 rounded-3">
                          <h6 class="fw-bold text-primary mb-3"><i class="bi bi-4-circle-fill me-1"></i> Tarjeta 4 (Evidencias)</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-bold small text-dark">Imagen Tarjeta 4 (Híbrida):</label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="proy_e4_img_texto" name="proy_e4_img_nombre" value="<?php echo htmlspecialchars($contenido_proyectos['e4_img'] ?? 'imagen 33.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre...">
                                <input type="file" id="proy_e4_img_file" name="proy_e4_img_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('proy_e4_img_file').click();"><i class="bi bi-upload"></i> Subir</button>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <label for="proy_e4_badge" class="form-label fw-bold small text-dark">Botón Decorativo:</label>
                              <input type="text" class="form-control" id="proy_e4_badge" name="proy_e4_badge" value="<?php echo htmlspecialchars($contenido_proyectos['e4_badge'] ?? 'Pavimentación', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_e4_titulo" class="form-label fw-bold small text-dark">Título:</label>
                              <input type="text" class="form-control" id="proy_e4_titulo" name="proy_e4_titulo" value="<?php echo htmlspecialchars($contenido_proyectos['e4_titulo'] ?? 'Entrega Final y Señalización Vial', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                              <label for="proy_e4_vinetas" class="form-label fw-bold small text-dark">Viñetas de evidencia (una por línea):</label>
                              <textarea class="form-control font-monospace" id="proy_e4_vinetas" name="proy_e4_vinetas" rows="3"><?php echo htmlspecialchars($contenido_proyectos['e4_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                </div>

                <!-- Botón flotante para deslizar rápidamente al inicio del formulario -->
                <button type="button" class="btn-slide-top shadow" title="Deslizar arriba" onclick="this.closest('.form-scroll-container').scrollTo({top: 0, behavior: 'smooth'});">
                  <i class="bi bi-chevron-up"></i>
                </button>

              </div>

              <!-- Footer del Modal (Fijo en la base con flex-shrink-0) -->
              <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center border-top flex-shrink-0">
                <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal">
                  <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>
                <button type="submit" name="guardar_proyectos" class="btn-corp-yellow px-4 py-2">
                  <i class="bi bi-check2-circle me-1 fs-5"></i>
                  <span>Guardar Cambios de Proyectos</span>
                </button>
              </div>

            </form>

          </div>
        </div>
      </div>

      <!-- ==========================================================================
           MODAL EXTRA GRANDE (modal-xl): EDICIÓN DE SECCIÓN NOSOTROS
           ========================================================================== -->
      <div class="modal fade" id="modalEditarNosotros" tabindex="-1" aria-labelledby="modalEditarNosotrosLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-height: 92vh;">
          <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; max-height: 92vh; display: flex; flex-direction: column;">
            
            <!-- Header del Modal -->
            <div class="modal-header modal-header-corp py-3 px-4 flex-shrink-0">
              <div class="d-flex align-items-center gap-3">
                <div class="brand-icon-box" style="width: 40px; height: 40px; font-size: 13px;">M&M</div>
                <div>
                  <h5 class="modal-title fw-bold text-white mb-0" id="modalEditarNosotrosLabel">
                    <i class="bi bi-people-fill text-warning me-2"></i>Gestión de Contenido: Sección NOSOTROS
                  </h5>
                  <small class="text-white-50">Edita en tiempo real el carrusel, información corporativa, misión, visión, valores y métricas</small>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark text-warning border border-warning-subtle small d-none d-md-inline-flex align-items-center gap-1 py-2 px-3">
                  <i class="bi bi-arrows-vertical"></i> Deslizamiento Activo
                </span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
            </div>

            <!-- Formulario con soporte para subida de archivos y enlaces directos -->
            <form method="POST" action="" enctype="multipart/form-data" class="d-flex flex-column h-100 overflow-hidden m-0 p-0" style="min-height: 0; flex: 1 1 auto;">
              
              <div class="modal-body p-4 bg-light form-scroll-container">
                
                <!-- Barra de Navegación Rápida con Deslizamiento Suave (Quick Slide) -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 px-3 bg-white rounded-3 border shadow-sm">
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                      <i class="bi bi-arrows-vertical me-1"></i> Deslizar a:
                    </span>
                    <span class="small text-muted fw-semibold d-none d-sm-inline">Navegación rápida institucional</span>
                  </div>
                  <div class="d-flex align-items-center gap-1 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionNos('headingNosCarrusel', '#collapseNosCarrusel')">
                      <i class="bi bi-images me-1 text-warning"></i>1. Carrusel
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionNos('headingNosQuienes', '#collapseNosQuienes')">
                      <i class="bi bi-person-badge me-1 text-warning"></i>2. Quiénes Somos
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionNos('headingNosMisionVision', '#collapseNosMisionVision')">
                      <i class="bi bi-compass me-1 text-warning"></i>3. Misión y Visión
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionNos('headingNosValores', '#collapseNosValores')">
                      <i class="bi bi-shield-check me-1 text-warning"></i>4. Valores
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionNos('headingNosMetricas', '#collapseNosMetricas')">
                      <i class="bi bi-bar-chart-line me-1 text-warning"></i>5. Métricas
                    </button>
                  </div>
                </div>

                <div class="accordion accordion-corp" id="accordionEditorNosotros">
                  
                  <!-- ================================================================
                       ACORDEÓN 1 - BLOQUE 1 (CARRUSEL)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingNosCarrusel">
                      <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNosCarrusel" aria-expanded="true" aria-controls="collapseNosCarrusel">
                        <i class="bi bi-images text-warning me-2 fs-5"></i>
                        <span>Bloque 1: Carrusel de Encabezado (3 Imágenes)</span>
                      </button>
                    </h2>
                    <div id="collapseNosCarrusel" class="accordion-collapse collapse show" aria-labelledby="headingNosCarrusel" data-bs-parent="#accordionEditorNosotros">
                      <div class="accordion-body p-4 bg-white">
                        <p class="text-muted small mb-4">
                          <i class="bi bi-info-circle me-1 text-primary"></i>
                          Puedes <strong>pegar una URL externa directa</strong> (Imgur, Cloudinary, etc.) o pulsar <strong>"Subir"</strong> para elegir una imagen de tu computadora.
                        </p>

                        <div class="row g-3">
                          <!-- Imagen 1 -->
                          <div class="col-md-4">
                            <div class="card p-3 border rounded-3 h-100">
                              <label class="form-label fw-bold small text-dark mb-1">
                                <i class="bi bi-image text-warning me-1"></i>Imagen 1 (Hero):
                              </label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="nos_carrusel_img1_texto" name="nos_carrusel_img1_nombre" value="<?php echo htmlspecialchars($contenido_nosotros['carrusel_img1'] ?? 'imagen 22.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre de archivo...">
                                <input type="file" id="nos_carrusel_img1_file" name="nos_carrusel_img1_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('nos_carrusel_img1_file').click();" title="Subir desde PC">
                                  <i class="bi bi-upload"></i> Subir
                                </button>
                              </div>
                              <small class="text-muted mt-2" style="font-size: 0.75rem;">Archivo actual o enlace guardado</small>
                            </div>
                          </div>

                          <!-- Imagen 2 -->
                          <div class="col-md-4">
                            <div class="card p-3 border rounded-3 h-100">
                              <label class="form-label fw-bold small text-dark mb-1">
                                <i class="bi bi-image text-warning me-1"></i>Imagen 2 (Hero):
                              </label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="nos_carrusel_img2_texto" name="nos_carrusel_img2_nombre" value="<?php echo htmlspecialchars($contenido_nosotros['carrusel_img2'] ?? 'imagen 44.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre de archivo...">
                                <input type="file" id="nos_carrusel_img2_file" name="nos_carrusel_img2_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('nos_carrusel_img2_file').click();" title="Subir desde PC">
                                  <i class="bi bi-upload"></i> Subir
                                </button>
                              </div>
                              <small class="text-muted mt-2" style="font-size: 0.75rem;">Archivo actual o enlace guardado</small>
                            </div>
                          </div>

                          <!-- Imagen 3 -->
                          <div class="col-md-4">
                            <div class="card p-3 border rounded-3 h-100">
                              <label class="form-label fw-bold small text-dark mb-1">
                                <i class="bi bi-image text-warning me-1"></i>Imagen 3 (Hero):
                              </label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="nos_carrusel_img3_texto" name="nos_carrusel_img3_nombre" value="<?php echo htmlspecialchars($contenido_nosotros['carrusel_img3'] ?? 'imagen 33.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre de archivo...">
                                <input type="file" id="nos_carrusel_img3_file" name="nos_carrusel_img3_file" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('nos_carrusel_img3_file').click();" title="Subir desde PC">
                                  <i class="bi bi-upload"></i> Subir
                                </button>
                              </div>
                              <small class="text-muted mt-2" style="font-size: 0.75rem;">Archivo actual o enlace guardado</small>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 2 - BLOQUE 2 (¿QUIÉNES SOMOS?)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingNosQuienes">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNosQuienes" aria-expanded="false" aria-controls="collapseNosQuienes">
                        <i class="bi bi-person-badge text-warning me-2 fs-5"></i>
                        <span>Bloque 2: ¿Quiénes Somos? (Información Institucional)</span>
                      </button>
                    </h2>
                    <div id="collapseNosQuienes" class="accordion-collapse collapse" aria-labelledby="headingNosQuienes" data-bs-parent="#accordionEditorNosotros">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="row g-3">
                          <!-- Imagen Principal Híbrida -->
                          <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">
                              <i class="bi bi-image text-warning me-1"></i>Imagen Principal (Híbrida):
                            </label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="nos_qs_imagen_texto" name="nos_qs_imagen_nombre" value="<?php echo htmlspecialchars($contenido_nosotros['qs_imagen'] ?? 'imagen4.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL o nombre...">
                              <input type="file" id="nos_qs_imagen_file" name="nos_qs_imagen_file" class="d-none" accept="image/*">
                              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('nos_qs_imagen_file').click();"><i class="bi bi-upload"></i> Subir</button>
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">Fotografía del equipo u obra institucional</small>
                          </div>

                          <!-- Título -->
                          <div class="col-md-6">
                            <label for="nos_qs_titulo" class="form-label fw-bold small text-dark">Título de la Sección:</label>
                            <input type="text" class="form-control" id="nos_qs_titulo" name="nos_qs_titulo" value="<?php echo htmlspecialchars($contenido_nosotros['qs_titulo'] ?? '¿Quiénes somos?', ENT_QUOTES, 'UTF-8'); ?>">
                          </div>

                          <!-- Texto General -->
                          <div class="col-12">
                            <label for="nos_qs_texto" class="form-label fw-bold small text-dark">Texto General:</label>
                            <textarea class="form-control" id="nos_qs_texto" name="nos_qs_texto" rows="4"><?php echo htmlspecialchars($contenido_nosotros['qs_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                          </div>

                          <!-- Lista de Características -->
                          <div class="col-12">
                            <label for="nos_qs_vinetas" class="form-label fw-bold small text-dark">
                              <i class="bi bi-check-circle-fill text-gold me-1"></i>Lista de Características (una por línea):
                            </label>
                            <textarea class="form-control font-monospace" id="nos_qs_vinetas" name="nos_qs_vinetas" rows="4" placeholder="+15 años de experiencia&#10;Cumplimiento normativo&#10;Personal certificado&#10;Proyectos a tiempo"><?php echo htmlspecialchars($contenido_nosotros['qs_vinetas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <small class="text-muted" style="font-size: 0.75rem;">Escribe una característica por línea (ej: +15 años de experiencia, Cumplimiento normativo, Personal certificado, Proyectos a tiempo). En la web se renderizarán como viñetas con diseño original.</small>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 3 - BLOQUE 3 (MISIÓN Y VISIÓN)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingNosMisionVision">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNosMisionVision" aria-expanded="false" aria-controls="collapseNosMisionVision">
                        <i class="bi bi-compass text-warning me-2 fs-5"></i>
                        <span>Bloque 3: Misión y Visión (2 Tarjetas)</span>
                      </button>
                    </h2>
                    <div id="collapseNosMisionVision" class="accordion-collapse collapse" aria-labelledby="headingNosMisionVision" data-bs-parent="#accordionEditorNosotros">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="row g-4">
                          <!-- Tarjeta 1: Misión -->
                          <div class="col-md-6">
                            <div class="card p-3 border-start border-4 border-warning bg-light h-100 rounded-3">
                              <h6 class="fw-bold text-navy mb-3"><i class="bi bi-compass-fill text-warning me-2"></i>Tarjeta 1: Misión</h6>
                              <div class="mb-3">
                                <label for="nos_mision_titulo" class="form-label fw-bold small text-dark">Título:</label>
                                <input type="text" class="form-control" id="nos_mision_titulo" name="nos_mision_titulo" value="<?php echo htmlspecialchars($contenido_nosotros['mision_titulo'] ?? 'Misión', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>
                              <div>
                                <label for="nos_mision_texto" class="form-label fw-bold small text-dark">Texto de la Misión:</label>
                                <textarea class="form-control" id="nos_mision_texto" name="nos_mision_texto" rows="4"><?php echo htmlspecialchars($contenido_nosotros['mision_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>
                            </div>
                          </div>

                          <!-- Tarjeta 2: Visión -->
                          <div class="col-md-6">
                            <div class="card p-3 border-start border-4 border-primary bg-light h-100 rounded-3">
                              <h6 class="fw-bold text-navy mb-3"><i class="bi bi-eye-fill text-primary me-2"></i>Tarjeta 2: Visión</h6>
                              <div class="mb-3">
                                <label for="nos_vision_titulo" class="form-label fw-bold small text-dark">Título:</label>
                                <input type="text" class="form-control" id="nos_vision_titulo" name="nos_vision_titulo" value="<?php echo htmlspecialchars($contenido_nosotros['vision_titulo'] ?? 'Visión', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>
                              <div>
                                <label for="nos_vision_texto" class="form-label fw-bold small text-dark">Texto de la Visión:</label>
                                <textarea class="form-control" id="nos_vision_texto" name="nos_vision_texto" rows="4"><?php echo htmlspecialchars($contenido_nosotros['vision_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 4 - BLOQUE 4 (NUESTROS VALORES)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingNosValores">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNosValores" aria-expanded="false" aria-controls="collapseNosValores">
                        <i class="bi bi-shield-check text-warning me-2 fs-5"></i>
                        <span>Bloque 4: Nuestros Valores (Cabecera y 4 Mini-Tarjetas)</span>
                      </button>
                    </h2>
                    <div id="collapseNosValores" class="accordion-collapse collapse" aria-labelledby="headingNosValores" data-bs-parent="#accordionEditorNosotros">
                      <div class="accordion-body p-4 bg-white">
                        
                        <!-- Cabecera de Valores -->
                        <div class="card p-3 bg-light border-0 mb-4 rounded-3">
                          <h6 class="fw-bold text-dark mb-3"><i class="bi bi-card-heading text-warning me-2"></i>Cabecera Principal de Valores</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label for="nos_valores_titulo" class="form-label fw-bold small text-dark">Título Principal:</label>
                              <input type="text" class="form-control" id="nos_valores_titulo" name="nos_valores_titulo" value="<?php echo htmlspecialchars($contenido_nosotros['valores_titulo'] ?? 'Nuestros Valores', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-6">
                              <label for="nos_valores_subtitulo" class="form-label fw-bold small text-dark">Subtítulo Descriptivo:</label>
                              <textarea class="form-control" id="nos_valores_subtitulo" name="nos_valores_subtitulo" rows="2"><?php echo htmlspecialchars($contenido_nosotros['valores_subtitulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                          </div>
                        </div>

                        <!-- 4 Mini-Tarjetas de Valores -->
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-grid-fill text-warning me-2"></i>4 Mini-Tarjetas de Valores</h6>
                        <div class="row g-3">
                          <!-- Valor 1: Seguridad -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-shield-check text-warning fs-5"></i>
                                <span class="fw-bold text-navy small">Valor 1</span>
                              </div>
                              <div class="mb-2">
                                <label for="nos_v1_titulo" class="form-label fw-bold small text-dark">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="nos_v1_titulo" name="nos_v1_titulo" value="<?php echo htmlspecialchars($contenido_nosotros['v1_titulo'] ?? 'Seguridad', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>
                              <div>
                                <label for="nos_v1_texto" class="form-label fw-bold small text-dark">Texto:</label>
                                <textarea class="form-control form-control-sm" id="nos_v1_texto" name="nos_v1_texto" rows="3"><?php echo htmlspecialchars($contenido_nosotros['v1_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>
                            </div>
                          </div>

                          <!-- Valor 2: Calidad -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-award-fill text-warning fs-5"></i>
                                <span class="fw-bold text-navy small">Valor 2</span>
                              </div>
                              <div class="mb-2">
                                <label for="nos_v2_titulo" class="form-label fw-bold small text-dark">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="nos_v2_titulo" name="nos_v2_titulo" value="<?php echo htmlspecialchars($contenido_nosotros['v2_titulo'] ?? 'Calidad', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>
                              <div>
                                <label for="nos_v2_texto" class="form-label fw-bold small text-dark">Texto:</label>
                                <textarea class="form-control form-control-sm" id="nos_v2_texto" name="nos_v2_texto" rows="3"><?php echo htmlspecialchars($contenido_nosotros['v2_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>
                            </div>
                          </div>

                          <!-- Valor 3: Cumplimiento -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-clock-history text-warning fs-5"></i>
                                <span class="fw-bold text-navy small">Valor 3</span>
                              </div>
                              <div class="mb-2">
                                <label for="nos_v3_titulo" class="form-label fw-bold small text-dark">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="nos_v3_titulo" name="nos_v3_titulo" value="<?php echo htmlspecialchars($contenido_nosotros['v3_titulo'] ?? 'Cumplimiento', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>
                              <div>
                                <label for="nos_v3_texto" class="form-label fw-bold small text-dark">Texto:</label>
                                <textarea class="form-control form-control-sm" id="nos_v3_texto" name="nos_v3_texto" rows="3"><?php echo htmlspecialchars($contenido_nosotros['v3_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>
                            </div>
                          </div>

                          <!-- Valor 4: Sostenibilidad -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-tree-fill text-warning fs-5"></i>
                                <span class="fw-bold text-navy small">Valor 4</span>
                              </div>
                              <div class="mb-2">
                                <label for="nos_v4_titulo" class="form-label fw-bold small text-dark">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="nos_v4_titulo" name="nos_v4_titulo" value="<?php echo htmlspecialchars($contenido_nosotros['v4_titulo'] ?? 'Sostenibilidad', ENT_QUOTES, 'UTF-8'); ?>">
                              </div>
                              <div>
                                <label for="nos_v4_texto" class="form-label fw-bold small text-dark">Texto:</label>
                                <textarea class="form-control form-control-sm" id="nos_v4_texto" name="nos_v4_texto" rows="3"><?php echo htmlspecialchars($contenido_nosotros['v4_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              </div>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 5 - BLOQUE 5 (MÉTRICAS Y LOGROS - BANNER AZUL)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingNosMetricas">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNosMetricas" aria-expanded="false" aria-controls="collapseNosMetricas">
                        <i class="bi bi-bar-chart-line text-warning me-2 fs-5"></i>
                        <span>Bloque 5: Métricas y Logros (Banner Azul - 4 Grupos)</span>
                      </button>
                    </h2>
                    <div id="collapseNosMetricas" class="accordion-collapse collapse" aria-labelledby="headingNosMetricas" data-bs-parent="#accordionEditorNosotros">
                      <div class="accordion-body p-4 bg-white">
                        <p class="text-muted small mb-3">
                          <i class="bi bi-info-circle me-1 text-primary"></i>
                          Configura las 4 estadísticas numéricas y sus etiquetas que se muestran en el banner azul corporativo.
                        </p>

                        <div class="row g-3">
                          <!-- Métrica 1 -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100 bg-light">
                              <h6 class="fw-bold text-navy mb-2"><i class="bi bi-1-circle-fill text-warning me-1"></i> Métrica 1</h6>
                              <div class="mb-2">
                                <label for="nos_m1_valor" class="form-label fw-bold small text-dark">Valor (Cifra):</label>
                                <input type="text" class="form-control" id="nos_m1_valor" name="nos_m1_valor" value="<?php echo htmlspecialchars($contenido_nosotros['m1_valor'] ?? '+150', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ej: +150">
                              </div>
                              <div>
                                <label for="nos_m1_texto" class="form-label fw-bold small text-dark">Texto descriptivo:</label>
                                <input type="text" class="form-control" id="nos_m1_texto" name="nos_m1_texto" value="<?php echo htmlspecialchars($contenido_nosotros['m1_texto'] ?? 'Proyectos Ejecutados', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ej: Proyectos Ejecutados">
                              </div>
                            </div>
                          </div>

                          <!-- Métrica 2 -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100 bg-light">
                              <h6 class="fw-bold text-navy mb-2"><i class="bi bi-2-circle-fill text-warning me-1"></i> Métrica 2</h6>
                              <div class="mb-2">
                                <label for="nos_m2_valor" class="form-label fw-bold small text-dark">Valor (Cifra):</label>
                                <input type="text" class="form-control" id="nos_m2_valor" name="nos_m2_valor" value="<?php echo htmlspecialchars($contenido_nosotros['m2_valor'] ?? '+50 Km', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ej: +50 Km">
                              </div>
                              <div>
                                <label for="nos_m2_texto" class="form-label fw-bold small text-dark">Texto descriptivo:</label>
                                <input type="text" class="form-control" id="nos_m2_texto" name="nos_m2_texto" value="<?php echo htmlspecialchars($contenido_nosotros['m2_texto'] ?? 'Redes de Gas Instaladas', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ej: Redes de Gas Instaladas">
                              </div>
                            </div>
                          </div>

                          <!-- Métrica 3 -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100 bg-light">
                              <h6 class="fw-bold text-navy mb-2"><i class="bi bi-3-circle-fill text-warning me-1"></i> Métrica 3</h6>
                              <div class="mb-2">
                                <label for="nos_m3_valor" class="form-label fw-bold small text-dark">Valor (Cifra):</label>
                                <input type="text" class="form-control" id="nos_m3_valor" name="nos_m3_valor" value="<?php echo htmlspecialchars($contenido_nosotros['m3_valor'] ?? '100%', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ej: 100%">
                              </div>
                              <div>
                                <label for="nos_m3_texto" class="form-label fw-bold small text-dark">Texto descriptivo:</label>
                                <input type="text" class="form-control" id="nos_m3_texto" name="nos_m3_texto" value="<?php echo htmlspecialchars($contenido_nosotros['m3_texto'] ?? 'Certificación Normativa', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ej: Certificación Normativa">
                              </div>
                            </div>
                          </div>

                          <!-- Métrica 4 -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100 bg-light">
                              <h6 class="fw-bold text-navy mb-2"><i class="bi bi-4-circle-fill text-warning me-1"></i> Métrica 4</h6>
                              <div class="mb-2">
                                <label for="nos_m4_valor" class="form-label fw-bold small text-dark">Valor (Cifra):</label>
                                <input type="text" class="form-control" id="nos_m4_valor" name="nos_m4_valor" value="<?php echo htmlspecialchars($contenido_nosotros['m4_valor'] ?? '+15', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ej: +15">
                              </div>
                              <div>
                                <label for="nos_m4_texto" class="form-label fw-bold small text-dark">Texto descriptivo:</label>
                                <input type="text" class="form-control" id="nos_m4_texto" name="nos_m4_texto" value="<?php echo htmlspecialchars($contenido_nosotros['m4_texto'] ?? 'Años de Trayectoria', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ej: Años de Trayectoria">
                              </div>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                </div>

                <!-- Botón flotante para deslizar rápidamente al inicio del formulario -->
                <button type="button" class="btn-slide-top shadow" title="Deslizar arriba" onclick="this.closest('.form-scroll-container').scrollTo({top: 0, behavior: 'smooth'});">
                  <i class="bi bi-chevron-up"></i>
                </button>

              </div>

              <!-- Footer del Modal (Fijo en la base con flex-shrink-0) -->
              <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center border-top flex-shrink-0">
                <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal">
                  <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>
                <button type="submit" name="guardar_nosotros" class="btn-corp-yellow px-4 py-2">
                  <i class="bi bi-check2-circle me-1 fs-5"></i>
                  <span>Guardar Cambios de Nosotros</span>
                </button>
              </div>

            </form>

          </div>
        </div>
      </div>

      <!-- ==========================================================================
           MODAL EXTRA GRANDE (modal-xl): EDICIÓN DE SECCIÓN MAQUINARIA
           ========================================================================== -->
      <div class="modal fade" id="modalEditarMaquinaria" tabindex="-1" aria-labelledby="modalEditarMaquinariaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-height: 92vh;">
          <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; max-height: 92vh; display: flex; flex-direction: column;">
            
            <!-- Header del Modal -->
            <div class="modal-header modal-header-corp py-3 px-4 flex-shrink-0">
              <div class="d-flex align-items-center gap-3">
                <div class="brand-icon-box" style="width: 40px; height: 40px; font-size: 13px;">M&M</div>
                <div>
                  <h5 class="modal-title fw-bold text-white mb-0" id="modalEditarMaquinariaLabel">
                    <i class="bi bi-truck text-warning me-2"></i>Gestión de Contenido: Sección MAQUINARIA
                  </h5>
                  <small class="text-white-50">Edita en tiempo real el encabezado principal, las pestañas del catálogo y el respaldo técnico</small>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark text-warning border border-warning-subtle small d-none d-md-inline-flex align-items-center gap-1 py-2 px-3">
                  <i class="bi bi-shield-lock"></i> Protección Activa
                </span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
            </div>

            <!-- Formulario con soporte para subida de archivos y enlaces directos -->
            <form method="POST" action="" enctype="multipart/form-data" class="d-flex flex-column h-100 overflow-hidden m-0 p-0" style="min-height: 0; flex: 1 1 auto;">
              
              <div class="modal-body p-4 bg-light form-scroll-container">
                
                <!-- Barra de Navegación Rápida con Deslizamiento Suave (Quick Slide) -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 px-3 bg-white rounded-3 border shadow-sm">
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                      <i class="bi bi-arrows-vertical me-1"></i> Deslizar a:
                    </span>
                    <span class="small text-muted fw-semibold d-none d-sm-inline">Navegación rápida de maquinaria</span>
                  </div>
                  <div class="d-flex align-items-center gap-1 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionMaq('headingMaqHero', '#collapseMaqHero')">
                      <i class="bi bi-card-image me-1 text-warning"></i>1. Hero Principal
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionMaq('headingMaqTabs', '#collapseMaqTabs')">
                      <i class="bi bi-segmented-nav me-1 text-warning"></i>2. Filtros del Catálogo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionMaq('headingMaqGarantia', '#collapseMaqGarantia')">
                      <i class="bi bi-shield-fill-check me-1 text-warning"></i>3. Garantía y Respaldo
                    </button>
                  </div>
                </div>

                <div class="accordion accordion-corp" id="accordionEditorMaquinaria">
                  
                  <!-- ================================================================
                       ACORDEÓN 1 - BLOQUE 1 (HERO - ENCABEZADO PRINCIPAL)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingMaqHero">
                      <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMaqHero" aria-expanded="true" aria-controls="collapseMaqHero">
                        <i class="bi bi-card-image text-warning me-2 fs-5"></i>
                        <span>Bloque 1: Hero (Encabezado Principal de Maquinaria)</span>
                      </button>
                    </h2>
                    <div id="collapseMaqHero" class="accordion-collapse collapse show" aria-labelledby="headingMaqHero" data-bs-parent="#accordionEditorMaquinaria">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="row g-3">
                          <!-- Imagen de Fondo Híbrida -->
                          <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">
                              <i class="bi bi-image text-warning me-1"></i>Imagen de Fondo Hero (Híbrida):
                            </label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="maq_hero_imagen_texto" name="maq_hero_imagen_nombre" value="<?php echo htmlspecialchars($contenido_maquinaria['hero_imagen'] ?? 'imagen3.png', ENT_QUOTES, 'UTF-8'); ?>" placeholder="URL directa o nombre...">
                              <input type="file" id="maq_hero_imagen_file" name="maq_hero_imagen_file" class="d-none" accept="image/*">
                              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('maq_hero_imagen_file').click();" title="Subir desde PC">
                                <i class="bi bi-upload"></i> Subir
                              </button>
                            </div>
                            <div class="form-text small">Archivo local o enlace web de la imagen de fondo.</div>
                          </div>

                          <!-- Badge Superior -->
                          <div class="col-md-6">
                            <label for="maq_hero_badge" class="form-label fw-bold small text-dark">Badge Superior:</label>
                            <input type="text" class="form-control" id="maq_hero_badge" name="maq_hero_badge" maxlength="100" value="<?php echo htmlspecialchars($contenido_maquinaria['hero_badge'] ?? 'FLOTA PESADA Y EQUIPOS CERTIFICADOS', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-text small">Máx 100 caracteres.</div>
                          </div>

                          <!-- Título Principal -->
                          <div class="col-12">
                            <label for="maq_hero_titulo" class="form-label fw-bold small text-dark">Título Principal:</label>
                            <input type="text" class="form-control" id="maq_hero_titulo" name="maq_hero_titulo" maxlength="150" value="<?php echo htmlspecialchars($contenido_maquinaria['hero_titulo'] ?? 'FLOTA DE MAQUINARIA Y EQUIPOS', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-text small">Máx 150 caracteres.</div>
                          </div>

                          <!-- Texto Principal -->
                          <div class="col-12">
                            <label for="maq_hero_texto" class="form-label fw-bold small text-dark">Texto Principal:</label>
                            <textarea class="form-control" id="maq_hero_texto" name="maq_hero_texto" rows="3" maxlength="300"><?php echo htmlspecialchars($contenido_maquinaria['hero_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <div class="form-text small">Máx 300 caracteres.</div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 2 - BLOQUE 2 (FILTROS DEL CATÁLOGO - PESTAÑAS)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingMaqTabs">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMaqTabs" aria-expanded="false" aria-controls="collapseMaqTabs">
                        <i class="bi bi-segmented-nav text-warning me-2 fs-5"></i>
                        <span>Bloque 2: Filtros del Catálogo (Nombres de las 4 Pestañas)</span>
                      </button>
                    </h2>
                    <div id="collapseMaqTabs" class="accordion-collapse collapse" aria-labelledby="headingMaqTabs" data-bs-parent="#accordionEditorMaquinaria">
                      <div class="accordion-body p-4 bg-white">
                        <p class="text-muted small mb-3">
                          <i class="bi bi-info-circle me-1 text-primary"></i>
                          Personaliza las etiquetas de los botones de filtro superiores que organizan el catálogo técnico de maquinaria.
                        </p>

                        <div class="row g-3">
                          <!-- Pestaña 1 -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100 bg-light">
                              <label for="maq_tab1_nombre" class="form-label fw-bold small text-dark">Pestaña 1 (Todas):</label>
                              <input type="text" class="form-control" id="maq_tab1_nombre" name="maq_tab1_nombre" maxlength="50" value="<?php echo htmlspecialchars($contenido_maquinaria['tab1_nombre'] ?? 'Todas las Máquinas', ENT_QUOTES, 'UTF-8'); ?>">
                              <div class="form-text small">Máx 50 caracteres.</div>
                            </div>
                          </div>

                          <!-- Pestaña 2 -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100 bg-light">
                              <label for="maq_tab2_nombre" class="form-label fw-bold small text-dark">Pestaña 2 (Excavación):</label>
                              <input type="text" class="form-control" id="maq_tab2_nombre" name="maq_tab2_nombre" maxlength="50" value="<?php echo htmlspecialchars($contenido_maquinaria['tab2_nombre'] ?? 'Excavación y Tierras', ENT_QUOTES, 'UTF-8'); ?>">
                              <div class="form-text small">Máx 50 caracteres.</div>
                            </div>
                          </div>

                          <!-- Pestaña 3 -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100 bg-light">
                              <label for="maq_tab3_nombre" class="form-label fw-bold small text-dark">Pestaña 3 (Tuberías):</label>
                              <input type="text" class="form-control" id="maq_tab3_nombre" name="maq_tab3_nombre" maxlength="50" value="<?php echo htmlspecialchars($contenido_maquinaria['tab3_nombre'] ?? 'Tuberías y Redes de Gas', ENT_QUOTES, 'UTF-8'); ?>">
                              <div class="form-text small">Máx 50 caracteres.</div>
                            </div>
                          </div>

                          <!-- Pestaña 4 -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100 bg-light">
                              <label for="maq_tab4_nombre" class="form-label fw-bold small text-dark">Pestaña 4 (Transporte):</label>
                              <input type="text" class="form-control" id="maq_tab4_nombre" name="maq_tab4_nombre" maxlength="50" value="<?php echo htmlspecialchars($contenido_maquinaria['tab4_nombre'] ?? 'Carga y Transporte', ENT_QUOTES, 'UTF-8'); ?>">
                              <div class="form-text small">Máx 50 caracteres.</div>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 3 - BLOQUE 3 (GARANTÍA Y RESPALDO EN OBRA)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingMaqGarantia">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMaqGarantia" aria-expanded="false" aria-controls="collapseMaqGarantia">
                        <i class="bi bi-shield-fill-check text-warning me-2 fs-5"></i>
                        <span>Bloque 3: Garantía y Respaldo en Obra (Cabecera y 4 Mini-Tarjetas)</span>
                      </button>
                    </h2>
                    <div id="collapseMaqGarantia" class="accordion-collapse collapse" aria-labelledby="headingMaqGarantia" data-bs-parent="#accordionEditorMaquinaria">
                      <div class="accordion-body p-4 bg-white">
                        
                        <!-- Cabecera de Garantía -->
                        <div class="card p-3 bg-light border-0 mb-4 rounded-3">
                          <h6 class="fw-bold text-dark mb-3"><i class="bi bi-card-heading text-warning me-2"></i>Cabecera Principal de Respaldo</h6>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label for="maq_garantia_titulo" class="form-label fw-bold small text-dark">Título Principal:</label>
                              <input type="text" class="form-control" id="maq_garantia_titulo" name="maq_garantia_titulo" maxlength="100" value="<?php echo htmlspecialchars($contenido_maquinaria['garantia_titulo'] ?? 'Garantía y Respaldo en Obra', ENT_QUOTES, 'UTF-8'); ?>">
                              <div class="form-text small">Máx 100 caracteres.</div>
                            </div>
                            <div class="col-md-6">
                              <label for="maq_garantia_texto" class="form-label fw-bold small text-dark">Texto General:</label>
                              <textarea class="form-control" id="maq_garantia_texto" name="maq_garantia_texto" rows="2" maxlength="250"><?php echo htmlspecialchars($contenido_maquinaria['garantia_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                              <div class="form-text small">Máx 250 caracteres.</div>
                            </div>
                          </div>
                        </div>

                        <!-- 4 Mini-Tarjetas de Garantía -->
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-grid-fill text-warning me-2"></i>4 Mini-Tarjetas de Respaldo Técnico</h6>
                        <div class="row g-3">
                          <!-- Mini-Tarjeta 1: Mantenimiento -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-shield-fill-check text-warning fs-5"></i>
                                <span class="fw-bold text-navy small">Mantenimiento</span>
                              </div>
                              <div class="mb-2">
                                <label for="maq_g1_titulo" class="form-label fw-bold small text-dark">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="maq_g1_titulo" name="maq_g1_titulo" maxlength="100" value="<?php echo htmlspecialchars($contenido_maquinaria['g1_titulo'] ?? 'Mantenimiento Preventivo', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres.</div>
                              </div>
                              <div>
                                <label for="maq_g1_texto" class="form-label fw-bold small text-dark">Texto:</label>
                                <textarea class="form-control form-control-sm" id="maq_g1_texto" name="maq_g1_texto" rows="3" maxlength="200"><?php echo htmlspecialchars($contenido_maquinaria['g1_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <div class="form-text small">Máx 200 caracteres.</div>
                              </div>
                            </div>
                          </div>

                          <!-- Mini-Tarjeta 2: Operadores -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-person-check-fill text-warning fs-5"></i>
                                <span class="fw-bold text-navy small">Operadores</span>
                              </div>
                              <div class="mb-2">
                                <label for="maq_g2_titulo" class="form-label fw-bold small text-dark">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="maq_g2_titulo" name="maq_g2_titulo" maxlength="100" value="<?php echo htmlspecialchars($contenido_maquinaria['g2_titulo'] ?? 'Operadores Certificados', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres.</div>
                              </div>
                              <div>
                                <label for="maq_g2_texto" class="form-label fw-bold small text-dark">Texto:</label>
                                <textarea class="form-control form-control-sm" id="maq_g2_texto" name="maq_g2_texto" rows="3" maxlength="200"><?php echo htmlspecialchars($contenido_maquinaria['g2_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <div class="form-text small">Máx 200 caracteres.</div>
                              </div>
                            </div>
                          </div>

                          <!-- Mini-Tarjeta 3: Asistencia -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-clock-history text-warning fs-5"></i>
                                <span class="fw-bold text-navy small">Asistencia</span>
                              </div>
                              <div class="mb-2">
                                <label for="maq_g3_titulo" class="form-label fw-bold small text-dark">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="maq_g3_titulo" name="maq_g3_titulo" maxlength="100" value="<?php echo htmlspecialchars($contenido_maquinaria['g3_titulo'] ?? 'Asistencia 24/7', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres.</div>
                              </div>
                              <div>
                                <label for="maq_g3_texto" class="form-label fw-bold small text-dark">Texto:</label>
                                <textarea class="form-control form-control-sm" id="maq_g3_texto" name="maq_g3_texto" rows="3" maxlength="200"><?php echo htmlspecialchars($contenido_maquinaria['g3_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <div class="form-text small">Máx 200 caracteres.</div>
                              </div>
                            </div>
                          </div>

                          <!-- Mini-Tarjeta 4: Certificaciones -->
                          <div class="col-md-6 col-xl-3">
                            <div class="card p-3 border rounded-3 h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-file-earmark-text-fill text-warning fs-5"></i>
                                <span class="fw-bold text-navy small">Certificaciones</span>
                              </div>
                              <div class="mb-2">
                                <label for="maq_g4_titulo" class="form-label fw-bold small text-dark">Título:</label>
                                <input type="text" class="form-control form-control-sm" id="maq_g4_titulo" name="maq_g4_titulo" maxlength="100" value="<?php echo htmlspecialchars($contenido_maquinaria['g4_titulo'] ?? 'Certificaciones Vigentes', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres.</div>
                              </div>
                              <div>
                                <label for="maq_g4_texto" class="form-label fw-bold small text-dark">Texto:</label>
                                <textarea class="form-control form-control-sm" id="maq_g4_texto" name="maq_g4_texto" rows="3" maxlength="200"><?php echo htmlspecialchars($contenido_maquinaria['g4_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <div class="form-text small">Máx 200 caracteres.</div>
                              </div>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                </div>

                <!-- Botón flotante para deslizar rápidamente al inicio del formulario -->
                <button type="button" class="btn-slide-top shadow" title="Deslizar arriba" onclick="this.closest('.form-scroll-container').scrollTo({top: 0, behavior: 'smooth'});">
                  <i class="bi bi-chevron-up"></i>
                </button>

              </div>

              <!-- Footer del Modal (Fijo en la base con flex-shrink-0) -->
              <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center border-top flex-shrink-0">
                <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal">
                  <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>
                <button type="submit" name="guardar_maquinaria" class="btn-corp-yellow px-4 py-2">
                  <i class="bi bi-check2-circle me-1 fs-5"></i>
                  <span>Guardar Cambios de Maquinaria</span>
                </button>
              </div>

            </form>

          </div>
        </div>
      </div>

      <!-- ==========================================================================
           MODAL EXTRA GRANDE (modal-xl): EDICIÓN DE SECCIÓN CONTACTO
           ========================================================================== -->
      <div class="modal fade" id="modalEditarContacto" tabindex="-1" aria-labelledby="modalEditarContactoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-height: 92vh;">
          <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; max-height: 92vh; display: flex; flex-direction: column;">
            
            <!-- Header del Modal -->
            <div class="modal-header modal-header-corp py-3 px-4 flex-shrink-0">
              <div class="d-flex align-items-center gap-3">
                <div class="brand-icon-box" style="width: 40px; height: 40px; font-size: 13px;">M&M</div>
                <div>
                  <h5 class="modal-title fw-bold text-white mb-0" id="modalEditarContactoLabel">
                    <i class="bi bi-headset text-warning me-2"></i>Gestión de Canales: Sección CONTACTO
                  </h5>
                  <small class="text-white-50">Edita los textos de atención, configuración directa de WhatsApp, tarjetas informativas y mapa interactivo</small>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark text-warning border border-warning-subtle small d-none d-md-inline-flex align-items-center gap-1 py-2 px-3">
                  <i class="bi bi-shield-lock"></i> Protección Activa
                </span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
            </div>

            <!-- Formulario de Edición de Contacto -->
            <form method="POST" action="" class="d-flex flex-column h-100 overflow-hidden m-0 p-0" style="min-height: 0; flex: 1 1 auto;">
              
              <div class="modal-body p-4 bg-light form-scroll-container">
                
                <!-- Barra de Navegación Rápida con Deslizamiento Suave (Quick Slide) -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 px-3 bg-white rounded-3 border shadow-sm">
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                      <i class="bi bi-arrows-vertical me-1"></i> Deslizar a:
                    </span>
                    <span class="small text-muted fw-semibold d-none d-sm-inline">Navegación rápida de contacto</span>
                  </div>
                  <div class="d-flex align-items-center gap-1 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionCont('headingContHero', '#collapseContHero')">
                      <i class="bi bi-chat-dots me-1 text-warning"></i>1. Encabezado y WhatsApp
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionCont('headingContTarjetas', '#collapseContTarjetas')">
                      <i class="bi bi-card-list me-1 text-warning"></i>2. Tarjetas de Información
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-medium" onclick="deslizarSeccionCont('headingContMapa', '#collapseContMapa')">
                      <i class="bi bi-geo-alt-fill me-1 text-warning"></i>3. Mapa Interactivo
                    </button>
                  </div>
                </div>

                <div class="accordion accordion-corp" id="accordionEditorContacto">
                  
                  <!-- ================================================================
                       ACORDEÓN 1 - BLOQUE 1 (ENCABEZADO Y CONFIGURACIÓN DE WHATSAPP)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingContHero">
                      <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContHero" aria-expanded="true" aria-controls="collapseContHero">
                        <i class="bi bi-chat-dots text-warning me-2 fs-5"></i>
                        <span>Bloque 1: Encabezado y Configuración de WhatsApp</span>
                      </button>
                    </h2>
                    <div id="collapseContHero" class="accordion-collapse collapse show" aria-labelledby="headingContHero" data-bs-parent="#accordionEditorContacto">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="row g-3">
                          <!-- Título Principal -->
                          <div class="col-12">
                            <label for="cont_hero_titulo" class="form-label fw-bold small text-dark">Título Principal del Hero:</label>
                            <input type="text" class="form-control" id="cont_hero_titulo" name="cont_hero_titulo" maxlength="150" value="<?php echo htmlspecialchars($contenido_contacto['hero_titulo'] ?? 'Hablemos de su proximo proyecto', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-text small">Máx 150 caracteres. Título principal visible al inicio de la página.</div>
                          </div>

                          <!-- Texto Descriptivo -->
                          <div class="col-12">
                            <label for="cont_hero_texto" class="form-label fw-bold small text-dark">Texto Descriptivo:</label>
                            <textarea class="form-control" id="cont_hero_texto" name="cont_hero_texto" rows="3" maxlength="300"><?php echo htmlspecialchars($contenido_contacto['hero_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <div class="form-text small">Máx 300 caracteres. Descripción orientada a la asesoría y cotización rápida.</div>
                          </div>

                          <!-- Número de WhatsApp -->
                          <div class="col-md-6">
                            <label for="cont_wpp_numero" class="form-label fw-bold small text-dark">
                              <i class="bi bi-whatsapp text-success me-1"></i>Número Principal de WhatsApp:
                            </label>
                            <div class="input-group">
                              <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                              <input type="text" class="form-control" id="cont_wpp_numero" name="cont_wpp_numero" maxlength="50" value="<?php echo htmlspecialchars($contenido_contacto['wpp_numero'] ?? '573026043945', ENT_QUOTES, 'UTF-8'); ?>" placeholder="573026043945">
                            </div>
                            <div class="form-text small"><strong>Nota importante:</strong> Ingresar sin el símbolo '+' e incluir indicativo de país (ej. 573026043945).</div>
                          </div>

                          <!-- Mensaje Botón Principal -->
                          <div class="col-md-6">
                            <label for="cont_wpp_mensaje_hero" class="form-label fw-bold small text-dark">Mensaje Botón Principal (Hero):</label>
                            <input type="text" class="form-control" id="cont_wpp_mensaje_hero" name="cont_wpp_mensaje_hero" maxlength="250" value="<?php echo htmlspecialchars($contenido_contacto['wpp_mensaje_hero'] ?? 'Hola, quiero informacion sobre sus servicios', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-text small">Máx 250 caracteres. Texto predeterminado que envía el usuario al pulsar el botón del hero.</div>
                          </div>

                          <!-- Mensaje Botón Flotante -->
                          <div class="col-12">
                            <label for="cont_wpp_mensaje_flotante" class="form-label fw-bold small text-dark">
                              <i class="bi bi-chat-quote text-success me-1"></i>Mensaje Botón Flotante (Esquina inferior):
                            </label>
                            <input type="text" class="form-control" id="cont_wpp_mensaje_flotante" name="cont_wpp_mensaje_flotante" maxlength="250" value="<?php echo htmlspecialchars($contenido_contacto['wpp_mensaje_flotante'] ?? 'Hola, como estas información sobre sus servicios', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-text small">Máx 250 caracteres. Texto predeterminado del botón circular fijo en la esquina inferior.</div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 2 - BLOQUE 2 (TARJETAS DE INFORMACIÓN)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingContTarjetas">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContTarjetas" aria-expanded="false" aria-controls="collapseContTarjetas">
                        <i class="bi bi-card-list text-warning me-2 fs-5"></i>
                        <span>Bloque 2: Tarjetas de Información (4 Canales de Contacto)</span>
                      </button>
                    </h2>
                    <div id="collapseContTarjetas" class="accordion-collapse collapse" aria-labelledby="headingContTarjetas" data-bs-parent="#accordionEditorContacto">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="row g-4">
                          <!-- Tarjeta 1: Teléfono -->
                          <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary text-white p-2 rounded-circle"><i class="bi bi-telephone-fill"></i></span>
                                <h6 class="fw-bold mb-0 text-navy">Canal 1: Teléfono</h6>
                              </div>
                              <div class="mb-3">
                                <label for="cont_c1_dato" class="form-label fw-bold small text-dark">Dato de contacto (Teléfono):</label>
                                <input type="text" class="form-control" id="cont_c1_dato" name="cont_c1_dato" maxlength="100" value="<?php echo htmlspecialchars($contenido_contacto['c1_dato'] ?? '+57 310 291 6140', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres. Ej: +57 310 291 6140</div>
                              </div>
                              <div>
                                <label for="cont_c1_desc" class="form-label fw-bold small text-dark">Texto descriptivo:</label>
                                <input type="text" class="form-control" id="cont_c1_desc" name="cont_c1_desc" maxlength="100" value="<?php echo htmlspecialchars($contenido_contacto['c1_desc'] ?? 'Atencion comercial', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres. Ej: Atención comercial</div>
                              </div>
                            </div>
                          </div>

                          <!-- Tarjeta 2: WhatsApp -->
                          <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success text-white p-2 rounded-circle"><i class="bi bi-whatsapp"></i></span>
                                <h6 class="fw-bold mb-0 text-navy">Canal 2: WhatsApp</h6>
                              </div>
                              <div class="mb-3">
                                <label for="cont_c2_dato" class="form-label fw-bold small text-dark">Dato de contacto (Línea WhatsApp):</label>
                                <input type="text" class="form-control" id="cont_c2_dato" name="cont_c2_dato" maxlength="100" value="<?php echo htmlspecialchars($contenido_contacto['c2_dato'] ?? '+57 302 604 3945', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres. Ej: +57 302 604 3945</div>
                              </div>
                              <div>
                                <label for="cont_c2_desc" class="form-label fw-bold small text-dark">Texto descriptivo:</label>
                                <input type="text" class="form-control" id="cont_c2_desc" name="cont_c2_desc" maxlength="100" value="<?php echo htmlspecialchars($contenido_contacto['c2_desc'] ?? 'Respuesta rapida', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres. Ej: Respuesta rápida</div>
                              </div>
                            </div>
                          </div>

                          <!-- Tarjeta 3: Correo -->
                          <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-danger text-white p-2 rounded-circle"><i class="bi bi-envelope-fill"></i></span>
                                <h6 class="fw-bold mb-0 text-navy">Canal 3: Correo Electrónico</h6>
                              </div>
                              <div class="mb-3">
                                <label for="cont_c3_dato" class="form-label fw-bold small text-dark">Dato de contacto (Correo):</label>
                                <input type="text" class="form-control" id="cont_c3_dato" name="cont_c3_dato" maxlength="100" value="<?php echo htmlspecialchars($contenido_contacto['c3_dato'] ?? 'contacto@constructoresmym.com', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres. Ej: contacto@constructoresmym.com</div>
                              </div>
                              <div>
                                <label for="cont_c3_desc" class="form-label fw-bold small text-dark">Texto descriptivo:</label>
                                <input type="text" class="form-control" id="cont_c3_desc" name="cont_c3_desc" maxlength="100" value="<?php echo htmlspecialchars($contenido_contacto['c3_desc'] ?? 'Cotizaciones y soporte', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres. Ej: Cotizaciones y soporte</div>
                              </div>
                            </div>
                          </div>

                          <!-- Tarjeta 4: Ubicación -->
                          <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light h-100">
                              <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-warning text-dark p-2 rounded-circle"><i class="bi bi-geo-alt-fill"></i></span>
                                <h6 class="fw-bold mb-0 text-navy">Canal 4: Ubicación</h6>
                              </div>
                              <div class="mb-3">
                                <label for="cont_c4_dato" class="form-label fw-bold small text-dark">Dato de contacto (Ubicación física):</label>
                                <input type="text" class="form-control" id="cont_c4_dato" name="cont_c4_dato" maxlength="100" value="<?php echo htmlspecialchars($contenido_contacto['c4_dato'] ?? 'Neiva, Huila, Colombia', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres. Ej: Neiva, Huila, Colombia</div>
                              </div>
                              <div>
                                <label for="cont_c4_desc" class="form-label fw-bold small text-dark">Texto descriptivo:</label>
                                <input type="text" class="form-control" id="cont_c4_desc" name="cont_c4_desc" maxlength="100" value="<?php echo htmlspecialchars($contenido_contacto['c4_desc'] ?? 'Area de servicio', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text small">Máx 100 caracteres. Ej: Área de servicio</div>
                              </div>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- ================================================================
                       ACORDEÓN 3 - BLOQUE 3 (MAPA INTERACTIVO)
                       ================================================================ -->
                  <div class="accordion-item shadow-sm">
                    <h2 class="accordion-header" id="headingContMapa">
                      <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContMapa" aria-expanded="false" aria-controls="collapseContMapa">
                        <i class="bi bi-geo-alt-fill text-warning me-2 fs-5"></i>
                        <span>Bloque 3: Mapa Interactivo</span>
                      </button>
                    </h2>
                    <div id="collapseContMapa" class="accordion-collapse collapse" aria-labelledby="headingContMapa" data-bs-parent="#accordionEditorContacto">
                      <div class="accordion-body p-4 bg-white">
                        
                        <div class="row g-3">
                          <!-- Dirección de Búsqueda -->
                          <div class="col-12">
                            <label for="cont_mapa_busqueda" class="form-label fw-bold small text-dark">
                              <i class="bi bi-search text-warning me-1"></i>Dirección de búsqueda para el mapa:
                            </label>
                            <input type="text" class="form-control" id="cont_mapa_busqueda" name="cont_mapa_busqueda" maxlength="150" value="<?php echo htmlspecialchars($contenido_contacto['mapa_busqueda'] ?? 'Neiva, Huila, Colombia', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Neiva, Huila, Colombia">
                            <div class="form-text small">Máx 150 caracteres. Este campo se usará para actualizar el iframe de Google Maps y el botón de redirección automáticamente.</div>
                          </div>

                          <!-- Título del Cuadro Informativo -->
                          <div class="col-12">
                            <label for="cont_mapa_titulo" class="form-label fw-bold small text-dark">Título del cuadro informativo:</label>
                            <input type="text" class="form-control" id="cont_mapa_titulo" name="cont_mapa_titulo" maxlength="150" value="<?php echo htmlspecialchars($contenido_contacto['mapa_titulo'] ?? 'Estamos en Neiva, Huila', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-text small">Máx 150 caracteres. Título explicativo ubicado bajo el mapa.</div>
                          </div>

                          <!-- Texto Explicativo -->
                          <div class="col-12">
                            <label for="cont_mapa_texto" class="form-label fw-bold small text-dark">Texto explicativo:</label>
                            <textarea class="form-control" id="cont_mapa_texto" name="cont_mapa_texto" rows="3" maxlength="350"><?php echo htmlspecialchars($contenido_contacto['mapa_texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <div class="form-text small">Máx 350 caracteres. Explicación de cobertura regional y disponibilidad para obras.</div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                </div>

                <!-- Botón flotante para deslizar rápidamente al inicio del formulario -->
                <button type="button" class="btn-slide-top shadow" title="Deslizar arriba" onclick="this.closest('.form-scroll-container').scrollTo({top: 0, behavior: 'smooth'});">
                  <i class="bi bi-chevron-up"></i>
                </button>

              </div>

              <!-- Footer del Modal (Fijo en la base con flex-shrink-0) -->
              <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center border-top flex-shrink-0">
                <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal">
                  <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>
                <button type="submit" name="guardar_contacto" class="btn-corp-yellow px-4 py-2">
                  <i class="bi bi-check2-circle me-1 fs-5"></i>
                  <span>Guardar Cambios de Contacto</span>
                </button>
              </div>

            </form>

          </div>
        </div>
      </div>

    </main>
  </div>

  <!-- ==========================================================================
       SCRIPTS: HOVER DEL SIDEBAR Y MENÚ DE USUARIO
       ========================================================================== -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // 1. Control de Apertura/Cierre del Sidebar (Hover en PC + Clic/Táctil en Celular y Tablet)
      const sidebar = document.getElementById('adminSidebar');
      const trigger = document.getElementById('sidebarTrigger');
      const backdrop = document.getElementById('sidebarBackdrop');
      const closeBtn = document.getElementById('closeSidebarBtn');
      let closeTimeout = null;

      // Detección de dispositivo con cursor/mouse fino para hover
      const isHoverCapable = () => window.matchMedia('(hover: hover) and (pointer: fine)').matches;

      const openSidebar = () => {
        if (closeTimeout) clearTimeout(closeTimeout);
        if (sidebar) sidebar.classList.add('sidebar-open');
        if (backdrop) backdrop.classList.add('show');
      };

      const closeSidebar = () => {
        if (closeTimeout) clearTimeout(closeTimeout);
        if (sidebar) sidebar.classList.remove('sidebar-open');
        if (backdrop) backdrop.classList.remove('show');
      };

      const toggleSidebar = (e) => {
        if (e) e.stopPropagation();
        if (sidebar) {
          const isOpen = sidebar.classList.contains('sidebar-open');
          if (isOpen) {
            closeSidebar();
          } else {
            openSidebar();
          }
        }
      };

      if (trigger) {
        // En PC con mouse: Abre al pasar el puntero
        trigger.addEventListener('mouseenter', () => {
          if (isHoverCapable()) openSidebar();
        });

        trigger.addEventListener('mouseleave', (e) => {
          if (isHoverCapable() && sidebar && !sidebar.contains(e.relatedTarget)) {
            closeTimeout = setTimeout(closeSidebar, 220);
          }
        });

        // En pantallas táctiles / toque con el dedo: Conmuta al hacer clic o tocar
        trigger.addEventListener('click', toggleSidebar);
      }

      if (sidebar) {
        sidebar.addEventListener('mouseenter', () => {
          if (isHoverCapable() && closeTimeout) clearTimeout(closeTimeout);
        });

        sidebar.addEventListener('mouseleave', () => {
          if (isHoverCapable()) {
            closeTimeout = setTimeout(closeSidebar, 220);
          }
        });
      }

      if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          closeSidebar();
        });
      }

      if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
      }

      // 2. Control del Menú Desplegable de Usuario
      const userAvatarBtn = document.getElementById('userAvatarBtn');
      const userDropdown = document.getElementById('userDropdown');

      userAvatarBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
        userAvatarBtn.classList.toggle('active');
      });

      document.addEventListener('click', (e) => {
        if (!userDropdown.contains(e.target) && !userAvatarBtn.contains(e.target)) {
          userDropdown.classList.remove('show');
          userAvatarBtn.classList.remove('active');
        }
      });

      // 3. Conexión de Selectores de Archivos Compactos para el Editor de Inicio
      const conectarSelector = (fileId, textId) => {
        const fileInput = document.getElementById(fileId);
        const textInput = document.getElementById(textId);
        if (!fileInput || !textInput) return;

        fileInput.addEventListener('change', function () {
          if (this.files && this.files.length > 0) {
            textInput.value = this.files[0].name;
          }
        });
      };

      conectarSelector('hero_imagen_file', 'hero_imagen_texto');
      conectarSelector('carrusel_img1_file', 'carrusel_img1_texto');
      conectarSelector('carrusel_img2_file', 'carrusel_img2_texto');
      conectarSelector('carrusel_img3_file', 'carrusel_img3_texto');

      // Conexión de Selectores de Archivos Híbridos para el Editor de Proyectos
      conectarSelector('proy_carrusel_img1_file', 'proy_carrusel_img1_texto');
      conectarSelector('proy_carrusel_img2_file', 'proy_carrusel_img2_texto');
      conectarSelector('proy_carrusel_img3_file', 'proy_carrusel_img3_texto');
      conectarSelector('proy_c1_img_file', 'proy_c1_img_texto');
      conectarSelector('proy_c2_img_file', 'proy_c2_img_texto');
      conectarSelector('proy_c3_img_file', 'proy_c3_img_texto');
      conectarSelector('proy_t1_img_file', 'proy_t1_img_texto');
      conectarSelector('proy_t2_img_file', 'proy_t2_img_texto');
      conectarSelector('proy_e1_img_file', 'proy_e1_img_texto');
      conectarSelector('proy_e2_img_file', 'proy_e2_img_texto');
      conectarSelector('proy_e3_img_file', 'proy_e3_img_texto');
      conectarSelector('proy_e4_img_file', 'proy_e4_img_texto');

      // Conexión de Selectores de Archivos Híbridos para el Editor de Nosotros
      conectarSelector('nos_carrusel_img1_file', 'nos_carrusel_img1_texto');
      conectarSelector('nos_carrusel_img2_file', 'nos_carrusel_img2_texto');
      conectarSelector('nos_carrusel_img3_file', 'nos_carrusel_img3_texto');
      conectarSelector('nos_qs_imagen_file', 'nos_qs_imagen_texto');

      // Conexión de Selectores de Archivos Híbridos para el Editor de Maquinaria
      conectarSelector('maq_hero_imagen_file', 'maq_hero_imagen_texto');

      // 4. Control de Deslizamiento Suave (Smooth Scrolling) en el Formulario del Modal Inicio
      window.deslizarSeccion = function (headerId, collapseId) {
        const headerEl = document.getElementById(headerId);
        const modalInicio = document.getElementById('modalEditarInicio');
        if (!headerEl || !modalInicio) return;
        const modalBody = modalInicio.querySelector('.form-scroll-container');
        if (!modalBody) return;

        // Si el bloque está cerrado, abrirlo automáticamente
        if (collapseId) {
          const collapseEl = document.querySelector(collapseId);
          if (collapseEl && !collapseEl.classList.contains('show')) {
            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl);
            bsCollapse.show();
          }
        }

        // Deslizar suavemente hacia el encabezado seleccionado
        setTimeout(() => {
          headerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 120);
      };

      // Control de Deslizamiento Suave (Smooth Scrolling) en el Formulario del Modal Proyectos
      window.deslizarSeccionProy = function (headerId, collapseId) {
        const headerEl = document.getElementById(headerId);
        const modalProy = document.getElementById('modalEditarProyectos');
        if (!headerEl || !modalProy) return;
        const modalBody = modalProy.querySelector('.form-scroll-container');
        if (!modalBody) return;

        if (collapseId) {
          const collapseEl = document.querySelector(collapseId);
          if (collapseEl && !collapseEl.classList.contains('show')) {
            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl);
            bsCollapse.show();
          }
        }

        setTimeout(() => {
          headerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 120);
      };

      // Control de Deslizamiento Suave (Smooth Scrolling) en el Formulario del Modal Nosotros
      window.deslizarSeccionNos = function (headerId, collapseId) {
        const headerEl = document.getElementById(headerId);
        const modalNos = document.getElementById('modalEditarNosotros');
        if (!headerEl || !modalNos) return;
        const modalBody = modalNos.querySelector('.form-scroll-container');
        if (!modalBody) return;

        if (collapseId) {
          const collapseEl = document.querySelector(collapseId);
          if (collapseEl && !collapseEl.classList.contains('show')) {
            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl);
            bsCollapse.show();
          }
        }

        setTimeout(() => {
          headerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 120);
      };

      // Control de Deslizamiento Suave (Smooth Scrolling) en el Formulario del Modal Maquinaria
      window.deslizarSeccionMaq = function (headerId, collapseId) {
        const headerEl = document.getElementById(headerId);
        const modalMaq = document.getElementById('modalEditarMaquinaria');
        if (!headerEl || !modalMaq) return;
        const modalBody = modalMaq.querySelector('.form-scroll-container');
        if (!modalBody) return;

        if (collapseId) {
          const collapseEl = document.querySelector(collapseId);
          if (collapseEl && !collapseEl.classList.contains('show')) {
            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl);
            bsCollapse.show();
          }
        }

        setTimeout(() => {
          headerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 120);
      };

      // Control de Deslizamiento Suave (Smooth Scrolling) en el Formulario del Modal Contacto
      window.deslizarSeccionCont = function (headerId, collapseId) {
        const headerEl = document.getElementById(headerId);
        const modalCont = document.getElementById('modalEditarContacto');
        if (!headerEl || !modalCont) return;
        const modalBody = modalCont.querySelector('.form-scroll-container');
        if (!modalBody) return;

        if (collapseId) {
          const collapseEl = document.querySelector(collapseId);
          if (collapseEl && !collapseEl.classList.contains('show')) {
            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl);
            bsCollapse.show();
          }
        }

        setTimeout(() => {
          headerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 120);
      };

      // Deslizamiento automático hacia la sección cuando el usuario abre cualquier acordeón
      const accordionEditor = document.getElementById('accordionEditorInicio');
      if (accordionEditor) {
        accordionEditor.addEventListener('shown.bs.collapse', function (e) {
          const item = e.target.closest('.accordion-item');
          if (item) {
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        });
      }

      const accordionProy = document.getElementById('accordionEditorProyectos');
      if (accordionProy) {
        accordionProy.addEventListener('shown.bs.collapse', function (e) {
          const item = e.target.closest('.accordion-item');
          if (item) {
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        });
      }

      const accordionNos = document.getElementById('accordionEditorNosotros');
      if (accordionNos) {
        accordionNos.addEventListener('shown.bs.collapse', function (e) {
          const item = e.target.closest('.accordion-item');
          if (item) {
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        });
      }

      const accordionMaq = document.getElementById('accordionEditorMaquinaria');
      if (accordionMaq) {
        accordionMaq.addEventListener('shown.bs.collapse', function (e) {
          const item = e.target.closest('.accordion-item');
          if (item) {
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        });
      }

      const accordionCont = document.getElementById('accordionEditorContacto');
      if (accordionCont) {
        accordionCont.addEventListener('shown.bs.collapse', function (e) {
          const item = e.target.closest('.accordion-item');
          if (item) {
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        });
      }
    });
  </script>

  <!-- Componente Chatbot Flotante (Izquierda) -->
  <?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
