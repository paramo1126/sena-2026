<?php
/**
 * Chatbot Asistente Virtual - M&M Construcciones y Servicios S.A.S.
 * Diseño: Chatbot Corporativo Oficial M&M (Paleta #0A192F Navy + #FFC107 Amarillo Corporativo)
 * Deduplicación Estricta: Cada fila de movimientos corresponde a un vehículo DISTINTO (sin repetir el mismo carro)
 * Formato Ultra-Compacto: Evita el deslizamiento vertical excesivo, adaptándose con elegancia a la ventana
 */

// Helper para generar tarjetas ultra-compactas de maquinarias/vehículos
function renderChatbotVehiculoCard($maq, $movs = []) {
    $badgeEstado = 'badge bg-success';
    $estLower = strtolower($maq['estado'] ?? '');
    if (str_contains($estLower, 'trab') || str_contains($estLower, 'obra')) {
        $badgeEstado = 'badge bg-primary';
    } elseif (str_contains($estLower, 'mant') || str_contains($estLower, 'tall')) {
        $badgeEstado = 'badge bg-warning text-dark';
    }

    $entStr = !empty($maq['fecha_entrada']) ? date('d/m', strtotime($maq['fecha_entrada'])) . ' ' . substr($maq['hora_entrada'] ?? '', 0, 5) : '--';
    $salStr = !empty($maq['fecha_salida']) ? date('d/m', strtotime($maq['fecha_salida'])) . ' ' . substr($maq['hora_salida'] ?? '', 0, 5) : '--';

    $caract = !empty($maq['caracteristicas']) ? $maq['caracteristicas'] : 'Sin detalles registrados';
    if (mb_strlen($caract) > 65) {
        $caract = mb_substr($caract, 0, 62) . '...';
    }

    $ubic = !empty($maq['ubicacion_actual']) ? $maq['ubicacion_actual'] : 'Sin ubicación';
    if (mb_strlen($ubic) > 20) {
        $ubic = mb_substr($ubic, 0, 18) . '..';
    }

    $placa = htmlspecialchars($maq['placa'] ?? '');
    $modelo = htmlspecialchars($maq['modelo'] ?? '');
    $estado = htmlspecialchars($maq['estado'] ?? '');
    $categoria = htmlspecialchars($maq['categoria'] ?? '');

    $card = "<div class='mmchat-vcard'>";
    $card .= "  <div class='mmchat-vcard-top'>";
    $card .= "    <span class='badge bg-dark fw-bold'>{$placa}</span>";
    $card .= "    <strong class='mmchat-vcard-title'>{$modelo}</strong>";
    $card .= "    <span class='{$badgeEstado} ms-auto'>{$estado}</span>";
    $card .= "  </div>";

    $card .= "  <div class='mmchat-vcard-row'>";
    $card .= "    <span><i class='bi bi-tag text-warning me-1'></i>{$categoria}</span>";
    $card .= "    <span><i class='bi bi-geo-alt text-danger me-1'></i>" . htmlspecialchars($ubic) . "</span>";
    $card .= "  </div>";

    $card .= "  <div class='mmchat-vcard-desc' title='" . htmlspecialchars($maq['caracteristicas'] ?? '') . "'>";
    $card .= "    <i class='bi bi-card-text text-secondary me-1'></i>" . htmlspecialchars($caract);
    $card .= "  </div>";

    $card .= "  <div class='mmchat-vcard-dates text-muted'>";
    $card .= "    <span>Ent: {$entStr}</span> &bull; <span>Sal: {$salStr}</span>";
    $card .= "  </div>";

    if (!empty($movs)) {
        $mv = $movs[0];
        $fReg = !empty($mv['fecha_registro']) ? date('d/m H:i', strtotime($mv['fecha_registro'])) : '';
        $accion = htmlspecialchars(mb_substr($mv['accion'] ?? '', 0, 24));
        $ubicMov = !empty($mv['ubicacion']) ? " (" . htmlspecialchars(mb_substr($mv['ubicacion'], 0, 14)) . ")" : "";
        $card .= "  <div class='mmchat-vcard-mov'>";
        $card .= "    <i class='bi bi-arrow-repeat text-primary me-1'></i><strong>Último:</strong> [{$fReg}] {$accion}{$ubicMov}";
        $card .= "  </div>";
    }

    $card .= "</div>";
    return $card;
}

