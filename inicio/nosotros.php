<?php
/**
 * Constructores M&M - Nosotros
 * Conexión centralizada y consulta a contenido_inicio para footer dinámico
 * Consulta y renderizado dinámico de la tabla contenido_nosotros
 */
require_once __DIR__ . '/../config/conexion.php';

// 1. Footer Universal (Código DRY) desde contenido_inicio
$contenido = [];
if (isset($conexion) && $conexion instanceof mysqli && !$conexion->connect_error) {
    try {
        $res = $conexion->query("SELECT * FROM `contenido_inicio` WHERE `id` = 1 LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $contenido = $res->fetch_assoc();
        }
    } catch (\Throwable $e_ini) {
        $contenido = [];
    }
}

// 2. Consulta de Contenido Dinámico de la Sección NOSOTROS
$contenido_nosotros = [];
$valores_defecto_nos = [
    'carrusel_img1' => 'imagen 22.png',
    'carrusel_img2' => 'imagen 44.png',
    'carrusel_img3' => 'imagen 33.png',
    'qs_imagen' => 'imagen4.png',
    'qs_titulo' => '¿Quiénes somos?',
    'qs_texto' => 'Constructores M&M es una empresa líder especializada en construcción civil y soluciones integrales de infraestructura para redes de gas natural. Contamos con un equipo multidisciplinario de ingenieros, arquitectos y técnicos altamente certificados que desarrollan proyectos ejecutando estándares rigurosos desde el diseño preliminar hasta la entrega final.',
    'qs_vinetas' => "+15 años de experiencia\nCumplimiento normativo\nPersonal certificado\nProyectos a tiempo",
    'mision_titulo' => 'Misión',
    'mision_texto' => 'Proveer soluciones integrales e innovadoras en construcción civil e infraestructura de redes de gas, garantizando altos estándares de calidad, seguridad industrial y cumplimiento normativo. Buscamos impactar positivamente en las comunidades a través de obras sostenibles, eficientes y confiables.',
    'vision_titulo' => 'Visión',
    'vision_texto' => 'Ser reconocidos para el 2030 como la empresa referente a nivel regional y nacional en el desarrollo de proyectos de ingeniería civil y redes de infraestructura energética, destacándonos por la excelencia operativa, el desarrollo de nuestro talento humano y el compromiso medioambiental.',
    'valores_titulo' => 'Nuestros Valores',
    'valores_subtitulo' => 'Los principios que guían la ejecución de cada uno de nuestros proyectos.',
    'v1_titulo' => 'Seguridad',
    'v1_texto' => 'Protegemos la vida de nuestros colaboradores y de la comunidad en cada obra.',
    'v2_titulo' => 'Calidad',
    'v2_texto' => 'Uso de materiales certificados y procesos con estándares técnicos rigurosos.',
    'v3_titulo' => 'Cumplimiento',
    'v3_texto' => 'Entregamos los proyectos respetando cronogramas y presupuestos pactados.',
    'v4_titulo' => 'Sostenibilidad',
    'v4_texto' => 'Respeto por el medio ambiente y mitigación del impacto ambiental.',
    'm1_valor' => '+150',
    'm1_texto' => 'Proyectos Ejecutados',
    'm2_valor' => '+50 Km',
    'm2_texto' => 'Redes de Gas Instaladas',
    'm3_valor' => '100%',
    'm3_texto' => 'Certificación Normativa',
    'm4_valor' => '+15',
    'm4_texto' => 'Años de Trayectoria'
];

if (isset($conexion) && $conexion instanceof mysqli && !$conexion->connect_error) {
    try {
        $res_nos = $conexion->query("SELECT * FROM `contenido_nosotros` WHERE `id` = 1 LIMIT 1");
        if ($res_nos && $res_nos->num_rows > 0) {
            $contenido_nosotros = $res_nos->fetch_assoc();
        }
    } catch (\Throwable $e_nos) {
        $contenido_nosotros = [];
    }
}

