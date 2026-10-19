<?php
/**
 * Cierre Seguro de Sesión Administrativa
 * M&M CONSTRUCCIONES Y SERVICIOS S.A.S.
 */
session_start();

// Vaciar todas las variables de sesión
$_SESSION = [];

// Destruir cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir sesión en el servidor
session_destroy();

// Redirigir al inicio de sesión con mensaje
header("Location: ../iniciaradmin/login.php");
exit();
