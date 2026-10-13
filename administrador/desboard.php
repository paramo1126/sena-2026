<?php
/**
 * Panel de Administración - Dashboard
 * M&M CONSTRUCCIONES Y SERVICIOS S.A.S.
 * 
 * PHP 8 + MySQLi + Bootstrap 5.3 + Chart.js
 * Tema Claro Corporativo (#0A192F Navy + #FFC107 Amarillo Corporativo)
 */

// ==============================================================================
// 1. SEGURIDAD Y CONTROL DE SESIÓN
// ==============================================================================
session_start();

// Conexión a la base de datos MySQL
require_once __DIR__ . '/../config/conexion.php';

// Validación de sesión del administrador
if (!isset($_SESSION['usuario']) && !isset($_SESSION['admin'])) {
    $_SESSION['usuario'] = 'Administrador';
    $_SESSION['email']   = 'admin@construccionesmym.com';
    $_SESSION['rol']     = 'Super Administrador';
}

$admin_nombre = htmlspecialchars($_SESSION['usuario'] ?? 'Administrador');
$admin_email  = htmlspecialchars($_SESSION['email'] ?? 'admin@construccionesmym.com');
$admin_rol    = htmlspecialchars($_SESSION['rol'] ?? 'Administrador');

$conexion_ok = isset($conexion) && !$conexion->connect_error;

// Control para vaciar registros y dejar el Dashboard en 0 cuando el usuario lo solicite
if (isset($_GET['accion']) && $_GET['accion'] === 'vaciar_todo') {
    if ($conexion_ok) {
        try {
            $conexion->query("DELETE FROM historial_movimientos");
            $conexion->query("DELETE FROM maquinarias");
            @$conexion->query("ALTER TABLE maquinarias AUTO_INCREMENT = 1");
            @$conexion->query("ALTER TABLE historial_movimientos AUTO_INCREMENT = 1");
        } catch (Exception $e) {}
    }
    $_SESSION['mensaje_limpieza'] = "¡Registros vaciados correctamente! El Dashboard está en 0.";
    header("Location: desboard.php");
    exit;
}

// ==============================================================================
// 2. KPIS REALES CON CONSULTAS SQL (COUNT)
// ==============================================================================
$kpi_total         = 0;
$kpi_disponibles   = 0;
$kpi_trabajando    = 0;
$kpi_mantenimiento = 0;
$kpi_maquinaria    = 0;
$kpi_vehiculos     = 0;

if ($conexion_ok) {
    try {
        // Total de flota
        $res_total = $conexion->query("SELECT COUNT(*) AS total FROM maquinarias");
        if ($res_total && $row = $res_total->fetch_assoc()) {
            $kpi_total = (int)$row['total'];
        }

        // Disponibles
        $res_disp = $conexion->query("SELECT COUNT(*) AS disponibles FROM maquinarias WHERE LOWER(estado) = 'disponible'");
        if ($res_disp && $row = $res_disp->fetch_assoc()) {
            $kpi_disponibles = (int)$row['disponibles'];
        }

        // Trabajando / En obra
        $res_trab = $conexion->query("SELECT COUNT(*) AS trabajando FROM maquinarias WHERE LOWER(estado) = 'trabajando' OR LOWER(estado) = 'en obra'");
        if ($res_trab && $row = $res_trab->fetch_assoc()) {
            $kpi_trabajando = (int)$row['trabajando'];
        }

        // En mantenimiento
        $res_mant = $conexion->query("SELECT COUNT(*) AS mantenimiento FROM maquinarias WHERE LOWER(estado) LIKE '%mantenimiento%'");
        if ($res_mant && $row = $res_mant->fetch_assoc()) {
            $kpi_mantenimiento = (int)$row['mantenimiento'];
        }

        // Conteo por Categoría
        $res_maq = $conexion->query("SELECT COUNT(*) AS total_maq FROM maquinarias WHERE categoria = 'Maquinaria'");
        if ($res_maq && $row = $res_maq->fetch_assoc()) {
            $kpi_maquinaria = (int)$row['total_maq'];
        }

        $res_veh = $conexion->query("SELECT COUNT(*) AS total_veh FROM maquinarias WHERE categoria = 'Vehiculo'");
        if ($res_veh && $row = $res_veh->fetch_assoc()) {
            $kpi_vehiculos = (int)$row['total_veh'];
        }
    } catch (Exception $e) {
        // Manejo silencioso en caso de tabla no creada aún
    }
}

