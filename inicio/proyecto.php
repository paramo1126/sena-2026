<?php
/**
 * Constructores M&M - Proyectos
 * Conexión centralizada y consulta a contenido_inicio para footer dinámico (DRY)
 * Consulta y renderizado dinámico de la tabla contenido_proyectos
 */
require_once __DIR__ . '/../config/conexion.php';

// 1. Footer Universal (Código DRY) desde contenido_inicio
$contenido_inicio = [];
if (isset($conexion) && $conexion instanceof mysqli && !$conexion->connect_error) {
    try {
        $res_inicio = $conexion->query("SELECT footer_vinetas FROM `contenido_inicio` WHERE `id` = 1 LIMIT 1");
        if ($res_inicio && $res_inicio->num_rows > 0) {
            $contenido_inicio = $res_inicio->fetch_assoc();
        }
    } catch (\Throwable $e_ini) {
        $contenido_inicio = [];
    }
}

// 2. Consulta de Contenido Dinámico de la Sección PROYECTOS
$contenido_proyectos = [];
$valores_defecto_proy = [
    'carrusel_img1' => 'imagen 44.png',
    'carrusel_img2' => 'imagen 22.png',
    'carrusel_img3' => 'imagen 33.png',
    'btn1_nombre' => 'Calles pavimentadas',
    'btn2_nombre' => 'Tuberias y redes',
    'btn3_nombre' => 'Evidencias',
    'calles_titulo' => 'Calles pavimentadas',
    'calles_texto' => "Obras de pavimentacion, mejoramiento de vias, conformacion de andenes y adecuacion de espacio publico.",
    'calles_badge' => 'Evidencia con direccion de referencia',
    'c1_img' => 'imagen1.png',
    'c1_badge' => 'Pavimentacion',
    'c1_titulo' => 'Mejoramiento vial barrio Las Palmas',
    'c1_vinetas' => "Calle 8 # 21-45, Neiva, Huila\nEntrega de carpeta asfaltica y nivelacion de via\nRegistro fotografico antes, durante y despues",
    'c2_img' => 'imagen2.png',
    'c2_badge' => 'Andenes',
    'c2_titulo' => 'Andenes y sardineles sector Centro',
    'c2_vinetas' => "Carrera 5 # 10-32, Neiva, Huila\nConstruccion de andenes, bordillos y accesos\nAcabados seguros para transito peatonal",
    'c3_img' => 'imagen3.png',
    'c3_badge' => 'Via urbana',
    'c3_titulo' => 'Adecuacion vial barrio El Jardin',
    'c3_vinetas' => "Calle 18 Sur # 7-80, Neiva, Huila\nCompactacion, base granular y acabado final\nControl de calidad por etapas de obra",
    'tuberias_titulo' => 'Tuberias y redes',
    'tuberias_texto' => "Instalacion, rehabilitacion y mantenimiento de redes de gas e infraestructura hidraulica.",
    'tuberias_badge' => 'Redes instaladas y verificadas',
    't1_img' => 'imagen4.png',
    't1_badge' => 'Redes de gas',
    't1_titulo' => 'Instalacion de red interna residencial',
    't1_vinetas' => "Carrera 16 # 22-18, Neiva, Huila\nTrazado, instalacion y prueba de hermeticidad\nSoporte tecnico de cumplimiento normativo",
    't2_img' => 'imagen2.png',
    't2_badge' => 'Red hidraulica',
    't2_titulo' => 'Rehabilitacion de tuberia principal',
    't2_vinetas' => "Avenida La Toma # 3-60, Neiva, Huila\nExcavacion tecnica, cambio de tramo y relleno compactado\nInspeccion y reporte final de funcionamiento",
    'evidencias_badge1' => 'Galería Técnica',
    'evidencias_badge2' => 'Control de Calidad',
    'evidencias_titulo' => 'Evidencias y Registro Fotográfico',
    'evidencias_texto' => "Documentación visual de procesos constructivos, inspecciones técnicas y control de calidad en cada fase del proyecto.",
    'evidencias_badge3' => 'Auditoría y trazabilidad en cada fase del proyecto',
    'e1_img' => 'imagen 44.png',
    'e1_badge' => 'Obra Civil',
    'e1_titulo' => 'Pavimentación y Control de Asfalto',
    'e1_vinetas' => "Calle 10 con Carrera 15, Neiva, Huila\nControl densimétrico y verificación de espesores\nRegistro fotográfico de tendido y compactación",
    'e2_img' => 'imagen3.png',
    'e2_badge' => 'Red Hidráulica',
    'e2_titulo' => 'Canalización e Instalación Subterránea',
    'e2_vinetas' => "Zona Industrial Sur, Neiva, Huila\nExcavación con zanjadora y entibado de seguridad\nPrueba hidrostática a 150 PSI aprobada",
    'e3_img' => 'imagen 22.png',
    'e3_badge' => 'Redes de Gas',
    'e3_titulo' => 'Empalme y Válvulas de Seguridad',
    'e3_vinetas' => "Sector Oriente, Neiva, Huila\nTermofusión de polietileno de alta densidad\nInspección radiográfica y sellado certificado",
    'e4_img' => 'imagen 33.png',
    'e4_badge' => 'Pavimentación',
    'e4_titulo' => 'Entrega Final y Señalización Vial',
    'e4_vinetas' => "Avenida Circunvalar # 14-20, Neiva, Huila\nDemarcación termoplástica y señalización vertical\nActa de entrega y conformidad ciudadana"
];

