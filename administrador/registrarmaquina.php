<?php
/**
 * Panel de Administración - Gestión e Inventario de Maquinarias
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
$mensaje_alerta = '';
$tipo_alerta = '';

// Asegurar que la columna caracteristicas sea VARCHAR(100) en la base de datos
if ($conexion_ok) {
    @$conexion->query("ALTER TABLE maquinarias MODIFY COLUMN caracteristicas VARCHAR(100)");
}

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

// Control para vaciar registros y dejar la tabla en 0 cuando el usuario lo solicite
if (isset($_GET['accion']) && $_GET['accion'] === 'vaciar_todo') {
    if ($conexion_ok) {
        try {
            $conexion->query("DELETE FROM historial_movimientos");
            $conexion->query("DELETE FROM maquinarias");
            @$conexion->query("ALTER TABLE maquinarias AUTO_INCREMENT = 1");
            @$conexion->query("ALTER TABLE historial_movimientos AUTO_INCREMENT = 1");
        } catch (Exception $e) {}
    }
    $_SESSION['mensaje_alerta_maquinas'] = "¡Todos los registros han sido vaciados! La lista y el Dashboard están en 0.";
    header("Location: registrarmaquina.php");
    exit;
}

// Control para eliminar un transporte vía GET con confirmación
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id_del = (int)$_GET['id'];
    if ($id_del > 0 && $conexion_ok) {
        try {
            $conexion->begin_transaction();
            $stmtH = $conexion->prepare("DELETE FROM historial_movimientos WHERE id_maquina = ?");
            if ($stmtH) {
                $stmtH->bind_param("i", $id_del);
                $stmtH->execute();
                $stmtH->close();
            }
            $stmtM = $conexion->prepare("DELETE FROM maquinarias WHERE id_maquina = ?");
            if ($stmtM) {
                $stmtM->bind_param("i", $id_del);
                $stmtM->execute();
                $stmtM->close();
            }
            $conexion->commit();
            $_SESSION['mensaje_alerta_maquinas'] = "¡Registro eliminado permanentemente del sistema!";
            header("Location: registrarmaquina.php");
            exit;
        } catch (Exception $e) {
            if ($conexion_ok) $conexion->rollback();
        }
    }
}

// ==============================================================================
// 2. PROCESAMIENTO: REGISTRAR NUEVO TRANSPORTE O ACTUALIZAR
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // CASO 1: REGISTRAR NUEVO (Única vez)
    if ($_POST['action'] === 'guardar_nuevo') {
        $categoria       = trim($_POST['categoria'] ?? 'Maquinaria');
        $placa_raw       = trim($_POST['placa'] ?? '');
        $placa           = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $placa_raw)); // Solo 6 caracteres alfanuméricos en mayúsculas
        $modelo          = trim($_POST['modelo'] ?? '');
        $caracteristicas = mb_substr(trim($_POST['caracteristicas'] ?? ''), 0, 100);
        $ubicacion       = trim($_POST['ubicacion_actual'] ?? '');
        $estado          = trim($_POST['estado'] ?? 'Disponible');
        $fecha_entrada   = !empty($_POST['fecha_entrada']) ? $_POST['fecha_entrada'] : null;
        $hora_entrada    = !empty($_POST['hora_entrada']) ? $_POST['hora_entrada'] : null;
        $fecha_salida    = !empty($_POST['fecha_salida']) ? $_POST['fecha_salida'] : null;
        $hora_salida     = !empty($_POST['hora_salida']) ? $_POST['hora_salida'] : null;

        // Validar categoría
        if (!in_array($categoria, ['Maquinaria', 'Vehiculo'])) {
            $categoria = 'Maquinaria';
        }

        // Validar placa exactamente a 6 caracteres alfanuméricos
        if (strlen($placa) !== 6) {
            $mensaje_alerta = "La placa debe contener exactamente 6 caracteres alfanuméricos en mayúsculas y sin guiones (ej: ABC123).";
            $tipo_alerta = "warning";
        } elseif (empty($modelo)) {
            $mensaje_alerta = "Por favor ingrese el modelo o nombre del transporte.";
            $tipo_alerta = "warning";
        } else {
            if ($conexion_ok) {
                try {
                    // Verificar si la placa ya existe
                    $checkStmt = $conexion->prepare("SELECT id_maquina FROM maquinarias WHERE placa = ?");
                    $checkStmt->bind_param("s", $placa);
                    $checkStmt->execute();
                    $checkStmt->store_result();
                    if ($checkStmt->num_rows > 0) {
                        $mensaje_alerta = "Ya existe un registro con la placa '$placa'. Ingrese una placa única.";
                        $tipo_alerta = "danger";
                        $checkStmt->close();
                    } else {
                        $checkStmt->close();
                        $stmt = $conexion->prepare("INSERT INTO maquinarias (categoria, placa, modelo, caracteristicas, ubicacion_actual, estado, fecha_entrada, hora_entrada, fecha_salida, hora_salida) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        if ($stmt) {
                            $stmt->bind_param("ssssssssss", $categoria, $placa, $modelo, $caracteristicas, $ubicacion, $estado, $fecha_entrada, $hora_entrada, $fecha_salida, $hora_salida);
                            if ($stmt->execute()) {
                                $nuevo_id = $conexion->insert_id;
                                $stmt->close();

                                // Insertar movimiento en historial_movimientos
                                $accion_mov = "Registro inicial de $categoria ($placa - $modelo)";
                                $stmtHist = $conexion->prepare("INSERT INTO historial_movimientos (id_maquina, accion, ubicacion, estado, fecha_entrada, hora_entrada, fecha_salida, hora_salida, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                                if ($stmtHist) {
                                    $stmtHist->bind_param("isssssss", $nuevo_id, $accion_mov, $ubicacion, $estado, $fecha_entrada, $hora_entrada, $fecha_salida, $hora_salida);
                                    $stmtHist->execute();
                                    $stmtHist->close();
                                }

                                $mensaje_alerta = "¡$categoria con placa $placa registrada con éxito en el sistema!";
                                $tipo_alerta = "success";
                            } else {
                                $mensaje_alerta = "Error al registrar en la base de datos: " . $stmt->error;
                                $tipo_alerta = "danger";
                                $stmt->close();
                            }
                        } else {
                            $mensaje_alerta = "Error al preparar inserción: " . $conexion->error;
                            $tipo_alerta = "danger";
                        }
                    }
                } catch (Exception $e) {
                    $mensaje_alerta = "Excepción al registrar: " . $e->getMessage();
                    $tipo_alerta = "danger";
                }
            }
        }
    }

    // CASO 2: ACTUALIZAR EXISTENTE (Solo campos operativos)
    if ($_POST['action'] === 'actualizar_maquinaria') {
        $id_maquina      = (int)($_POST['id_maquina'] ?? 0);
        $caracteristicas = mb_substr(trim($_POST['caracteristicas'] ?? ''), 0, 100);
        $ubicacion       = trim($_POST['ubicacion_actual'] ?? '');
        $estado          = trim($_POST['estado'] ?? 'Disponible');
        $fecha_entrada   = !empty($_POST['fecha_entrada']) ? $_POST['fecha_entrada'] : null;
        $hora_entrada    = !empty($_POST['hora_entrada']) ? $_POST['hora_entrada'] : null;
        $fecha_salida    = !empty($_POST['fecha_salida']) ? $_POST['fecha_salida'] : null;
        $hora_salida     = !empty($_POST['hora_salida']) ? $_POST['hora_salida'] : null;

        if ($id_maquina > 0 && $conexion_ok) {
            try {
                // Obtener datos actuales para conocer placa, modelo y categoría
                $stmtCheck = $conexion->prepare("SELECT categoria, placa, modelo FROM maquinarias WHERE id_maquina = ?");
                $stmtCheck->bind_param("i", $id_maquina);
                $stmtCheck->execute();
                $resData = $stmtCheck->get_result();
                $maqData = $resData->fetch_assoc();
                $stmtCheck->close();

                if ($maqData) {
                    $stmtUpdate = $conexion->prepare("UPDATE maquinarias SET caracteristicas = ?, ubicacion_actual = ?, estado = ?, fecha_entrada = ?, hora_entrada = ?, fecha_salida = ?, hora_salida = ? WHERE id_maquina = ?");
                    if ($stmtUpdate) {
                        $stmtUpdate->bind_param("sssssssi", $caracteristicas, $ubicacion, $estado, $fecha_entrada, $hora_entrada, $fecha_salida, $hora_salida, $id_maquina);
                        if ($stmtUpdate->execute()) {
                            $stmtUpdate->close();

                            // Registrar en historial_movimientos
                            $accion_mov = "Actualización operativa de " . $maqData['categoria'] . " (" . $maqData['placa'] . ")";
                            $stmtHist = $conexion->prepare("INSERT INTO historial_movimientos (id_maquina, accion, ubicacion, estado, fecha_entrada, hora_entrada, fecha_salida, hora_salida, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                            if ($stmtHist) {
                                $stmtHist->bind_param("isssssss", $id_maquina, $accion_mov, $ubicacion, $estado, $fecha_entrada, $hora_entrada, $fecha_salida, $hora_salida);
                                $stmtHist->execute();
                                $stmtHist->close();
                            }

                            $mensaje_alerta = "¡Datos de " . $maqData['placa'] . " actualizados correctamente!";
                            $tipo_alerta = "success";
                        } else {
                            $mensaje_alerta = "Error al actualizar: " . $stmtUpdate->error;
                            $tipo_alerta = "danger";
                            $stmtUpdate->close();
                        }
                    }
                } else {
                    $mensaje_alerta = "No se encontró el registro a actualizar.";
                    $tipo_alerta = "warning";
                }
            } catch (Exception $e) {
                $mensaje_alerta = "Excepción al actualizar: " . $e->getMessage();
                $tipo_alerta = "danger";
            }
        }
    }

    // CASO 3: ELIMINAR REGISTRO PERMANENTEMENTE
    if ($_POST['action'] === 'eliminar_maquinaria') {
        $id_maquina = (int)($_POST['id_maquina'] ?? 0);
        if ($id_maquina > 0 && $conexion_ok) {
            try {
                // Obtener datos del transporte antes de eliminar para el mensaje de alerta
                $stmtCheck = $conexion->prepare("SELECT categoria, placa, modelo FROM maquinarias WHERE id_maquina = ?");
                $stmtCheck->bind_param("i", $id_maquina);
                $stmtCheck->execute();
                $resData = $stmtCheck->get_result();
                $maqData = $resData->fetch_assoc();
                $stmtCheck->close();

                if ($maqData) {
                    $conexion->begin_transaction();

                    // 1. Eliminar movimientos asociados en historial_movimientos
                    $stmtHistDel = $conexion->prepare("DELETE FROM historial_movimientos WHERE id_maquina = ?");
                    if ($stmtHistDel) {
                        $stmtHistDel->bind_param("i", $id_maquina);
                        $stmtHistDel->execute();
                        $stmtHistDel->close();
                    }

                    // 2. Eliminar de la tabla maquinarias
                    $stmtDel = $conexion->prepare("DELETE FROM maquinarias WHERE id_maquina = ?");
                    if ($stmtDel) {
                        $stmtDel->bind_param("i", $id_maquina);
                        $stmtDel->execute();
                        $stmtDel->close();
                    }

                    $conexion->commit();
                    $mensaje_alerta = "¡" . htmlspecialchars($maqData['categoria']) . " con placa " . htmlspecialchars($maqData['placa']) . " (" . htmlspecialchars($maqData['modelo']) . ") ha sido eliminado permanentemente del sistema!";
                    $tipo_alerta = "success";
                } else {
                    $mensaje_alerta = "El registro a eliminar no fue encontrado.";
                    $tipo_alerta = "warning";
                }
            } catch (Exception $e) {
                if ($conexion_ok) {
                    $conexion->rollback();
                }
                $mensaje_alerta = "Error al eliminar el registro: " . $e->getMessage();
                $tipo_alerta = "danger";
            }
        }
    }
}

// ==============================================================================
// 3. CONSULTA DE MAQUINARIAS Y VEHÍCULOS REGISTRADOS
// ==============================================================================
$maquinarias_list = [];
$vehiculos_list   = [];

if ($conexion_ok) {
    try {
        $sql_query = "
            SELECT 
                id_maquina,
                categoria,
                placa,
                modelo,
                caracteristicas,
                ubicacion_actual,
                estado,
                fecha_entrada,
                hora_entrada,
                fecha_salida,
                hora_salida
            FROM maquinarias
            ORDER BY id_maquina DESC
        ";
        $res_all = $conexion->query($sql_query);
        if ($res_all && $res_all->num_rows > 0) {
            while ($row = $res_all->fetch_assoc()) {
                if ($row['categoria'] === 'Vehiculo') {
                    $vehiculos_list[] = $row;
                } else {
                    $maquinarias_list[] = $row;
                }
            }
        }
    } catch (Exception $e) {
        // Manejo de errores
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maquinarias | M&M Construcciones y Servicios S.A.S.</title>

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

    .panel-clean {
      background-color: #ffffff;
      border: 1px solid var(--border-subtle);
      border-radius: 14px;
      padding: 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      margin-bottom: 28px;
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

    /* Truncado de características largas y responsividad */
    .campo-caract {
      max-width: 180px;
    }

    .caract-truncate {
      display: inline-block;
      max-width: 170px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      vertical-align: middle;
    }

    .table-responsive {
      border-radius: 8px;
      -webkit-overflow-scrolling: touch;
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

    @media (max-width: 992px) {
      .campo-caract {
        max-width: 140px;
      }
      .caract-truncate {
        max-width: 130px;
      }
    }

    @media (max-width: 768px) {
      .admin-topbar {
        padding: 0 16px 0 80px;
      }
      .logo-floating-trigger {
        padding: 4px;
      }
      .admin-content {
        padding: 20px 12px 50px 12px;
      }
      .panel-clean {
        padding: 16px 14px;
      }
      .table-custom th, .table-custom td {
        padding: 10px 12px;
        font-size: 0.82rem;
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

        <!-- Opción 2: Maquinarias (Página Actual Activa) -->
        <a href="registrarmaquina.php" class="nav-item-btn active">
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
            <div class="dropdown-user-name"><?php echo htmlspecialchars($admin_nombre); ?></div>
            <div class="dropdown-user-email"><?php echo htmlspecialchars($admin_email); ?></div>
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

    <!-- Área de Contenido Principal: INVENTARIO Y REGISTRO DE MAQUINARIAS -->
    <main class="admin-content">

      <?php if (!empty($_SESSION['mensaje_alerta_maquinas'])): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
          <i class="bi bi-check-circle-fill me-2 fs-5"></i>
          <div><?php echo htmlspecialchars($_SESSION['mensaje_alerta_maquinas']); unset($_SESSION['mensaje_alerta_maquinas']); ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if (!empty($mensaje_alerta)): ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
          <i class="bi <?php echo ($tipo_alerta === 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2 fs-5"></i>
          <div><?php echo htmlspecialchars($mensaje_alerta); ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div class="section-header mb-0">
          <h1 class="section-title">Inventario de Maquinarias y Vehículos</h1>
          <p class="section-desc">Control y administración de la flota registrada en la base de datos.</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
          <a href="registrarmaquina.php?accion=vaciar_todo" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1" onclick="return confirm('¿Seguro que deseas vaciar todos los registros y dejar todo en 0?');" title="Vaciar todos los registros">
            <i class="bi bi-trash"></i>
            <span>Vaciar a 0</span>
          </a>
          <!-- Botón que abre el modal para registrar nuevo elemento -->
          <button type="button" class="btn-corp-yellow" data-bs-toggle="modal" data-bs-target="#modalRegistrarMaquina">
            <i class="bi bi-plus-lg"></i>
            <span>Registrar Nuevo</span>
          </button>
        </div>
      </div>

      <!-- ================================================================
           BLOQUE 1: MAQUINARIA
           ================================================================ -->
      <div class="panel-clean mb-4">
        <!-- Encabezado de sección Maquinaria -->
        <h2 class="fw-bold mb-3" style="font-size:1.25rem; color:var(--navy-deep); display:flex; align-items:center; gap:10px;">
          <i class="bi bi-gear-fill text-secondary"></i> Maquinaria
        </h2>

        <!-- Filtros Maquinaria -->
        <div class="row g-2 align-items-center mb-3">
          <div class="col-md-5">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
              <input type="text" id="filtroTexto" class="form-control border-start-0 bg-light" placeholder="Buscar por placa, modelo o características...">
            </div>
          </div>
          <div class="col-md-3">
            <select id="filtroEstado" class="form-select bg-light">
              <option value="">Todos los estados</option>
              <option value="Disponible">Disponible</option>
              <option value="Trabajando">Trabajando</option>
              <option value="En mantenimiento">En mantenimiento</option>
            </select>
          </div>
          <div class="col-md-4 text-md-end text-muted small">
            Total listadas: <strong id="contadorMaquinas"><?php echo count($maquinarias_list); ?></strong> máquinas
          </div>
        </div>

        <!-- Tabla Maquinaria -->
        <div class="table-responsive">
          <table class="table-custom" id="tablaMaquinarias">
            <thead>
              <tr>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Características</th>
                <th>Ubicación Actual</th>
                <th>Estado</th>
                <th>Entrada / Salida</th>
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
                    elseif (str_contains($est, 'mant') || str_contains($est, 'tall')) $badge = 'status-mant';

                    $entrada_txt = (!empty($maq['fecha_entrada']) ? date('d/m/Y', strtotime($maq['fecha_entrada'])) : '') . (!empty($maq['hora_entrada']) ? ' ' . substr($maq['hora_entrada'], 0, 5) : '');
                    $salida_txt  = (!empty($maq['fecha_salida']) ? date('d/m/Y', strtotime($maq['fecha_salida'])) : '') . (!empty($maq['hora_salida']) ? ' ' . substr($maq['hora_salida'], 0, 5) : '');
                  ?>
                  <?php 
                    $caract_raw = !empty($maq['caracteristicas']) ? $maq['caracteristicas'] : 'Sin descripción';
                    $caract_truncada = (mb_strlen($caract_raw) > 28) ? mb_substr($caract_raw, 0, 28) . '...' : $caract_raw;
                  ?>
                  <tr class="fila-maquinaria" data-estado="<?php echo htmlspecialchars($maq['estado']); ?>">
                    <td class="fw-bold text-dark campo-placa"><span class="badge bg-dark px-2 py-1"><?php echo htmlspecialchars($maq['placa']); ?></span></td>
                    <td><strong class="text-primary campo-modelo"><?php echo htmlspecialchars($maq['modelo']); ?></strong></td>
                    <td class="text-secondary small campo-caract">
                      <span class="caract-truncate" title="Clic en Actualizar para ver el texto completo">
                        <?php echo htmlspecialchars($caract_truncada); ?>
                      </span>
                    </td>
                    <td class="text-muted campo-lugar">
                      <i class="bi bi-geo-alt me-1 text-danger"></i><?php echo htmlspecialchars(!empty($maq['ubicacion_actual']) ? $maq['ubicacion_actual'] : 'Sin ubicación'); ?>
                    </td>
                    <td>
                      <span class="status-pill-badge <?php echo $badge; ?>">
                        <span class="status-dot-sm"></span>
                        <?php echo htmlspecialchars($maq['estado']); ?>
                      </span>
                    </td>
                    <td class="small text-muted">
                      <div><i class="bi bi-box-arrow-in-right text-success me-1"></i><?php echo $entrada_txt ?: 'No registrada'; ?></div>
                      <?php if ($salida_txt): ?>
                        <div><i class="bi bi-box-arrow-right text-warning me-1"></i><?php echo $salida_txt; ?></div>
                      <?php endif; ?>
                    </td>
                    <td style="text-align: right; padding-right: 16px; white-space: nowrap;">
                      <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                        <button type="button" 
                                class="btn btn-sm btn-outline-primary fw-semibold btn-abrir-actualizar"
                                data-id="<?php echo $maq['id_maquina']; ?>"
                                data-categoria="<?php echo htmlspecialchars($maq['categoria']); ?>"
                                data-placa="<?php echo htmlspecialchars($maq['placa']); ?>"
                                data-modelo="<?php echo htmlspecialchars($maq['modelo']); ?>"
                                data-caracteristicas="<?php echo htmlspecialchars($maq['caracteristicas'] ?? ''); ?>"
                                data-ubicacion="<?php echo htmlspecialchars($maq['ubicacion_actual'] ?? ''); ?>"
                                data-estado="<?php echo htmlspecialchars($maq['estado'] ?? 'Disponible'); ?>"
                                data-fecha-entrada="<?php echo htmlspecialchars($maq['fecha_entrada'] ?? ''); ?>"
                                data-hora-entrada="<?php echo htmlspecialchars($maq['hora_entrada'] ?? ''); ?>"
                                data-fecha-salida="<?php echo htmlspecialchars($maq['fecha_salida'] ?? ''); ?>"
                                data-hora-salida="<?php echo htmlspecialchars($maq['hora_salida'] ?? ''); ?>"
                                title="Actualizar registro">
                          <i class="bi bi-pencil-square me-1"></i> Actualizar
                        </button>
                        <form method="POST" action="registrarmaquina.php" class="d-inline m-0" onsubmit="return confirm('¿Está seguro de eliminar permanentemente la maquinaria con placa <?php echo htmlspecialchars($maq['placa']); ?>? Esta acción no se puede deshacer.');">
                          <input type="hidden" name="action" value="eliminar_maquinaria">
                          <input type="hidden" name="id_maquina" value="<?php echo (int)$maq['id_maquina']; ?>">
                          <button type="submit" class="btn btn-danger btn-sm fw-semibold" title="Eliminar maquinaria permanentemente">
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr id="filaVaciaMaquinaria">
                  <td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                    No hay maquinaria registrada en la base de datos.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ================================================================
           BLOQUE 2: VEHÍCULOS
           ================================================================ -->
      <div class="panel-clean mb-4">
        <!-- Encabezado de sección Vehículos -->
        <h2 class="fw-bold mb-3" style="font-size:1.25rem; color:var(--navy-deep); display:flex; align-items:center; gap:10px;">
          <i class="bi bi-car-front-fill text-secondary"></i> Vehículos
        </h2>

        <!-- Filtros Vehículos -->
        <div class="row g-2 align-items-center mb-3">
          <div class="col-md-5">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
              <input type="text" id="filtroTextoVehiculos" class="form-control border-start-0 bg-light" placeholder="Buscar por placa, modelo o características...">
            </div>
          </div>
          <div class="col-md-3">
            <select id="filtroEstadoVehiculos" class="form-select bg-light">
              <option value="">Todos los estados</option>
              <option value="Disponible">Disponible</option>
              <option value="Trabajando">Trabajando</option>
              <option value="En mantenimiento">En mantenimiento</option>
            </select>
          </div>
          <div class="col-md-4 text-md-end text-muted small">
            Total listadas: <strong id="contadorVehiculos"><?php echo count($vehiculos_list); ?></strong> vehículos
          </div>
        </div>

        <!-- Tabla Vehículos -->
        <div class="table-responsive">
          <table class="table-custom" id="tablaVehiculos">
            <thead>
              <tr>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Características</th>
                <th>Ubicación Actual</th>
                <th>Estado</th>
                <th>Entrada / Salida</th>
                <th style="text-align: right; padding-right: 20px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($vehiculos_list)): ?>
                <?php foreach ($vehiculos_list as $veh): ?>
                  <?php 
                    $est = strtolower($veh['estado'] ?? '');
                    $badge = 'status-inact';
                    if (str_contains($est, 'disp')) $badge = 'status-disp';
                    elseif (str_contains($est, 'trab') || str_contains($est, 'obra')) $badge = 'status-trab';
                    elseif (str_contains($est, 'mant') || str_contains($est, 'tall')) $badge = 'status-mant';

                    $entrada_txt = (!empty($veh['fecha_entrada']) ? date('d/m/Y', strtotime($veh['fecha_entrada'])) : '') . (!empty($veh['hora_entrada']) ? ' ' . substr($veh['hora_entrada'], 0, 5) : '');
                    $salida_txt  = (!empty($veh['fecha_salida']) ? date('d/m/Y', strtotime($veh['fecha_salida'])) : '') . (!empty($veh['hora_salida']) ? ' ' . substr($veh['hora_salida'], 0, 5) : '');
                  ?>
                  <?php 
                    $caract_raw_v = !empty($veh['caracteristicas']) ? $veh['caracteristicas'] : 'Sin descripción';
                    $caract_truncada_v = (mb_strlen($caract_raw_v) > 28) ? mb_substr($caract_raw_v, 0, 28) . '...' : $caract_raw_v;
                  ?>
                  <tr class="fila-vehiculo" data-estado="<?php echo htmlspecialchars($veh['estado']); ?>">
                    <td class="fw-bold text-dark campo-placa"><span class="badge bg-dark px-2 py-1"><?php echo htmlspecialchars($veh['placa']); ?></span></td>
                    <td><strong class="text-primary campo-modelo"><?php echo htmlspecialchars($veh['modelo']); ?></strong></td>
                    <td class="text-secondary small campo-caract">
                      <span class="caract-truncate" title="Clic en Actualizar para ver el texto completo">
                        <?php echo htmlspecialchars($caract_truncada_v); ?>
                      </span>
                    </td>
                    <td class="text-muted campo-lugar">
                      <i class="bi bi-geo-alt me-1 text-danger"></i><?php echo htmlspecialchars(!empty($veh['ubicacion_actual']) ? $veh['ubicacion_actual'] : 'Sin ubicación'); ?>
                    </td>
                    <td>
                      <span class="status-pill-badge <?php echo $badge; ?>">
                        <span class="status-dot-sm"></span>
                        <?php echo htmlspecialchars($veh['estado']); ?>
                      </span>
                    </td>
                    <td class="small text-muted">
                      <div><i class="bi bi-box-arrow-in-right text-success me-1"></i><?php echo $entrada_txt ?: 'No registrada'; ?></div>
                      <?php if ($salida_txt): ?>
                        <div><i class="bi bi-box-arrow-right text-warning me-1"></i><?php echo $salida_txt; ?></div>
                      <?php endif; ?>
                    </td>
                    <td style="text-align: right; padding-right: 16px; white-space: nowrap;">
                      <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                        <button type="button" 
                                class="btn btn-sm btn-outline-primary fw-semibold btn-abrir-actualizar"
                                data-id="<?php echo $veh['id_maquina']; ?>"
                                data-categoria="<?php echo htmlspecialchars($veh['categoria']); ?>"
                                data-placa="<?php echo htmlspecialchars($veh['placa']); ?>"
                                data-modelo="<?php echo htmlspecialchars($veh['modelo']); ?>"
                                data-caracteristicas="<?php echo htmlspecialchars($veh['caracteristicas'] ?? ''); ?>"
                                data-ubicacion="<?php echo htmlspecialchars($veh['ubicacion_actual'] ?? ''); ?>"
                                data-estado="<?php echo htmlspecialchars($veh['estado'] ?? 'Disponible'); ?>"
                                data-fecha-entrada="<?php echo htmlspecialchars($veh['fecha_entrada'] ?? ''); ?>"
                                data-hora-entrada="<?php echo htmlspecialchars($veh['hora_entrada'] ?? ''); ?>"
                                data-fecha-salida="<?php echo htmlspecialchars($veh['fecha_salida'] ?? ''); ?>"
                                data-hora-salida="<?php echo htmlspecialchars($veh['hora_salida'] ?? ''); ?>"
                                title="Actualizar registro">
                          <i class="bi bi-pencil-square me-1"></i> Actualizar
                        </button>
                        <form method="POST" action="registrarmaquina.php" class="d-inline m-0" onsubmit="return confirm('¿Está seguro de eliminar permanentemente el vehículo con placa <?php echo htmlspecialchars($veh['placa']); ?>? Esta acción no se puede deshacer.');">
                          <input type="hidden" name="action" value="eliminar_maquinaria">
                          <input type="hidden" name="id_maquina" value="<?php echo (int)$veh['id_maquina']; ?>">
                          <button type="submit" class="btn btn-danger btn-sm fw-semibold" title="Eliminar vehículo permanentemente">
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <!-- Fila vacía cuando no hay vehículos -->
                <tr id="filaVaciaVehiculos">
                  <td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-car-front fs-2 d-block mb-2 text-secondary"></i>
                    No hay vehículos registrados en la base de datos.
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
       MODAL 1: REGISTRAR NUEVO (POR ÚNICA VEZ)
       ========================================================================== -->
  <div class="modal fade" id="modalRegistrarMaquina" tabindex="-1" aria-labelledby="modalRegistrarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header text-white" style="background-color: var(--navy-deep);">
          <h5 class="modal-title fw-bold" id="modalRegistrarLabel">
            <i class="bi bi-plus-circle text-warning me-2"></i>Registrar Nuevo Transporte (Única vez)
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="registrarmaquina.php" id="formRegistrarNuevo">
          <input type="hidden" name="action" value="guardar_nuevo">
          <div class="modal-body p-4">
            
            <div class="row g-3">
              <!-- Categoría -->
              <div class="col-md-4">
                <label for="inputCategoria" class="form-label small fw-bold text-dark">Categoría *</label>
                <select class="form-select" id="inputCategoria" name="categoria" required>
                  <option value="Maquinaria">Maquinaria</option>
                  <option value="Vehiculo">Vehículo</option>
                </select>
              </div>

              <!-- Placa -->
              <div class="col-md-4">
                <label for="inputPlaca" class="form-label small fw-bold text-dark">Placa (Exactamente 6 caracteres) *</label>
                <input type="text" class="form-control text-uppercase fw-bold" id="inputPlaca" name="placa" 
                       placeholder="Ej: ABC123" maxlength="6" pattern="[A-Za-z0-9]{6}" 
                       title="Debe contener exactamente 6 caracteres alfanuméricos sin guiones" required>
                <div class="form-text small">6 caracteres alfanuméricos sin guiones.</div>
              </div>

              <!-- Modelo -->
              <div class="col-md-4">
                <label for="inputModelo" class="form-label small fw-bold text-dark">Modelo / Referencia *</label>
                <input type="text" class="form-control" id="inputModelo" name="modelo" placeholder="Ej: Caterpillar 320D / Hilux" required>
              </div>

              <!-- Características -->
              <div class="col-12">
                <label for="inputCaracteristicas" class="form-label small fw-bold text-dark">Características / Especificaciones (Máx. 100 caracteres)</label>
                <textarea class="form-control" id="inputCaracteristicas" name="caracteristicas" rows="2" maxlength="100" placeholder="Capacidad, potencia, kilometraje o detalles técnicos (máx. 100 caracteres)..."></textarea>
                <div class="form-text small text-muted">Límite: 100 caracteres.</div>
              </div>

              <!-- Ubicación Actual (Input de texto libre) -->
              <div class="col-md-6">
                <label for="inputUbicacion" class="form-label small fw-bold text-dark">Ubicación Actual (Texto Libre)</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-geo-alt text-danger"></i></span>
                  <input type="text" class="form-control" id="inputUbicacion" name="ubicacion_actual" placeholder="Ej: Frente Autopista Norte Km 15, Patio Central, Taller...">
                </div>
              </div>

              <!-- Estado -->
              <div class="col-md-6">
                <label for="selectEstado" class="form-label small fw-bold text-dark">Estado Operativo</label>
                <select class="form-select" id="selectEstado" name="estado">
                  <option value="Disponible" selected>Disponible</option>
                  <option value="Trabajando">Trabajando</option>
                  <option value="En mantenimiento">En mantenimiento</option>
                </select>
              </div>

              <!-- Fecha y Hora de Entrada -->
              <div class="col-md-6">
                <label for="inputFechaEntrada" class="form-label small fw-bold text-dark">Fecha de Entrada</label>
                <input type="date" class="form-control" id="inputFechaEntrada" name="fecha_entrada" value="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="col-md-6">
                <label for="inputHoraEntrada" class="form-label small fw-bold text-dark">Hora de Entrada</label>
                <input type="time" class="form-control" id="inputHoraEntrada" name="hora_entrada" value="<?php echo date('H:i'); ?>">
              </div>

              <!-- Fecha y Hora de Salida (Opcionales) -->
              <div class="col-md-6">
                <label for="inputFechaSalida" class="form-label small fw-bold text-dark">Fecha de Salida (Opcional)</label>
                <input type="date" class="form-control" id="inputFechaSalida" name="fecha_salida">
              </div>
              <div class="col-md-6">
                <label for="inputHoraSalida" class="form-label small fw-bold text-dark">Hora de Salida (Opcional)</label>
                <input type="time" class="form-control" id="inputHoraSalida" name="hora_salida">
              </div>
            </div>

          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn-corp-yellow">
              <i class="bi bi-save me-1"></i> Guardar Nuevo Transporte
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL 2: ACTUALIZAR TRANSPORTE (CATEGORÍA, PLACA Y MODELO BLOQUEADOS READONLY)
       ========================================================================== -->
  <div class="modal fade" id="modalActualizarMaquina" tabindex="-1" aria-labelledby="modalActualizarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header text-white" style="background-color: var(--navy-deep);">
          <h5 class="modal-title fw-bold" id="modalActualizarLabel">
            <i class="bi bi-pencil-square text-warning me-2"></i>Actualizar Estado y Movimiento
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="registrarmaquina.php" id="formActualizarMaquina">
          <input type="hidden" name="action" value="actualizar_maquinaria">
          <input type="hidden" name="id_maquina" id="editIdMaquina">
          <div class="modal-body p-4">
            
            <div class="row g-3">
              <!-- Datos bloqueados (Readonly) -->
              <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Categoría <i class="bi bi-lock-fill text-secondary ms-1"></i></label>
                <input type="text" class="form-control bg-light" id="editCategoria" readonly>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Placa <i class="bi bi-lock-fill text-secondary ms-1"></i></label>
                <input type="text" class="form-control bg-light fw-bold" id="editPlaca" readonly>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Modelo <i class="bi bi-lock-fill text-secondary ms-1"></i></label>
                <input type="text" class="form-control bg-light" id="editModelo" readonly>
              </div>

              <div class="col-12"><hr class="my-1 text-muted"></div>

              <!-- Campos editables permitidos -->
              <div class="col-12">
                <label for="editCaracteristicas" class="form-label small fw-bold text-dark">Características / Especificaciones (Máx. 100 caracteres)</label>
                <textarea class="form-control" id="editCaracteristicas" name="caracteristicas" rows="3" maxlength="100" placeholder="Detalles técnicos completos (hasta 100 caracteres)..."></textarea>
                <div class="form-text small text-muted">Aquí puedes visualizar y editar el texto completo de hasta 100 caracteres.</div>
              </div>

              <!-- Ubicación Actual (Input de texto libre) -->
              <div class="col-md-6">
                <label for="editUbicacion" class="form-label small fw-bold text-dark">Ubicación Actual (Texto Libre)</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-geo-alt text-danger"></i></span>
                  <input type="text" class="form-control" id="editUbicacion" name="ubicacion_actual" placeholder="Ubicación o frente de obra...">
                </div>
              </div>

              <!-- Estado -->
              <div class="col-md-6">
                <label for="editEstado" class="form-label small fw-bold text-dark">Estado Operativo</label>
                <select class="form-select" id="editEstado" name="estado">
                  <option value="Disponible">Disponible</option>
                  <option value="Trabajando">Trabajando</option>
                  <option value="En mantenimiento">En mantenimiento</option>
                </select>
              </div>

              <!-- Fechas y horas de entrada/salida -->
              <div class="col-md-6">
                <label for="editFechaEntrada" class="form-label small fw-bold text-dark">Fecha de Entrada</label>
                <input type="date" class="form-control" id="editFechaEntrada" name="fecha_entrada">
              </div>
              <div class="col-md-6">
                <label for="editHoraEntrada" class="form-label small fw-bold text-dark">Hora de Entrada</label>
                <input type="time" class="form-control" id="editHoraEntrada" name="hora_entrada">
              </div>

              <div class="col-md-6">
                <label for="editFechaSalida" class="form-label small fw-bold text-dark">Fecha de Salida</label>
                <input type="date" class="form-control" id="editFechaSalida" name="fecha_salida">
              </div>
              <div class="col-md-6">
                <label for="editHoraSalida" class="form-label small fw-bold text-dark">Hora de Salida</label>
                <input type="time" class="form-control" id="editHoraSalida" name="hora_salida">
              </div>
            </div>

          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn-corp-yellow">
              <i class="bi bi-check-lg me-1"></i> Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       SCRIPTS: HOVER DEL SIDEBAR, FILTROS EN TIEMPO REAL Y MODALES
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
        trigger.addEventListener('mouseenter', () => {
          if (isHoverCapable()) openSidebar();
        });

        trigger.addEventListener('mouseleave', (e) => {
          if (isHoverCapable() && sidebar && !sidebar.contains(e.relatedTarget)) {
            closeTimeout = setTimeout(closeSidebar, 220);
          }
        });

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

      if (userAvatarBtn && userDropdown) {
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
      }

      // 3. Filtrado dinámico en tiempo real de la tabla de Maquinaria
      const filtroTexto = document.getElementById('filtroTexto');
      const filtroEstado = document.getElementById('filtroEstado');
      const filasM = document.querySelectorAll('.fila-maquinaria');
      const contadorM = document.getElementById('contadorMaquinas');

      function filtrarTablaMaquinaria() {
        const query = (filtroTexto.value || '').toLowerCase().trim();
        const estadoSel = (filtroEstado.value || '').toLowerCase().trim();
        let visibles = 0;

        filasM.forEach(fila => {
          const texto = fila.textContent.toLowerCase();
          const estadoFila = (fila.getAttribute('data-estado') || '').toLowerCase();

          const coincideTexto = !query || texto.includes(query);
          const coincideEstado = !estadoSel || estadoFila.includes(estadoSel);

          if (coincideTexto && coincideEstado) {
            fila.style.display = '';
            visibles++;
          } else {
            fila.style.display = 'none';
          }
        });

        if (contadorM) contadorM.textContent = visibles;
      }

      if (filtroTexto) filtroTexto.addEventListener('input', filtrarTablaMaquinaria);
      if (filtroEstado) filtroEstado.addEventListener('change', filtrarTablaMaquinaria);

      // 4. Filtrado dinámico en tiempo real de la tabla de Vehículos
      const filtroTextoV = document.getElementById('filtroTextoVehiculos');
      const filtroEstadoV = document.getElementById('filtroEstadoVehiculos');
      const filasV = document.querySelectorAll('.fila-vehiculo');
      const contadorV = document.getElementById('contadorVehiculos');

      function filtrarTablaVehiculos() {
        const query = (filtroTextoV.value || '').toLowerCase().trim();
        const estadoSel = (filtroEstadoV.value || '').toLowerCase().trim();
        let visibles = 0;

        filasV.forEach(fila => {
          const texto = fila.textContent.toLowerCase();
          const estadoFila = (fila.getAttribute('data-estado') || '').toLowerCase();

          const coincideTexto = !query || texto.includes(query);
          const coincideEstado = !estadoSel || estadoFila.includes(estadoSel);

          if (coincideTexto && coincideEstado) {
            fila.style.display = '';
            visibles++;
          } else {
            fila.style.display = 'none';
          }
        });

        if (contadorV) contadorV.textContent = visibles;
      }

      if (filtroTextoV) filtroTextoV.addEventListener('input', filtrarTablaVehiculos);
      if (filtroEstadoV) filtroEstadoV.addEventListener('change', filtrarTablaVehiculos);

      // 5. Configurar Modal de Actualización (Pasa datos de la fila con Categoria, Placa y Modelo readonly)
      const modalActualizarEl = document.getElementById('modalActualizarMaquina');
      const modalActualizarBS = modalActualizarEl ? new bootstrap.Modal(modalActualizarEl) : null;

      document.querySelectorAll('.btn-abrir-actualizar').forEach(btn => {
        btn.addEventListener('click', () => {
          document.getElementById('editIdMaquina').value       = btn.getAttribute('data-id') || '';
          document.getElementById('editCategoria').value       = btn.getAttribute('data-categoria') || '';
          document.getElementById('editPlaca').value           = btn.getAttribute('data-placa') || '';
          document.getElementById('editModelo').value          = btn.getAttribute('data-modelo') || '';
          document.getElementById('editCaracteristicas').value = btn.getAttribute('data-caracteristicas') || '';
          document.getElementById('editUbicacion').value       = btn.getAttribute('data-ubicacion') || '';
          document.getElementById('editEstado').value          = btn.getAttribute('data-estado') || 'Disponible';
          document.getElementById('editFechaEntrada').value    = btn.getAttribute('data-fecha-entrada') || '';
          document.getElementById('editHoraEntrada').value     = btn.getAttribute('data-hora-entrada') || '';
          document.getElementById('editFechaSalida').value     = btn.getAttribute('data-fecha-salida') || '';
          document.getElementById('editHoraSalida').value      = btn.getAttribute('data-hora-salida') || '';

          if (modalActualizarBS) {
            modalActualizarBS.show();
          }
        });
      });

      // 6. Validación de placa en el formulario de registro (Solo 6 caracteres alfanuméricos)
      const inputPlaca = document.getElementById('inputPlaca');
      if (inputPlaca) {
        inputPlaca.addEventListener('input', () => {
          inputPlaca.value = inputPlaca.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
      }
    });
  </script>

  <!-- Componente Chatbot Flotante (Izquierda) -->
  <?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>
