<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin | Constructores M&M</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="../estilos/style.css">
</head>
<body class="admin-page">
  <div class="mobile-header">
    <strong>Constructores M&M</strong>
    <span>Admin</span>
  </div>

  <div class="admin-layout">
    <aside class="sidebar">
      <a class="brand" href="admin.html" aria-label="Dashboard de administracion">
        <span class="brand-badge">M&M</span>
        <span>
          <strong>Panel Admin</strong>
          <span>Maquinaria</span>
        </span>
      </a>

      <nav class="nav" aria-label="Navegacion del panel">
        <a class="active" href="admin.html"><i class="bi bi-speedometer2"></i>Dashboard</a>
        <a href="modulos/imagenes/index.php"><i class="bi bi-images"></i>Imagenes</a>
        <a href="modulos/contenido/index.php"><i class="bi bi-file-text"></i>Contenido</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i>Salir</a>
      </nav>

      <div class="sidebar-footer">
        Vista limpia para revisar disponibilidad, ocupacion y mantenimiento de maquinaria.
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <div>
          <small>Dashboard principal</small>
          <h1>Estado de maquinaria</h1>
        </div>
        <div class="status-pill">
          <span class="status-dot"></span>
          Sistema activo
        </div>
      </header>

      <section class="dashboard" aria-label="Graficos del estado de maquinaria">
        <article class="panel circle-panel">
          <div class="panel-header">
            <h2>Porcentaje actual</h2>
            <p>Distribucion general de la flota registrada.</p>
          </div>

          <canvas id="graficoCircular" aria-label="Grafico circular de maquinaria"></canvas>

          <div class="total-text">53 maquinas registradas</div>

          <div class="legend-clean">
            <div class="legend-item">
              <span>Disponibles</span>
              <strong>21</strong>
            </div>
            <div class="legend-item">
              <span>Ocupadas</span>
              <strong>26</strong>
            </div>
            <div class="legend-item">
              <span>Mantenimiento</span>
              <strong>6</strong>
            </div>
          </div>
        </article>

        <article class="panel line-panel">
          <div class="panel-header">
            <h2>Movimiento semanal</h2>
            <p>Comparacion lineal de disponibilidad, ocupacion y mantenimiento.</p>
          </div>

          <div class="chart-box">
            <canvas id="graficoLineal" aria-label="Grafico lineal de maquinaria por semana"></canvas>
          </div>
        </article>
      </section>
    </main>
  </div>

  <script>
    const colores = {
      azul: '#0b1f3a',
      azulSuave: '#143a66',
      naranja: '#e85d04',
      amarillo: '#f2b705',
      verde: '#4c9a5b',
      rojo: '#d94a4a',
      gris: '#657286',
      borde: '#dfe6ef'
    };

    const datosMaquinaria = {
      disponibles: 21,
      ocupadas: 26,
      mantenimiento: 6
    };

    const total = datosMaquinaria.disponibles + datosMaquinaria.ocupadas + datosMaquinaria.mantenimiento;

    new Chart(document.getElementById('graficoCircular'), {
      type: 'doughnut',
      data: {
        labels: ['Disponibles', 'Ocupadas', 'Mantenimiento'],
        datasets: [{
          data: [datosMaquinaria.disponibles, datosMaquinaria.ocupadas, datosMaquinaria.mantenimiento],
          backgroundColor: [colores.verde, colores.azulSuave, colores.amarillo],
          borderColor: '#ffffff',
          borderWidth: 6,
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '68%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 12,
              boxHeight: 12,
              color: colores.gris,
              font: {
                family: 'Inter',
                size: 13,
                weight: '700'
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const valor = context.raw;
                const porcentaje = Math.round((valor / total) * 100);
                return ' ' + context.label + ': ' + valor + ' maquinas (' + porcentaje + '%)';
              }
            }
          }
        }
      },
      plugins: [{
        id: 'textoCentral',
        afterDraw(chart) {
          const meta = chart.getDatasetMeta(0).data[0];
          const x = meta.x;
          const y = meta.y;
          const ocupadas = Math.round((datosMaquinaria.ocupadas / total) * 100);
          const ctx = chart.ctx;

          ctx.save();
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillStyle = colores.azul;
          ctx.font = '700 48px Oswald';
          ctx.fillText(ocupadas + '%', x, y - 10);
          ctx.fillStyle = colores.gris;
          ctx.font = '800 12px Inter';
          ctx.fillText('OCUPACION', x, y + 32);
          ctx.restore();
        }
      }]
    });

    new Chart(document.getElementById('graficoLineal'), {
      type: 'line',
      data: {
        labels: ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'],
        datasets: [
          {
            label: 'Disponibles',
            data: [18, 20, 19, 22, 21, 23, 21],
            borderColor: colores.verde,
            backgroundColor: 'rgba(76, 154, 91, 0.12)',
            borderWidth: 3,
            tension: 0.35,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: colores.verde
          },
          {
            label: 'Ocupadas',
            data: [28, 27, 29, 25, 26, 24, 26],
            borderColor: colores.azulSuave,
            backgroundColor: 'rgba(20, 58, 102, 0.08)',
            borderWidth: 3,
            tension: 0.35,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: colores.azulSuave
          },
          {
            label: 'Mantenimiento',
            data: [7, 6, 5, 6, 6, 6, 6],
            borderColor: colores.amarillo,
            backgroundColor: 'rgba(242, 183, 5, 0.12)',
            borderWidth: 3,
            tension: 0.35,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: colores.amarillo
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: colores.gris,
              font: {
                family: 'Inter',
                size: 13,
                weight: '700'
              }
            }
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            },
            ticks: {
              color: colores.gris,
              font: {
                family: 'Inter',
                weight: '700'
              }
            }
          },
          y: {
            beginAtZero: true,
            suggestedMax: 35,
            grid: {
              color: colores.borde
            },
            ticks: {
              color: colores.gris,
              precision: 0,
              font: {
                family: 'Inter',
                weight: '700'
              }
            }
          }
        }
      }
    });
  </script>
</body>
</html>
