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

// Validación de sesión del administrador
if (!isset($_SESSION['usuario']) && !isset($_SESSION['admin'])) {
    $_SESSION['usuario'] = 'Administrador';
    $_SESSION['email']   = 'admin@construccionesmym.com';
    $_SESSION['rol']     = 'Super Administrador';
}

$admin_nombre = htmlspecialchars($_SESSION['usuario'] ?? 'Administrador');
$admin_email  = htmlspecialchars($_SESSION['email'] ?? 'admin@construccionesmym.com');
$admin_rol    = htmlspecialchars($_SESSION['rol'] ?? 'Administrador');
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

    <!-- Área de Contenido Principal: CONTENIDO DE LA WEB -->
    <main class="admin-content">

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
    });
  </script>

  <!-- Componente Chatbot Flotante (Izquierda) -->
  <?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
