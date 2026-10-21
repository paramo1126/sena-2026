<?php
/**
 * Constructores M&M - Contacto
 * Conexión centralizada y consulta a contenido_inicio para footer dinámico (DRY)
 * Consulta y renderizado dinámico de la tabla contenido_contacto
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

// 2. Consulta de Contenido Dinámico de la Sección CONTACTO
$contenido_contacto = [];
$valores_defecto_cont = [
    'hero_titulo' => 'Hablemos de su proximo proyecto',
    'hero_texto' => 'Estamos listos para asesorarle en construccion civil, redes de gas, mantenimiento e infraestructura. Cuentenos que necesita y nuestro equipo le contactara con una respuesta clara y oportuna.',
    'wpp_numero' => '573026043945',
    'wpp_mensaje_hero' => 'Hola, quiero informacion sobre sus servicios',
    'wpp_mensaje_flotante' => 'Hola, como estas información sobre sus servicios',
    'c1_dato' => '+57 310 291 6140',
    'c1_desc' => 'Atencion comercial',
    'c2_dato' => '+57 302 604 3945',
    'c2_desc' => 'Respuesta rapida',
    'c3_dato' => 'contacto@constructoresmym.com',
    'c3_desc' => 'Cotizaciones y soporte',
    'c4_dato' => 'Neiva, Huila, Colombia',
    'c4_desc' => 'Area de servicio',
    'mapa_busqueda' => 'Neiva, Huila, Colombia',
    'mapa_titulo' => 'Estamos en Neiva, Huila',
    'mapa_texto' => 'Atendemos proyectos en la ciudad y zonas cercanas con la mejor calidad tecnica y cumplimiento normativo.'
];

if (isset($conexion) && $conexion instanceof mysqli && !$conexion->connect_error) {
    try {
        $res_cont = $conexion->query("SELECT * FROM `contenido_contacto` WHERE `id` = 1 LIMIT 1");
        if ($res_cont && $res_cont->num_rows > 0) {
            $contenido_contacto = $res_cont->fetch_assoc();
        }
    } catch (\Throwable $e_cont) {
        $contenido_contacto = [];
    }
}

if (!is_array($contenido_contacto)) {
    $contenido_contacto = [];
}
foreach ($valores_defecto_cont as $k => $v_def) {
    if (!isset($contenido_contacto[$k]) || trim((string)$contenido_contacto[$k]) === '') {
        $contenido_contacto[$k] = $v_def;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Constructores M&M | Canales de Atención y Contacto</title>
  <meta name="description" content="Constructores M&M: soluciones integrales en construcción civil e infraestructura de redes de gas. Contáctenos para asesoría y cotizaciones inmediatas.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../estilos/contactostyle.css">
</head>
<body>
  <!-- Top Marquee Banner -->
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
            <a class="nav-link" href="../inicio/nosotros.php">Nosotros</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../inicio/maquinaria.php">Maquinaria</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="../inicio/contacto.php">Contacto</a>
          </li>
        </ul>
        <a class="login-nav-btn" href="../iniciaradmin/login.php">Iniciar sesión</a>
      </div>
    </div>
  </nav>

  <!-- Hero Section Contacto Dinámico con text-break -->
  <section class="contact-hero">
    <div class="container">
      <span class="contact-eyebrow"><i class="bi bi-headset"></i> Atencion y cotizaciones</span>
      <h1 class="text-break"><?php echo htmlspecialchars($contenido_contacto['hero_titulo']); ?></h1>
      <p class="text-break">
        <?php echo nl2br(htmlspecialchars($contenido_contacto['hero_texto'])); ?>
      </p>
      <div class="d-flex flex-wrap gap-3 mt-4">
        <!-- Integración Avanzada de WhatsApp en el Hero -->
        <a href="https://wa.me/<?php echo htmlspecialchars($contenido_contacto['wpp_numero']); ?>?text=<?php echo urlencode($contenido_contacto['wpp_mensaje_hero']); ?>" class="btn btn-warning btn-lg quick-contact text-break" target="_blank" rel="noopener noreferrer">
          <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
        <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $contenido_contacto['c1_dato'])); ?>" class="btn btn-outline-light btn-lg quick-contact text-break">
          <i class="bi bi-telephone-fill"></i> Llamar ahora
        </a>
      </div>
    </div>
  </section>

  <!-- Contenido Principal -->
  <main>
    <section class="py-5 bg-light">
      <div class="container">
        
        <!-- Bloque 2: Tarjetas de Información Dinámicas -->
        <div class="row g-4 mb-5">
          <!-- Tarjeta 1: Teléfono -->
          <div class="col-md-6 col-xl-3">
            <div class="contact-info-card">
              <div class="contact-info-icon"><i class="bi bi-telephone-fill"></i></div>
              <h2 class="h5 fw-bold text-navy text-break">Teléfono</h2>
              <p class="text-gray mb-2 text-break"><?php echo htmlspecialchars($contenido_contacto['c1_desc']); ?></p>
              <a class="fw-semibold text-navy text-decoration-none text-break" href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $contenido_contacto['c1_dato'])); ?>">
                <?php echo htmlspecialchars($contenido_contacto['c1_dato']); ?>
              </a>
            </div>
          </div>

          <!-- Tarjeta 2: WhatsApp -->
          <div class="col-md-6 col-xl-3">
            <div class="contact-info-card" style="animation-delay: 0.08s;">
              <div class="contact-info-icon"><i class="bi bi-whatsapp"></i></div>
              <h2 class="h5 fw-bold text-navy text-break">WhatsApp</h2>
              <p class="text-gray mb-2 text-break"><?php echo htmlspecialchars($contenido_contacto['c2_desc']); ?></p>
              <a class="fw-semibold text-navy text-decoration-none text-break" href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/[^0-9]/', '', $contenido_contacto['c2_dato'] ?: $contenido_contacto['wpp_numero'])); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo htmlspecialchars($contenido_contacto['c2_dato']); ?>
              </a>
            </div>
          </div>

          <!-- Tarjeta 3: Correo -->
          <div class="col-md-6 col-xl-3">
            <div class="contact-info-card" style="animation-delay: 0.16s;">
              <div class="contact-info-icon"><i class="bi bi-envelope-fill"></i></div>
              <h2 class="h5 fw-bold text-navy text-break">Correo</h2>
              <p class="text-gray mb-2 text-break"><?php echo htmlspecialchars($contenido_contacto['c3_desc']); ?></p>
              <a class="fw-semibold text-navy text-decoration-none text-break" href="mailto:<?php echo htmlspecialchars($contenido_contacto['c3_dato']); ?>">
                <?php echo htmlspecialchars($contenido_contacto['c3_dato']); ?>
              </a>
            </div>
          </div>

          <!-- Tarjeta 4: Ubicación -->
          <div class="col-md-6 col-xl-3">
            <div class="contact-info-card" style="animation-delay: 0.24s;">
              <div class="contact-info-icon"><i class="bi bi-geo-alt-fill"></i></div>
              <h2 class="h5 fw-bold text-navy text-break">Ubicación</h2>
              <p class="text-gray mb-2 text-break"><?php echo htmlspecialchars($contenido_contacto['c4_desc']); ?></p>
              <span class="fw-semibold text-navy text-break"><?php echo htmlspecialchars($contenido_contacto['c4_dato']); ?></span>
            </div>
          </div>
        </div>

        <!-- Bloque 3: Mapa Interactivo Dinámico -->
        <div class="row justify-content-center">
          <div class="col-lg-9">
            <div class="map-panel">
              <iframe
                title="<?php echo htmlspecialchars($contenido_contacto['mapa_titulo'] ?? 'Ubicación'); ?>"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q=<?php echo urlencode($contenido_contacto['mapa_busqueda']); ?>&output=embed">
              </iframe>
              <div class="map-caption">
                <h2 class="h5 fw-bold text-navy mb-2 text-break"><?php echo htmlspecialchars($contenido_contacto['mapa_titulo']); ?></h2>
                <p class="text-gray mb-3 text-break">
                  <?php echo nl2br(htmlspecialchars($contenido_contacto['mapa_texto'])); ?>
                </p>
                <a class="btn btn-outline-navy text-break" href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($contenido_contacto['mapa_busqueda']); ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-map-fill me-2"></i>Ver en Google Maps
                </a>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </main>

  <!-- Footer Universal (Código DRY conectado a contenido_inicio) -->
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
              <span class="text-break"><?php echo htmlspecialchars($c_limpia); ?></span>
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

  <!-- Botón Flotante de WhatsApp con Animación e Integración Dinámica -->
  <a href="https://wa.me/<?php echo htmlspecialchars($contenido_contacto['wpp_numero']); ?>?text=<?php echo urlencode($contenido_contacto['wpp_mensaje_flotante']); ?>" 
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
</body>
</html>