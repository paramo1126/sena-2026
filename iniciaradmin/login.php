<?php
// 1. Iniciamos sesión y llamamos a la conexión (subiendo un nivel para buscar la carpeta config)
session_start();
require_once __DIR__ . '/../config/conexion.php';

$error = ""; // Variable para guardar mensajes si las credenciales fallan

// 2. Verificamos si el usuario le dio clic al botón de enviar (método POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario_ingresado = $_POST['usuario'];
    $password_ingresada = $_POST['password'];

    // 3. Consulta preparada para buscar el usuario de forma segura
    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $usuario_ingresado);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // 4. Verificamos si encontramos al usuario en la base de datos
    if ($resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
        
        // Comparamos la contraseña ingresada con la de la base de datos
        if ($password_ingresada === $row['clave']) {
            // Si coincide, creamos la sesión y redirigimos al indexx.php dentro de la carpeta administrador
            $_SESSION['admin_activo'] = $usuario_ingresado;
            header("Location: ../administrador/desboard.php");
            exit();
        } else {
            // Contraseña incorrecta
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        // Usuario no existe
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesion | Constructores M&M</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../estilos/style.css">
</head>
<body class="login-page">
  <main class="login-shell">
    <section class="brand-panel" aria-label="Constructores M&M">
      <div class="brand-mark"><i class="bi bi-building-fill me-2"></i>Constructores M&M</div>
      <h1>Control seguro de operaciones</h1>
      <p>Acceso privado para administrar contenido, imagenes publicadas y el estado general de maquinaria.</p>
    </section>

    <section class="form-panel">
      <div class="login-card">
        <h2>Iniciar sesion</h2>
        <p class="subtitle">Ingresa con tu usuario administrativo.</p>

        <!-- Si hubo error al iniciar sesión, mostramos la alerta -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger py-2 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
          </div>
        <?php endif; ?>

        <!-- El action vacío envía los datos correctamente al mismo login.php -->
        <form action="" method="POST">
          <div class="mb-3">
            <label for="usuario" class="form-label">Usuario</label>
            <input type="text" class="form-control" id="usuario" name="usuario" placeholder="admin" required>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="********" required>
          </div>

          <div class="d-grid mb-4">
            <button type="submit" class="btn btn-login">Entrar al panel</button>
          </div>
        </form>

        <a class="back-link" href="../inicio/index.php"><i class="bi bi-arrow-left me-2"></i>Volver al sitio</a>
      </div>
    </section>
  </main>
</body>
</html>