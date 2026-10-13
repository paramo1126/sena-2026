<?php
/**
 * Chatbot Asistente Virtual - M&M Construcciones y Servicios S.A.S.
 * Widget Flotante — Esquina Inferior Derecha
 * Paleta: #0A192F (Navy) + #FFC107 (Amarillo Corporativo)
 */

// Endpoint AJAX para responder consultas de placa y base de datos
if (isset($_GET['ajax_chat_query'])) {
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/../config/conexion.php';

    $consulta = trim($_GET['q'] ?? '');
    $respuesta = '';

    // Extraer placa si viene en el texto (ej: ABC123 o 'placa ABC123')
    $placaBuscada = '';
    if (preg_match('/[a-zA-Z0-9]{6}/', $consulta, $matches)) {
        $posiblePlaca = strtoupper($matches[0]);
        // Verificar si existe en la BD
        if (isset($conexion) && !$conexion->connect_error) {
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
    }

    if (!empty($placaBuscada)) {
        if (isset($conexion) && !$conexion->connect_error) {
            $stmt = $conexion->prepare("SELECT * FROM maquinarias WHERE placa = ? LIMIT 1");
            $stmt->bind_param("s", $placaBuscada);
            $stmt->execute();
            $maq = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($maq) {
                // Consultar últimos movimientos de esta máquina
                $movs = [];
                $stmtM = $conexion->prepare("SELECT * FROM historial_movimientos WHERE id_maquina = ? ORDER BY id_movimiento DESC LIMIT 3");
                if ($stmtM) {
                    $stmtM->bind_param("i", $maq['id_maquina']);
                    $stmtM->execute();
                    $resM = $stmtM->get_result();
                    while ($m = $resM->fetch_assoc()) {
                        $movs[] = $m;
                    }
                    $stmtM->close();
                }

                $badgeEstado = 'badge bg-success';
                $estLower = strtolower($maq['estado']);
                if (str_contains($estLower, 'trab')) $badgeEstado = 'badge bg-primary';
                elseif (str_contains($estLower, 'mant')) $badgeEstado = 'badge bg-warning text-dark';

                $entStr = (!empty($maq['fecha_entrada']) ? date('d/m/Y', strtotime($maq['fecha_entrada'])) : 'No reg.') . (!empty($maq['hora_entrada']) ? ' ' . substr($maq['hora_entrada'], 0, 5) : '');
                $salStr = (!empty($maq['fecha_salida']) ? date('d/m/Y', strtotime($maq['fecha_salida'])) : 'No reg.') . (!empty($maq['hora_salida']) ? ' ' . substr($maq['hora_salida'], 0, 5) : '');

                $html = "<div class='p-1'>";
                $html .= "<div class='fw-bold text-dark border-bottom pb-1 mb-2'><i class='bi bi-search text-warning me-1'></i> Información de <strong>{$maq['placa']}</strong></div>";
                $html .= "<div><strong>Categoría:</strong> {$maq['categoria']}</div>";
                $html .= "<div><strong>Modelo:</strong> {$maq['modelo']}</div>";
                $html .= "<div><strong>Estado:</strong> <span class='{$badgeEstado}'>{$maq['estado']}</span></div>";
                $html .= "<div><strong>Ubicación actual:</strong> " . htmlspecialchars(!empty($maq['ubicacion_actual']) ? $maq['ubicacion_actual'] : 'Sin ubicación') . "</div>";
                $html .= "<div><strong>Características:</strong> " . htmlspecialchars(!empty($maq['caracteristicas']) ? $maq['caracteristicas'] : 'Sin detalles') . "</div>";
                $html .= "<div class='mt-1 small text-muted'><strong>Entrada:</strong> {$entStr} | <strong>Salida:</strong> {$salStr}</div>";

                if (!empty($movs)) {
                    $html .= "<div class='mt-2 pt-2 border-top small'><strong>Últimos movimientos:</strong><ul class='mb-0 ps-3 mt-1'>";
                    foreach ($movs as $mv) {
                        $fReg = !empty($mv['fecha_registro']) ? date('d/m H:i', strtotime($mv['fecha_registro'])) : '';
                        $html .= "<li>[{$fReg}] {$mv['accion']} - <em>" . htmlspecialchars($mv['ubicacion'] ?? 'Sin ubic.') . "</em></li>";
                    }
                    $html .= "</ul></div>";
                }
                $html .= "</div>";

                $respuesta = $html;
            }
        }
    }

    echo json_encode(['found' => !empty($respuesta), 'html' => $respuesta]);
    exit;
}
?>

<style>
/* ==========================================================
   CHATBOT VARIABLES Y RESET
   ========================================================== */
.mmchat-btn,
.mmchat-window,
.mmchat-btn * ,
.mmchat-window * {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

/* ==========================================================
   BOTÓN FAB — DERECHA INFERIOR
   ========================================================== */
.mmchat-fab {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 2000;
}

.mmchat-btn {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  background: linear-gradient(145deg, #0A192F 0%, #0f2341 50%, #0A192F 100%);
  box-shadow:
    0 4px 15px rgba(10, 25, 47, 0.5),
    0 0 0 3px rgba(255, 193, 7, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  transition:
    transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
    box-shadow 0.3s ease;
  position: relative;
  -webkit-tap-highlight-color: transparent;
  user-select: none;
}

.mmchat-btn:hover {
  transform: translateY(-5px) scale(1.06);
  box-shadow:
    0 12px 30px rgba(10, 25, 47, 0.5),
    0 0 0 4px rgba(255, 193, 7, 0.7),
    0 0 20px rgba(255, 193, 7, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.mmchat-btn:active {
  transform: scale(0.93);
  transition: transform 0.1s ease;
}

/* Logo M&M dentro del botón */
.mmchat-logo-badge {
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
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
  transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.mmchat-btn:hover .mmchat-logo-badge {
  transform: rotate(-8deg) scale(1.08);
}

/* Indicador online pulsante */
.mmchat-online-dot {
  position: absolute;
  top: 3px;
  right: 3px;
  width: 14px;
  height: 14px;
  background: #10b981;
  border: 2.5px solid #ffffff;
  border-radius: 50%;
  animation: mmchatPulse 2.2s ease-in-out infinite;
}

@keyframes mmchatPulse {
  0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
  50%  { box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
  100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* Tooltip lateral izquierdo del botón */
.mmchat-tooltip {
  position: absolute;
  right: 76px;
  top: 50%;
  transform: translateY(-50%) translateX(8px);
  background: #0A192F;
  color: #ffffff;
  padding: 7px 14px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 600;
  white-space: nowrap;
  border: 1px solid rgba(255, 193, 7, 0.4);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.25s ease, transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.mmchat-tooltip::after {
  content: '';
  position: absolute;
  right: -6px;
  top: 50%;
  transform: translateY(-50%);
  border: 5px solid transparent;
  border-left-color: #0A192F;
  border-right: none;
}

.mmchat-btn:hover + .mmchat-tooltip,
.mmchat-fab:hover .mmchat-tooltip {
  opacity: 1;
  transform: translateY(-50%) translateX(0);
}

/* ==========================================================
   VENTANA DEL CHATBOT — APARECE DESDE ABAJO DERECHA
   ========================================================== */
.mmchat-window {
  position: fixed;
  bottom: 104px;
  right: 24px;
  width: 390px;
  max-width: calc(100vw - 36px);
  height: 560px;
  max-height: calc(100vh - 130px);
  background: #ffffff;
  border-radius: 22px;
  box-shadow:
    0 25px 60px rgba(10, 25, 47, 0.3),
    0 8px 20px rgba(10, 25, 47, 0.15),
    0 0 0 1px rgba(10, 25, 47, 0.07);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 1999;
  /* Estado cerrado */
  opacity: 0;
  visibility: hidden;
  transform: translateY(30px) scale(0.9);
  transform-origin: bottom right;
  pointer-events: none;
  transition:
    opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1),
    transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1),
    visibility 0.4s;
}

.mmchat-window.mmchat-open {
  opacity: 1;
  visibility: visible;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

/* ----- HEADER ----- */
.mmchat-header {
  background: linear-gradient(135deg, #071224 0%, #0A192F 45%, #0f2341 100%);
  padding: 0 18px;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 2px solid rgba(255, 193, 7, 0.6);
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
}

/* Decoración de fondo del header */
.mmchat-header::before {
  content: '';
  position: absolute;
  top: -30px;
  right: -20px;
  width: 120px;
  height: 120px;
  background: radial-gradient(circle, rgba(255, 193, 7, 0.12) 0%, transparent 70%);
  pointer-events: none;
}

.mmchat-header::after {
  content: '';
  position: absolute;
  bottom: -20px;
  left: -10px;
  width: 80px;
  height: 80px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.04) 0%, transparent 70%);
  pointer-events: none;
}

.mmchat-header-left {
  display: flex;
  align-items: center;
  gap: 13px;
  z-index: 1;
}

.mmchat-header-avatar {
  width: 46px;
  height: 46px;
  background: #FFC107;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 900;
  color: #0A192F;
  letter-spacing: -0.5px;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
  position: relative;
  animation: mmchatFloat 3.5s ease-in-out infinite;
}

@keyframes mmchatFloat {
  0%, 100% { transform: translateY(0); }
  50%       { transform: translateY(-3px); }
}

.mmchat-header-avatar-dot {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 12px;
  height: 12px;
  background: #10b981;
  border: 2px solid #0A192F;
  border-radius: 50%;
}

.mmchat-header-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.mmchat-header-name {
  font-size: 0.95rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: 0.3px;
  line-height: 1.2;
}

.mmchat-header-name span {
  color: #FFC107;
}

.mmchat-header-status {
  font-size: 0.72rem;
  color: rgba(255, 255, 255, 0.65);
  display: flex;
  align-items: center;
  gap: 5px;
}

.mmchat-status-green {
  width: 7px;
  height: 7px;
  background: #10b981;
  border-radius: 50%;
  flex-shrink: 0;
  animation: mmchatPulse 2.2s ease-in-out infinite;
}

.mmchat-header-right {
  display: flex;
  align-items: center;
  gap: 6px;
  z-index: 1;
}

.mmchat-header-btn {
  width: 34px;
  height: 34px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 9px;
  color: rgba(255, 255, 255, 0.75);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  -webkit-tap-highlight-color: transparent;
}

.mmchat-header-btn:hover {
  background: rgba(255, 193, 7, 0.2);
  border-color: rgba(255, 193, 7, 0.5);
  color: #FFC107;
  transform: scale(1.05);
}

.mmchat-header-btn.close-btn:hover {
  background: rgba(220, 38, 38, 0.2);
  border-color: rgba(220, 38, 38, 0.5);
  color: #f87171;
}

/* ----- MENSAJES ----- */
.mmchat-messages {
  flex: 1;
  padding: 18px 16px;
  overflow-y: auto;
  background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
  display: flex;
  flex-direction: column;
  gap: 14px;
  scroll-behavior: smooth;
}

.mmchat-messages::-webkit-scrollbar { width: 5px; }
.mmchat-messages::-webkit-scrollbar-track { background: transparent; }
.mmchat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.mmchat-messages::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* Fecha separator */
.mmchat-date-sep {
  text-align: center;
  margin: 4px 0;
}
.mmchat-date-sep span {
  font-size: 0.7rem;
  font-weight: 600;
  color: #94a3b8;
  background: #e2e8f0;
  padding: 3px 12px;
  border-radius: 20px;
  letter-spacing: 0.4px;
}

/* Mensajes del Bot */
.mmchat-msg {
  display: flex;
  gap: 10px;
  max-width: 88%;
  animation: mmchatMsgIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes mmchatMsgIn {
  from { opacity: 0; transform: translateY(12px) scale(0.95); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

.mmchat-msg.bot  { align-self: flex-start; }
.mmchat-msg.user { align-self: flex-end; flex-direction: row-reverse; }

.mmchat-msg-icon {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 900;
  letter-spacing: -0.4px;
  flex-shrink: 0;
  align-self: flex-end;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.mmchat-msg.bot  .mmchat-msg-icon { background: #FFC107; color: #0A192F; }
.mmchat-msg.user .mmchat-msg-icon {
  background: linear-gradient(135deg, #0A192F, #172a45);
  color: #ffffff;
  font-size: 0.88rem;
}

.mmchat-msg-content { display: flex; flex-direction: column; gap: 4px; }

.mmchat-bubble {
  padding: 12px 16px;
  border-radius: 16px;
  font-size: 0.875rem;
  line-height: 1.55;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  position: relative;
}

.mmchat-msg.bot .mmchat-bubble {
  background: #ffffff;
  color: #1e293b;
  border: 1px solid rgba(226, 232, 240, 0.8);
  border-bottom-left-radius: 5px;
}

.mmchat-msg.user .mmchat-bubble {
  background: linear-gradient(135deg, #0A192F 0%, #172a45 100%);
  color: #ffffff;
  border-bottom-right-radius: 5px;
}

.mmchat-msg-time {
  font-size: 0.67rem;
  color: #94a3b8;
  padding: 0 4px;
}

.mmchat-msg.user .mmchat-msg-time { text-align: right; color: rgba(255,255,255,0.5); }

/* Opciones rápidas */
.mmchat-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
  margin-top: 6px;
}

.mmchat-chip {
  background: #ffffff;
  border: 1.5px solid rgba(255, 193, 7, 0.4);
  color: #0A192F;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 6px 13px;
  border-radius: 20px;
  cursor: pointer;
  transition:
    background 0.25s ease,
    border-color 0.25s ease,
    transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
    box-shadow 0.25s ease;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  -webkit-tap-highlight-color: transparent;
}

.mmchat-chip:hover {
  background: #FFC107;
  border-color: #FFC107;
  color: #0A192F;
  transform: translateY(-2px) scale(1.03);
  box-shadow: 0 6px 14px rgba(255, 193, 7, 0.35);
}

/* Typing */
.mmchat-typing {
  display: none;
  align-self: flex-start;
  background: #ffffff;
  border: 1px solid rgba(226, 232, 240, 0.8);
  padding: 12px 16px;
  border-radius: 16px;
  border-bottom-left-radius: 5px;
  gap: 5px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  animation: mmchatMsgIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

.mmchat-typing-dot {
  width: 7px;
  height: 7px;
  background: #94a3b8;
  border-radius: 50%;
  animation: mmchatTyping 1.4s ease-in-out infinite both;
}
.mmchat-typing-dot:nth-child(1) { animation-delay: 0s; }
.mmchat-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.mmchat-typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes mmchatTyping {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
  30%           { transform: translateY(-6px); opacity: 1; }
}

/* ----- FOOTER — INPUT ----- */
.mmchat-footer {
  padding: 12px 14px 14px;
  background: #ffffff;
  border-top: 1px solid #e8edf5;
  flex-shrink: 0;
}

/* Indicadores de quick actions sobre el input */
.mmchat-footer-hint {
  font-size: 0.68rem;
  color: #94a3b8;
  text-align: center;
  margin-bottom: 8px;
  letter-spacing: 0.3px;
}

.mmchat-input-row {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f1f5f9;
  border-radius: 28px;
  padding: 5px 5px 5px 16px;
  border: 1.5px solid #e2e8f0;
  transition:
    border-color 0.25s ease,
    background 0.25s ease,
    box-shadow 0.3s ease;
}

.mmchat-input-row:focus-within {
  border-color: #FFC107;
  background: #ffffff;
  box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.15);
}

.mmchat-input-row input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  font-size: 0.875rem;
  color: #1e293b;
  font-family: inherit;
}

.mmchat-input-row input::placeholder {
  color: #94a3b8;
}

.mmchat-send {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #FFC107, #ffcd39);
  border: none;
  color: #0A192F;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1rem;
  flex-shrink: 0;
  transition:
    transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
    box-shadow 0.25s ease;
  box-shadow: 0 3px 8px rgba(255, 193, 7, 0.4);
}

.mmchat-send:hover {
  transform: scale(1.1) rotate(-10deg);
  box-shadow: 0 6px 16px rgba(255, 193, 7, 0.55);
}

.mmchat-send:active {
  transform: scale(0.93);
  transition: transform 0.1s;
}

/* ==========================================================
   RESPONSIVE
   ========================================================== */
@media (max-width: 480px) {
  .mmchat-fab { bottom: 18px; right: 18px; }
  .mmchat-window {
    bottom: 94px;
    right: 12px;
    width: calc(100vw - 24px);
    height: calc(100vh - 120px);
  }
  .mmchat-tooltip { display: none !important; }
}
</style>

<!-- ========== BOTÓN FAB DERECHO ========== -->
<div class="mmchat-fab" id="mmchatFab">
  <button type="button" class="mmchat-btn" id="mmchatFabBtn"
          aria-label="Abrir chat de soporte M&M" title="Hablar con el asistente M&M">
    <div class="mmchat-logo-badge">M&M</div>
    <span class="mmchat-online-dot"></span>
  </button>
  <div class="mmchat-tooltip">¿En qué puedo ayudarte?</div>
</div>

<!-- ========== VENTANA DEL CHAT ========== -->
<div class="mmchat-window" id="mmchatWindow" role="dialog" aria-modal="true" aria-label="Chat de soporte M&M Construcciones">

  <!-- HEADER -->
  <div class="mmchat-header">
    <div class="mmchat-header-left">
      <div class="mmchat-header-avatar">
        M&M
        <span class="mmchat-header-avatar-dot"></span>
      </div>
      <div class="mmchat-header-text">
        <div class="mmchat-header-name">
          <span>M&M</span> Asistente
          <span style="display:inline-block;background:rgba(255,193,7,0.2);border:1px solid rgba(255,193,7,0.5);color:#FFC107;font-size:0.62rem;font-weight:700;padding:1px 7px;border-radius:10px;margin-left:5px;vertical-align:middle;letter-spacing:0.3px;">IA</span>
        </div>
        <div class="mmchat-header-status">
          <span class="mmchat-status-green"></span>
          En línea · Construcciones M&M
        </div>
      </div>
    </div>
    <div class="mmchat-header-right">
      <button type="button" class="mmchat-header-btn" id="mmchatMinBtn" title="Minimizar">
        <i class="bi bi-dash"></i>
      </button>
      <button type="button" class="mmchat-header-btn close-btn" id="mmchatCloseBtn" title="Cerrar">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  </div>

  <!-- MENSAJES -->
  <div class="mmchat-messages" id="mmchatMessages">

    <div class="mmchat-date-sep">
      <span>Hoy · <?php echo date('d M Y'); ?></span>
    </div>

    <!-- Mensaje inicial de bienvenida -->
    <div class="mmchat-msg bot" id="mmchatWelcomeMsg">
      <div class="mmchat-msg-icon">M&M</div>
      <div class="mmchat-msg-content">
        <div class="mmchat-bubble">
          👋 ¡Bienvenido a <strong>Construcciones M&M</strong>!<br><br>
          Soy tu asistente virtual. ¿En qué puedo ayudarte hoy?
        </div>
        <span class="mmchat-msg-time"><?php echo date('h:i A'); ?></span>
      </div>
    </div>

    <!-- Chips de acceso rápido -->
    <div class="mmchat-chips" id="mmchatChips">
      <button class="mmchat-chip" data-query="¿Cómo gestiono maquinarias y vehículos?">🚜 Maquinarias y Vehículos</button>
      <button class="mmchat-chip" data-query="¿Cómo busco por placa un transporte?">🔍 Consultar Placa</button>
      <button class="mmchat-chip" data-query="¿Cómo leo las métricas del Dashboard?">📊 Dashboard</button>
      <button class="mmchat-chip" data-query="¿Cuál es el contacto del soporte técnico?">📞 Soporte</button>
    </div>

    <!-- Indicador de escritura -->
    <div class="mmchat-typing" id="mmchatTyping" style="display:none;">
      <div class="mmchat-typing-dot"></div>
      <div class="mmchat-typing-dot"></div>
      <div class="mmchat-typing-dot"></div>
    </div>

  </div>

  <!-- FOOTER — INPUT -->
  <div class="mmchat-footer">
    <p class="mmchat-footer-hint">Escribe una placa (ej: ABC123) o tu consulta</p>
    <form id="mmchatForm" autocomplete="off">
      <div class="mmchat-input-row">
        <input type="text" id="mmchatInput" placeholder="Escribe una placa o mensaje…" maxlength="300">
        <button type="submit" class="mmchat-send" aria-label="Enviar">
          <i class="bi bi-send-fill"></i>
        </button>
      </div>
    </form>
  </div>

</div>

<!-- ========== SCRIPT ========== -->
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

  /* ---- Toggle ---- */
  function openChat() {
    isOpen = true;
    chatWin.classList.add('mmchat-open');
    setTimeout(() => input && input.focus(), 350);
  }

  function closeChat() {
    isOpen = false;
    chatWin.classList.remove('mmchat-open');
  }

  if (fab) fab.addEventListener('click', () => isOpen ? closeChat() : openChat());
  if (closeBtn) closeBtn.addEventListener('click', closeChat);
  if (minBtn)   minBtn.addEventListener('click', closeChat);

  /* ---- Hora actual ---- */
  function nowTime() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  /* ---- Escape HTML ---- */
  function esc(t) {
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
  }

  /* ---- Agregar mensaje ---- */
  function addMsg(role, html, delay = 0) {
    setTimeout(() => {
      const wrap = document.createElement('div');
      wrap.className = `mmchat-msg ${role}`;

      const icon = document.createElement('div');
      icon.className = 'mmchat-msg-icon';
      icon.innerHTML = role === 'bot'
        ? 'M&M'
        : '<i class="bi bi-person-fill" style="font-size:0.9rem"></i>';

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

  /* ---- Respuestas del bot ---- */
  const responses = [
    {
      keys: ['placa', 'buscar placa', 'consultar placa'],
      reply: 'Para consultar información de un transporte, <strong>escribe directamente los 6 caracteres de su placa</strong> (por ejemplo: <code>ABC123</code>). Te mostraré su categoría, modelo, ubicación actual, estado y sus últimos movimientos registrados en la base de datos.'
    },
    {
      keys: ['maquinaria', 'vehiculo', 'transporte', 'equipo', 'inventario', 'flota'],
      reply: 'En el módulo de <strong>Inventario de Maquinarias y Vehículos</strong> puedes registrar nuevos equipos (placa validada a 6 caracteres) y actualizar en tiempo real sus ubicaciones, estados y horas operativas. Además, puedes escribir cualquier placa aquí para consultarla al instante.'
    },
    {
      keys: ['dashboard', 'kpi', 'metrica', 'estadistica', 'grafico', 'reporte'],
      reply: 'El <strong>Dashboard</strong> muestra en tiempo real los KPIs de tu flota: total, disponibles, trabajando en obra y en mantenimiento, junto con la distribución por maquinaria y vehículos y el historial reciente de movimientos. 📊'
    },
    {
      keys: ['web', 'contenido', 'editar', 'pagina', 'sitio'],
      reply: 'En <strong>Contenido de Web</strong> puedes editar la información corporativa, servicios y testimonios que aparecen en el sitio público de M&M. 🌐'
    },
    {
      keys: ['soporte', 'contacto', 'telefono', 'correo', 'email', 'ayuda'],
      reply: '📞 Puedes contactarnos:<br>· Email: <strong>admin@construccionesmym.com</strong><br>· PBX: <strong>(601) 345-6789</strong><br>Horario: Lun–Vie 7:00 am – 6:00 pm.'
    },
    {
      keys: ['hola', 'buenos', 'buenas', 'saludos', 'hey'],
      reply: '¡Hola! 👋 Es un gusto atenderte. Puedes escribirme cualquier placa de 6 caracteres (ej: <code>ABC123</code>) para consultar su información y movimientos en la base de datos, o preguntarme sobre el panel.'
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
    return `Recibí tu consulta sobre <em>"${esc(userText)}"</em>. Recuerda que puedes escribir directamente la <strong>placa</strong> de 6 caracteres (ej: ABC123) de cualquier máquina o vehículo para ver su estado actual y movimientos.`;
  }

  /* ---- Enviar con integración AJAX a la base de datos ---- */
  async function send(text) {
    if (!text || !text.trim()) return;
    const cleanText = text.trim();
    addMsg('user', esc(cleanText));

    /* Ocultar chips tras primera interacción */
    if (chips) {
      chips.style.transition = 'opacity 0.3s ease';
      chips.style.opacity = '0';
      setTimeout(() => chips.remove(), 300);
    }

    /* Mostrar typing */
    typingEl.style.display = 'flex';
    messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });

    clearTimeout(typingTimer);

    // Intentar buscar por placa en la base de datos si contiene patrón alfanumérico
    try {
      const response = await fetch(`chatbot.php?ajax_chat_query=1&q=${encodeURIComponent(cleanText)}`);
      if (response.ok) {
        const data = await response.json();
        if (data && data.found && data.html) {
          typingTimer = setTimeout(() => {
            typingEl.style.display = 'none';
            addMsg('bot', data.html);
          }, 600);
          return;
        }
      }
    } catch (e) {
      // Continuar con respuesta estándar si falla la petición
    }

    typingTimer = setTimeout(() => {
      typingEl.style.display = 'none';
      addMsg('bot', botReply(cleanText));
    }, 700 + Math.random() * 300);
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