if (!is_array($contenido_nosotros)) {
    $contenido_nosotros = [];
}
foreach ($valores_defecto_nos as $k => $v_def) {
    if (!isset($contenido_nosotros[$k]) || trim((string)$contenido_nosotros[$k]) === '') {
        $contenido_nosotros[$k] = $v_def;
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
  <title>Constructores M&M | Construcción Civil y Redes de Gas</title>
  <meta name="description" content="Constructores M&M: soluciones integrales en construcción civil e infraestructura de redes de gas. Experiencia, seguridad y cumplimiento normativo.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../estilos/nosotrostyle.css">
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
            <a class="nav-link" aria-current="page" href="../inicio/index.php">Inicio</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="../inicio/proyecto.php" id="menuProyectos" role="button" aria-expanded="false">
              Proyectos
            </a>
            <ul class="dropdown-menu" aria-labelledby="menuProyectos">
              <li><a class="dropdown-item" href="../inicio/proyecto.php#calles">Calles pavimentadas</a></li>
              <li><a class="dropdown-item" href="../inicio/proyecto.php#tuberias">Tuberias</a></li>
              <li><a class="dropdown-item" href="../inicio/proyecto.php#evidencias">Evidencias</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="../inicio/nosotros.php">Nosotros</a>
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
        <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_nosotros['carrusel_img1'] ?? 'imagen 22.png')); ?>" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 1">
      </div>
      <div class="carousel-item">
        <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_nosotros['carrusel_img2'] ?? 'imagen 44.png')); ?>" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 2">
      </div>
      <div class="carousel-item">
        <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_nosotros['carrusel_img3'] ?? 'imagen 33.png')); ?>" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 3">
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

  <!-- Nosotros -->
  <!-- ======================================================= -->
  <!-- SECCIÓN NOSOTROS COMPLETA Y MEJORADA                    -->
  <!-- ======================================================= -->
  <section id="nosotros" class="py-5">
    <div class="container py-4">
      
      <!-- 1. Quiénes Somos -->
      <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
          <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_nosotros['qs_imagen'] ?? 'imagen4.png')); ?>" alt="Equipo de Constructores M&M en obra" class="img-fluid rounded-3 shadow">
        </div>
        <div class="col-lg-6">
          <h2 class="section-title text-start"><?php echo htmlspecialchars($contenido_nosotros['qs_titulo'] ?? '¿Quiénes somos?'); ?></h2>
          <p class="text-gray mb-4">
            <?php echo nl2br(htmlspecialchars($contenido_nosotros['qs_texto'] ?? '')); ?>
          </p>
          <div class="row g-3">
            <?php 
            $qs_items = array_filter(array_map('trim', explode("\n", (string)($contenido_nosotros['qs_vinetas'] ?? ''))));
            if (empty($qs_items)) {
              $qs_items = ['+15 años de experiencia', 'Cumplimiento normativo', 'Personal certificado', 'Proyectos a tiempo'];
            }
            foreach ($qs_items as $qs_item):
              $qs_txt = ltrim($qs_item, "-•* \t\n\r\0\x0B");
              if ($qs_txt === '') continue;
            ?>
              <div class="col-6">
                <div class="d-flex align-items-center">
                  <i class="bi bi-check-circle-fill text-gold fs-4 me-2"></i>
                  <span class="fw-semibold text-navy"><?php echo htmlspecialchars($qs_txt); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- 2. Misión y Visión -->
      <div class="row g-4 my-5">
        <div class="col-md-6">
          <div class="card h-100 border-0 shadow-sm p-4 border-start border-4 border-gold bg-light">
            <div class="d-flex align-items-center mb-3">
              <div class="service-icon me-3 mb-0" style="width: 50px; height: 50px; font-size: 1.5rem;">
                <i class="bi bi-compass-fill"></i>
              </div>
              <h3 class="h4 fw-bold text-navy mb-0"><?php echo htmlspecialchars($contenido_nosotros['mision_titulo'] ?? 'Misión'); ?></h3>
            </div>
            <p class="text-gray mb-0">
              <?php echo nl2br(htmlspecialchars($contenido_nosotros['mision_texto'] ?? '')); ?>
            </p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card h-100 border-0 shadow-sm p-4 border-start border-4 border-navy bg-light">
            <div class="d-flex align-items-center mb-3">
              <div class="service-icon me-3 mb-0" style="width: 50px; height: 50px; font-size: 1.5rem; background-color: rgba(11, 31, 58, 0.1); color: var(--color-navy);">
                <i class="bi bi-eye-fill"></i>
              </div>
              <h3 class="h4 fw-bold text-navy mb-0"><?php echo htmlspecialchars($contenido_nosotros['vision_titulo'] ?? 'Visión'); ?></h3>
            </div>
            <p class="text-gray mb-0">
              <?php echo nl2br(htmlspecialchars($contenido_nosotros['vision_texto'] ?? '')); ?>
            </p>
          </div>
        </div>
      </div>

      <!-- 3. Valores Corporativos -->
      <div class="my-5 text-center">
        <h2 class="section-title"><?php echo htmlspecialchars($contenido_nosotros['valores_titulo'] ?? 'Nuestros Valores'); ?></h2>
        <p class="text-gray mb-4"><?php echo nl2br(htmlspecialchars($contenido_nosotros['valores_subtitulo'] ?? '')); ?></p>
        
        <div class="row g-4 mt-2">
          <div class="col-6 col-md-3">
            <div class="why-card card h-100 border-0 shadow-sm">
              <i class="bi bi-shield-check why-icon"></i>
              <h5 class="fw-bold text-navy"><?php echo htmlspecialchars($contenido_nosotros['v1_titulo'] ?? 'Seguridad'); ?></h5>
              <p class="small text-gray mb-0"><?php echo htmlspecialchars($contenido_nosotros['v1_texto'] ?? ''); ?></p>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="why-card card h-100 border-0 shadow-sm">
              <i class="bi bi-award-fill why-icon"></i>
              <h5 class="fw-bold text-navy"><?php echo htmlspecialchars($contenido_nosotros['v2_titulo'] ?? 'Calidad'); ?></h5>
              <p class="small text-gray mb-0"><?php echo htmlspecialchars($contenido_nosotros['v2_texto'] ?? ''); ?></p>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="why-card card h-100 border-0 shadow-sm">
              <i class="bi bi-clock-history why-icon"></i>
              <h5 class="fw-bold text-navy"><?php echo htmlspecialchars($contenido_nosotros['v3_titulo'] ?? 'Cumplimiento'); ?></h5>
              <p class="small text-gray mb-0"><?php echo htmlspecialchars($contenido_nosotros['v3_texto'] ?? ''); ?></p>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="why-card card h-100 border-0 shadow-sm">
              <i class="bi bi-tree-fill why-icon"></i>
              <h5 class="fw-bold text-navy"><?php echo htmlspecialchars($contenido_nosotros['v4_titulo'] ?? 'Sostenibilidad'); ?></h5>
              <p class="small text-gray mb-0"><?php echo htmlspecialchars($contenido_nosotros['v4_texto'] ?? ''); ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Banner de Logros / Métricas -->
      <div class="bg-navy rounded-3 text-white p-4 p-md-5 mt-5 shadow">
        <div class="row text-center g-4">
          <div class="col-6 col-md-3">
            <div class="display-6 fw-bold text-gold"><?php echo htmlspecialchars($contenido_nosotros['m1_valor'] ?? '+150'); ?></div>
            <div class="small text-uppercase"><?php echo htmlspecialchars($contenido_nosotros['m1_texto'] ?? 'Proyectos Ejecutados'); ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div class="display-6 fw-bold text-gold"><?php echo htmlspecialchars($contenido_nosotros['m2_valor'] ?? '+50 Km'); ?></div>
            <div class="small text-uppercase"><?php echo htmlspecialchars($contenido_nosotros['m2_texto'] ?? 'Redes de Gas Instaladas'); ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div class="display-6 fw-bold text-gold"><?php echo htmlspecialchars($contenido_nosotros['m3_valor'] ?? '100%'); ?></div>
            <div class="small text-uppercase"><?php echo htmlspecialchars($contenido_nosotros['m3_texto'] ?? 'Certificación Normativa'); ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div class="display-6 fw-bold text-gold"><?php echo htmlspecialchars($contenido_nosotros['m4_valor'] ?? '+15'); ?></div>
            <div class="small text-uppercase"><?php echo htmlspecialchars($contenido_nosotros['m4_texto'] ?? 'Años de Trayectoria'); ?></div>
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
          $raw_footer = (string)($contenido['footer_vinetas'] ?? $contenido['footer_contacto_vinetas'] ?? '');
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
        localStorage.setItem(storageKey, String(startTime));
      }

      var elapsed = (Date.now() - startTime) % marqueeDuration;
      document.documentElement.style.setProperty('--marquee-duration', marqueeDuration + 'ms');
      document.documentElement.style.setProperty('--marquee-delay', '-' + elapsed + 'ms');
    })();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
