<?php
// 1. Incluimos el archivo de conexión
require_once __DIR__ . '/../config/conexion.php';

// 2. Preparamos la consulta SQL uniendo la tabla maquinarias y lugares
$sql = "SELECT m.placa, m.modelo, m.caracteristicas, m.estado, l.nombre_lugar 
        FROM maquinarias m 
        LEFT JOIN lugares l ON m.id_lugar_actual = l.id";

// 3. Ejecutamos la consulta
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Maquinarias</title>
    <!-- Incluimos Bootstrap 5 para el diseño -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4 text-primary">Control de Maquinarias</h2>
            
            <!-- Estructura de la tabla con Bootstrap -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Placa</th>
                                <th>Modelo</th>
                                <th>Características</th>
                                <th>Estado</th>
                                <th>Ubicación Actual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // 4. Mostramos los datos fila por fila
                            if ($resultado->num_rows > 0) {
                                while($fila = $resultado->fetch_assoc()) {
                                    
                                    // Cambiar el color del texto según el estado para que se vea más profesional
                                    $color_estado = "text-success"; // Verde para Disponible
                                    if ($fila['estado'] == 'Trabajando') $color_estado = "text-primary"; // Azul
                                    if ($fila['estado'] == 'En mantenimiento') $color_estado = "text-danger"; // Rojo

                                    echo "<tr>";
                                    echo "<td>" . $fila['placa'] . "</td>";
                                    echo "<td>" . $fila['modelo'] . "</td>";
                                    echo "<td>" . $fila['caracteristicas'] . "</td>";
                                    echo "<td class='fw-bold $color_estado'>" . $fila['estado'] . "</td>";
                                    // Si no tiene un lugar asignado, mostramos un mensaje
                                    echo "<td>" . ($fila['nombre_lugar'] ? $fila['nombre_lugar'] : "Sin ubicación") . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No hay maquinarias registradas</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>

</body>
</html>