if (isset($conexion) && $conexion instanceof mysqli && !$conexion->connect_error) {
    try {
        $res_proy = $conexion->query("SELECT * FROM `contenido_proyectos` WHERE `id` = 1 LIMIT 1");
        if ($res_proy && $res_proy->num_rows > 0) {
            $contenido_proyectos = $res_proy->fetch_assoc();
        }
    } catch (\Throwable $e_proy) {
        $contenido_proyectos = [];
    }
}

// Fallback para campos vacíos o no existentes
if (!is_array($contenido_proyectos)) {
    $contenido_proyectos = [];
}
foreach ($valores_defecto_proy as $k => $v_def) {
    if (!isset($contenido_proyectos[$k]) || trim((string)$contenido_proyectos[$k]) === '') {
        $contenido_proyectos[$k] = $v_def;
    }
}

// Función auxiliar para resolver la ruta de imágenes (archivos locales o enlaces URL directos)
if (!function_exists('resolver_ruta_img')) {
    function resolver_ruta_img($ruta, $base_dir = '../imagenes/') {
        $ruta = trim((string)$ruta);
        if ($ruta === '') {
            return $base_dir . 'imagen1.png';
        }
        if (preg_match('/^https?:\/\//i', $ruta) || str_starts_with($ruta, '/') || str_starts_with($ruta, '../')) {
            return $ruta;
        }
        return rtrim($base_dir, '/') . '/' . ltrim($ruta, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proyectos realizados | Constructores M&M</title>
  <meta name="description" content="Proyectos realizados por Constructores M&M en pavimentacion de calles, andenes y tuberias. Evidencias con direcciones de obra.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../estilos/proyectostyle.css">
</head>
<body>
  <div class="top-banner" aria-label="Mensaje de bienvenida">
    <div class="marquee-track">
      <div class="marquee-group">
        <span>BIENVENIDO A CONSTRUCTORES M&M</span>
        <span>CALIDAD EN CONSTRUCCION CIVIL</span>
        <span>MAQUINARIA</span>
        <span>SEGURIDAD Y CUMPLIMIENTO</span>
      </div>
      <div class="marquee-group" aria-hidden="true">
        <span>BIENVENIDO A CONSTRUCTORES M&M</span>
        <span>CALIDAD EN CONSTRUCCION CIVIL</span>
        <span>MAQUINARIA</span>
        <span>SEGURIDAD Y CUMPLIMIENTO</span>
      </div>
    </div>
  </div>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg main-navbar">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="../inicio/index.php">
        <i class="bi bi-building-fill me-2"></i>
        Constructores M&M
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="menuPrincipal">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="../inicio/index.php">Inicio</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle active" href="../inicio/proyecto.php" id="menuProyectos" role="button" aria-expanded="false">
              Proyectos
            </a>
            <ul class="dropdown-menu" aria-labelledby="menuProyectos">
              <li><a class="dropdown-item" href="../inicio/proyecto.php#calles"><?php echo htmlspecialchars($contenido_proyectos['btn1_nombre'] ?? 'Calles pavimentadas'); ?></a></li>
              <li><a class="dropdown-item" href="../inicio/proyecto.php#tuberias"><?php echo htmlspecialchars($contenido_proyectos['btn2_nombre'] ?? 'Tuberias y redes'); ?></a></li>
              <li><a class="dropdown-item" href="../inicio/proyecto.php#evidencias"><?php echo htmlspecialchars($contenido_proyectos['btn3_nombre'] ?? 'Evidencias'); ?></a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../inicio/nosotros.php">Nosotros</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../inicio/maquinaria.php">Maquinaria</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../inicio/contacto.php">Contacto</a>
          </li>
        </ul>
        <a class="login-nav-btn" href="../iniciaradmin/login.php">Iniciar sesión</a>
      </div>
    </div>
  </nav>

  <!-- Carrusel Hero -->
  <div id="carouselExampleControlsNoTouching" class="carousel slide carousel-hero" data-bs-ride="carousel" data-bs-touch="true" data-bs-interval="3500" data-bs-pause="false">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['carrusel_img1'] ?? 'imagen 44.png')); ?>" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 1">
      </div>
      <div class="carousel-item">
        <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['carrusel_img2'] ?? 'imagen 22.png')); ?>" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 2">
      </div>
      <div class="carousel-item">
        <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['carrusel_img3'] ?? 'imagen 33.png')); ?>" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 3">
      </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControlsNoTouching" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControlsNoTouching" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>

  <main>
    <!-- Botonera Blanca de Filtros / Anclas -->
    <div class="container">
      <div class="anchor-panel">
        <div class="row g-2">
          <div class="col-md-4">
            <a class="anchor-link" href="#calles"><i class="bi bi-signpost-split-fill"></i> <?php echo htmlspecialchars($contenido_proyectos['btn1_nombre'] ?? 'Calles pavimentadas'); ?></a>
          </div>
          <div class="col-md-4">
            <a class="anchor-link" href="#tuberias"><i class="bi bi-fire"></i> <?php echo htmlspecialchars($contenido_proyectos['btn2_nombre'] ?? 'Tuberias y redes'); ?></a>
          </div>
          <div class="col-md-4">
            <a class="anchor-link" href="#evidencias"><i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($contenido_proyectos['btn3_nombre'] ?? 'Evidencias'); ?></a>
          </div>
        </div>
      </div>
    </div>

    <!-- Sección 1: Calles Pavimentadas -->
    <section id="calles" class="py-5">
      <div class="container py-4">
        <div class="row align-items-end mb-4">
          <div class="col-lg-7">
            <h2 class="section-title"><?php echo htmlspecialchars($contenido_proyectos['calles_titulo'] ?? 'Calles pavimentadas'); ?></h2>
            <p class="text-gray mb-lg-0">
              <?php echo nl2br(htmlspecialchars($contenido_proyectos['calles_texto'] ?? 'Obras de pavimentacion, mejoramiento de vias, conformacion de andenes y adecuacion de espacio publico.')); ?>
            </p>
          </div>
          <div class="col-lg-5 text-lg-end">
            <span class="project-badge"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($contenido_proyectos['calles_badge'] ?? 'Evidencia con direccion de referencia'); ?></span>
          </div>
        </div>

        <div class="row g-4">
          <!-- Tarjeta 1 Calles -->
          <div class="col-md-6 col-xl-4">
            <article class="card project-card">
              <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['c1_img'] ?? 'imagen1.png')); ?>" alt="<?php echo htmlspecialchars($contenido_proyectos['c1_titulo'] ?? 'Pavimentacion'); ?>">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-signpost-fill"></i> <?php echo htmlspecialchars($contenido_proyectos['c1_badge'] ?? 'Pavimentacion'); ?></span>
                <h3 class="h5 fw-bold text-navy"><?php echo htmlspecialchars($contenido_proyectos['c1_titulo'] ?? 'Mejoramiento vial barrio Las Palmas'); ?></h3>
                <ul class="evidence-list mt-3">
                  <?php 
                  $c1_items = array_filter(array_map('trim', explode("\n", (string)($contenido_proyectos['c1_vinetas'] ?? ''))));
                  $iconos_c1 = ['bi bi-geo-alt-fill', 'bi bi-calendar-check-fill', 'bi bi-camera-fill', 'bi bi-shield-check'];
                  $idx_c1 = 0;
                  foreach ($c1_items as $item):
                    $txt = ltrim($item, "-•* \t\n\r\0\x0B");
                    if ($txt === '') continue;
                    $ic = $iconos_c1[$idx_c1] ?? 'bi bi-check-circle-fill';
                    $idx_c1++;
                  ?>
                    <li><i class="<?php echo $ic; ?>"></i><span><?php echo htmlspecialchars($txt); ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>

          <!-- Tarjeta 2 Calles -->
          <div class="col-md-6 col-xl-4">
            <article class="card project-card" style="animation-delay: 0.08s;">
              <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['c2_img'] ?? 'imagen2.png')); ?>" alt="<?php echo htmlspecialchars($contenido_proyectos['c2_titulo'] ?? 'Andenes'); ?>">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-bricks"></i> <?php echo htmlspecialchars($contenido_proyectos['c2_badge'] ?? 'Andenes'); ?></span>
                <h3 class="h5 fw-bold text-navy"><?php echo htmlspecialchars($contenido_proyectos['c2_titulo'] ?? 'Andenes y sardineles sector Centro'); ?></h3>
                <ul class="evidence-list mt-3">
                  <?php 
                  $c2_items = array_filter(array_map('trim', explode("\n", (string)($contenido_proyectos['c2_vinetas'] ?? ''))));
                  $iconos_c2 = ['bi bi-geo-alt-fill', 'bi bi-calendar-check-fill', 'bi bi-shield-check', 'bi bi-check-circle-fill'];
                  $idx_c2 = 0;
                  foreach ($c2_items as $item):
                    $txt = ltrim($item, "-•* \t\n\r\0\x0B");
                    if ($txt === '') continue;
                    $ic = $iconos_c2[$idx_c2] ?? 'bi bi-check-circle-fill';
                    $idx_c2++;
                  ?>
                    <li><i class="<?php echo $ic; ?>"></i><span><?php echo htmlspecialchars($txt); ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>

          <!-- Tarjeta 3 Calles -->
          <div class="col-md-6 col-xl-4">
            <article class="card project-card" style="animation-delay: 0.16s;">
              <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['c3_img'] ?? 'imagen3.png')); ?>" alt="<?php echo htmlspecialchars($contenido_proyectos['c3_titulo'] ?? 'Via urbana'); ?>">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-cone-striped"></i> <?php echo htmlspecialchars($contenido_proyectos['c3_badge'] ?? 'Via urbana'); ?></span>
                <h3 class="h5 fw-bold text-navy"><?php echo htmlspecialchars($contenido_proyectos['c3_titulo'] ?? 'Adecuacion vial barrio El Jardin'); ?></h3>
                <ul class="evidence-list mt-3">
                  <?php 
                  $c3_items = array_filter(array_map('trim', explode("\n", (string)($contenido_proyectos['c3_vinetas'] ?? ''))));
                  $iconos_c3 = ['bi bi-geo-alt-fill', 'bi bi-calendar-check-fill', 'bi bi-clipboard2-check-fill', 'bi bi-check-circle-fill'];
                  $idx_c3 = 0;
                  foreach ($c3_items as $item):
                    $txt = ltrim($item, "-•* \t\n\r\0\x0B");
                    if ($txt === '') continue;
                    $ic = $iconos_c3[$idx_c3] ?? 'bi bi-check-circle-fill';
                    $idx_c3++;
                  ?>
                    <li><i class="<?php echo $ic; ?>"></i><span><?php echo htmlspecialchars($txt); ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 2: Tuberías y Redes -->
    <section id="tuberias" class="py-5 bg-light">
      <div class="container py-4">
        <div class="row align-items-end mb-4">
          <div class="col-lg-7">
            <h2 class="section-title"><?php echo htmlspecialchars($contenido_proyectos['tuberias_titulo'] ?? 'Tuberias y redes'); ?></h2>
            <p class="text-gray mb-lg-0">
              <?php echo nl2br(htmlspecialchars($contenido_proyectos['tuberias_texto'] ?? 'Instalacion, rehabilitacion y mantenimiento de redes de gas e infraestructura hidraulica.')); ?>
            </p>
          </div>
          <div class="col-lg-5 text-lg-end">
            <span class="project-badge"><i class="bi bi-tools"></i> <?php echo htmlspecialchars($contenido_proyectos['tuberias_badge'] ?? 'Redes instaladas y verificadas'); ?></span>
          </div>
        </div>

        <div class="row g-4">
          <!-- Tarjeta 1 Tuberías -->
          <div class="col-md-6">
            <article class="card project-card">
              <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['t1_img'] ?? 'imagen4.png')); ?>" alt="<?php echo htmlspecialchars($contenido_proyectos['t1_titulo'] ?? 'Redes de gas'); ?>">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-fire"></i> <?php echo htmlspecialchars($contenido_proyectos['t1_badge'] ?? 'Redes de gas'); ?></span>
                <h3 class="h5 fw-bold text-navy"><?php echo htmlspecialchars($contenido_proyectos['t1_titulo'] ?? 'Instalacion de red interna residencial'); ?></h3>
                <ul class="evidence-list mt-3">
                  <?php 
                  $t1_items = array_filter(array_map('trim', explode("\n", (string)($contenido_proyectos['t1_vinetas'] ?? ''))));
                  $iconos_t1 = ['bi bi-geo-alt-fill', 'bi bi-check2-circle', 'bi bi-file-earmark-check-fill', 'bi bi-shield-check'];
                  $idx_t1 = 0;
                  foreach ($t1_items as $item):
                    $txt = ltrim($item, "-•* \t\n\r\0\x0B");
                    if ($txt === '') continue;
                    $ic = $iconos_t1[$idx_t1] ?? 'bi bi-check2-circle';
                    $idx_t1++;
                  ?>
                    <li><i class="<?php echo $ic; ?>"></i><span><?php echo htmlspecialchars($txt); ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>

          <!-- Tarjeta 2 Tuberías -->
          <div class="col-md-6">
            <article class="card project-card" style="animation-delay: 0.08s;">
              <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['t2_img'] ?? 'imagen2.png')); ?>" alt="<?php echo htmlspecialchars($contenido_proyectos['t2_titulo'] ?? 'Red hidraulica'); ?>">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-droplet-fill"></i> <?php echo htmlspecialchars($contenido_proyectos['t2_badge'] ?? 'Red hidraulica'); ?></span>
                <h3 class="h5 fw-bold text-navy"><?php echo htmlspecialchars($contenido_proyectos['t2_titulo'] ?? 'Rehabilitacion de tuberia principal'); ?></h3>
                <ul class="evidence-list mt-3">
                  <?php 
                  $t2_items = array_filter(array_map('trim', explode("\n", (string)($contenido_proyectos['t2_vinetas'] ?? ''))));
                  $iconos_t2 = ['bi bi-geo-alt-fill', 'bi bi-check2-circle', 'bi bi-clipboard-data-fill', 'bi bi-shield-check'];
                  $idx_t2 = 0;
                  foreach ($t2_items as $item):
                    $txt = ltrim($item, "-•* \t\n\r\0\x0B");
                    if ($txt === '') continue;
                    $ic = $iconos_t2[$idx_t2] ?? 'bi bi-check2-circle';
                    $idx_t2++;
                  ?>
                    <li><i class="<?php echo $ic; ?>"></i><span><?php echo htmlspecialchars($txt); ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 3: Evidencias y Registro Fotográfico -->
    <section id="evidencias" class="py-5">
      <div class="container py-4">
        <!-- Botones decorativos superiores -->
        <div class="d-flex flex-wrap gap-2 mb-3">
          <?php if (!empty($contenido_proyectos['evidencias_badge1'])): ?>
            <span class="project-badge"><i class="bi bi-camera-fill"></i> <?php echo htmlspecialchars($contenido_proyectos['evidencias_badge1']); ?></span>
          <?php endif; ?>
          <?php if (!empty($contenido_proyectos['evidencias_badge2'])): ?>
            <span class="project-badge"><i class="bi bi-patch-check-fill"></i> <?php echo htmlspecialchars($contenido_proyectos['evidencias_badge2']); ?></span>
          <?php endif; ?>
        </div>

        <div class="row align-items-end mb-4">
          <div class="col-lg-7">
            <h2 class="section-title"><?php echo htmlspecialchars($contenido_proyectos['evidencias_titulo'] ?? 'Evidencias y Registro Fotográfico'); ?></h2>
            <p class="text-gray mb-lg-0">
              <?php echo nl2br(htmlspecialchars($contenido_proyectos['evidencias_texto'] ?? 'Documentación visual de procesos constructivos, inspecciones técnicas y control de calidad en cada fase del proyecto.')); ?>
            </p>
          </div>
          <div class="col-lg-5 text-lg-end">
            <?php if (!empty($contenido_proyectos['evidencias_badge3'])): ?>
              <span class="project-badge"><i class="bi bi-shield-check"></i> <?php echo htmlspecialchars($contenido_proyectos['evidencias_badge3']); ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="row g-4">
          <!-- Tarjeta 1 Evidencias -->
          <div class="col-md-6 col-xl-3">
            <article class="card project-card">
              <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['e1_img'] ?? 'imagen 44.png')); ?>" alt="<?php echo htmlspecialchars($contenido_proyectos['e1_titulo'] ?? 'Obra Civil'); ?>">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-building"></i> <?php echo htmlspecialchars($contenido_proyectos['e1_badge'] ?? 'Obra Civil'); ?></span>
                <h3 class="h5 fw-bold text-navy"><?php echo htmlspecialchars($contenido_proyectos['e1_titulo'] ?? 'Pavimentación y Control de Asfalto'); ?></h3>
                <ul class="evidence-list mt-3">
                  <?php 
                  $e1_items = array_filter(array_map('trim', explode("\n", (string)($contenido_proyectos['e1_vinetas'] ?? ''))));
                  $iconos_e = ['bi bi-geo-alt-fill', 'bi bi-check2-circle', 'bi bi-camera-fill', 'bi bi-shield-check'];
                  $idx_e1 = 0;
                  foreach ($e1_items as $item):
                    $txt = ltrim($item, "-•* \t\n\r\0\x0B");
                    if ($txt === '') continue;
                    $ic = $iconos_e[$idx_e1] ?? 'bi bi-check2-circle';
                    $idx_e1++;
                  ?>
                    <li><i class="<?php echo $ic; ?>"></i><span><?php echo htmlspecialchars($txt); ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>

          <!-- Tarjeta 2 Evidencias -->
          <div class="col-md-6 col-xl-3">
            <article class="card project-card" style="animation-delay: 0.08s;">
              <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['e2_img'] ?? 'imagen3.png')); ?>" alt="<?php echo htmlspecialchars($contenido_proyectos['e2_titulo'] ?? 'Red Hidráulica'); ?>">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-droplet-half"></i> <?php echo htmlspecialchars($contenido_proyectos['e2_badge'] ?? 'Red Hidráulica'); ?></span>
                <h3 class="h5 fw-bold text-navy"><?php echo htmlspecialchars($contenido_proyectos['e2_titulo'] ?? 'Canalización Subterránea'); ?></h3>
                <ul class="evidence-list mt-3">
                  <?php 
                  $e2_items = array_filter(array_map('trim', explode("\n", (string)($contenido_proyectos['e2_vinetas'] ?? ''))));
                  $idx_e2 = 0;
                  foreach ($e2_items as $item):
                    $txt = ltrim($item, "-•* \t\n\r\0\x0B");
                    if ($txt === '') continue;
                    $ic = $iconos_e[$idx_e2] ?? 'bi bi-check2-circle';
                    $idx_e2++;
                  ?>
                    <li><i class="<?php echo $ic; ?>"></i><span><?php echo htmlspecialchars($txt); ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>

          <!-- Tarjeta 3 Evidencias -->
          <div class="col-md-6 col-xl-3">
            <article class="card project-card" style="animation-delay: 0.16s;">
              <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['e3_img'] ?? 'imagen 22.png')); ?>" alt="<?php echo htmlspecialchars($contenido_proyectos['e3_titulo'] ?? 'Redes de Gas'); ?>">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-fire"></i> <?php echo htmlspecialchars($contenido_proyectos['e3_badge'] ?? 'Redes de Gas'); ?></span>
                <h3 class="h5 fw-bold text-navy"><?php echo htmlspecialchars($contenido_proyectos['e3_titulo'] ?? 'Empalme y Válvulas'); ?></h3>
                <ul class="evidence-list mt-3">
                  <?php 
                  $e3_items = array_filter(array_map('trim', explode("\n", (string)($contenido_proyectos['e3_vinetas'] ?? ''))));
                  $idx_e3 = 0;
                  foreach ($e3_items as $item):
                    $txt = ltrim($item, "-•* \t\n\r\0\x0B");
                    if ($txt === '') continue;
                    $ic = $iconos_e[$idx_e3] ?? 'bi bi-check2-circle';
                    $idx_e3++;
                  ?>
                    <li><i class="<?php echo $ic; ?>"></i><span><?php echo htmlspecialchars($txt); ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>

          <!-- Tarjeta 4 Evidencias -->
          <div class="col-md-6 col-xl-3">
            <article class="card project-card" style="animation-delay: 0.24s;">
              <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_proyectos['e4_img'] ?? 'imagen 33.png')); ?>" alt="<?php echo htmlspecialchars($contenido_proyectos['e4_titulo'] ?? 'Pavimentación'); ?>">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-cone-striped"></i> <?php echo htmlspecialchars($contenido_proyectos['e4_badge'] ?? 'Pavimentación'); ?></span>
                <h3 class="h5 fw-bold text-navy"><?php echo htmlspecialchars($contenido_proyectos['e4_titulo'] ?? 'Entrega y Señalización'); ?></h3>
                <ul class="evidence-list mt-3">
                  <?php 
                  $e4_items = array_filter(array_map('trim', explode("\n", (string)($contenido_proyectos['e4_vinetas'] ?? ''))));
                  $idx_e4 = 0;
                  foreach ($e4_items as $item):
                    $txt = ltrim($item, "-•* \t\n\r\0\x0B");
                    if ($txt === '') continue;
                    $ic = $iconos_e[$idx_e4] ?? 'bi bi-check2-circle';
                    $idx_e4++;
                  ?>
                    <li><i class="<?php echo $ic; ?>"></i><span><?php echo htmlspecialchars($txt); ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer con Sincronización Universal DRY desde contenido_inicio -->
  <footer id="contacto" class="bg-navy-light pt-5 pb-4">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4">
          <h4 class="fw-bold text-white mb-3">
            <i class="bi bi-building-fill text-gold me-2"></i>Constructores M&M
          </h4>
          <p>
            Soluciones integrales en construccion civil y redes de gas. Comprometidos con la calidad, la seguridad y la satisfaccion de nuestros clientes.
          </p>
          <div class="d-flex mt-3">
            <a href="#" class="social-icon" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-icon" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-icon" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="https://wa.me/573026043945" class="social-icon" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
          </div>
        </div>
        <div class="col-lg-4 col-sm-6">
          <h5 class="fw-bold text-white mb-3">Enlaces rapidos</h5>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="../inicio/index.php">Inicio</a></li>
            <li class="mb-2"><a href="../inicio/proyecto.php#calles">Calles pavimentadas</a></li>
            <li class="mb-2"><a href="../inicio/proyecto.php#tuberias">Tuberias</a></li>
            <li class="mb-2"><a href="../inicio/contacto.php">Contacto</a></li>
            <li class="mb-2"><a href="../iniciaradmin/login.php">Iniciar Sesión</a></li>
          </ul>
        </div>
        <div class="col-lg-4 col-sm-6">
          <h5 class="fw-bold text-white mb-3">Contacto</h5>
          <?php 
          $raw_footer = (string)($contenido_inicio['footer_vinetas'] ?? '');
          $contacto_items = array_filter(array_map('trim', explode("\n", $raw_footer)));
          if (empty($contacto_items)) {
            $contacto_items = [
              'Neiva, Huila, Colombia',
              '+57 310 291 6140',
              'contacto@constructoresmym.com',
              'Lunes a viernes: 8:00 a.m. - 5:00 p.m.'
            ];
          }
          $iconos_contacto = [
            'bi bi-geo-alt-fill',
            'bi bi-telephone-fill',
            'bi bi-envelope-fill',
            'bi bi-clock-fill'
          ];
          $idx_c = 0;
          foreach ($contacto_items as $c_item):
            $c_limpia = ltrim($c_item, "-•* \t\n\r\0\x0B");
            if ($c_limpia === '') continue;
            $icono_c = $iconos_contacto[$idx_c] ?? 'bi bi-info-circle-fill';
            $idx_c++;
          ?>
            <div class="footer-contact-item">
              <i class="<?php echo $icono_c; ?>"></i>
              <span><?php echo htmlspecialchars($c_limpia); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <hr class="border-secondary mt-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-center small" style="color: rgba(255,255,255,0.6);">
        <span>© 2026 Constructores M&M. Todos los derechos reservados.</span>
        <span class="mt-2 mt-md-0">Construccion civil y redes de gas natural</span>
      </div>
    </div>
  </footer>

  <a href="https://wa.me/573026043945?text=Hola,%20quiero%20informacion%20sobre%20sus%20proyectos" class="btn-whatsapp" target="_blank" rel="noopener noreferrer" aria-label="Contactar por WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>
  <script>
    (function () {
      var marqueeDuration = 24000;
      var storageKey = 'constructoresMarqueeStart';
      var startTime = Number(localStorage.getItem(storageKey));

      if (!startTime) {
        startTime = Date.now();
        localStorage.setItem(storageKey, String(startTime));
      }

      var elapsed = (Date.now() - startTime) % marqueeDuration;
      document.documentElement.style.setProperty('--marquee-duration', marqueeDuration + 'ms');
      document.documentElement.style.setProperty('--marquee-delay', '-' + elapsed + 'ms');
    })();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('.carousel').forEach(function (carouselElement) {
      var carousel = bootstrap.Carousel.getOrCreateInstance(carouselElement, {
        interval: 3500,
        pause: false,
        ride: 'carousel',
        touch: true,
        wrap: true
      });

      carousel.cycle();
    });
  </script>
</body>
</html>
