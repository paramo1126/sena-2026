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
            <a class="nav-link" href="../inicio/contacto.php">Contacto</a>
          </li>
        </ul>
        <a class="login-nav-btn" href="../iniciaradmin/login.php">Iniciar sesión</a>
      </div>
    </div>
  </nav>

  <main>
    <div id="carouselExampleControlsNoTouching" class="carousel slide" data-bs-ride="carousel" data-bs-touch="true" data-bs-interval="3500" data-bs-pause="false">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="../imagenes/imagen1.png" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 1">
    </div>
    <div class="carousel-item">
      <img src="../imagenes/imagen2.png" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 2">
    </div>
    <div class="carousel-item">
      <img src="../imagenes/imagen3.png" class="d-block w-100" alt="Proyecto de construcción realizado por Constructores M&M - imagen 3">
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


    <div class="container">
      <div class="anchor-panel">
        <div class="row g-2">
          <div class="col-md-4">
            <a class="anchor-link" href="#calles"><i class="bi bi-signpost-split-fill"></i> Calles pavimentadas</a>
          </div>
          <div class="col-md-4">
            <a class="anchor-link" href="#tuberias"><i class="bi bi-fire"></i> Tuberias</a>
          </div>
          <div class="col-md-4">
            <a class="anchor-link" href="#evidencias"><i class="bi bi-geo-alt-fill"></i> Evidencias</a>
          </div>
        </div>
      </div>
    </div>

    <section id="calles" class="py-5">
      <div class="container py-4">
        <div class="row align-items-end mb-4">
          <div class="col-lg-7">
            <h2 class="section-title">Calles pavimentadas</h2>
            <p class="text-gray mb-lg-0">
              Obras de pavimentacion, mejoramiento de vias, conformacion de andenes y adecuacion de espacio publico.
            </p>
          </div>
          <div class="col-lg-5 text-lg-end">
            <span class="project-badge"><i class="bi bi-check-circle-fill"></i> Evidencia con direccion de referencia</span>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-6 col-xl-4">
            <article class="card project-card">
              <img src="../imagenes/imagen1.png" alt="Pavimentacion de calle urbana">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-signpost-fill"></i> Pavimentacion</span>
                <h3 class="h5 fw-bold text-navy">Mejoramiento vial barrio Las Palmas</h3>
                <ul class="evidence-list mt-3">
                  <li><i class="bi bi-geo-alt-fill"></i><span>Calle 8 # 21-45, Neiva, Huila</span></li>
                  <li><i class="bi bi-calendar-check-fill"></i><span>Entrega de carpeta asfaltica y nivelacion de via</span></li>
                  <li><i class="bi bi-camera-fill"></i><span>Registro fotografico antes, durante y despues</span></li>
                </ul>
              </div>
            </article>
          </div>

          <div class="col-md-6 col-xl-4">
            <article class="card project-card" style="animation-delay: 0.08s;">
              <img src="../imagenes/imagen2.png" alt="Construccion de anden y sardinel">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-bricks"></i> Andenes</span>
                <h3 class="h5 fw-bold text-navy">Andenes y sardineles sector Centro</h3>
                <ul class="evidence-list mt-3">
                  <li><i class="bi bi-geo-alt-fill"></i><span>Carrera 5 # 10-32, Neiva, Huila</span></li>
                  <li><i class="bi bi-calendar-check-fill"></i><span>Construccion de andenes, bordillos y accesos</span></li>
                  <li><i class="bi bi-shield-check"></i><span>Acabados seguros para transito peatonal</span></li>
                </ul>
              </div>
            </article>
          </div>

          <div class="col-md-6 col-xl-4">
            <article class="card project-card" style="animation-delay: 0.16s;">
              <img src="../imagenes/imagen3.png" alt="Adecuacion de via residencial">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-cone-striped"></i> Via urbana</span>
                <h3 class="h5 fw-bold text-navy">Adecuacion vial barrio El Jardin</h3>
                <ul class="evidence-list mt-3">
                  <li><i class="bi bi-geo-alt-fill"></i><span>Calle 18 Sur # 7-80, Neiva, Huila</span></li>
                  <li><i class="bi bi-calendar-check-fill"></i><span>Compactacion, base granular y acabado final</span></li>
                  <li><i class="bi bi-clipboard2-check-fill"></i><span>Control de calidad por etapas de obra</span></li>
                </ul>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section id="tuberias" class="py-5 bg-light">
      <div class="container py-4">
        <div class="row align-items-end mb-4">
          <div class="col-lg-7">
            <h2 class="section-title">Tuberias y redes</h2>
            <p class="text-gray mb-lg-0">
              Instalacion, rehabilitacion y mantenimiento de redes de gas e infraestructura hidraulica.
            </p>
          </div>
          <div class="col-lg-5 text-lg-end">
            <span class="project-badge"><i class="bi bi-tools"></i> Redes instaladas y verificadas</span>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-6">
            <article class="card project-card">
              <img src="../imagenes/imagen4.png" alt="Instalacion de tuberias de gas">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-fire"></i> Redes de gas</span>
                <h3 class="h5 fw-bold text-navy">Instalacion de red interna residencial</h3>
                <ul class="evidence-list mt-3">
                  <li><i class="bi bi-geo-alt-fill"></i><span>Carrera 16 # 22-18, Neiva, Huila</span></li>
                  <li><i class="bi bi-check2-circle"></i><span>Trazado, instalacion y prueba de hermeticidad</span></li>
                  <li><i class="bi bi-file-earmark-check-fill"></i><span>Soporte tecnico de cumplimiento normativo</span></li>
                </ul>
              </div>
            </article>
          </div>

          <div class="col-md-6">
            <article class="card project-card" style="animation-delay: 0.08s;">
              <img src="../imagenes/imagen2.png" alt="Red hidraulica instalada en obra">
              <div class="card-body p-4">
                <span class="project-badge"><i class="bi bi-droplet-fill"></i> Red hidraulica</span>
                <h3 class="h5 fw-bold text-navy">Rehabilitacion de tuberia principal</h3>
                <ul class="evidence-list mt-3">
                  <li><i class="bi bi-geo-alt-fill"></i><span>Avenida La Toma # 3-60, Neiva, Huila</span></li>
                  <li><i class="bi bi-check2-circle"></i><span>Excavacion tecnica, cambio de tramo y relleno compactado</span></li>
                  <li><i class="bi bi-clipboard-data-fill"></i><span>Inspeccion y reporte final de funcionamiento</span></li>
                </ul>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

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
          <div class="footer-contact-item">
            <i class="bi bi-geo-alt-fill"></i>
            <span>Neiva, Huila, Colombia</span>
          </div>
          <div class="footer-contact-item">
            <i class="bi bi-telephone-fill"></i>
            <span>+57 310 291 6140</span>
          </div>
          <div class="footer-contact-item">
            <i class="bi bi-envelope-fill"></i>
            <span>contacto@constructoresmym.com</span>
          </div>
          <div class="footer-contact-item">
            <i class="bi bi-clock-fill"></i>
            <span>Lunes a viernes: 8:00 a.m. - 5:00 p.m.</span>
          </div>
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
