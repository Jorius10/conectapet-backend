<?php
session_start();
require_once 'conexion.php';

// ── Guard: solo usuarios logueados ──────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    $qs = http_build_query($_GET);
    header('Location: login.php?redir=' . urlencode('donar-general.php?' . $qs));
    exit;
}
$user_nombre = $_SESSION['user_nombre'] ?? '';
$user_correo = $_SESSION['user_correo'] ?? '';


$tipo       = isset($_GET['tipo']) ? htmlspecialchars(trim($_GET['tipo'])) : 'general';
$albergue_id = isset($_GET['albergue_id']) ? intval($_GET['albergue_id']) : 0;

// Si hay albergue_id, traemos el nombre del albergue
$nombre_albergue = '';
if ($albergue_id > 0) {
    $res_alb = $conn->query("SELECT nombre, logo_url FROM albergues WHERE id = $albergue_id");
    if ($res_alb && $res_alb->num_rows > 0) {
        $alb_data = $res_alb->fetch_assoc();
        $nombre_albergue = $alb_data['nombre'];
    }
}

$titulos = [
    'alimentos'  => ['titulo' => 'Donar para Alimentos 🍖',   'desc' => 'Tu aporte garantiza comida diaria y de calidad para los rescatados.', 'color' => '#f59e0b', 'icon' => 'ri-restaurant-fill'],
    'medicinas'  => ['titulo' => 'Donar para Medicinas 💊',   'desc' => 'Ayuda a cubrir vacunas, cirugías y emergencias veterinarias urgentes.', 'color' => 'var(--c-primary)', 'icon' => 'ri-first-aid-kit-fill'],
    'refugios'   => ['titulo' => 'Donar para Refugios 🏠',    'desc' => 'Apoya con mantas, limpieza y mejoras para mantener espacios dignos.',  'color' => 'var(--c-accent)', 'icon' => 'ri-home-heart-fill'],
    'albergue'   => ['titulo' => 'Donar al Albergue 🏡',      'desc' => 'Tu donación va directamente a este albergue.',                          'color' => 'var(--c-primary)', 'icon' => 'ri-home-smile-2-fill'],
    'general'    => ['titulo' => 'Hacer una Donación 💙',     'desc' => 'Tu donación llega donde más se necesita dentro de ConectaPet.',        'color' => 'var(--c-primary)', 'icon' => 'ri-hand-coin-fill'],
];

$info = $titulos[$tipo] ?? $titulos['general'];