// Endpoint AJAX para responder consultas globales en la base de datos
if (isset($_GET['ajax_chat_query'])) {
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/../config/conexion.php';

    $consulta = trim($_GET['q'] ?? '');
    $respuesta = '';

    if (isset($conexion) && !$conexion->connect_error && !empty($consulta)) {

        // -------------------------------------------------------------
        // 1. BÚSQUEDA POR FECHA (DD/MM/YYYY, YYYY-MM-DD, o 'hoy' / 'ayer')
        // -------------------------------------------------------------
        $fechaSql = null;
        $fechaHumana = null;

        // Detectar 'hoy'
        if (preg_match('/\b(hoy|el dia de hoy|movimientos de hoy)\b/iu', $consulta)) {
            $fechaSql = date('Y-m-d');
            $fechaHumana = date('d/m/Y');
        }
        // Detectar 'ayer'
        elseif (preg_match('/\b(ayer|el dia de ayer|movimientos de ayer)\b/iu', $consulta)) {
            $fechaSql = date('Y-m-d', strtotime('-1 day'));
            $fechaHumana = date('d/m/Y', strtotime('-1 day'));
        }
        // Formato DD/MM/YYYY o DD-MM-YYYY (ej: 15/10/2026)
        elseif (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $consulta, $mFmt1)) {
            $dia  = str_pad($mFmt1[1], 2, '0', STR_PAD_LEFT);
            $mes  = str_pad($mFmt1[2], 2, '0', STR_PAD_LEFT);
            $anio = $mFmt1[3];
            if (checkdate((int)$mes, (int)$dia, (int)$anio)) {
                $fechaSql = "$anio-$mes-$dia";
                $fechaHumana = "$dia/$mes/$anio";
            }
        }
        // Formato YYYY-MM-DD o YYYY/MM/DD (ej: 2026-10-15)
        elseif (preg_match('/\b(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})\b/', $consulta, $mFmt2)) {
            $anio = $mFmt2[1];
            $mes  = str_pad($mFmt2[2], 2, '0', STR_PAD_LEFT);
            $dia  = str_pad($mFmt2[3], 2, '0', STR_PAD_LEFT);
            if (checkdate((int)$mes, (int)$dia, (int)$anio)) {
                $fechaSql = "$anio-$mes-$dia";
                $fechaHumana = "$dia/$mes/$anio";
            }
        }

        if ($fechaSql) {
            $stmtF = $conexion->prepare("
                SELECT 
                    hm.*,
                    m.placa,
                    m.modelo,
                    m.categoria
                FROM historial_movimientos hm
                LEFT JOIN maquinarias m ON hm.id_maquina = m.id_maquina
                WHERE DATE(hm.fecha_registro) = ? OR hm.fecha_entrada = ? OR hm.fecha_salida = ?
                ORDER BY hm.id_movimiento DESC
                LIMIT 30
            ");
            $todosMovsFecha = [];
            if ($stmtF) {
                $stmtF->bind_param("sss", $fechaSql, $fechaSql, $fechaSql);
                $stmtF->execute();
                $resF = $stmtF->get_result();
                while ($rowF = $resF->fetch_assoc()) {
                    $todosMovsFecha[] = $rowF;
                }
                $stmtF->close();
            }

            // DEDUPLICACIÓN ESTRICTA: No repetir el movimiento del mismo carro, sino mostrar carros distintos
            $movsDistintos = [];
            $carrosVistos = [];
            foreach ($todosMovsFecha as $mv) {
                $keyCarro = !empty($mv['id_maquina']) ? $mv['id_maquina'] : ($mv['placa'] ?? null);
                if ($keyCarro && !isset($carrosVistos[$keyCarro])) {
                    $carrosVistos[$keyCarro] = true;
                    $movsDistintos[] = $mv;
                }
            }

            // Consultar entradas/salidas programadas de ese día en maquinarias sin repetir carros ya vistos
            $maqsFechaDistintas = [];
            $stmtMF = $conexion->prepare("
                SELECT * FROM maquinarias 
                WHERE fecha_entrada = ? OR fecha_salida = ?
                ORDER BY id_maquina DESC
                LIMIT 20
            ");
            if ($stmtMF) {
                $stmtMF->bind_param("ss", $fechaSql, $fechaSql);
                $stmtMF->execute();
                $resMF = $stmtMF->get_result();
                while ($rowMF = $resMF->fetch_assoc()) {
                    $mId = !empty($rowMF['id_maquina']) ? $rowMF['id_maquina'] : ($rowMF['placa'] ?? null);
                    if ($mId && !isset($carrosVistos[$mId])) {
                        $carrosVistos[$mId] = true;
                        $maqsFechaDistintas[] = $rowMF;
                    }
                }
                $stmtMF->close();
            }

            $totalCarrosDistintos = count($movsDistintos) + count($maqsFechaDistintas);

            $html = "<div class='mmchat-vcard'>";
            $html .= "  <div class='mmchat-vcard-top border-bottom pb-1 mb-1'>";
            $html .= "    <strong class='text-dark'><i class='bi bi-calendar-event text-warning me-1'></i> Reporte {$fechaHumana}</strong>";
            $html .= "    <span class='badge bg-success ms-auto'>{$totalCarrosDistintos} vehículos</span>";
            $html .= "  </div>";

            if ($totalCarrosDistintos === 0) {
                $html .= "<div class='text-muted small py-1'><i class='bi bi-info-circle me-1'></i>Sin movimientos de vehículos registrados para esta fecha.</div>";
            } else {
                $html .= "<div class='mmchat-vcard-list'>";
                $mostrados = 0;
                // Cada movimiento corresponde a un carro DISTINTO
                foreach ($movsDistintos as $mv) {
                    if ($mostrados >= 3) break;
                    $horaReg = !empty($mv['fecha_registro']) ? date('H:i', strtotime($mv['fecha_registro'])) : '--:--';
                    $placaTxt = !empty($mv['placa']) ? $mv['placa'] : 'ID #' . $mv['id_maquina'];
                    $modeloTxt = !empty($mv['modelo']) ? " (" . htmlspecialchars(mb_substr($mv['modelo'], 0, 10)) . ")" : "";
                    $accion = mb_substr($mv['accion'] ?? '', 0, 22);
                    $ubic = !empty($mv['ubicacion']) ? " &bull; <em>" . htmlspecialchars(mb_substr($mv['ubicacion'], 0, 12)) . "</em>" : "";
                    $html .= "<div class='mmchat-mini-row'><span class='badge bg-secondary me-1'>{$horaReg}</span> <strong>{$placaTxt}</strong>{$modeloTxt}: " . htmlspecialchars($accion) . "{$ubic}</div>";
                    $mostrados++;
                }
                foreach ($maqsFechaDistintas as $mq) {
                    if ($mostrados >= 3) break;
                    $tipo = ($mq['fecha_entrada'] === $fechaSql) ? 'Entrada' : 'Salida';
                    $hora = ($tipo === 'Entrada') ? substr($mq['hora_entrada'] ?? '00:00', 0, 5) : substr($mq['hora_salida'] ?? '00:00', 0, 5);
                    $html .= "<div class='mmchat-mini-row'><span class='badge bg-secondary me-1'>{$hora}</span> <strong>{$mq['placa']}</strong> (" . htmlspecialchars(mb_substr($mq['modelo'], 0, 10)) . "): {$tipo} programada</div>";
                    $mostrados++;
                }
                $html .= "</div>";

                if ($totalCarrosDistintos > $mostrados) {
                    $resto = $totalCarrosDistintos - $mostrados;
                    $html .= "<div class='text-muted small mt-1'>+{$resto} vehículo(s) distinto(s) más. Consulta por placa para detalle.</div>";
                }
            }
            $html .= "</div>";
            $respuesta = $html;
        }

        // -------------------------------------------------------------
        // 2. BÚSQUEDA POR PLACA EXACTA (6 CARACTERES)
        // -------------------------------------------------------------
        if (empty($respuesta)) {
            $placaBuscada = '';
            if (preg_match('/\b[a-zA-Z0-9]{6}\b/', $consulta, $matches)) {
                $posiblePlaca = strtoupper($matches[0]);
                $stmt = $conexion->prepare("SELECT * FROM maquinarias WHERE placa = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param("s", $posiblePlaca);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res && $res->num_rows > 0) {
                        $placaBuscada = $posiblePlaca;
                    }
                    $stmt->close();
                }
            }

            if (!empty($placaBuscada)) {
                $stmt = $conexion->prepare("SELECT * FROM maquinarias WHERE placa = ? LIMIT 1");
                $stmt->bind_param("s", $placaBuscada);
                $stmt->execute();
                $maq = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($maq) {
                    $movs = [];
                    $stmtM = $conexion->prepare("SELECT * FROM historial_movimientos WHERE id_maquina = ? ORDER BY id_movimiento DESC LIMIT 1");
                    if ($stmtM) {
                        $stmtM->bind_param("i", $maq['id_maquina']);
                        $stmtM->execute();
                        $resM = $stmtM->get_result();
                        while ($m = $resM->fetch_assoc()) {
                            $movs[] = $m;
                        }
                        $stmtM->close();
                    }

                    $respuesta = renderChatbotVehiculoCard($maq, $movs);
                }
            }
        }

        // -------------------------------------------------------------
        // 3. CONSULTA DE ÚLTIMOS MOVIMIENTOS GLOBALES (CARROS DISTINTOS)
        // -------------------------------------------------------------
        if (empty($respuesta) && preg_match('/\b(movimientos|ultimos movimientos|movimiento|actividad reciente|que carros se movieron)\b/iu', $consulta)) {
            $stmtMovs = $conexion->prepare("
                SELECT 
                    hm.*,
                    m.placa,
                    m.modelo,
                    m.categoria,
                    m.estado
                FROM historial_movimientos hm
                INNER JOIN maquinarias m ON hm.id_maquina = m.id_maquina
                ORDER BY hm.id_movimiento DESC
                LIMIT 25
            ");
            if ($stmtMovs) {
                $stmtMovs->execute();
                $resMovs = $stmtMovs->get_result();
                $distintos = [];
                $vistos = [];
                while ($mv = $resMovs->fetch_assoc()) {
                    $idM = $mv['id_maquina'];
                    if (!isset($vistos[$idM])) {
                        $vistos[$idM] = true;
                        $distintos[] = $mv;
                    }
                }
                $stmtMovs->close();

                if (!empty($distintos)) {
                    $html = "<div class='mmchat-vcard'>";
                    $html .= "  <div class='mmchat-vcard-top border-bottom pb-1 mb-1'>";
                    $html .= "    <strong class='text-dark'><i class='bi bi-arrow-left-right text-warning me-1'></i> Movimientos recientes</strong>";
                    $html .= "    <span class='badge bg-success ms-auto'>" . count($distintos) . " vehículos</span>";
                    $html .= "  </div>";
                    $html .= "  <div class='mmchat-vcard-list'>";
                    $cont = 0;
                    foreach ($distintos as $mv) {
                        if ($cont >= 3) break;
                        $fReg = !empty($mv['fecha_registro']) ? date('d/m H:i', strtotime($mv['fecha_registro'])) : '--:--';
                        $placa = $mv['placa'] ?? 'ID #' . $mv['id_maquina'];
                        $mod = !empty($mv['modelo']) ? " (" . htmlspecialchars(mb_substr($mv['modelo'], 0, 10)) . ")" : "";
                        $acc = htmlspecialchars(mb_substr($mv['accion'] ?? '', 0, 22));
                        $ubi = !empty($mv['ubicacion']) ? " &bull; <em>" . htmlspecialchars(mb_substr($mv['ubicacion'], 0, 12)) . "</em>" : "";
                        $html .= "<div class='mmchat-mini-row'><span class='badge bg-secondary me-1'>{$fReg}</span> <strong>{$placa}</strong>{$mod}: {$acc}{$ubi}</div>";
                        $cont++;
                    }
                    $html .= "  </div>";
                    if (count($distintos) > $cont) {
                        $html .= "<div class='text-muted small mt-1'>+" . (count($distintos) - $cont) . " vehículos distintos más. Consulta una placa para su ficha técnica.</div>";
                    }
                    $html .= "</div>";
                    $respuesta = $html;
                }
            }
        }

        // -------------------------------------------------------------
        // 4. BÚSQUEDA POR MODELO, CATEGORÍA, ESTADO O UBICACIÓN (CARROS DISTINTOS, MAX 2 TARJETAS)
        // -------------------------------------------------------------
        if (empty($respuesta)) {
            $limpio = trim(preg_replace('/^(buscar|consultar|mostrar|ver|donde esta|informacion de|info de|estado de|quien es|que es)\s+/iu', '', $consulta));
            if (mb_strlen($limpio) >= 2) {
                $likeParam = "%" . $limpio . "%";
                $stmtBusq = $conexion->prepare("
                    SELECT * FROM maquinarias 
                    WHERE modelo LIKE ? 
                       OR categoria LIKE ? 
                       OR ubicacion_actual LIKE ? 
                       OR placa LIKE ?
                       OR estado LIKE ?
                       OR caracteristicas LIKE ?
                    ORDER BY id_maquina DESC
                    LIMIT 15
                ");
                if ($stmtBusq) {
                    $stmtBusq->bind_param("ssssss", $likeParam, $likeParam, $likeParam, $likeParam, $likeParam, $likeParam);
                    $stmtBusq->execute();
                    $resBusq = $stmtBusq->get_result();

                    $coincidencias = [];
                    $carrosVistos = [];
                    while ($c = $resBusq->fetch_assoc()) {
                        $k = !empty($c['id_maquina']) ? $c['id_maquina'] : $c['placa'];
                        if (!isset($carrosVistos[$k])) {
                            $carrosVistos[$k] = true;
                            $coincidencias[] = $c;
                        }
                    }
                    $stmtBusq->close();

                    if (!empty($coincidencias)) {
                        $totalC = count($coincidencias);
                        $cards = [];
                        $limiteMostrar = min(2, $totalC);
                        for ($i = 0; $i < $limiteMostrar; $i++) {
                            $maq = $coincidencias[$i];
                            $movs = [];
                            $stmtM = $conexion->prepare("SELECT * FROM historial_movimientos WHERE id_maquina = ? ORDER BY id_movimiento DESC LIMIT 1");
                            if ($stmtM) {
                                $stmtM->bind_param("i", $maq['id_maquina']);
                                $stmtM->execute();
                                $resM = $stmtM->get_result();
                                while ($m = $resM->fetch_assoc()) {
                                    $movs[] = $m;
                                }
                                $stmtM->close();
                            }
                            $cards[] = renderChatbotVehiculoCard($maq, $movs);
                        }

                        $html = "<div class='d-flex flex-column gap-2'>";
                        $html .= "<div class='fw-bold text-dark border-bottom pb-1 small'><i class='bi bi-search text-warning me-1'></i> Encontrado" . ($totalC > 1 ? "s {$totalC} vehículos distintos" : " 1 vehículo") . " para <em>\"" . htmlspecialchars($limpio) . "\"</em>:</div>";
                        $html .= implode("", $cards);
                        if ($totalC > 2) {
                            $html .= "<div class='text-muted small'>+" . ($totalC - 2) . " vehículo(s) distinto(s) más. Escribe la placa exacta para ver detalles.</div>";
                        }
                        $html .= "</div>";
                        $respuesta = $html;
                    }
                }
            }
        }
    }

    echo json_encode(['found' => !empty($respuesta), 'html' => $respuesta]);
    exit;
}
?>

<style>
/* ==========================================================
   CHATBOT ASISTENTE CORPORATIVO - M&M CONSTRUCCIONES Y SERVICIOS
   Paleta: #0A192F (Navy Profundo) + #FFC107 (Amarillo Corporativo)
   Diseño Limpio, Moderno y Ultra-Compacto
   ========================================================== */

.mmchat-fab,
.mmchat-window,
.mmchat-fab *,
.mmchat-window * {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

/* BOTÓN FLOTANTE (FAB) CORPORATIVO */
.mmchat-fab {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 2000;
  display: flex;
  align-items: center;
}

.mmchat-btn {
  width: 62px;
  height: 62px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  background: linear-gradient(145deg, #0A192F 0%, #112a4d 50%, #0A192F 100%);
  box-shadow:
    0 4px 18px rgba(10, 25, 47, 0.45),
    0 0 0 3px rgba(255, 193, 7, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  transition:
    transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1),
    box-shadow 0.3s ease;
  position: relative;
  -webkit-tap-highlight-color: transparent;
  user-select: none;
}

.mmchat-btn:hover {
  transform: translateY(-4px) scale(1.06);
  box-shadow:
    0 10px 26px rgba(10, 25, 47, 0.5),
    0 0 0 4px rgba(255, 193, 7, 0.75),
    0 0 16px rgba(255, 193, 7, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.mmchat-btn:active {
  transform: scale(0.93);
}

.mmchat-logo-badge {
  width: 38px;
  height: 38px;
  background: #FFC107;
  border-radius: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.82rem;
  font-weight: 900;
  color: #0A192F;
  letter-spacing: -0.5px;
  box-shadow: 0 2px 7px rgba(0, 0, 0, 0.28);
  transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.mmchat-btn:hover .mmchat-logo-badge {
  transform: rotate(-6deg) scale(1.08);
}

.mmchat-online-dot {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 13px;
  height: 13px;
  background: #10b981;
  border: 2.5px solid #ffffff;
  border-radius: 50%;
  animation: mmchatPulse 2.2s ease-in-out infinite;
}

@keyframes mmchatPulse {
  0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
  50%  { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
  100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.mmchat-tooltip {
  position: absolute;
  right: 74px;
  top: 50%;
  transform: translateY(-50%) translateX(6px);
  background: #0A192F;
  color: #ffffff;
  padding: 6px 13px;
  border-radius: 18px;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
  border: 1px solid rgba(255, 193, 7, 0.4);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.mmchat-tooltip::after {
  content: '';
  position: absolute;
  right: -5px;
  top: 50%;
  transform: translateY(-50%);
  border: 5px solid transparent;
  border-left-color: #0A192F;
  border-right: none;
}

.mmchat-fab:hover .mmchat-tooltip {
  opacity: 1;
  transform: translateY(-50%) translateX(0);
}

/* VENTANA DEL CHATBOT CORPORATIVO */
.mmchat-window {
  position: fixed;
  bottom: 94px;
  right: 24px;
  width: 380px;
  max-width: calc(100vw - 28px);
  height: 525px;
  max-height: calc(100vh - 116px);
  background: #ffffff;
  border-radius: 18px;
  box-shadow:
    0 25px 60px rgba(10, 25, 47, 0.28),
    0 8px 20px rgba(10, 25, 47, 0.12),
    0 0 0 1px rgba(10, 25, 47, 0.08);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  overflow-x: hidden !important;
  z-index: 1999;
  opacity: 0;
  visibility: hidden;
  transform: translateY(20px) scale(0.95);
  transform-origin: bottom right;
  pointer-events: none;
  transition:
    opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1),
    transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
    visibility 0.35s;
}

.mmchat-window.mmchat-open {
  opacity: 1;
  visibility: visible;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

/* ENCABEZADO CORPORATIVO */
.mmchat-header {
  background: linear-gradient(135deg, #071224 0%, #0A192F 45%, #0f2341 100%);
  height: 62px;
  padding: 0 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 2.5px solid #FFC107;
  color: #ffffff;
  flex-shrink: 0;
  position: relative;
  z-index: 2;
}

.mmchat-header-left {
  display: flex;
  align-items: center;
  gap: 11px;
}

.mmchat-header-avatar {
  width: 40px;
  height: 40px;
  background: #FFC107;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 900;
  color: #0A192F;
  letter-spacing: -0.5px;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(255, 193, 7, 0.35);
  position: relative;
}

.mmchat-header-avatar-dot {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 10px;
  height: 10px;
  background: #10b981;
  border: 2px solid #0A192F;
  border-radius: 50%;
}

.mmchat-header-text {
  display: flex;
  flex-direction: column;
  line-height: 1.25;
}

.mmchat-header-name {
  font-size: 0.95rem;
  font-weight: 800;
  color: #ffffff;
  display: flex;
  align-items: center;
  gap: 6px;
}

.mmchat-badge-ia {
  background: rgba(255, 193, 7, 0.2);
  border: 1px solid rgba(255, 193, 7, 0.5);
  color: #FFC107;
  font-size: 0.62rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 10px;
  letter-spacing: 0.3px;
}

.mmchat-header-status {
  font-size: 0.72rem;
  color: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  gap: 5px;
}

.mmchat-status-dot {
  width: 6px;
  height: 6px;
  background: #10b981;
  border-radius: 50%;
  display: inline-block;
  animation: mmchatPulse 2.2s infinite;
}

.mmchat-header-right {
  display: flex;
  align-items: center;
  gap: 5px;
}

.mmchat-hbtn {
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.mmchat-hbtn:hover {
  background: rgba(255, 193, 7, 0.2);
  border-color: rgba(255, 193, 7, 0.5);
  color: #FFC107;
}

.mmchat-hbtn.close-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.5);
  color: #f87171;
}

/* ÁREA DE MENSAJES */
.mmchat-messages {
  flex: 1;
  padding: 14px 14px;
  overflow-y: auto;
  overflow-x: hidden !important;
  max-width: 100%;
  background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
  display: flex;
  flex-direction: column;
  gap: 11px;
  scroll-behavior: smooth;
}

.mmchat-messages::-webkit-scrollbar { width: 5px; }
.mmchat-messages::-webkit-scrollbar-track { background: transparent; }
.mmchat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

/* SEPARADOR DE FECHA */
.mmchat-date-sep {
  text-align: center;
  margin: 2px 0 4px;
}

.mmchat-date-sep span {
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  background: #e2e8f0;
  padding: 3px 12px;
  border-radius: 12px;
  letter-spacing: 0.3px;
}

/* MENSAJES Y BURBUJAS */
.mmchat-msg {
  display: flex;
  gap: 8px;
  max-width: 88%;
  min-width: 0;
  animation: mmMsgIn 0.28s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes mmMsgIn {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

.mmchat-msg.bot  { align-self: flex-start; }
.mmchat-msg.user { align-self: flex-end; flex-direction: row-reverse; }

.mmchat-msg-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 900;
  letter-spacing: -0.3px;
  flex-shrink: 0;
  align-self: flex-end;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.mmchat-msg.bot  .mmchat-msg-icon { background: #FFC107; color: #0A192F; }
.mmchat-msg.user .mmchat-msg-icon {
  background: linear-gradient(135deg, #0A192F, #172a45);
  color: #ffffff;
  font-size: 0.82rem;
}

.mmchat-msg-content {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
  max-width: 100%;
}

.mmchat-bubble {
  padding: 9px 13px;
  border-radius: 14px;
  font-size: 0.83rem;
  line-height: 1.45;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  position: relative;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  word-break: break-word !important;
  max-width: 100%;
}

.mmchat-msg.bot .mmchat-bubble {
  background: #ffffff;
  color: #1e293b;
  border: 1px solid rgba(226, 232, 240, 0.9);
  border-bottom-left-radius: 3px;
}

.mmchat-msg.user .mmchat-bubble {
  background: linear-gradient(135deg, #0A192F 0%, #172a45 100%);
  color: #ffffff;
  border-bottom-right-radius: 3px;
}

.mmchat-msg-time {
  font-size: 0.65rem;
  color: #94a3b8;
  padding: 0 4px;
}

.mmchat-msg.user .mmchat-msg-time {
  text-align: right;
  color: #64748b;
}

/* TARJETAS ULTRA-COMPACTAS DE VEHÍCULOS / RESULTADOS */
.mmchat-vcard {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 10px;
  font-size: 11.5px;
  line-height: 1.35;
  color: #1e293b;
  max-width: 100%;
}

.mmchat-vcard-top {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 4px;
}

.mmchat-vcard-title {
  font-weight: 700;
  color: #0A192F;
  font-size: 12px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mmchat-vcard-row {
  display: flex;
  justify-content: space-between;
  gap: 6px;
  color: #475569;
  margin-bottom: 3px;
  font-size: 11px;
}

.mmchat-vcard-desc {
  color: #64748b;
  font-size: 10.5px;
  margin-bottom: 3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mmchat-vcard-dates {
  font-size: 10px;
  margin-bottom: 4px;
}

.mmchat-vcard-mov {
  background: #ffffff;
  border-left: 3px solid #FFC107;
  padding: 3px 6px;
  border-radius: 4px;
  font-size: 10px;
  color: #334155;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}

.mmchat-vcard-list {
  display: flex;
  flex-direction: column;
  gap: 3px;
  margin: 4px 0;
}

.mmchat-mini-row {
  font-size: 11px;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* CHIPS DE ACCESO RÁPIDO */
.mmchat-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-top: 4px;
  align-self: flex-start;
}

.mmchat-chip {
  background: #ffffff;
  border: 1.5px solid rgba(255, 193, 7, 0.4);
  color: #0A192F;
  font-size: 11px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  -webkit-tap-highlight-color: transparent;
}

.mmchat-chip:hover {
  background: #FFC107;
  border-color: #FFC107;
  color: #0A192F;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
}

/* INDICADOR DE ESCRITURA */
.mmchat-typing {
  display: none;
  align-self: flex-start;
  background: #ffffff;
  border: 1px solid rgba(226, 232, 240, 0.9);
  padding: 9px 13px;
  border-radius: 14px;
  border-bottom-left-radius: 3px;
  gap: 4px;
  align-items: center;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
}

.mmchat-typing-dot {
  width: 6px;
  height: 6px;
  background: #FFC107;
  border-radius: 50%;
  animation: mmTyping 1.4s infinite ease-in-out both;
}

.mmchat-typing-dot:nth-child(1) { animation-delay: 0s; }
.mmchat-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.mmchat-typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes mmTyping {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
  30%           { transform: translateY(-5px); opacity: 1; }
}

/* PIE DE PÁGINA (INPUT) */
.mmchat-footer {
  padding: 9px 12px 11px;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
  flex-shrink: 0;
}

.mmchat-footer-hint {
  font-size: 0.67rem;
  color: #94a3b8;
  text-align: center;
  margin-bottom: 6px;
  letter-spacing: 0.2px;
}

.mmchat-input-row {
  display: flex;
  align-items: center;
  gap: 7px;
  background: #f1f5f9;
  border-radius: 28px;
  padding: 4px 4px 4px 14px;
  border: 1.5px solid #e2e8f0;
  transition: all 0.2s ease;
}

.mmchat-input-row:focus-within {
  border-color: #FFC107;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.15);
}

.mmchat-input-row input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  font-size: 13px;
  color: #1e293b;
}

.mmchat-input-row input::placeholder {
  color: #94a3b8;
  font-size: 12.5px;
}

.mmchat-send {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #FFC107, #ffcd39);
  border: none;
  color: #0A192F;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.95rem;
  flex-shrink: 0;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);
}

.mmchat-send:hover {
  transform: scale(1.08);
  box-shadow: 0 4px 12px rgba(255, 193, 7, 0.55);
}

.mmchat-send:active {
  transform: scale(0.93);
}

/* RESPONSIVE */
@media (max-width: 480px) {
  .mmchat-fab { bottom: 16px; right: 16px; }
  .mmchat-window {
    bottom: 84px;
    right: 8px;
    width: calc(100vw - 16px);
    height: calc(100vh - 100px);
    border-radius: 14px;
  }
  .mmchat-tooltip { display: none !important; }
}
</style>

<!-- ========== BOTÓN FAB CHATBOT CORPORATIVO ========== -->
<div class="mmchat-fab" id="mmchatFab">
  <button type="button" class="mmchat-btn" id="mmchatFabBtn"
          aria-label="Abrir asistente de flota M&M" title="Hablar con el asistente M&M">
    <div class="mmchat-logo-badge">M&M</div>
    <span class="mmchat-online-dot"></span>
  </button>
  <div class="mmchat-tooltip">¿En qué puedo ayudarte? · M&M</div>
</div>

<!-- ========== VENTANA DEL CHATBOT CORPORATIVO ========== -->
<div class="mmchat-window" id="mmchatWindow" role="dialog" aria-modal="true" aria-label="Chatbot de flota M&M Construcciones">

  <!-- ENCABEZADO -->
  <div class="mmchat-header">
    <div class="mmchat-header-left">
      <div class="mmchat-header-avatar">
        M&M
        <span class="mmchat-header-avatar-dot"></span>
      </div>
      <div class="mmchat-header-text">
        <div class="mmchat-header-name">
          M&M Asistente
          <span class="mmchat-badge-ia">IA</span>
        </div>
        <div class="mmchat-header-status">
          <span class="mmchat-status-dot"></span>
          En línea · Construcciones M&M
        </div>
      </div>
    </div>
    <div class="mmchat-header-right">
      <button type="button" class="mmchat-hbtn" id="mmchatMinBtn" title="Minimizar">
        <i class="bi bi-dash-lg"></i>
      </button>
      <button type="button" class="mmchat-hbtn close-btn" id="mmchatCloseBtn" title="Cerrar">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  </div>

  <!-- CANVA DE MENSAJES -->
  <div class="mmchat-messages" id="mmchatMessages">

    <div class="mmchat-date-sep">
      <span>Hoy · <?php echo date('d M Y'); ?></span>
    </div>

    <!-- Mensaje inicial del Bot -->
    <div class="mmchat-msg bot" id="mmchatWelcomeMsg">
      <div class="mmchat-msg-icon">M&M</div>
      <div class="mmchat-msg-content">
        <div class="mmchat-bubble">
          👋 ¡Hola! Bienvenido al consultor de flota de <strong>Construcciones M&M</strong>.<br><br>
          Puedes consultarme en tiempo real por <strong>placa</strong> (ej: <code>ABC123</code>), <strong>fecha</strong> (ej: <code><?php echo date('d/m/Y'); ?></code>) o <strong>modelo</strong> (ej: <code>WIWI</code>, <code>RENAULT</code>).
        </div>
        <span class="mmchat-msg-time"><?php echo date('h:i A'); ?></span>
      </div>
    </div>

    <!-- Chips de sugerencias compactos -->
    <div class="mmchat-chips" id="mmchatChips">
      <button class="mmchat-chip" data-query="<?php echo date('d/m/Y'); ?>">📅 Movimientos de hoy</button>
      <button class="mmchat-chip" data-query="ultimos movimientos">🚜 Últimos movimientos</button>
      <button class="mmchat-chip" data-query="¿Cómo busco por placa?">🔍 Buscar placa</button>
      <button class="mmchat-chip" data-query="¿Cómo leo las métricas del Dashboard?">📊 Dashboard</button>
      <button class="mmchat-chip" data-query="¿Cuál es el contacto del soporte técnico?">📞 Soporte</button>
    </div>

    <!-- Indicador de escritura animado -->
    <div class="mmchat-typing" id="mmchatTyping">
      <div class="mmchat-typing-dot"></div>
      <div class="mmchat-typing-dot"></div>
      <div class="mmchat-typing-dot"></div>
    </div>

  </div>

  <!-- PIE DE PÁGINA (INPUT) -->
  <div class="mmchat-footer">
    <p class="mmchat-footer-hint">Consulta por placa (ABC123), fecha (DD/MM/YYYY) o modelo</p>
    <form id="mmchatForm" autocomplete="off">
      <div class="mmchat-input-row">
        <input type="text" id="mmchatInput" placeholder="Escribe una placa, fecha o modelo…" maxlength="250">
        <button type="submit" class="mmchat-send" aria-label="Enviar mensaje" title="Enviar">
          <i class="bi bi-send-fill"></i>
        </button>
      </div>
    </form>
  </div>

</div>

<!-- ========== SCRIPT CORPORATIVO ========== -->
<script>
(function () {
  'use strict';

  const fab        = document.getElementById('mmchatFabBtn');
  const chatWin    = document.getElementById('mmchatWindow');
  const closeBtn   = document.getElementById('mmchatCloseBtn');
  const minBtn     = document.getElementById('mmchatMinBtn');
  const form       = document.getElementById('mmchatForm');
  const input      = document.getElementById('mmchatInput');
  const messages   = document.getElementById('mmchatMessages');
  const typingEl   = document.getElementById('mmchatTyping');
  const chips      = document.getElementById('mmchatChips');

  let isOpen = false;
  let typingTimer = null;

  /* ---- Apertura y Cierre ---- */
  function openChat() {
    isOpen = true;
    chatWin.classList.add('mmchat-open');
    setTimeout(() => input && input.focus(), 300);
  }

  function closeChat() {
    isOpen = false;
    chatWin.classList.remove('mmchat-open');
  }

  if (fab) fab.addEventListener('click', () => isOpen ? closeChat() : openChat());
  if (closeBtn) closeBtn.addEventListener('click', closeChat);
  if (minBtn)   minBtn.addEventListener('click', closeChat);

  /* ---- Formato de Hora Actual ---- */
  function nowTime() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  /* ---- Escape HTML ---- */
  function esc(t) {
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
  }

  /* ---- Agregar Mensaje al Canvas ---- */
  function addMsg(role, html, delay = 0) {
    setTimeout(() => {
      const wrap = document.createElement('div');
      wrap.className = `mmchat-msg ${role}`;

      const icon = document.createElement('div');
      icon.className = 'mmchat-msg-icon';
      icon.innerHTML = role === 'bot'
        ? 'M&M'
        : '<i class="bi bi-person-fill"></i>';

      const content = document.createElement('div');
      content.className = 'mmchat-msg-content';

      const bubble = document.createElement('div');
      bubble.className = 'mmchat-bubble';
      bubble.innerHTML = html;

      const time = document.createElement('span');
      time.className = 'mmchat-msg-time';
      time.textContent = nowTime();

      content.appendChild(bubble);
      content.appendChild(time);
      wrap.appendChild(icon);
      wrap.appendChild(content);

      messages.insertBefore(wrap, typingEl);
      messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });
    }, delay);
  }

  /* ---- Respuestas Base de Conocimiento ---- */
  const responses = [
    {
      keys: ['placa', 'buscar placa', 'consultar placa'],
      reply: 'Para consultar información de un vehículo o máquina, <strong>escribe los 6 caracteres de su placa</strong> (ej: <code>ABC123</code>). Te mostraré su categoría, modelo, ubicación, estado y último movimiento.'
    },
    {
      keys: ['maquinaria', 'vehiculo', 'transporte', 'equipo', 'inventario', 'flota'],
      reply: 'En el módulo de <strong>Maquinarias</strong> puedes registrar equipos con placas de 6 caracteres y gestionar en tiempo real sus estados y ubicaciones.'
    },
    {
      keys: ['dashboard', 'kpi', 'metrica', 'estadistica', 'grafico', 'reporte'],
      reply: 'El <strong>Dashboard</strong> muestra KPIs en tiempo real: máquinas disponibles, en obra y mantenimiento, además de la actividad reciente.'
    },
    {
      keys: ['web', 'contenido', 'editar', 'pagina', 'sitio'],
      reply: 'En <strong>Contenido de Web</strong> puedes editar los textos corporativos, servicios y testimonios del sitio público.'
    },
    {
      keys: ['soporte', 'contacto', 'telefono', 'correo', 'email', 'ayuda'],
      reply: '📞 <strong>Canales de Soporte:</strong><br>· Email: admin@construccionesmym.com<br>· PBX: (601) 345-6789<br>Lun–Vie 7:00 am – 6:00 pm.'
    },
    {
      keys: ['hola', 'buenos', 'buenas', 'saludos', 'hey'],
      reply: '¡Hola! 👋 Es un gusto atenderte. Puedes escribir una <strong>placa</strong> (ej: <code>ABC123</code>), una <strong>fecha</strong> o <code>movimientos</code> para consultar la base de datos.'
    },
    {
      keys: ['gracias', 'perfecto', 'listo', 'excelente', 'genial'],
      reply: '¡Con gusto! 😊 Si necesitas consultar otra placa o dato de la flota, aquí estaré.'
    }
  ];

  function botReply(userText) {
    const low = userText.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    for (const r of responses) {
      if (r.keys.some(k => low.includes(k))) return r.reply;
    }
    return `Recibí tu consulta sobre <em>"${esc(userText)}"</em>. Puedes consultar directamente por <strong>placa</strong> (ej: <code>ABC123</code>), <strong>fecha</strong> (ej: <code><?php echo date('d/m/Y'); ?></code>) o <strong>modelo</strong> (ej: <code>WIWI</code>).`;
  }

  /* ---- Envío con Integración AJAX en Tiempo Real ---- */
  async function send(text) {
    if (!text || !text.trim()) return;
    const cleanText = text.trim();
    addMsg('user', esc(cleanText));

    /* Ocultar chips tras primera interacción */
    if (chips) {
      chips.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
      chips.style.opacity = '0';
      chips.style.transform = 'translateY(-6px)';
      setTimeout(() => chips.remove(), 200);
    }

    /* Mostrar animación de escritura */
    typingEl.style.display = 'flex';
    messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });

    clearTimeout(typingTimer);

    // Búsqueda en base de datos vía AJAX
    try {
      const response = await fetch(`chatbot.php?ajax_chat_query=1&q=${encodeURIComponent(cleanText)}`);
      if (response.ok) {
        const data = await response.json();
        if (data && data.found && data.html) {
          typingTimer = setTimeout(() => {
            typingEl.style.display = 'none';
            addMsg('bot', data.html);
          }, 450);
          return;
        }
      }
    } catch (e) {
      // Fallback a bot estándar
    }

    typingTimer = setTimeout(() => {
      typingEl.style.display = 'none';
      addMsg('bot', botReply(cleanText));
    }, 550);
  }

  if (form) {
    form.addEventListener('submit', e => {
      e.preventDefault();
      const val = input.value;
      input.value = '';
      send(val);
    });
  }

  if (chips) {
    chips.addEventListener('click', e => {
      const btn = e.target.closest('.mmchat-chip');
      if (btn) send(btn.getAttribute('data-query'));
    });
  }

  /* ---- Cerrar con Esc ---- */
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && isOpen) closeChat();
  });

})();
</script>
