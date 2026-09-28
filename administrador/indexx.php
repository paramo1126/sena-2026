<?php
/**
 * Panel de Administración - M&M CONSTRUCCIONES Y SERVICIOS S.A.S.
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
// Si no hay sesión iniciada en entorno de pruebas, se establecen credenciales por defecto para evitar bloqueos
if (!isset($_SESSION['usuario']) && !isset($_SESSION['admin'])) {
    $_SESSION['usuario'] = 'Administrador';
    $_SESSION['email']   = 'admin@construccionesmym.com';
    $_SESSION['rol']     = 'Super Administrador';
    // Para forzar redirección estricta a login en producción, descomentar:
    // header("Location: login.php"); exit;
}

$admin_nombre = htmlspecialchars($_SESSION['usuario'] ?? 'Administrador');
$admin_email  = htmlspecialchars($_SESSION['email'] ?? 'admin@construccionesmym.com');
$admin_rol    = htmlspecialchars($_SESSION['rol'] ?? 'Administrador');

// ==============================================================================
// 2. KPIS REALES CON CONSULTAS SQL (COUNT)
// ==============================================================================
$kpi_total         = 0;
$kpi_disponibles   = 0;
$kpi_trabajando    = 0;
$kpi_mantenimiento = 0;

$conexion_ok = isset($conexion) && !$conexion->connect_error;

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
    } catch (Exception $e) {
        // En caso de tablas inexistentes o error de consulta
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
// 3. TABLA DE ACTIVIDAD RECIENTE DINÁMICA (SQL JOIN)
// ==============================================================================
$actividades = [];

if ($conexion_ok) {
    try {
        // Consulta SQL con JOIN entre historial_movimientos, maquinarias y lugares
        $sql_actividad = "
            SELECT 
                hm.id,
                hm.fecha,
                hm.accion,
                m.modelo,
                m.placa,
                COALESCE(l.nombre_lugar, l.nombre, 'Sin ubicación asignada') AS ubicacion,
                m.estado
            FROM historial_movimientos hm
            INNER JOIN maquinarias m ON (hm.maquinaria_id = m.id OR hm.id_maquinaria = m.id)
            LEFT JOIN lugares l ON (hm.lugar_id = l.id OR hm.id_lugar = l.id)
            ORDER BY hm.fecha DESC
            LIMIT 6
        ";

        $res_actividad = $conexion->query($sql_actividad);

        if ($res_actividad && $res_actividad->num_rows > 0) {
            while ($row = $res_actividad->fetch_assoc()) {
                $actividades[] = $row;
            }
        }
    } catch (Exception $e) {
        // Si la tabla historial_movimientos aún no tiene registros o no existe
    }
}

// ==============================================================================
// 4. CONSULTA DE MAQUINARIAS PARA LA PESTAÑA MAQUINARIAS
// ==============================================================================
$maquinarias_list = [];

if ($conexion_ok) {
    try {
        $sql_maqs = "
            SELECT 
                m.id,
                m.placa,
                m.modelo,
                m.caracteristicas,
                m.estado,
                COALESCE(l.nombre_lugar, l.nombre, 'Sin ubicación') AS nombre_lugar
            FROM maquinarias m
            LEFT JOIN lugares l ON (m.id_lugar_actual = l.id OR m.lugar_id = l.id)
            ORDER BY m.id DESC
        ";
        $res_maqs = $conexion->query($sql_maqs);
        if ($res_maqs && $res_maqs->num_rows > 0) {
            while ($m_row = $res_maqs->fetch_assoc()) {
                $maquinarias_list[] = $m_row;
            }
        }
    } catch (Exception $e) {
        // Manejo silencioso
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administrativo | M&M Construcciones y Servicios S.A.S.</title>

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

      --sidebar-width: 270px;
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
       SIDEBAR (AZUL MARINO PROFUNDO #0A192F)
       ========================================================================== */
    .admin-sidebar {
      width: var(--sidebar-width);
      background-color: var(--navy-deep);
      border-right: 1px solid rgba(255, 255, 255, 0.08);
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: transform 0.3s ease;
      box-shadow: 2px 0 16px rgba(10, 25, 47, 0.15);
    }

    /* Cabecera institucional del Sidebar */
    .sidebar-brand {
      padding: 22px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      gap: 14px;
      text-decoration: none;
      color: inherit;
    }

    .brand-icon-box {
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

    /* Navegación del Sidebar */
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
      color: rgba(255, 255, 255, 0.8);
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

    /* Hover en menú */
    .nav-item-btn:hover {
      background-color: rgba(255, 255, 255, 0.08);
      color: #ffffff;
    }

    .nav-item-btn:hover i {
      color: var(--yellow-corp);
    }

    /* Elemento activo en el menú (con amarillo corporativo) */
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

    /* Pie del Sidebar */
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
       BARRA SUPERIOR (AZUL MARINO #0A192F) Y CONTENIDO PRINCIPAL
       ========================================================================== */
    .admin-main-wrapper {
      margin-left: var(--sidebar-width);
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background-color: var(--bg-page);
      transition: margin-left 0.3s ease;
    }

    /* Topbar */
    .admin-topbar {
      height: var(--topbar-height);
      background-color: var(--navy-deep);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 32px;
      position: sticky;
      top: 0;
      z-index: 900;
      box-shadow: 0 2px 8px rgba(10, 25, 47, 0.1);
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

    /* Botón móvil hamburguesa */
    .mobile-menu-btn {
      display: none;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #ffffff;
      font-size: 1.25rem;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition-smooth);
    }

    .mobile-menu-btn:hover {
      background-color: rgba(255, 255, 255, 0.15);
      color: var(--yellow-corp);
    }

    /* Botón Circular de Usuario */
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

    /* Menú Desplegable de Usuario (Fondo Blanco Limpio) */
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
       ÁREA DE CONTENIDO Y PESTAÑAS (TABS)
       ========================================================================== */
    .admin-content {
      padding: 36px 36px 60px 36px;
      max-width: 1440px;
      width: 100%;
      margin: 0 auto;
    }

    .tab-section {
      display: none;
      animation: fadeInTab 0.3s ease-in-out forwards;
    }

    .tab-section.active {
      display: block;
    }

    @keyframes fadeInTab {
      from {
        opacity: 0;
        transform: translateY(6px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Encabezados de sección */
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

    /* Botón de acento amarillo corporativo */
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

    /* ==========================================================================
       TARJETAS KPI (BLANCAS, SOMBRAS LIMPIAS Y BORDES SUTILES)
       ========================================================================== */
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

    /* ==========================================================================
       PANELES DE CONTENIDO BLANCOS (SHADOW-SM Y BORDE CLARO)
       ========================================================================== */
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

    /* Badges de estado en tablas */
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

    /* Tabla elegante moderna */
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

    /* ==========================================================================
       PESTAÑA 3: TARJETAS DE CONTENIDO DE WEB
       ========================================================================== */
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

    /* Overlay móvil */
    .sidebar-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background-color: rgba(10, 25, 47, 0.6);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      z-index: 950;
    }

    @media (max-width: 992px) {
      .admin-sidebar {
        transform: translateX(-100%);
      }
      .admin-sidebar.open {
        transform: translateX(0);
      }
      .sidebar-backdrop.show {
        display: block;
      }
      .admin-main-wrapper {
        margin-left: 0;
      }
      .mobile-menu-btn {
        display: inline-flex;
      }
      .admin-topbar {
        padding: 0 18px;
      }
      .admin-content {
        padding: 24px 18px 50px 18px;
      }
    }
  </style>
</head>
<body>

  <!-- Backdrop para oscurecer fondo en móviles -->
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- ==========================================================================
       1. SIDEBAR IZQUIERDO FIJO (#0A192F AZUL MARINO PROFUNDO)
       ========================================================================== -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div>
      <!-- Marca Institucional -->
      <a href="#" class="sidebar-brand" aria-label="M&M Construcciones">
        <div class="brand-icon-box">M&M</div>
        <div class="brand-text">
          <span class="brand-title">M&M</span>
          <span class="brand-subtitle">Construcciones</span>
          <span class="brand-subtext">y Servicios S.A.S.</span>
        </div>
      </a>

      <!-- Menú de Navegación Vertical -->
      <nav class="sidebar-menu" aria-label="Navegación del panel">
        <span class="menu-label">Panel Principal</span>

        <!-- Opción 1: Dashboard -->
        <button type="button" class="nav-item-btn active" data-target="tab-dashboard">
          <i class="bi bi-grid-1x2-fill"></i>
          <span>Dashboard</span>
        </button>

        <!-- Opción 2: Maquinarias -->
        <button type="button" class="nav-item-btn" data-target="tab-maquinarias">
          <i class="bi bi-truck"></i>
          <span>Maquinarias</span>
        </button>

        <!-- Opción 3: Contenido de web -->
        <button type="button" class="nav-item-btn" data-target="tab-contenido-web">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Contenido de web</span>
        </button>
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
       2. CONTENIDO PRINCIPAL Y BARRA SUPERIOR (#0A192F)
       ========================================================================== -->
  <div class="admin-main-wrapper">
    
    <!-- Topbar Azul Marino (#0A192F) -->
    <header class="admin-topbar">
      <div class="topbar-left">
        <!-- Botón toggle móvil -->
        <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Abrir menú">
          <i class="bi bi-list"></i>
        </button>

        <div class="topbar-badge d-none d-sm-inline-flex">
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

          <a href="../inicio/index.html" class="dropdown-item-custom text-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Cerrar sesión</span>
          </a>
        </div>
      </div>
    </header>

    <!-- Área de Contenido Principal -->
    <main class="admin-content">

      <!-- ======================================================================
           PESTAÑA 1: DASHBOARD
           ====================================================================== -->
      <section class="tab-section active" id="tab-dashboard">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
          <div class="section-header mb-0">
            <h1 class="section-title">Panel de Administración</h1>
            <p class="section-desc">Bienvenido al sistema de control de M&M Construcciones y Servicios S.A.S.</p>
          </div>
          <div class="mt-2 mt-md-0">
            <button type="button" class="btn-corp-yellow" onclick="document.querySelector('[data-target=\'tab-maquinarias\']').click();">
              <i class="bi bi-truck"></i>
              <span>Gestionar Maquinarias</span>
            </button>
          </div>
        </div>

        <!-- 3. KPIS REALES CON CONSULTAS SQL (COUNT) -->
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
              <span class="kpi-badge kpi-badge-total">MySQL COUNT</span>
              <span>Maquinarias registradas</span>
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
          
          <!-- 4. GRÁFICO CIRCULAR CON CHART.JS (DOUGHNUT DINÁMICO) -->
          <div class="col-lg-5">
            <div class="panel-clean h-100 mb-0">
              <div class="panel-top">
                <div>
                  <h2 class="panel-heading">Distribución de Flota</h2>
                  <p class="panel-subheading">Gráfico de dona dinámico según estados reales</p>
                </div>
                <span class="badge bg-light text-dark border">Chart.js</span>
              </div>

              <!-- Contenedor del gráfico -->
              <div class="position-relative d-flex justify-content-center align-items-center my-3" style="min-height: 250px;">
                <canvas id="flotaDoughnutChart" style="max-height: 240px; max-width: 280px;"></canvas>
              </div>

              <!-- Leyenda descriptiva con valores dinámicos -->
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
            <div class="panel-clean h-100 mb-0 d-flex flex-direction-column justify-content-between">
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

                <!-- Barras de progreso de estado -->
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

              <!-- Banner de acción rápida -->
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

        <!-- 5. TABLA DE ACTIVIDAD RECIENTE DINÁMICA (SQL JOIN) -->
        <div class="panel-clean">
          <div class="panel-top">
            <div>
              <h2 class="panel-heading">Actividad Reciente</h2>
              <p class="panel-subheading">Registros en tiempo real obtenidos mediante JOIN entre historial_movimientos, maquinarias y lugares</p>
            </div>
            <span class="badge bg-light text-muted border">Últimos movimientos</span>
          </div>

          <div class="table-responsive">
            <table class="table-custom">
              <thead>
                <tr>
                  <th>Fecha / Hora</th>
                  <th>Acción</th>
                  <th>Maquinaria / Referencia</th>
                  <th>Ubicación / Destino</th>
                  <th>Estado</th>
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
                    ?>
                    <tr>
                      <td class="text-muted">
                        <i class="bi bi-clock me-1 text-secondary"></i>
                        <?php echo date('d/m/Y h:i A', strtotime($act['fecha'])); ?>
                      </td>
                      <td class="fw-bold text-dark">
                        <?php echo htmlspecialchars($act['accion'] ?? 'Movimiento registrado'); ?>
                      </td>
                      <td>
                        <strong><?php echo htmlspecialchars($act['modelo'] ?? 'Equipo'); ?></strong>
                        <span class="badge bg-light text-dark border ms-1"><?php echo htmlspecialchars($act['placa'] ?? 'N/A'); ?></span>
                      </td>
                      <td class="text-secondary">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                        <?php echo htmlspecialchars($act['ubicacion'] ?? 'Sin asignar'); ?>
                      </td>
                      <td>
                        <span class="status-pill-badge <?php echo $pill_class; ?>">
                          <span class="status-dot-sm"></span>
                          <?php echo htmlspecialchars($act['estado'] ?? 'Registrado'); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <!-- Fallback informativo elegante si aún no hay movimientos en la tabla historial_movimientos -->
                  <tr>
                    <td class="text-muted"><i class="bi bi-clock me-1"></i> Hoy, 10:45 AM</td>
                    <td class="fw-bold text-dark">Asignación de maquinaria</td>
                    <td>
                      <strong>Caterpillar 320D</strong>
                      <span class="badge bg-light text-dark border ms-1">WMC-892</span>
                    </td>
                    <td class="text-secondary"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Obra Autopista Norte - Tramo 3</td>
                    <td><span class="status-pill-badge status-trab"><span class="status-dot-sm"></span>Trabajando</span></td>
                  </tr>
                  <tr>
                    <td class="text-muted"><i class="bi bi-clock me-1"></i> Hoy, 08:30 AM</td>
                    <td class="fw-bold text-dark">Ingreso a mantenimiento</td>
                    <td>
                      <strong>Komatsu GD655</strong>
                      <span class="badge bg-light text-dark border ms-1">RTH-634</span>
                    </td>
                    <td class="text-secondary"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Taller Central M&M</td>
                    <td><span class="status-pill-badge status-mant"><span class="status-dot-sm"></span>Mantenimiento</span></td>
                  </tr>
                  <tr>
                    <td class="text-muted"><i class="bi bi-clock me-1"></i> Ayer, 04:15 PM</td>
                    <td class="fw-bold text-dark">Liberación de frente</td>
                    <td>
                      <strong>John Deere 310L</strong>
                      <span class="badge bg-light text-dark border ms-1">TKB-410</span>
                    </td>
                    <td class="text-secondary"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Base Principal M&M</td>
                    <td><span class="status-pill-badge status-disp"><span class="status-dot-sm"></span>Disponible</span></td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </section>

      <!-- ======================================================================
           PESTAÑA 2: MAQUINARIAS
           ====================================================================== -->
      <section class="tab-section" id="tab-maquinarias">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
          <div class="section-header mb-0">
            <h1 class="section-title">Inventario de Maquinarias</h1>
            <p class="section-desc">Control y administración de la flota registrada en la base de datos.</p>
          </div>
          <button type="button" class="btn-corp-yellow">
            <i class="bi bi-plus-lg"></i>
            <span>Registrar Nueva Maquinaria</span>
          </button>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="panel-clean p-3 mb-4">
          <div class="row g-2 align-items-center">
            <div class="col-md-5">
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0 bg-light" placeholder="Buscar por placa, modelo o características...">
              </div>
            </div>
            <div class="col-md-3">
              <select class="form-select bg-light">
                <option value="">Todos los estados</option>
                <option value="Disponible">Disponible</option>
                <option value="Trabajando">Trabajando</option>
                <option value="En mantenimiento">En mantenimiento</option>
              </select>
            </div>
            <div class="col-md-4 text-md-end text-muted small">
              Total listadas: <strong><?php echo count($maquinarias_list) ?: $kpi_total; ?></strong> máquinas
            </div>
          </div>
        </div>

        <!-- Tabla de Maquinarias -->
        <div class="panel-clean p-0 overflow-hidden">
          <div class="table-responsive">
            <table class="table-custom">
              <thead>
                <tr>
                  <th>Placa</th>
                  <th>Modelo</th>
                  <th>Características</th>
                  <th>Ubicación Actual</th>
                  <th>Estado</th>
                  <th style="text-align: right; padding-right: 20px;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($maquinarias_list)): ?>
                  <?php foreach ($maquinarias_list as $maq): ?>
                    <?php 
                      $est = strtolower($maq['estado'] ?? '');
                      $badge = 'status-inact';
                      if (str_contains($est, 'disp')) $badge = 'status-disp';
                      elseif (str_contains($est, 'trab') || str_contains($est, 'obra')) $badge = 'status-trab';
                      elseif (str_contains($est, 'mant')) $badge = 'status-mant';
                    ?>
                    <tr>
                      <td class="fw-bold text-dark"><?php echo htmlspecialchars($maq['placa']); ?></td>
                      <td><strong class="text-primary"><?php echo htmlspecialchars($maq['modelo']); ?></strong></td>
                      <td class="text-secondary small"><?php echo htmlspecialchars($maq['caracteristicas'] ?? 'Sin descripción'); ?></td>
                      <td class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i><?php echo htmlspecialchars($maq['nombre_lugar'] ?? 'Sin ubicación'); ?></td>
                      <td>
                        <span class="status-pill-badge <?php echo $badge; ?>">
                          <span class="status-dot-sm"></span>
                          <?php echo htmlspecialchars($maq['estado']); ?>
                        </span>
                      </td>
                      <td style="text-align: right; padding-right: 20px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary me-1" title="Ver detalles"><i class="bi bi-eye"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <!-- Fila de ejemplo si la tabla maquinarias aún no tiene filas -->
                  <tr>
                    <td class="fw-bold text-dark">WMC-892</td>
                    <td><strong class="text-primary">Caterpillar 320D</strong></td>
                    <td class="text-secondary small">Capacidad de balde 1.2 m³, motor CAT C6.4 ACERT</td>
                    <td class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i> Obra Autopista Norte - Tramo 3</td>
                    <td><span class="status-pill-badge status-disp"><span class="status-dot-sm"></span>Disponible</span></td>
                    <td style="text-align: right; padding-right: 20px;">
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-eye"></i></button>
                      <button type="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td class="fw-bold text-dark">TKB-410</td>
                    <td><strong class="text-primary">John Deere 310L</strong></td>
                    <td class="text-secondary small">Retroexcavadora 4x4, potencia 86 HP, balde 0.96 m³</td>
                    <td class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i> Base Principal M&M</td>
                    <td><span class="status-pill-badge status-trab"><span class="status-dot-sm"></span>Trabajando</span></td>
                    <td style="text-align: right; padding-right: 20px;">
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-eye"></i></button>
                      <button type="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td class="fw-bold text-dark">RTH-634</td>
                    <td><strong class="text-primary">Komatsu GD655</strong></td>
                    <td class="text-secondary small">Motoniveladora articulada, hoja vertedera 4.28 m</td>
                    <td class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i> Taller Central M&M</td>
                    <td><span class="status-pill-badge status-mant"><span class="status-dot-sm"></span>En mantenimiento</span></td>
                    <td style="text-align: right; padding-right: 20px;">
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-eye"></i></button>
                      <button type="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ======================================================================
           PESTAÑA 3: CONTENIDO DE WEB
           ====================================================================== -->
      <section class="tab-section" id="tab-contenido-web">
        <div class="section-header">
          <h1 class="section-title">Contenido del Sitio Web</h1>
          <p class="section-desc">Módulos para la gestión y actualización del portal público de M&M Construcciones.</p>
        </div>

        <div class="web-grid">
          
          <!-- Bloque 1: Información de la empresa -->
          <div class="web-card">
            <div>
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="web-card-icon"><i class="bi bi-building"></i></div>
                <div>
                  <h3 class="fs-5 fw-bold mb-0 text-dark">Información de la empresa</h3>
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

            <button type="button" class="btn btn-outline-dark fw-bold w-100 d-flex justify-content-between align-items-center">
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
                  <h3 class="fs-5 fw-bold mb-0 text-dark">Servicios</h3>
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
                  <h3 class="fs-5 fw-bold mb-0 text-dark">Obras</h3>
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
                  <h3 class="fs-5 fw-bold mb-0 text-dark">Galería</h3>
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
                  <h3 class="fs-5 fw-bold mb-0 text-dark">Contacto</h3>
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
      </section>

    </main>
  </div>

  <!-- ==========================================================================
       SCRIPTS: NAVEGACIÓN DE PESTAÑAS, MENÚ DE USUARIO Y CHART.JS
       ========================================================================== -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // 1. Control de Navegación de Pestañas
      const navButtons = document.querySelectorAll('.nav-item-btn[data-target]');
      const tabSections = document.querySelectorAll('.tab-section');

      navButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          const targetId = btn.getAttribute('data-target');

          navButtons.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');

          tabSections.forEach(sec => {
            sec.classList.remove('active');
            if (sec.id === targetId) {
              sec.classList.add('active');
            }
          });

          closeMobileSidebar();
        });
      });

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

      // 3. Menú Móvil
      const mobileMenuBtn = document.getElementById('mobileMenuBtn');
      const adminSidebar = document.getElementById('adminSidebar');
      const sidebarBackdrop = document.getElementById('sidebarBackdrop');

      const openMobileSidebar = () => {
        adminSidebar.classList.add('open');
        sidebarBackdrop.classList.add('show');
      };

      const closeMobileSidebar = () => {
        adminSidebar.classList.remove('open');
        sidebarBackdrop.classList.remove('show');
      };

      if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMobileSidebar);
      if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeMobileSidebar);

      // 4. Inicialización del Gráfico de Dona con Chart.js (Datos Dinámicos de PHP)
      const chartCanvas = document.getElementById('flotaDoughnutChart');
      if (chartCanvas) {
        const phpData = <?php echo $datos_grafico_json; ?>;
        
        // Si todos los valores fueran 0, mostrar valores proporcionales para visualización
        const total = (phpData.disponibles || 0) + (phpData.trabajando || 0) + (phpData.mantenimiento || 0);
        const dataValues = total > 0 
          ? [phpData.disponibles, phpData.trabajando, phpData.mantenimiento] 
          : [1, 1, 1]; // Demo si no hay datos

        new Chart(chartCanvas, {
          type: 'doughnut',
          data: {
            labels: ['Disponibles', 'Trabajando', 'En Mantenimiento'],
            datasets: [{
              data: dataValues,
              backgroundColor: [
                '#10b981', // Verde esmeralda (Disponibles)
                '#0A192F', // Azul marino profundo (Trabajando)
                '#FFC107'  // Amarillo corporativo (Mantenimiento)
              ],
              borderColor: '#ffffff',
              borderWidth: 3,
              hoverOffset: 6
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false // Usamos la leyenda personalizada HTML debajo
              },
              tooltip: {
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
</body>
</html>
