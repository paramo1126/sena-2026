<?php
/**
 * ==============================================================================
 * Constructores M&M - Página Principal (Inicio)
 * Conexión centralizada y consulta directa a la base de datos MySQL
 * ==============================================================================
 */
require_once __DIR__ . '/../config/conexion.php';

// Consulta directa a la tabla contenido_inicio (fila id = 1)
$contenido = [];
$btn_proy = [];
if (isset($conexion) && $conexion instanceof mysqli && !$conexion->connect_error) {
    $res = $conexion->query("SELECT * FROM `contenido_inicio` WHERE `id` = 1 LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $contenido = $res->fetch_assoc();
    }
    $res_p = $conexion->query("SELECT `btn1_nombre`, `btn2_nombre`, `btn3_nombre` FROM `contenido_proyectos` WHERE `id` = 1 LIMIT 1");
    if ($res_p && $res_p->num_rows > 0) {
        $btn_proy = $res_p->fetch_assoc();
    }
}

// Función auxiliar para resolver la ruta de imágenes sin archivos intermediarios
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
  <title>Constructores M&M | Construcción Civil y Redes de Gas</title>
  <meta name="description" content="Constructores M&M: soluciones integrales en construcción civil e infraestructura de redes de gas. Experiencia, seguridad y cumplimiento normativo.">
  <link href="../bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../estilos/iniciostyle.css">
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
            <a class="nav-link active" aria-current="page" href="../inicio/index.php">Inicio</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="../inicio/proyecto.php" id="menuProyectos" role="button" aria-expanded="false">
              Proyectos
            </a>
            <ul class="dropdown-menu" aria-labelledby="menuProyectos">
              <li><a class="dropdown-item" href="../inicio/proyecto.php#calles"><?php echo htmlspecialchars($btn_proy['btn1_nombre'] ?? 'Calles pavimentadas'); ?></a></li>
              <li><a class="dropdown-item" href="../inicio/proyecto.php#tuberias"><?php echo htmlspecialchars($btn_proy['btn2_nombre'] ?? 'Tuberias'); ?></a></li>
              <li><a class="dropdown-item" href="../inicio/proyecto.php#evidencias"><?php echo htmlspecialchars($btn_proy['btn3_nombre'] ?? 'Evidencias'); ?></a></li>
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

  <!-- Hero Section -->
  <header class="hero-home">
    <div class="hero-home-bg">
      <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido['b1_imagen'] ?? $contenido['hero_imagen'] ?? 'imagen1.png')); ?>" alt="Construcciones M&M - Obras Civiles y Redes de Gas" class="hero-home-img" fetchpriority="high">
      <div class="hero-home-overlay"></div>
    </div>
    <div class="container position-relative hero-home-content">
      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9 text-center">
          <div class="hero-home-badge mb-3">
            <i class="bi bi-shield-fill-check me-2 text-gold"></i>
            <span>EXPERIENCIA • CALIDAD • SEGURIDAD</span>
          </div>
          <h1 class="hero-home-title text-uppercase">
            <?php 
            $hero_tit_val = htmlspecialchars($contenido['b1_titulo'] ?? $contenido['hero_titulo'] ?? 'BIENVENIDO A CONSTRUCCIONES M&M', ENT_QUOTES, 'UTF-8');
            if (stripos($hero_tit_val, 'CONSTRUCCIONES M&M') !== false) {
                echo str_ireplace('CONSTRUCCIONES M&M', '<span class="text-gold-highlight">CONSTRUCCIONES M&M</span>', $hero_tit_val);
            } else {
                echo $hero_tit_val;
            }
            ?>
          </h1>
          <p class="hero-home-lead">
            <?php echo nl2br(htmlspecialchars($contenido['b1_subtitulo'] ?? $contenido['hero_subtitulo'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
          </p>
          <div class="hero-home-buttons d-flex flex-wrap justify-content-center gap-3 mt-4">
            <a href="#nosotros" class="btn btn-hero-primary">
              <i class="bi bi-people-fill me-2"></i>Quiénes Somos
            </a>
            <a href="#servicios" class="btn btn-hero-secondary">
              <i class="bi bi-gear-wide-connected me-2"></i>Servicios
            </a>
          </div>
        </div>
      </div>
    </div>
    <a href="#nosotros" class="hero-home-scroll" aria-label="Desplazarse hacia abajo">
      <i class="bi bi-chevron-double-down"></i>
    </a>
  </header>

  <!-- Nosotros -->
  <section id="nosotros" class="py-5">
    <div class="container py-4">
      <div class="row align-items-center">
        <div class="col-lg-6 mb-4 mb-lg-0">
          <img src="../imagenes/imagen4.png" alt="Equipo de Constructores M&M en obra" class="img-fluid rounded-3 shadow">
        </div>
        <div class="col-lg-6">
          <h2 class="section-title text-start">¿Quiénes somos?</h2>
          <p class="text-gray mb-4">
            Constructores M&M es una empresa especializada en construcción civil y redes de gas natural. Contamos con un equipo multidisciplinario de ingenieros, arquitectos y técnicos certificados que desarrollan proyectos desde el diseño hasta la entrega final.
          </p>
          <div class="row g-3">
            <div class="col-6">
              <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill text-gold fs-4 me-2"></i>
                <span class="fw-semibold text-navy">+15 años de experiencia</span>
              </div>
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill text-gold fs-4 me-2"></i>
                <span class="fw-semibold text-navy">Cumplimiento normativo</span>
              </div>
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill text-gold fs-4 me-2"></i>
                <span class="fw-semibold text-navy">Personal certificado</span>
              </div>
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill text-gold fs-4 me-2"></i>
                <span class="fw-semibold text-navy">Proyectos a tiempo</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Servicios -->
  <section id="servicios" class="py-5 bg-light">
    <div class="container py-4">
      <div class="text-center mb-5">
        <h2 class="section-title"><?php echo htmlspecialchars($contenido['b2_titulo'] ?? $contenido['servicios_titulo'] ?? 'Nuestros servicios'); ?></h2>
        <p class="text-gray mx-auto" style="max-width: 600px;">
          <?php echo nl2br(htmlspecialchars($contenido['b2_subtitulo'] ?? $contenido['servicios_subtitulo'] ?? '')); ?>
        </p>
      </div>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="card service-card p-4">
            <div class="service-icon">
              <i class="bi bi-building"></i>
            </div>
            <h3 class="h4 text-navy fw-bold mb-3"><?php echo htmlspecialchars($contenido['t1_titulo'] ?? $contenido['servicio1_titulo'] ?? 'Construcción Civil'); ?></h3>
            <p class="text-gray">
              <?php echo nl2br(htmlspecialchars($contenido['t1_texto'] ?? $contenido['servicio1_texto'] ?? '')); ?>
            </p>
            <ul class="list-unstyled mt-3 text-gray">
              <?php 
              $vinetas1 = array_filter(array_map('trim', explode("\n", (string)($contenido['t1_vinetas'] ?? $contenido['servicio1_vinetas'] ?? ''))));
              foreach ($vinetas1 as $v):
                $v_limpia = ltrim($v, "-•* \t\n\r\0\x0B");
                if ($v_limpia === '') continue;
              ?>
                <li><i class="bi bi-chevron-right text-gold me-2"></i><?php echo htmlspecialchars($v_limpia); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card service-card p-4">
            <div class="service-icon">
              <i class="bi bi-fire"></i>
            </div>
            <h3 class="h4 text-navy fw-bold mb-3"><?php echo htmlspecialchars($contenido['t2_titulo'] ?? $contenido['servicio2_titulo'] ?? 'Redes de Gas'); ?></h3>
            <p class="text-gray">
              <?php echo nl2br(htmlspecialchars($contenido['t2_texto'] ?? $contenido['servicio2_texto'] ?? '')); ?>
            </p>
            <ul class="list-unstyled mt-3 text-gray">
              <?php 
              $vinetas2 = array_filter(array_map('trim', explode("\n", (string)($contenido['t2_vinetas'] ?? $contenido['servicio2_vinetas'] ?? ''))));
              foreach ($vinetas2 as $v):
                $v_limpia = ltrim($v, "-•* \t\n\r\0\x0B");
                if ($v_limpia === '') continue;
              ?>
                <li><i class="bi bi-chevron-right text-gold me-2"></i><?php echo htmlspecialchars($v_limpia); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Carrusel -->

  </section>
  <div id="carouselExampleControlsNoTouching" class="carousel slide" data-bs-ride="carousel" data-bs-touch="true" data-bs-interval="3500" data-bs-pause="false">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido['carrusel_img1'] ?? 'imagen 22.png')); ?>" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 1">
      </div>
      <div class="carousel-item">
        <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido['carrusel_img2'] ?? 'imagen 33.png')); ?>" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 2">
      </div>
      <div class="carousel-item">
        <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido['carrusel_img3'] ?? 'imagen 44.png')); ?>" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 3">
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

  <!-- Por qué elegirnos -->
  <section class="py-5">
    <div class="container py-4">
      <div class="text-center mb-5">
        <h2 class="section-title"><?php echo htmlspecialchars($contenido['b3_titulo'] ?? $contenido['elegirnos_titulo'] ?? '¿Por qué elegirnos?'); ?></h2>
        <p class="text-gray mx-auto" style="max-width: 600px;">
          <?php echo nl2br(htmlspecialchars($contenido['b3_subtitulo'] ?? $contenido['elegirnos_subtitulo'] ?? '')); ?>
        </p>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="why-card">
            <div class="why-icon"><i class="bi bi-shield-check"></i></div>
            <h4 class="text-navy fw-bold"><?php echo htmlspecialchars($contenido['m1_titulo'] ?? $contenido['elegirnos_item1_titulo'] ?? 'Seguridad garantizada'); ?></h4>
            <p class="text-gray"><?php echo nl2br(htmlspecialchars($contenido['m1_texto'] ?? $contenido['elegirnos_item1_texto'] ?? '')); ?></p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="why-card">
            <div class="why-icon"><i class="bi bi-award"></i></div>
            <h4 class="text-navy fw-bold"><?php echo htmlspecialchars($contenido['m2_titulo'] ?? $contenido['elegirnos_item2_titulo'] ?? 'Experiencia comprobada'); ?></h4>
            <p class="text-gray"><?php echo nl2br(htmlspecialchars($contenido['m2_texto'] ?? $contenido['elegirnos_item2_texto'] ?? '')); ?></p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="why-card">
            <div class="why-icon"><i class="bi bi-file-check"></i></div>
            <h4 class="text-navy fw-bold"><?php echo htmlspecialchars($contenido['m3_titulo'] ?? $contenido['elegirnos_item3_titulo'] ?? 'Cumplimiento normativo'); ?></h4>
            <p class="text-gray"><?php echo nl2br(htmlspecialchars($contenido['m3_texto'] ?? $contenido['elegirnos_item3_texto'] ?? '')); ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contacto" class="bg-navy-light pt-5 pb-4">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4">
          <h4 class="fw-bold text-white mb-3">
            <i class="bi bi-building-fill text-gold me-2"></i>Constructores M&M
          </h4>
          <p>
            Soluciones integrales en construcción civil y redes de gas. Comprometidos con la calidad, la seguridad y la satisfacción de nuestros clientes.
          </p>
          <div class="d-flex mt-3">
            <a href="#" class="social-icon" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-icon" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-icon" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="https://wa.me/3102916140" class="social-icon" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
          </div>
        </div>
        <div class="col-lg-4 col-sm-6">
          <h5 class="fw-bold text-white mb-3">Enlaces rápidos</h5>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="../inicio/index.php">Inicio</a></li>
            <li class="mb-2"><a href="../inicio/nosotros.php">Nosotros</a></li>
            <li class="mb-2"><a href="../inicio/proyecto.php">Proyectos</a></li>
            <li class="mb-2"><a href="../inicio/contacto.php">Contacto</a></li>
          </ul>
        </div>
        <div class="col-lg-4 col-sm-6">
          <h5 class="fw-bold text-white mb-3">Contacto</h5>
          <?php 
          $raw_footer = (string)($contenido['footer_vinetas'] ?? '');
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
        <span class="mt-2 mt-md-0">Construcción civil y redes de gas natural</span>
      </div>
    </div>
  </footer>

  <!-- Botón Flotante de WhatsApp con Animación -->
  <a href="https://wa.me/573026043945?text=Hola,%20como%20estas%20información%20sobre%20sus%20servicios" 
     class="btn-whatsapp" 
     target="_blank" 
     rel="noopener noreferrer" 
     aria-label="Contactar por WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>

  <script>
    (function () {
      var marqueeDuration = 24000;
      var storageKey = 'constructoresMarqueeStart';
      var startTime = Number(localStorage.getItem(storageKey));

      if (!startTime) {
        startTime = Date.now();
      }

      var elapsed = (Date.now() - startTime) % marqueeDuration;
      document.documentElement.style.setProperty('--marquee-duration', marqueeDuration + 'ms');
      document.documentElement.style.setProperty('--marquee-delay', '-' + elapsed + 'ms');
    })();
  </script>
  <script src="../bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('.dropdown-submenu > .dropdown-toggle').forEach(function (submenuToggle) {
      submenuToggle.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        submenuToggle.nextElementSibling.classList.toggle('show');
      });
    });

    document.querySelectorAll('.dropdown').forEach(function (dropdown) {
      dropdown.addEventListener('hide.bs.dropdown', function () {
        dropdown.querySelectorAll('.dropdown-menu.show').forEach(function (submenu) {
          submenu.classList.remove('show');
        });
      });
    });
  </script>
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
