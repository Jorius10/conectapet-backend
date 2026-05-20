<?php
session_start();
require_once 'conexion.php';

// Solo acepta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: albergues.php');
    exit;
}

// ===============================
// 1. Capturar variables del POST
// ===============================
$nombre     = trim($_POST['nombre']     ?? '');
$correo     = trim($_POST['correo']     ?? '');
$tipo       = trim($_POST['tipo']       ?? 'mascota');
$metodo     = trim($_POST['metodo_pago']?? '');
$comentario = trim($_POST['comentario'] ?? '');

// ── ID de usuario logueado (clave correcta de sesión) ──
$usuario_id = $_SESSION['user_id'] ?? null;

$mascota_id = isset($_POST['mascota_id']) && $_POST['mascota_id'] !== ''
              ? intval($_POST['mascota_id']) : null;

$albergue_id = isset($_POST['albergue_id']) && $_POST['albergue_id'] !== '' && intval($_POST['albergue_id']) > 0
               ? intval($_POST['albergue_id']) : null;

// Monto (maneja opción "otro")
if (isset($_POST['monto']) && $_POST['monto'] === 'otro') {
    $monto = floatval($_POST['monto_otro'] ?? 0);
} else {
    $monto = floatval($_POST['monto'] ?? 0);
}

// ===============================
// 2. Validación básica
// ===============================
if ($nombre === '' || $correo === '' || $monto <= 0 || $metodo === '') {
    echo "<script>alert('Por favor completa todos los campos correctamente.'); history.back();</script>";
    exit;
}

// ===============================
// 3. Obtener el nombre de la mascota (si aplica)
// ===============================
$nombre_mascota = "una mascota de ConectaPet";
$foto_mascota   = "";

if ($mascota_id) {
    $res = $conn->query("SELECT nombre, foto_url FROM mascotas WHERE id = $mascota_id");
    if ($res && $res->num_rows > 0) {
        $m = $res->fetch_assoc();
        $nombre_mascota = $m['nombre'];
        $foto_mascota   = $m['foto_url'];
    }
}

// ===============================
// 4. Insertar donación en la BD
// ===============================
$sql = $mascota_id
    ? "INSERT INTO donaciones (mascota_id, albergue_id, tipo, nombre, correo, monto, comentario, metodo_pago, id_usuario) VALUES (?,?,?,?,?,?,?,?,?)"
    : "INSERT INTO donaciones (mascota_id, albergue_id, tipo, nombre, correo, monto, comentario, metodo_pago, id_usuario) VALUES (NULL,?,?,?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
$ok = false;

if ($stmt) {
    if ($mascota_id) {
        $stmt->bind_param("iisssdssi", $mascota_id, $albergue_id, $tipo, $nombre, $correo, $monto, $comentario, $metodo, $usuario_id);
    } else {
        $stmt->bind_param("isssdssi", $albergue_id, $tipo, $nombre, $correo, $monto, $comentario, $metodo, $usuario_id);
    }
    $ok = $stmt->execute();
    $stmt->close();
}

// ===============================
// 5. Enviar correo de confirmación
// ===============================
$correo_enviado = false;

