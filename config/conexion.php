<?php
/**
 * ==============================================================================
 * M&M CONSTRUCCIONES Y SERVICIOS S.A.S.
 * Módulo Centralizado de Conexión a Base de Datos (MySQLi / PHP 8+)
 * ==============================================================================
 * 
 * Reglas de Arquitectura:
 * 1. Única fuente de verdad: Todas las credenciales residen exclusivamente aquí.
 * 2. Soporte para variables de entorno (getenv) con valores por defecto locales (XAMPP).
 * 3. Manejo de excepciones (try / catch) que registra el error técnico en logs y
 *    muestra un mensaje seguro al usuario SIN filtrar contraseñas o rutas internas.
 * 4. Estandarización de variables: Provee $conexion y el alias $conn para máxima compatibilidad.
 * 5. Charset UTF-8 (utf8mb4) configurado estrictamente para caracteres especiales y tildes.
 */

// Evitar múltiples instancias si el archivo ya fue cargado en la misma petición
if (isset($conexion) && $conexion instanceof mysqli && @$conexion->ping()) {
    return;
}

// ------------------------------------------------------------------------------
// 1. CONFIGURACIÓN CENTRALIZADA DE CREDENCIALES
// ------------------------------------------------------------------------------
// Permite sobreescritura automática por variables de entorno del servidor o contenedor
$db_host    = getenv('DB_HOST')     ?: 'localhost';
$db_user    = getenv('DB_USER')     ?: 'root';
$db_pass    = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db_name    = getenv('DB_NAME')     ?: 'construccionesmym';
$db_port    = (int)(getenv('DB_PORT') ?: 3306);
$db_charset = getenv('DB_CHARSET')  ?: 'utf8mb4';

// Definición de constantes globales seguras (si no existen)
if (!defined('DB_HOST'))    define('DB_HOST', $db_host);
if (!defined('DB_USER'))    define('DB_USER', $db_user);
if (!defined('DB_PASS'))    define('DB_PASS', $db_pass);
if (!defined('DB_NAME'))    define('DB_NAME', $db_name);
if (!defined('DB_PORT'))    define('DB_PORT', $db_port);
if (!defined('DB_CHARSET')) define('DB_CHARSET', $db_charset);

// ------------------------------------------------------------------------------
// 2. CREACIÓN DE LA CONEXIÓN CON CONTROL DE EXCEPCIONES (TRY / CATCH)
// ------------------------------------------------------------------------------
try {
    // Activar modo estricto de reporte de errores para MySQLi en PHP
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Inicializar instancia MySQLi con puerto
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    // Configurar el juego de caracteres a utf8mb4
    $conexion->set_charset(DB_CHARSET);

    // Alias estándar de compatibilidad para scripts que utilicen $conn o $conexion
    $conn = $conexion;

} catch (Throwable $e) {
    // 1. Registrar el detalle técnico en los logs del servidor (seguro, no visible al público)
    error_log("[ERROR BD - M&M] Fallo de conexión: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());

    // 2. Establecer código HTTP 500 en entornos web si aún no se han enviado cabeceras
    if (!headers_sent()) {
        http_response_code(500);
    }

    // 3. Respuesta limpia y amigable sin exponer credenciales, contraseñas ni rutas de servidor
    $es_peticion_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                        || (isset($_GET['ajax_chat_query']));

    if ($es_peticion_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error de conexión con la base de datos del servidor.'
        ]);
        exit;
    }

    // Pantalla segura de error genérico para el usuario
    die("
    <!DOCTYPE html>
    <html lang='es'>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <title>Error de Conexión | M&M Construcciones</title>
      <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #1e293b; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .error-card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.06); padding: 32px; max-width: 480px; width: 100%; text-align: center; }
        .error-icon { width: 54px; height: 54px; background: #fee2e2; color: #dc2626; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 16px; font-weight: bold; }
        h1 { font-size: 1.25rem; font-weight: 800; color: #0A192F; margin: 0 0 10px; }
        p { font-size: 0.9rem; color: #64748b; line-height: 1.5; margin: 0 0 20px; }
        .btn-retry { display: inline-block; background: #0A192F; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; transition: background 0.2s; }
        .btn-retry:hover { background: #172a45; }
      </style>
    </head>
    <body>
      <div class='error-card'>
        <div class='error-icon'>!</div>
        <h1>Servicio Temporalmente No Disponible</h1>
        <p>No fue posible establecer la conexión con la base de datos del sistema. Por favor, verifique el estado del servidor MySQL o contacte al administrador técnico.</p>
        <a href='javascript:location.reload()' class='btn-retry'>Reintentar conexión</a>
      </div>
    </body>
    </html>
    ");
}