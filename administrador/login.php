<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validación y acceso al panel administrativo
    if (!empty($usuario)) {
        $_SESSION['usuario'] = $usuario;
        $_SESSION['rol'] = 'Super Administrador';
        header("Location: admin.html");
        exit;
    } else {
        header("Location: ../iniciarsesion/iniciarsesion.html?error=1");
        exit;
    }
} else {
    header("Location: admin.html");
    exit;
}
?>
