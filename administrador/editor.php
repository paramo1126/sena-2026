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

          <button type="button" class="btn btn-outline-dark fw-bold w-100 d-flex justify-content-between align-items-center">
            <span>Gestionar Sección</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>

        <!-- Bloque 3: Obras -->
        <div class="web-card">
          <div>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="web-card-icon"><i class="bi bi-cone"></i></div>
              <div>
                <h3 class="fs-5 fw-bold mb-0 text-dark">NOSOTROS</h3>
                <small class="text-primary fw-semibold">Proyectos Ejecutados</small>
              </div>
            </div>
            <p class="text-muted small mb-3">Fichas técnicas y galerías de proyectos viales, infraestructura civil y obras en curso.</p>
            
            <ul class="list-unstyled small text-secondary bg-light p-3 rounded-3 mb-4">
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>8 proyectos destacados publicados</li>
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Fichas técnicas y contratantes</li>
              <li><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Reportes de avance fotográfico</li>
            </ul>
          </div>

          <button type="button" class="btn btn-outline-dark fw-bold w-100 d-flex justify-content-between align-items-center">
            <span>Gestionar Sección</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>

        <!-- Bloque 4: Galería -->
        <div class="web-card">
          <div>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="web-card-icon"><i class="bi bi-images"></i></div>
              <div>
                <h3 class="fs-5 fw-bold mb-0 text-dark">MAQUINARIA</h3>
                <small class="text-primary fw-semibold">Multimedia</small>
              </div>
            </div>
            <p class="text-muted small mb-3">Repositorio multimedia de imágenes de alta resolución sobre maquinaria y obras realizadas.</p>
            
            <ul class="list-unstyled small text-secondary bg-light p-3 rounded-3 mb-4">
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Álbumes clasificados por obra</li>
              <li class="mb-1"><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Fotografías de flota en operación</li>
              <li><i class="bi bi-check2 text-warning me-2 fw-bold"></i>Videos corporativos y dron</li>
            </ul>
          </div>

          <button type="button" class="btn btn-outline-dark fw-bold w-100 d-flex justify-content-between align-items-center">
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

          <button type="button" class="btn btn-outline-dark fw-bold w-100 d-flex justify-content-between align-items-center">
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

      // 4. Control de Deslizamiento Suave (Smooth Scrolling) en el Formulario del Modal
      window.deslizarSeccion = function (headerId, collapseId) {
        const headerEl = document.getElementById(headerId);
        const modalBody = document.querySelector('.form-scroll-container');
        if (!headerEl || !modalBody) return;

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
    });
  </script>

  <!-- Componente Chatbot Flotante (Izquierda) -->
  <?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