if ($ok) {
    try {
        require_once 'mailer.php';
        $mail = crearMailer();

        $mail->addAddress($correo, $nombre);
        $mail->Subject = "¡Tu donación a ConectaPet fue registrada! 🐾";

        $foto_html = $foto_mascota
            ? "<img src='cid:fotomascota' style='width:120px; height:120px; object-fit:cover; border-radius:50%; display:block; margin:0 auto 15px;'>"
            : "";

        $cuerpo = "
            <h2 style='color:#1F4A38; text-align:center;'>¡Gracias por tu donación, $nombre! 💙</h2>
            $foto_html
            <p style='text-align:center; color:#6b7280;'>Has donado a <strong>$nombre_mascota</strong>.</p>
            <div style='background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:20px; margin:20px 0;'>
                <table style='width:100%; border-collapse:collapse; font-size:14px; color:#374151;'>
                    <tr><td style='padding:8px 0; border-bottom:1px solid #e5e7eb;'><strong>Donación a:</strong></td><td>$nombre_mascota</td></tr>
                    <tr><td style='padding:8px 0; border-bottom:1px solid #e5e7eb;'><strong>Monto:</strong></td><td style='color:#B56143; font-weight:bold;'>S/ " . number_format($monto,2) . "</td></tr>
                    <tr><td style='padding:8px 0; border-bottom:1px solid #e5e7eb;'><strong>Método:</strong></td><td>$metodo</td></tr>
                    <tr><td style='padding:8px 0;'><strong>Fecha:</strong></td><td>" . date('d/m/Y H:i') . "</td></tr>
                </table>
            </div>
            " . ($comentario ? "<p style='color:#6b7280; font-style:italic;'>Tu mensaje: \"$comentario\"</p>" : "") . "
            <p style='margin-top:20px;'>Gracias por ayudar a que <strong>$nombre_mascota</strong> tenga una vida mejor 🐾</p>
            <div style='text-align:center; margin-top:25px;'>
                <a href='http://localhost/pagina%20proyecto/albergues.php' style='background:#1F4A38; color:white; padding:12px 25px; border-radius:25px; text-decoration:none; font-weight:bold;'>Ver más mascotas</a>
            </div>
        ";

        $mail->Body = plantillaCorreo($cuerpo);

        // Adjuntar foto de la mascota si existe como inline
        if ($foto_mascota && file_exists($foto_mascota)) {
            $mail->addEmbeddedImage($foto_mascota, 'fotomascota');
        }

        $mail->send();
        $correo_enviado = true;

    } catch (Throwable $e) {
        error_log("Error enviando correo de donación: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donación Registrada - ConectaPet</title>
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
<?php $active_nav = 'donaciones'; include 'header.php'; ?>

    <main style="padding-top: 150px; padding-bottom: 6rem; min-height: 80vh; display: flex; align-items: center;">
        <div class="container">
            <?php if ($ok): ?>
            <!-- Éxito -->
            <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: var(--radius-lg); padding: 4rem; text-align: center; box-shadow: var(--shadow-lg); border-top: 6px solid #10b981;">
                <i class="ri-heart-fill" style="font-size: 5rem; color: #10b981; display: block; margin-bottom: 1rem;"></i>
                <h1 style="font-size: 2.2rem; color: var(--c-primary); margin-bottom: 0.5rem;">¡Gracias, <?php echo htmlspecialchars($nombre); ?>! 💙</h1>
                <p style="color: var(--c-text-muted); font-size: 1.1rem; margin-bottom: 2rem;">
                    Tu donación de <strong style="color: var(--c-accent);">S/ <?php echo number_format($monto,2); ?></strong> a <strong><?php echo htmlspecialchars($nombre_mascota); ?></strong> fue registrada con éxito.
                </p>

                <?php if ($correo_enviado): ?>
                <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid #10b981; border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 2rem;">
                    <i class="ri-mail-check-fill" style="color: #10b981;"></i>
                    Te enviamos un comprobante a <strong><?php echo htmlspecialchars($correo); ?></strong>
                </div>
                <?php else: ?>
                <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid #f59e0b; border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 2rem; font-size: 0.9rem; color: var(--c-text-muted);">
                    <i class="ri-information-line"></i> El correo de confirmación no pudo enviarse, pero tu donación quedó registrada.
                </div>
                <?php endif; ?>

                <!-- Resumen tarjeta -->
                <div style="background: var(--c-bg); border: 1px solid var(--c-border); border-radius: var(--radius-md); padding: 1.5rem; text-align: left; margin-bottom: 2.5rem; font-size: 0.95rem;">
                    <div style="display:flex; justify-content:space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--c-border);"><span>Donación a</span><strong><?php echo htmlspecialchars($nombre_mascota); ?></strong></div>
                    <div style="display:flex; justify-content:space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--c-border);"><span>Monto</span><strong style="color:var(--c-accent);">S/ <?php echo number_format($monto,2); ?></strong></div>
                    <div style="display:flex; justify-content:space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--c-border);"><span>Método</span><strong><?php echo htmlspecialchars($metodo); ?></strong></div>
                    <div style="display:flex; justify-content:space-between; padding: 0.5rem 0;"><span>Fecha</span><strong><?php echo date('d/m/Y H:i'); ?></strong></div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <?php if ($mascota_id): ?>
                    <a href="mascota-perfil.php?id=<?php echo $mascota_id; ?>" class="btn btn-primary">Ver a <?php echo htmlspecialchars($nombre_mascota); ?></a>
                    <?php endif; ?>
                    <?php if ($usuario_id): ?>
                    <a href="mi-perfil.php?seccion=donaciones" class="btn btn-outline"><i class="ri-user-3-line"></i> Ver mis donaciones</a>
                    <?php endif; ?>
                    <a href="donaciones.php" class="btn btn-outline">Seguir Donando</a>
                </div>
            </div>
            <?php else: ?>
            <!-- Error -->
            <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: var(--radius-lg); padding: 4rem; text-align: center; box-shadow: var(--shadow-lg); border-top: 6px solid #ef4444;">
                <i class="ri-error-warning-fill" style="font-size: 5rem; color: #ef4444; display: block; margin-bottom: 1rem;"></i>
                <h1 style="color: #ef4444; margin-bottom: 1rem;">Algo salió mal</h1>
                <p style="color: var(--c-text-muted); margin-bottom: 2rem;">No pudimos registrar tu donación. Por favor intenta nuevamente.</p>
                <a href="javascript:history.back()" class="btn btn-primary">Volver al formulario</a>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
