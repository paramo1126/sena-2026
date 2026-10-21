<?php
/**
 * Constructores M&M - Maquinaria
 * Conexión centralizada y consulta a contenido_inicio para footer dinámico (DRY)
 * Consulta y renderizado dinámico de la tabla contenido_maquinaria e inventario maquinarias
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

// 2. Consulta de Contenido Dinámico de la Sección MAQUINARIA
$contenido_maquinaria = [];
$valores_defecto_maq = [
    'hero_imagen' => 'imagen3.png',
    'hero_badge' => 'FLOTA PESADA Y EQUIPOS CERTIFICADOS',
    'hero_titulo' => 'FLOTA DE MAQUINARIA Y EQUIPOS',
    'hero_texto' => 'Contamos con maquinaria pesada, equipos certificados para redes de gas natural y transporte especializado para ejecutar obras civiles e infraestructura de alta exigencia.',
    'tab1_nombre' => 'Todas las Máquinas',
    'tab2_nombre' => 'Excavación y Tierras',
    'tab3_nombre' => 'Tuberías y Redes de Gas',
    'tab4_nombre' => 'Carga y Transporte',
    'garantia_titulo' => 'Garantía y Respaldo en Obra',
    'garantia_texto' => 'Aseguramos la máxima productividad de nuestros equipos en sus proyectos civiles e hidrosanitarios.',
    'g1_titulo' => 'Mantenimiento Preventivo',
    'g1_texto' => 'Equipos revisados minuciosamente antes de cada despacho a obra.',
    'g2_titulo' => 'Operadores Certificados',
    'g2_texto' => 'Personal técnico capacitado en normas de seguridad industrial y SST.',
    'g3_titulo' => 'Asistencia 24/7',
    'g3_texto' => 'Soporte mecánico prioritario en sitio para evitar retrasos en el cronograma.',
    'g4_titulo' => 'Certificaciones Vigentes',
    'g4_texto' => 'Documentación al día para ingreso a proyectos públicos y privados.'
];

if (isset($conexion) && $conexion instanceof mysqli && !$conexion->connect_error) {
    try {
        $res_maq = $conexion->query("SELECT * FROM `contenido_maquinaria` WHERE `id` = 1 LIMIT 1");
        if ($res_maq && $res_maq->num_rows > 0) {
            $contenido_maquinaria = $res_maq->fetch_assoc();
        }
    } catch (\Throwable $e_maq) {
        $contenido_maquinaria = [];
    }
}

if (!is_array($contenido_maquinaria)) {
    $contenido_maquinaria = [];
}
foreach ($valores_defecto_maq as $k => $v_def) {
    if (!isset($contenido_maquinaria[$k]) || trim((string)$contenido_maquinaria[$k]) === '') {
        $contenido_maquinaria[$k] = $v_def;
    }
}

// 3. Consulta Dinámica del Inventario de Maquinarias
$lista_maquinarias = [];
if (isset($conexion) && $conexion instanceof mysqli && !$conexion->connect_error) {
    try {
        $res_inv = $conexion->query("SELECT * FROM `maquinarias` ORDER BY `id_maquina` DESC");
        if ($res_inv) {
            while ($fila = $res_inv->fetch_assoc()) {
                $lista_maquinarias[] = $fila;
            }
        }
    } catch (\Throwable $e_inv) {
        $lista_maquinarias = [];
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

// Función para renderizar cada tarjeta de maquinaria con protección text-break
if (!function_exists('renderizar_tarjeta_maquinaria')) {
    function renderizar_tarjeta_maquinaria($m, $index = 0) {
        $imagenes_catalogo = [
            'https://images.unsplash.com/photo-1578575437130-527eed3abbec?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1581094288338-2314dddb7ece?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=600&q=80',
            '../imagenes/imagen2.png',
            '../imagenes/imagen3.png'
        ];
        $img_src = $imagenes_catalogo[$index % count($imagenes_catalogo)];

        $badge_class = 'bg-secondary';
        $estado = trim((string)($m['estado'] ?? 'Disponible'));
        if ($estado === 'Disponible') {
            $badge_class = 'bg-success';
        } elseif ($estado === 'En mantenimiento') {
            $badge_class = 'bg-warning text-dark';
        } elseif ($estado === 'Trabajando') {
            $badge_class = 'bg-primary';
        }
        ?>
        <div class="col-lg-4 col-md-6">
          <div class="card h-100 border-0 shadow-sm service-card overflow-hidden">
            <div class="position-relative">
              <img src="<?php echo htmlspecialchars($img_src); ?>" 
                   class="card-img-top" 
                   alt="<?php echo htmlspecialchars($m['modelo'] ?? 'Equipo M&M'); ?>" 
                   style="height: 220px; object-fit: cover;"
                   onerror="this.onerror=null; this.src='../imagenes/imagen1.png';">
              <span class="badge <?php echo $badge_class; ?> position-absolute top-0 end-0 m-3 px-3 py-2 text-break">
                <?php echo htmlspecialchars($estado); ?>
              </span>
            </div>
            <div class="card-body p-4 d-flex flex-column">
              <div class="small text-gold fw-bold text-uppercase mb-1 text-break">
                <?php echo htmlspecialchars(($m['categoria'] ?? 'Maquinaria') . ' · Placa: ' . ($m['placa'] ?? 'S/P')); ?>
              </div>
              <h4 class="card-title fw-bold text-navy text-break">
                <?php echo htmlspecialchars($m['modelo'] ?? 'Equipo sin modelo'); ?>
              </h4>
              <p class="card-text text-gray small text-break mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; min-height: 4.2em;">
                <?php echo nl2br(htmlspecialchars($m['caracteristicas'] ?? 'Equipo de alto rendimiento disponible para obras civiles e infraestructura.')); ?>
              </p>
              
              <ul class="list-unstyled small border-top border-bottom py-2 my-2 text-navy mt-auto">
                <li class="d-flex justify-content-between mb-1">
                  <span class="text-gray"><i class="bi bi-geo-alt me-2 text-gold"></i>Ubicación:</span>
                  <strong class="text-break"><?php echo htmlspecialchars($m['ubicacion_actual'] ?? 'Base Principal'); ?></strong>
                </li>
                <li class="d-flex justify-content-between mb-1">
                  <span class="text-gray"><i class="bi bi-tag me-2 text-gold"></i>Categoría:</span>
                  <strong class="text-break"><?php echo htmlspecialchars($m['categoria'] ?? 'Maquinaria'); ?></strong>
                </li>
                <li class="d-flex justify-content-between">
                  <span class="text-gray"><i class="bi bi-card-checklist me-2 text-gold"></i>Placa / ID:</span>
                  <strong class="text-break"><?php echo htmlspecialchars($m['placa'] ?? 'N/A'); ?></strong>
                </li>
              </ul>

              <div class="mt-3">
                <a href="https://wa.me/573026043945?text=<?php echo urlencode('Hola, solicito información y cotización sobre la maquinaria: ' . ($m['modelo'] ?? '') . ' (Placa: ' . ($m['placa'] ?? '') . ')'); ?>" 
                   class="btn btn-sm btn-outline-dark w-100 fw-bold d-flex align-items-center justify-content-center gap-2" 
                   target="_blank" 
                   rel="noopener noreferrer">
                  <i class="bi bi-whatsapp text-success"></i>
                  <span>Cotizar / Consultar</span>
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Constructores M&M | Maquinaria y Equipos Certificados</title>
  <meta name="description" content="Constructores M&M: soluciones integrales en construcción civil e infraestructura de redes de gas. Flota de maquinaria pesada y equipos certificados.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../estilos/maquinariastyle.css">
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
            <a class="nav-link active" href="../inicio/maquinaria.php">Maquinaria</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../inicio/contacto.php">Contacto</a>
          </li>
        </ul>
        <a class="login-nav-btn" href="../iniciaradmin/login.php">Iniciar sesión</a>
      </div>
    </div>
  </nav>

  <!-- Hero Section Maquinaria Dinámico con Protección text-break -->
  <header class="hero-home hero-maquinaria">
    <div class="hero-home-bg">
      <img src="<?php echo htmlspecialchars(resolver_ruta_img($contenido_maquinaria['hero_imagen'] ?? 'imagen3.png')); ?>" 
           alt="<?php echo htmlspecialchars($contenido_maquinaria['hero_titulo'] ?? 'Flota de Maquinaria Pesada'); ?>" 
           class="hero-home-img" 
           fetchpriority="high">
      <div class="hero-home-overlay"></div>
    </div>
    <div class="container position-relative hero-home-content">
      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9 text-center">
          <div class="hero-home-badge mb-3 text-break">
            <i class="bi bi-truck text-gold me-2"></i>
            <span><?php echo htmlspecialchars($contenido_maquinaria['hero_badge'] ?? 'FLOTA PESADA Y EQUIPOS CERTIFICADOS'); ?></span>
          </div>
          <h1 class="hero-home-title text-uppercase text-break">
            <?php echo htmlspecialchars($contenido_maquinaria['hero_titulo'] ?? 'FLOTA DE MAQUINARIA Y EQUIPOS'); ?>
          </h1>
          <p class="hero-home-lead mb-4 text-break">
            <?php echo nl2br(htmlspecialchars($contenido_maquinaria['hero_texto'] ?? '')); ?>
          </p>
          <div class="d-flex flex-wrap justify-content-center gap-3 hero-home-buttons">
            <a href="#maquinaria-catalog" class="btn-hero-primary">
              <span>Ver Catálogo</span>
              <i class="bi bi-arrow-down ms-2"></i>
            </a>
            <a href="https://wa.me/573026043945?text=Hola,%20solicito%20cotizacion%20de%20maquinaria" class="btn-hero-secondary" target="_blank" rel="noopener noreferrer">
              <i class="bi bi-whatsapp me-2"></i>
              <span>Cotizar Alquiler</span>
            </a>
          </div>
        </div>
      </div>
    </div>
    <a href="#maquinaria-catalog" class="hero-home-scroll" aria-label="Desplazar a catálogo">
      <i class="bi bi-chevron-double-down"></i>
    </a>
  </header>

  <!-- SECCIÓN PRINCIPAL DE CATÁLOGO Y MAQUINARIA -->
  <section id="maquinaria-catalog" class="py-5">
    <div class="container py-3">
      
      <!-- Filtros por Tipo de Equipo (Pestañas Dinámicas con text-break) -->
      <ul class="nav nav-pills justify-content-center mb-5" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active fw-bold me-2 mb-2 text-break" id="pill-todas-tab" data-bs-toggle="pill" data-bs-target="#pill-todas" type="button" role="tab">
            <?php echo htmlspecialchars($contenido_maquinaria['tab1_nombre'] ?? 'Todas las Máquinas'); ?>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-bold me-2 mb-2 text-break" id="pill-excavacion-tab" data-bs-toggle="pill" data-bs-target="#pill-excavacion" type="button" role="tab">
            <?php echo htmlspecialchars($contenido_maquinaria['tab2_nombre'] ?? 'Excavación y Tierras'); ?>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-bold me-2 mb-2 text-break" id="pill-gas-tab" data-bs-toggle="pill" data-bs-target="#pill-gas" type="button" role="tab">
            <?php echo htmlspecialchars($contenido_maquinaria['tab3_nombre'] ?? 'Tuberías y Redes de Gas'); ?>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-bold mb-2 text-break" id="pill-transporte-tab" data-bs-toggle="pill" data-bs-target="#pill-transporte" type="button" role="tab">
            <?php echo htmlspecialchars($contenido_maquinaria['tab4_nombre'] ?? 'Carga y Transporte'); ?>
          </button>
        </li>
      </ul>

      <!-- Contenido de las Pestañas (Catálogo Dinámico desde BD) -->
      <div class="tab-content" id="pills-tabContent">
        
        <!-- PESTAÑA 1: TODAS LAS MÁQUINAS -->
        <div class="tab-pane fade show active" id="pill-todas" role="tabpanel">
          <div class="row g-4">
            <?php if (!empty($lista_maquinarias)): ?>
              <?php foreach ($lista_maquinarias as $idx => $maq): ?>
                <?php renderizar_tarjeta_maquinaria($maq, $idx); ?>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12 text-center py-5">
                <div class="alert alert-light border shadow-sm d-inline-block px-5 py-4">
                  <i class="bi bi-truck text-warning fs-1 d-block mb-2"></i>
                  <h4 class="fw-bold text-navy">No hay maquinaria registrada</h4>
                  <p class="text-gray mb-0">Actualmente se está actualizando el inventario de equipos y vehículos.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- PESTAÑA 2: EXCAVACIÓN Y TIERRAS -->
        <div class="tab-pane fade" id="pill-excavacion" role="tabpanel">
          <div class="row g-4">
            <?php
            $maqs_excavacion = array_filter($lista_maquinarias, function($m) {
                $texto_comp = strtolower(($m['categoria'] ?? '') . ' ' . ($m['modelo'] ?? '') . ' ' . ($m['caracteristicas'] ?? ''));
                return ($m['categoria'] ?? '') === 'Maquinaria' || preg_match('/excav|tierra|retro|oruga|balde|zanj|nivelac/i', $texto_comp);
            });
            ?>
            <?php if (!empty($maqs_excavacion)): ?>
              <?php foreach (array_values($maqs_excavacion) as $idx => $maq): ?>
                <?php renderizar_tarjeta_maquinaria($maq, $idx); ?>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12 text-center py-5">
                <div class="alert alert-light border shadow-sm d-inline-block px-4 py-3">
                  <i class="bi bi-info-circle text-warning fs-3 d-block mb-2"></i>
                  <h5 class="fw-bold text-navy">Sin equipos clasificados en esta categoría</h5>
                  <p class="text-gray small mb-0">Puedes consultar la totalidad de nuestros equipos en la primera pestaña.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- PESTAÑA 3: TUBERÍAS Y REDES DE GAS -->
        <div class="tab-pane fade" id="pill-gas" role="tabpanel">
          <div class="row g-4">
            <?php
            $maqs_gas = array_filter($lista_maquinarias, function($m) {
                $texto_comp = strtolower(($m['categoria'] ?? '') . ' ' . ($m['modelo'] ?? '') . ' ' . ($m['caracteristicas'] ?? ''));
                return preg_match('/gas|tub|termofus|electrofus|pe|poliet|compresor|neumat/i', $texto_comp) || ($m['categoria'] ?? '') === 'Maquinaria';
            });
            ?>
            <?php if (!empty($maqs_gas)): ?>
              <?php foreach (array_values($maqs_gas) as $idx => $maq): ?>
                <?php renderizar_tarjeta_maquinaria($maq, $idx + 2); ?>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12 text-center py-5">
                <div class="alert alert-light border shadow-sm d-inline-block px-4 py-3">
                  <i class="bi bi-info-circle text-warning fs-3 d-block mb-2"></i>
                  <h5 class="fw-bold text-navy">Sin equipos específicos de redes en este momento</h5>
                  <p class="text-gray small mb-0">Revisa la pestaña general para ver todos los equipos en inventario.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- PESTAÑA 4: CARGA Y TRANSPORTE -->
        <div class="tab-pane fade" id="pill-transporte" role="tabpanel">
          <div class="row g-4">
            <?php
            $maqs_transporte = array_filter($lista_maquinarias, function($m) {
                $texto_comp = strtolower(($m['categoria'] ?? '') . ' ' . ($m['modelo'] ?? '') . ' ' . ($m['caracteristicas'] ?? ''));
                return ($m['categoria'] ?? '') === 'Vehiculo' || preg_match('/transporte|carga|volqueta|camion|furgon|m3/i', $texto_comp);
            });
            ?>
            <?php if (!empty($maqs_transporte)): ?>
              <?php foreach (array_values($maqs_transporte) as $idx => $maq): ?>
                <?php renderizar_tarjeta_maquinaria($maq, $idx + 4); ?>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12 text-center py-5">
                <div class="alert alert-light border shadow-sm d-inline-block px-4 py-3">
                  <i class="bi bi-info-circle text-warning fs-3 d-block mb-2"></i>
                  <h5 class="fw-bold text-navy">Sin vehículos de carga específicos registrados</h5>
                  <p class="text-gray small mb-0">Revisa la pestaña general para ver todos los equipos disponibles.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- SECCIÓN GARANTÍA Y RESPALDO EN OBRA (DINÁMICA CON PROTECCIÓN text-break) -->
  <section class="bg-light py-5">
    <div class="container py-3">
      <div class="text-center mb-5">
        <h2 class="section-title text-break">
          <?php echo htmlspecialchars($contenido_maquinaria['garantia_titulo'] ?? 'Garantía y Respaldo en Obra'); ?>
        </h2>
        <p class="text-gray text-break">
          <?php echo nl2br(htmlspecialchars($contenido_maquinaria['garantia_texto'] ?? '')); ?>
        </p>
      </div>

      <div class="row g-4">
        <!-- Tarjeta 1: Mantenimiento Preventivo -->
        <div class="col-md-3 text-center">
          <div class="p-4 bg-white rounded-3 shadow-sm h-100">
            <i class="bi bi-shield-fill-check text-gold display-5 mb-3"></i>
            <h5 class="fw-bold text-navy text-break">
              <?php echo htmlspecialchars($contenido_maquinaria['g1_titulo'] ?? 'Mantenimiento Preventivo'); ?>
            </h5>
            <p class="small text-gray mb-0 text-break">
              <?php echo nl2br(htmlspecialchars($contenido_maquinaria['g1_texto'] ?? '')); ?>
            </p>
          </div>
        </div>

        <!-- Tarjeta 2: Operadores Certificados -->
        <div class="col-md-3 text-center">
          <div class="p-4 bg-white rounded-3 shadow-sm h-100">
            <i class="bi bi-person-check-fill text-gold display-5 mb-3"></i>
            <h5 class="fw-bold text-navy text-break">
              <?php echo htmlspecialchars($contenido_maquinaria['g2_titulo'] ?? 'Operadores Certificados'); ?>
            </h5>
            <p class="small text-gray mb-0 text-break">
              <?php echo nl2br(htmlspecialchars($contenido_maquinaria['g2_texto'] ?? '')); ?>
            </p>
          </div>
        </div>

        <!-- Tarjeta 3: Asistencia 24/7 -->
        <div class="col-md-3 text-center">
          <div class="p-4 bg-white rounded-3 shadow-sm h-100">
            <i class="bi bi-clock-history text-gold display-5 mb-3"></i>
            <h5 class="fw-bold text-navy text-break">
              <?php echo htmlspecialchars($contenido_maquinaria['g3_titulo'] ?? 'Asistencia 24/7'); ?>
            </h5>
            <p class="small text-gray mb-0 text-break">
              <?php echo nl2br(htmlspecialchars($contenido_maquinaria['g3_texto'] ?? '')); ?>
            </p>
          </div>
        </div>

        <!-- Tarjeta 4: Certificaciones Vigentes -->
        <div class="col-md-3 text-center">
          <div class="p-4 bg-white rounded-3 shadow-sm h-100">
            <i class="bi bi-file-earmark-text-fill text-gold display-5 mb-3"></i>
            <h5 class="fw-bold text-navy text-break">
              <?php echo htmlspecialchars($contenido_maquinaria['g4_titulo'] ?? 'Certificaciones Vigentes'); ?>
            </h5>
            <p class="small text-gray mb-0 text-break">
              <?php echo nl2br(htmlspecialchars($contenido_maquinaria['g4_texto'] ?? '')); ?>
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

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
</body>
</html>