// Si hay albergue, personalizamos el título y descripción
if ($nombre_albergue) {
    $info['titulo'] = "Donar a \"$nombre_albergue\" 🏡";
    $info['desc']   = "Tu donación va directamente al albergue $nombre_albergue.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $info['titulo']; ?> - ConectaPet</title>
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .monto-opciones { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem; }
        .monto-opciones input[type="radio"] { display:none; }
        .monto-opciones label { cursor:pointer; padding:0.8rem 1.5rem; border-radius:var(--radius-pill); border:2px solid var(--c-border); background:var(--c-bg); font-weight:600; color:var(--c-text-muted); transition:all 0.2s; display:inline-block; font-size:1rem; }
        .monto-opciones label:hover { border-color:var(--c-primary); color:var(--c-primary); }
        .monto-opciones input[type="radio"]:checked + label { background:var(--c-primary); color:white; border-color:var(--c-primary); }
        #info-pago { background:rgba(31,74,56,0.05); border:1px solid var(--c-primary); border-radius:var(--radius-md); padding:1.5rem; margin-top:1rem; display:none; text-align:center; }
        #info-pago h3 { color:var(--c-primary); margin-bottom:1rem; }
        #info-pago p { color:var(--c-text-muted); font-size:0.95rem; margin-bottom:0.5rem; }
    </style>
</head>
<body>
<?php $active_nav = 'donaciones'; include 'header.php'; ?>

    <main style="padding-top: 150px; padding-bottom: 6rem; min-height: 80vh; background: var(--c-bg);">
        <div class="container" style="max-width: 680px;">

            <a href="donaciones.php" style="display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:2rem; color:var(--c-primary); font-weight:bold; text-decoration:none;">
                <i class="ri-arrow-left-line"></i> Volver a Donaciones
            </a>

            <div style="background:white; border-radius:var(--radius-lg); padding:3rem; box-shadow:var(--shadow-md); border-top:5px solid <?php echo $info['color']; ?>;">

                <!-- Icono + Título -->
                <div style="text-align:center; margin-bottom:2.5rem;">
                    <div style="width:70px; height:70px; background:rgba(31,74,56,0.08); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                        <i class="<?php echo $info['icon']; ?>" style="font-size:2rem; color:<?php echo $info['color']; ?>;"></i>
                    </div>
                    <h1 style="font-size:2rem; color:var(--c-primary); margin-bottom:0.5rem;"><?php echo $info['titulo']; ?></h1>
                    <p style="color:var(--c-text-muted);"><?php echo $info['desc']; ?></p>
                </div>

                <form action="procesar_donacion.php" method="POST">
                    <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
                    <input type="hidden" name="mascota_id" value="">
                    <input type="hidden" name="albergue_id" value="<?php echo $albergue_id; ?>">

                    <h4 style="color:var(--c-accent); margin-bottom:1.5rem; padding-bottom:0.5rem; border-bottom:2px dashed var(--c-border);">Tus datos</h4>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                        <div class="form-group">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" name="nombre" class="form-control" required value="<?php echo htmlspecialchars($user_nombre); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Correo electrónico *</label>
                            <input type="email" name="correo" class="form-control" required value="<?php echo htmlspecialchars($user_correo); ?>">
                        </div>
                    </div>

                    <h4 style="color:var(--c-accent); margin-top:1rem; margin-bottom:1.5rem; padding-bottom:0.5rem; border-bottom:2px dashed var(--c-border);">Elige el monto (S/)</h4>
                    <div class="monto-opciones">
                        <input type="radio" id="m10"  name="monto" value="10"  required><label for="m10">S/ 10</label>
                        <input type="radio" id="m25"  name="monto" value="25"><label for="m25">S/ 25</label>
                        <input type="radio" id="m50"  name="monto" value="50"><label for="m50">S/ 50</label>
                        <input type="radio" id="m100" name="monto" value="100"><label for="m100">S/ 100</label>
                        <input type="radio" id="otro" name="monto" value="otro"><label for="otro">Otro</label>
                    </div>
                    <div class="form-group" id="campo-monto-otro" style="display:none;">
                        <label class="form-label">Monto personalizado *</label>
                        <input type="number" name="monto_otro" id="monto_otro" class="form-control" step="0.50" min="1" placeholder="Ej: 75">
                    </div>

                    <h4 style="color:var(--c-accent); margin-top:1.5rem; margin-bottom:1.5rem; padding-bottom:0.5rem; border-bottom:2px dashed var(--c-border);">Método de pago</h4>
                    <div class="form-group">
                        <select name="metodo_pago" id="metodo_pago" class="form-select form-control" required>
                            <option value="">Elige un método...</option>
                            <option value="Yape">💜 Yape</option>
                            <option value="Plin">🔵 Plin</option>
                            <option value="Transferencia">🏦 Transferencia Bancaria</option>
                        </select>
                    </div>
                    <div id="info-pago"></div>

                    <div class="form-group" style="margin-top:1.5rem;">
                        <label class="form-label">Comentario (opcional)</label>
                        <textarea name="comentario" class="form-control" rows="3" placeholder="Escribe un mensaje o dedicatoria..."></textarea>
                    </div>

                    <div style="margin-top:2.5rem;">
                        <button type="submit" class="btn btn-primary btn-large btn-block" style="font-size:1.15rem; padding:1.2rem; background:<?php echo $info['color']; ?>;">
                            <i class="ri-heart-fill"></i> Confirmar Donación
                        </button>
                        <p style="text-align:center; margin-top:1rem; color:var(--c-text-muted); font-size:0.85rem;">
                            100% de tu donación va directamente a <?php echo $tipo !== 'general' ? $tipo : 'las mascotas'; ?>.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">&copy; 2026 ConectaPet. Todos los derechos reservados.</div>
        </div>
    </footer>

    <script>
        const montoRadios = document.querySelectorAll('input[name="monto"]');
        const campoOtro = document.getElementById('campo-monto-otro');
        const inputOtro = document.getElementById('monto_otro');
        montoRadios.forEach(r => r.addEventListener('change', () => {
            campoOtro.style.display = (r.value === 'otro' && r.checked) ? 'block' : 'none';
            inputOtro.required = (r.value === 'otro' && r.checked);
        }));

        const metodoSelect = document.getElementById('metodo_pago');
        const infoPagoDiv  = document.getElementById('info-pago');
        const infoData = {
            'Yape': '<img src="qr/qr_yape.jpg" alt="QR Yape" style="width:180px;height:180px;object-fit:contain;display:block;margin:0 auto 0.75rem;border-radius:12px;border:3px solid #7c3aed;"> <h3>💜 Pago por Yape</h3><p>Transfiere al número: <strong>965 432 100</strong></p><p style="font-size:0.8rem;color:#999;">Titular: ConectaPet</p>',
            'Plin': '<img src="qr/qr_plin.png" alt="QR Plin" style="width:180px;height:180px;object-fit:contain;display:block;margin:0 auto 0.75rem;border-radius:12px;border:3px solid #2563eb;"> <h3>🔵 Pago por Plin</h3><p>Transfiere al número: <strong>965 432 100</strong></p><p style="font-size:0.8rem;color:#999;">Titular: ConectaPet</p>',
            'Transferencia': '<h3>🏦 Transferencia BCP</h3><p><strong>Cuenta:</strong> 123-45678901-0-12</p><p><strong>CCI:</strong> 002 1234 56789012 34</p><p><strong>Titular:</strong> ConectaPet Asociación</p>'
        };
        metodoSelect.addEventListener('change', function() {
            infoPagoDiv.innerHTML = infoData[this.value] || '';
            infoPagoDiv.style.display = infoData[this.value] ? 'block' : 'none';
        });
    </script>
</body>
</html>
