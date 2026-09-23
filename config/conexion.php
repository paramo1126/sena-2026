<?php
// Datos de conexión para XAMPP
$servidor = "localhost";
$usuario = "root";
$password = "";
$base_datos = "construccionesmym"; // El nombre de tu base de datos

// Crear la conexión usando MySQLi
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Verificar si hay errores en la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Opcional: Para que los caracteres especiales y tildes se vean bien
$conexion->set_charset("utf8mb4");
?>