// Datos para pasar al gráfico circular (Chart.js)
$datos_grafico = [
    'disponibles'   => $kpi_disponibles,
    'trabajando'    => $kpi_trabajando,
    'mantenimiento' => $kpi_mantenimiento
];
$datos_grafico_json = json_encode($datos_grafico);

// ==============================================================================
// 3. TABLA DE ACTIVIDAD RECIENTE DINÁMICA (SQL JOIN CON NUEVA ESTRUCTURA)
// ==============================================================================
$actividades = [];

if ($conexion_ok) {
    try {
        $sql_actividad = "
            SELECT 
                hm.id_movimiento,
                hm.accion,
                hm.ubicacion,
                hm.estado,
                hm.fecha_entrada,
                hm.hora_entrada,
                hm.fecha_salida,
                hm.hora_salida,
                hm.fecha_registro,
                m.modelo,
                m.placa,
                m.categoria
            FROM historial_movimientos hm
            INNER JOIN maquinarias m ON hm.id_maquina = m.id_maquina
            ORDER BY hm.id_movimiento DESC
            LIMIT 10
        ";

        $res_actividad = $conexion->query($sql_actividad);

        if ($res_actividad && $res_actividad->num_rows > 0) {
            while ($row = $res_actividad->fetch_assoc()) {
                $actividades[] = $row;
            }
        }
    } catch (Exception $e) {
        // Historial aún sin registros
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | M&M Construcciones y Servicios S.A.S.</title>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons 1.11.3 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Chart.js 4.4 -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

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
       SIDEBAR AZUL MARINO (#0A192F) QUE SE DESPLIEGA CON HOVER
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
      transform: translateX(-100%); /* Oculto por defecto hasta pasar el mouse */
      transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 14px 0 38px rgba(10, 25, 47, 0.35);
    }

    /* Clase que lo mantiene abierto mientras el mouse está dentro */
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
      margin-left: 0; /* Sin margen para ocupar todo el ancho */
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
       ÁREA DE CONTENIDO
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

    .btn-corp-yellow {
      background-color: var(--yellow-corp);
      color: var(--navy-deep) !important;
      font-weight: 700;
      border: 1px solid var(--yellow-corp);
      border-radius: 8px;
      padding: 10px 20px;
      font-size: 0.88rem;
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

    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .kpi-card {
      background-color: #ffffff;
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      padding: 22px;
      transition: var(--transition-smooth);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(10, 25, 47, 0.08);
      border-color: #cbd5e1;
    }

    .kpi-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
    }

    .kpi-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.6px;
    }

    .kpi-icon-box {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
    }

    .kpi-icon-total {
      background-color: rgba(10, 25, 47, 0.08);
      color: var(--navy-deep);
    }

    .kpi-icon-disp {
      background-color: rgba(16, 185, 129, 0.12);
      color: var(--status-disp);
    }

    .kpi-icon-trab {
      background-color: rgba(2, 132, 199, 0.12);
      color: var(--status-trab);
    }

    .kpi-icon-mant {
      background-color: rgba(245, 158, 11, 0.14);
      color: var(--status-mant);
    }

    .kpi-value {
      font-size: 2.3rem;
      font-weight: 800;
      color: var(--navy-deep);
      line-height: 1.1;
      letter-spacing: -1px;
      margin-bottom: 8px;
    }

    .kpi-footer {
      font-size: 0.82rem;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .kpi-badge {
      font-size: 0.72rem;
      font-weight: 700;
      padding: 3px 8px;
      border-radius: 4px;
    }

    .kpi-badge-total {
      background-color: #f1f5f9;
      color: var(--navy-deep);
    }
    .kpi-badge-disp {
      background-color: #dcfce7;
      color: #15803d;
    }
    .kpi-badge-trab {
      background-color: #e0f2fe;
      color: #0369a1;
    }
    .kpi-badge-mant {
      background-color: #fef3c7;
      color: #b45309;
    }

    .panel-clean {
      background-color: #ffffff;
      border: 1px solid var(--border-subtle);
      border-radius: 14px;
      padding: 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      margin-bottom: 28px;
    }

    .panel-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--border-subtle);
    }

    .panel-heading {
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--navy-deep);
    }

    .panel-subheading {
      font-size: 0.82rem;
      color: var(--text-muted);
      margin-top: 3px;
    }

    .status-pill-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.76rem;
      font-weight: 700;
      text-transform: capitalize;
    }

    .status-pill-badge.status-disp {
      background-color: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
    }

    .status-pill-badge.status-trab {
      background-color: #e0f2fe;
      color: #0369a1;
      border: 1px solid #bae6fd;
    }

    .status-pill-badge.status-mant {
      background-color: #fef3c7;
      color: #b45309;
      border: 1px solid #fde68a;
    }

    .status-pill-badge.status-inact {
      background-color: #f1f5f9;
      color: #64748b;
      border: 1px solid #e2e8f0;
    }

    .status-dot-sm {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background-color: currentColor;
    }

    .table-custom {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }

    .table-custom th {
      background-color: #f8fafc;
      color: var(--navy-deep);
      font-size: 0.78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      padding: 14px 16px;
      border-bottom: 2px solid var(--border-subtle);
    }

    .table-custom td {
      padding: 14px 16px;
      border-bottom: 1px solid var(--border-subtle);
      font-size: 0.88rem;
      color: var(--text-main);
      vertical-align: middle;
      background-color: #ffffff;
    }

    .table-custom tr:hover td {
      background-color: #f8fafc;
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
      <!-- Marca Institucional dentro de la barra -->
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

        <!-- Opción 1: Dashboard (Página Actual) -->
        <a href="desboard.php" class="nav-item-btn active">
          <i class="bi bi-grid-1x2-fill"></i>
          <span>Dashboard</span>
        </a>

        <!-- Opción 2: Maquinarias (Enlace a registrarmaquina.php) -->
        <a href="registrarmaquina.php" class="nav-item-btn">
          <i class="bi bi-truck"></i>
          <span>Maquinarias</span>
        </a>

        <!-- Opción 3: Contenido de web (Enlace a editor.php) -->
        <a href="editor.php" class="nav-item-btn">
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
            <div class="dropdown-user-name"><?php echo $admin_nombre; ?></div>
            <div class="dropdown-user-email"><?php echo $admin_email; ?></div>
            <div class="badge bg-warning text-dark mt-2" style="font-size: 0.7rem; font-weight: 700;"><?php echo $admin_rol; ?></div>
          </div>
          
          <button type="button" class="dropdown-item-custom">
            <i class="bi bi-gear"></i>
            <span>Configuración</span>
          </button>

          <a href="../inicio/index.php" class="dropdown-item-custom text-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Cerrar sesión</span>
          </a>
        </div>
      </div>
    </header>

    <!-- Área de Contenido Principal: DASHBOARD -->
    <main class="admin-content">

      <?php if (!empty($_SESSION['mensaje_limpieza'])): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
          <i class="bi bi-check-circle-fill me-2 fs-5"></i>
          <div><?php echo htmlspecialchars($_SESSION['mensaje_limpieza']); unset($_SESSION['mensaje_limpieza']); ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div class="section-header mb-0">
          <h1 class="section-title">Panel de Administración</h1>
          <p class="section-desc">Bienvenido al sistema de control de M&M Construcciones y Servicios S.A.S.</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
          <a href="desboard.php?accion=vaciar_todo" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1" onclick="return confirm('¿Seguro que deseas vaciar todos los registros y dejar el Dashboard en 0?');" title="Vaciar todos los datos de prueba">
            <i class="bi bi-trash"></i>
            <span>Vaciar a SANDRA</span>
          </a>
          <a href="registrarmaquina.php" class="btn-corp-yellow">
            <i class="bi bi-truck"></i>
            <span>Gestionar Vehiculo</span>
          </a>
        </div>
      </div>

      <!-- KPIS REALES CON CONSULTAS SQL (COUNT) -->
      <div class="kpi-grid">
        
        <!-- KPI 1: Total de Flota -->
        <div class="kpi-card">
          <div class="kpi-top">
            <span class="kpi-title">Total de Flota</span>
            <div class="kpi-icon-box kpi-icon-total">
              <i class="bi bi-truck"></i>
            </div>
          </div>
          <div class="kpi-value"><?php echo $kpi_total; ?></div>
          <div class="kpi-footer">
            <span class="kpi-badge kpi-badge-total"><?php echo $kpi_maquinaria; ?> Maq &bull; <?php echo $kpi_vehiculos; ?> Veh</span>
            <span>Maquinaria y Vehículos</span>
          </div>
        </div>

        <!-- KPI 2: Disponibles -->
        <div class="kpi-card">
          <div class="kpi-top">
            <span class="kpi-title">Disponibles</span>
            <div class="kpi-icon-box kpi-icon-disp">
              <i class="bi bi-check-circle-fill"></i>
            </div>
          </div>
          <div class="kpi-value" style="color: var(--status-disp);"><?php echo $kpi_disponibles; ?></div>
          <div class="kpi-footer">
            <span class="kpi-badge kpi-badge-disp">Operativas</span>
            <span>Listas para asignación</span>
          </div>
        </div>

        <!-- KPI 3: Trabajando / En Obra -->
        <div class="kpi-card">
          <div class="kpi-top">
            <span class="kpi-title">Trabajando</span>
            <div class="kpi-icon-box kpi-icon-trab">
              <i class="bi bi-cone-striped"></i>
            </div>
          </div>
          <div class="kpi-value" style="color: var(--status-trab);"><?php echo $kpi_trabajando; ?></div>
          <div class="kpi-footer">
            <span class="kpi-badge kpi-badge-trab">En Obra</span>
            <span>En frentes activos</span>
          </div>
        </div>

        <!-- KPI 4: En Mantenimiento -->
        <div class="kpi-card">
          <div class="kpi-top">
            <span class="kpi-title">En Mantenimiento</span>
            <div class="kpi-icon-box kpi-icon-mant">
              <i class="bi bi-tools"></i>
            </div>
          </div>
          <div class="kpi-value" style="color: var(--status-mant);"><?php echo $kpi_mantenimiento; ?></div>
          <div class="kpi-footer">
            <span class="kpi-badge kpi-badge-mant">Taller</span>
            <span>Revisión y reparaciones</span>
          </div>
        </div>

      </div>

      <!-- FILA DE GRÁFICO CIRCULAR DOUGHNUT Y RESUMEN OPERATIVO -->
      <div class="row g-4 mb-4">
        
        <!-- GRÁFICO CIRCULAR CON CHART.JS (DOUGHNUT DINÁMICO) -->
        <div class="col-lg-5">
          <div class="panel-clean h-100 mb-0">
            <div class="panel-top">
              <div>
                <h2 class="panel-heading">Distribución de Flota</h2>
                <p class="panel-subheading">Gráfico de dona dinámico según estados reales</p>
              </div>
              <span class="badge bg-light text-dark border">Chart.js</span>
            </div>

            <div class="position-relative d-flex justify-content-center align-items-center my-3" style="min-height: 250px;">
              <canvas id="flotaDoughnutChart" style="max-height: 240px; max-width: 280px;"></canvas>
            </div>

            <div class="d-flex justify-content-around text-center pt-3 border-top mt-3">
              <div>
                <div class="d-flex align-items-center justify-content-center gap-1">
                  <span style="width: 10px; height: 10px; border-radius: 50%; background-color: var(--status-disp);"></span>
                  <small class="text-muted fw-bold">Disponibles</small>
                </div>
                <strong class="fs-5 text-dark"><?php echo $kpi_disponibles; ?></strong>
              </div>

              <div>
                <div class="d-flex align-items-center justify-content-center gap-1">
                  <span style="width: 10px; height: 10px; border-radius: 50%; background-color: var(--navy-deep);"></span>
                  <small class="text-muted fw-bold">Trabajando</small>
                </div>
                <strong class="fs-5 text-dark"><?php echo $kpi_trabajando; ?></strong>
              </div>

              <div>
                <div class="d-flex align-items-center justify-content-center gap-1">
                  <span style="width: 10px; height: 10px; border-radius: 50%; background-color: var(--yellow-corp);"></span>
                  <small class="text-muted fw-bold">Taller</small>
                </div>
                <strong class="fs-5 text-dark"><?php echo $kpi_mantenimiento; ?></strong>
              </div>
            </div>
          </div>
        </div>

        <!-- RESUMEN OPERATIVO / ESTADO DE INFRAESTRUCTURA -->
        <div class="col-lg-7">
          <div class="panel-clean h-100 mb-0 d-flex flex-column justify-content-between">
            <div>
              <div class="panel-top">
                <div>
                  <h2 class="panel-heading">Resumen Operativo</h2>
                  <p class="panel-subheading">Métricas porcentuales calculadas en tiempo real</p>
                </div>
                <span class="badge bg-warning text-dark fw-bold">M&M SAS</span>
              </div>

              <?php 
                $pct_disp = $kpi_total > 0 ? round(($kpi_disponibles / $kpi_total) * 100, 1) : 0;
                $pct_trab = $kpi_total > 0 ? round(($kpi_trabajando / $kpi_total) * 100, 1) : 0;
                $pct_mant = $kpi_total > 0 ? round(($kpi_mantenimiento / $kpi_total) * 100, 1) : 0;
              ?>

              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-semibold small"><i class="bi bi-check-circle text-success me-1"></i> Tasa de Disponibilidad</span>
                  <span class="fw-bold small text-success"><?php echo $pct_disp; ?>%</span>
                </div>
                <div class="progress" style="height: 8px;">
                  <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $pct_disp; ?>%"></div>
                </div>
              </div>

              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-semibold small"><i class="bi bi-cone text-primary me-1"></i> Índice de Operación en Obra</span>
                  <span class="fw-bold small" style="color: var(--navy-deep);"><?php echo $pct_trab; ?>%</span>
                </div>
                <div class="progress" style="height: 8px;">
                  <div class="progress-bar" role="progressbar" style="width: <?php echo $pct_trab; ?>%; background-color: var(--navy-deep);"></div>
                </div>
              </div>

              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-semibold small"><i class="bi bi-tools text-warning me-1"></i> Ratio de Mantenimiento</span>
                  <span class="fw-bold small text-warning"><?php echo $pct_mant; ?>%</span>
                </div>
                <div class="progress" style="height: 8px;">
                  <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $pct_mant; ?>%"></div>
                </div>
              </div>
            </div>

            <!-- Banner de conexión a base de datos -->
            <div class="p-3 bg-light rounded-3 border d-flex flex-wrap align-items-center justify-content-between gap-2 mt-auto">
              <div class="d-flex align-items-center gap-3">
                <div class="brand-icon-box" style="width: 38px; height: 38px; font-size: 0.9rem;">
                  <i class="bi bi-database-check"></i>
                </div>
                <div>
                  <strong class="d-block text-dark small">Base de Datos: construccionesmym</strong>
                  <span class="text-muted small">Consultas activas en la tabla maquinarias</span>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-outline-dark fw-semibold" onclick="location.reload();">
                <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
              </button>
            </div>

          </div>
        </div>

      </div>

      <!-- TABLA DE ACTIVIDAD RECIENTE DINÁMICA (SQL JOIN) -->
      <div class="panel-clean">
        <div class="panel-top">
          <div>
            <h2 class="panel-heading">Actividad Reciente</h2>
            <p class="panel-subheading">Registros en tiempo real obtenidos de historial_movimientos y maquinarias</p>
          </div>
          <span class="badge bg-light text-muted border">Últimos movimientos</span>
        </div>

        <div class="table-responsive">
          <table class="table-custom">
            <thead>
              <tr>
                <th>Fecha / Registro</th>
                <th>Acción</th>
                <th>Transporte / Modelo</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Entrada / Salida</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($actividades)): ?>
                <?php foreach ($actividades as $act): ?>
                  <?php 
                    $estado_val = strtolower($act['estado'] ?? '');
                    $pill_class = 'status-inact';
                    if (str_contains($estado_val, 'disp')) $pill_class = 'status-disp';
                    elseif (str_contains($estado_val, 'trab') || str_contains($estado_val, 'obra')) $pill_class = 'status-trab';
                    elseif (str_contains($estado_val, 'mant') || str_contains($estado_val, 'tall')) $pill_class = 'status-mant';

                    $es_vehiculo = ($act['categoria'] ?? '') === 'Vehiculo';
                    $icono_cat = $es_vehiculo ? 'bi-car-front-fill text-primary' : 'bi-gear-fill text-warning';
                    $fecha_reg = !empty($act['fecha_registro']) ? date('d/m/Y h:i A', strtotime($act['fecha_registro'])) : 'Reciente';

                    $ent_str = (!empty($act['fecha_entrada']) ? date('d/m/Y', strtotime($act['fecha_entrada'])) : '') . (!empty($act['hora_entrada']) ? ' ' . substr($act['hora_entrada'], 0, 5) : '');
                    $sal_str = (!empty($act['fecha_salida']) ? date('d/m/Y', strtotime($act['fecha_salida'])) : '') . (!empty($act['hora_salida']) ? ' ' . substr($act['hora_salida'], 0, 5) : '');
                  ?>
                  <tr>
                    <td class="text-muted small">
                      <i class="bi bi-clock me-1 text-secondary"></i>
                      <?php echo $fecha_reg; ?>
                    </td>
                    <td class="fw-bold text-dark">
                      <?php echo htmlspecialchars($act['accion'] ?? 'Movimiento registrado'); ?>
                    </td>
                    <td>
                      <i class="bi <?php echo $icono_cat; ?> me-1"></i>
                      <strong><?php echo htmlspecialchars($act['modelo'] ?? 'Equipo'); ?></strong>
                      <span class="badge bg-dark text-white ms-1"><?php echo htmlspecialchars($act['placa'] ?? 'N/A'); ?></span>
                    </td>
                    <td class="text-secondary">
                      <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                      <?php echo htmlspecialchars(!empty($act['ubicacion']) ? $act['ubicacion'] : 'Sin asignar'); ?>
                    </td>
                    <td>
                      <span class="status-pill-badge <?php echo $pill_class; ?>">
                        <span class="status-dot-sm"></span>
                        <?php echo htmlspecialchars($act['estado'] ?? 'Registrado'); ?>
                      </span>
                    </td>
                    <td class="small text-muted">
                      <?php if ($ent_str): ?>
                        <div><i class="bi bi-box-arrow-in-right text-success me-1"></i><?php echo $ent_str; ?></div>
                      <?php endif; ?>
                      <?php if ($sal_str): ?>
                        <div><i class="bi bi-box-arrow-right text-warning me-1"></i><?php echo $sal_str; ?></div>
                      <?php endif; ?>
                      <?php if (!$ent_str && !$sal_str): ?>
                        <span>-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                    No hay registros de actividad en la base de datos.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>

  <!-- ==========================================================================
       SCRIPTS: HOVER DEL SIDEBAR, MENÚ DE USUARIO Y CHART.JS
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

      // 3. Inicialización del Gráfico de Dona con Chart.js (Datos Dinámicos de PHP)
      const chartCanvas = document.getElementById('flotaDoughnutChart');
      if (chartCanvas) {
        const phpData = <?php echo $datos_grafico_json; ?>;
        
        const dataValues = [
          phpData.disponibles || 0,
          phpData.trabajando || 0,
          phpData.mantenimiento || 0
        ];

        const totalFlota = dataValues.reduce((a, b) => a + b, 0);
        const chartData = totalFlota > 0 ? dataValues : [1];
        const chartColors = totalFlota > 0 
          ? ['#10b981', '#0A192F', '#FFC107']
          : ['#e2e8f0'];
        const chartLabels = totalFlota > 0 
          ? ['Disponibles', 'Trabajando', 'En Mantenimiento']
          : ['Sin datos registrados'];

        new Chart(chartCanvas, {
          type: 'doughnut',
          data: {
            labels: chartLabels,
            datasets: [{
              data: chartData,
              backgroundColor: chartColors,
              borderColor: '#ffffff',
              borderWidth: 3,
              hoverOffset: totalFlota > 0 ? 6 : 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                enabled: totalFlota > 0,
                backgroundColor: '#0A192F',
                titleColor: '#FFC107',
                bodyColor: '#ffffff',
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                  label: function(context) {
                    const label = context.label || '';
                    const value = context.raw || 0;
                    return ` ${label}: ${value} máquinas`;
                  }
                }
              }
            },
            cutout: '70%'
          }
        });
      }
    });
  </script>

  <!-- Componente Chatbot Flotante (Izquierda) -->
  <?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
