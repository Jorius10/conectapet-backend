<?php
session_start();
require_once 'conexion.php';

// ── Guard: solo usuarios logueados ──────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    $id_actual = $_GET['id'] ?? '';
    header('Location: login.php?redir=' . urlencode('adoptar.php?id=' . $id_actual));
    exit;
}
$user_id    = $_SESSION['user_id'];
$user_nombre = $_SESSION['user_nombre'] ?? '';
$user_correo = $_SESSION['user_correo'] ?? '';


// ── Cargar mascota ANTES del POST (la necesita el correo) ────────────
$id_buscar = isset($_POST['mascota_id']) ? intval($_POST['mascota_id'])
           : (isset($_GET['id'])         ? intval($_GET['id']) : 0);

if ($id_buscar === 0) {
    echo "<script>alert('No se seleccionó ninguna mascota.'); window.location.href='albergues.php';</script>";
    exit;
}

$_res_m = $conn->query("SELECT mascotas.*, albergues.nombre AS albergue_nombre
    FROM mascotas LEFT JOIN albergues ON mascotas.albergue_id = albergues.id
    WHERE mascotas.id = $id_buscar");

if (!$_res_m || $_res_m->num_rows === 0) {
    echo "<script>alert('Mascota no encontrada.'); window.location.href='albergues.php';</script>";
    exit;
}
$mascota = $_res_m->fetch_assoc();

// ===============================
// 1. PROCESAR FORMULARIO (POST)
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del formulario
    $mascota_id        = intval($_POST['mascota_id']);
    $nombre            = trim($_POST['nombre']);
    $apellidos         = trim($_POST['apellidos']);
    $dni               = trim($_POST['dni']);
    $fecha_nacimiento  = trim($_POST['fecha_nacimiento']);
    $correo            = trim($_POST['correo']);
    $telefono          = trim($_POST['telefono']);
    $telefono_alt      = trim($_POST['telefono_alt']);
    $direccion         = trim($_POST['direccion']);
    $ciudad            = trim($_POST['ciudad']);
    $distrito          = trim($_POST['distrito']);
    $departamento      = trim($_POST['departamento']);
    $tipo_vivienda     = trim($_POST['tipo_vivienda']);
    $vivienda_propia   = trim($_POST['vivienda_propia']);
    $tiene_mascotas    = trim($_POST['tiene_mascotas']);
    $experiencia       = trim($_POST['experiencia']);
    $motivo            = trim($_POST['motivo']);
    $tiempo_disponible = trim($_POST['tiempo_disponible']);
    $responsables      = trim($_POST['responsables']);

    $usuario_id = $user_id;

    // Validación básica rápida de campos nulos principales
    if (!$mascota_id || !$nombre || !$apellidos || !$dni || !$correo || !$telefono) {
        $mensaje = "Por favor completa todos los campos requeridos marcados con * 😿";
    } else {
        // Insertar TODOS los campos
        $sql_insert = "INSERT INTO adopciones (
            mascota_id, usuario_id, nombre, apellidos, dni, fecha_nacimiento, correo, 
            telefono, telefono_alt, direccion, ciudad, distrito, departamento, 
            tipo_vivienda, vivienda_propia, tiene_mascotas, experiencia, motivo, 
            tiempo_disponible, responsables, estado
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'En Revisión')";

        $stmt = $conn->prepare($sql_insert);
        if ($stmt) {
            $stmt->bind_param(
                "iissssssssssssssssss",
                $mascota_id, $usuario_id, $nombre, $apellidos, $dni, $fecha_nacimiento, 
                $correo, $telefono, $telefono_alt, $direccion, $ciudad, $distrito, 
                $departamento, $tipo_vivienda, $vivienda_propia, $tiene_mascotas, 
                $experiencia, $motivo, $tiempo_disponible, $responsables
            );

            if ($stmt->execute()) {
                // Actualizar estado de mascota a "Con Solicitud" 
                $update = $conn->prepare("UPDATE mascotas SET estado_tramite='Con Solicitud' WHERE id=?");
                $update->bind_param("i", $mascota_id);
                $update->execute();
                $update->close();

                $exito = true;
                $mensaje = "Tu solicitud de adopción ha sido enviada con éxito. El albergue se pondrá en contacto contigo pronto.";

                // ── Enviar correo de confirmación ──────────────────────────
                try {
                    require_once 'mailer.php';
                    $mail = crearMailer();
                    $mail->addAddress($correo, $nombre . ' ' . $apellidos);
                    $mail->Subject = "✅ Solicitud de adopción recibida - ConectaPet";

                    $cuerpo = "
                        <h2 style='color:#1F4A38; text-align:center;'>¡Recibimos tu solicitud, $nombre! 🐾</h2>
                        <p style='color:#6b7280; text-align:center;'>Gracias por querer darle un hogar a <strong>{$mascota['nombre']}</strong>.</p>
                        <div style='background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:20px; margin:20px 0;'>
                            <table style='width:100%; border-collapse:collapse; font-size:14px; color:#374151;'>
                                <tr><td style='padding:8px 0; border-bottom:1px solid #e5e7eb;'><strong>Mascota solicitada:</strong></td><td>{$mascota['nombre']}</td></tr>
                                <tr><td style='padding:8px 0; border-bottom:1px solid #e5e7eb;'><strong>Solicitante:</strong></td><td>$nombre $apellidos</td></tr>
                                <tr><td style='padding:8px 0; border-bottom:1px solid #e5e7eb;'><strong>DNI:</strong></td><td>$dni</td></tr>
                                <tr><td style='padding:8px 0; border-bottom:1px solid #e5e7eb;'><strong>Teléfono:</strong></td><td>$telefono</td></tr>
                                <tr><td style='padding:8px 0;'><strong>Fecha de solicitud:</strong></td><td>" . date('d/m/Y H:i') . "</td></tr>
                            </table>
                        </div>
                        <p>El equipo del albergue revisará tu solicitud y se pondrá en contacto contigo en los próximos días.</p>
                        <p style='color:#6b7280; font-size:13px;'>Si tienes dudas, escríbenos respondiendo este correo.</p>
                        <div style='text-align:center; margin-top:25px;'>
                            <a href='http://localhost/pagina%20proyecto/mascota-perfil.php?id=$mascota_id' style='background:#1F4A38; color:white; padding:12px 25px; border-radius:25px; text-decoration:none; font-weight:bold;'>Ver perfil de {$mascota['nombre']}</a>
                        </div>
                    ";
                    $mail->Body = plantillaCorreo($cuerpo);
                    $mail->send();
                } catch (Throwable $e) {
                    error_log("Error enviando correo de adopción: " . $e->getMessage());
                }
            } else {
                $mensaje = "Ocurrió un error al enviar tu solicitud. Inténtalo nuevamente.";
            }
            $stmt->close();
        } else {
            $mensaje = "Error de base de datos.";
        }
    }
}

// ===============================
// 2. VALIDAR ESTADO MASCOTA
// ===============================
// Solo permitimos adoptar si está "Disponible" o "Con Solicitud"
$estado_actual = strtolower(trim($mascota['estado_tramite']));
if ($estado_actual !== 'disponible' && $estado_actual !== 'con solicitud' && !$exito) {
    echo "<script>alert('Esta mascota ya se encuentra en un proceso avanzado o fue adoptada y no admite más solicitudes.'); window.location.href='mascota-perfil.php?id=$id_buscar';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comenzar trámite de Adopción - ConectaPet</title>
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body style="background-color: #fdfbf7;">
<?php $active_nav = 'albergues'; include 'header.php'; ?>

    <main style="padding-top: 150px; padding-bottom: 6rem; min-height: 80vh;">
        <div class="container">
            <?php if ($exito): ?>
                <!-- PANTALLA DE ÉXITO -->
                <div style="background: white; padding: 4rem; text-align: center; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); max-width: 700px; margin: 0 auto; border-top: 6px solid #10b981;">
                    <i class="ri-checkbox-circle-fill" style="font-size: 5rem; color: #10b981; margin-bottom: 1rem; display: block;"></i>
                    <h1 style="font-size: 2.5rem; color: var(--c-primary); margin-bottom: 1rem;">¡Gracias por Adoptar! 🐾</h1>
                    <p style="font-size: 1.2rem; color: var(--c-text-muted); line-height: 1.6; margin-bottom: 2.5rem;">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </p>
                    <a href="mascota-perfil.php?id=<?php echo $id_buscar; ?>" class="btn btn-primary btn-large">Ver perfil actualizado</a>
                </div>
            <?php else: ?>
                <!-- FORMULARIO DE SOLICITUD -->
                <a href="mascota-perfil.php?id=<?php echo $id_buscar; ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem; color: var(--c-primary); font-weight: bold; text-decoration: none;">
                    <i class="ri-arrow-left-line"></i> Cancelar y volver al Perfil
                </a>

                <?php if(!empty($mensaje)): ?>
                    <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 1rem 1.5rem; border-radius: var(--radius-sm); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ri-error-warning-fill"></i> <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 3rem; align-items: start;">
                    
                    <!-- Tarjeta Resumen de la Mascota (Columna Izquierda) -->
                    <div style="position: sticky; top: 120px; background: white; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md); border: 1px solid var(--c-border);">
                        <img src="<?php echo htmlspecialchars($mascota['foto_url']); ?>" style="width: 100%; height: 280px; object-fit: cover;" alt="<?php echo htmlspecialchars($mascota['nombre']); ?>">
                        <div style="padding: 2rem;">
                            <h3 style="font-size: 1.8rem; color: var(--c-primary); margin-bottom: 0.5rem;">Adopción de <?php echo htmlspecialchars($mascota['nombre']); ?></h3>
                            <p style="color: var(--c-text-muted); margin-bottom: 1.5rem;">Estás a punto de cambiar su vida.</p>
                            
                            <hr style="border: 0; border-top: 1px solid var(--c-border); margin-bottom: 1.5rem;">
                            
                            <div style="display: flex; flex-direction: column; gap: 1rem; color: var(--c-text-muted); font-size: 0.95rem;">
                                <div><i class="ri-home-4-line" style="color: var(--c-accent); margin-right: 5px;"></i> <strong>Albergue:</strong> <?php echo htmlspecialchars($mascota['albergue_nombre']); ?></div>
                                <div><i class="ri-calendar-event-line" style="color: var(--c-accent); margin-right: 5px;"></i> <strong>Edad:</strong> <?php echo htmlspecialchars($mascota['edad_texto']); ?></div>
                                <div><i class="ri-heart-pulse-line" style="color: var(--c-accent); margin-right: 5px;"></i> <strong>Médico:</strong> <?php echo htmlspecialchars($mascota['estado_medico']); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Datos Extremo (Columna Derecha) -->
                    <div style="background: white; border-radius: var(--radius-lg); padding: 3rem; box-shadow: var(--shadow-md); border: 1px solid var(--c-border);">
                        <div style="margin-bottom: 2.5rem;">
                            <h2 style="font-size: 2rem; color: var(--c-primary); margin-bottom: 0.5rem;">Formulario de Solicitud</h2>
                            <p style="color: var(--c-text-muted);">Llena este documento con honestidad. Será revisado por el equipo del albergue.</p>
                        </div>
                        
                        <form method="POST" action="adoptar.php">
                            <input type="hidden" name="mascota_id" value="<?php echo $id_buscar; ?>">

                            <h4 style="color: var(--c-accent); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px dashed var(--c-border);">1. Datos Personales</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label">Nombres *</label>
                                    <input type="text" name="nombre" class="form-control" required value="<?php echo htmlspecialchars($user_nombre); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Apellidos *</label>
                                    <input type="text" name="apellidos" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">DNI o Documento *</label>
                                    <input type="text" name="dni" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Fecha de Nacimiento *</label>
                                    <input type="date" name="fecha_nacimiento" class="form-control" required>
                                </div>
                            </div>

                            <h4 style="color: var(--c-accent); margin-top: 2rem; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px dashed var(--c-border);">2. Datos de Contacto</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label">Correo Electrónico *</label>
                                    <input type="email" name="correo" class="form-control" required value="<?php echo htmlspecialchars($user_correo); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Teléfono / Celular *</label>
                                    <input type="text" name="telefono" class="form-control" required>
                                </div>
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label class="form-label">Teléfono Alternativo (Opcional)</label>
                                    <input type="text" name="telefono_alt" class="form-control">
                                </div>
                            </div>

                            <h4 style="color: var(--c-accent); margin-top: 2rem; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px dashed var(--c-border);">3. Domicilio y Vivienda</h4>
                            <div class="form-group">
                                <label class="form-label">Dirección Completa *</label>
                                <input type="text" name="direccion" class="form-control" required>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label">Distrito *</label>
                                    <input type="text" name="distrito" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Ciudad *</label>
                                    <input type="text" name="ciudad" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Departamento</label>
                                    <input type="text" name="departamento" class="form-control">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label">Tipo de Vivienda</label>
                                    <select name="tipo_vivienda" class="form-select form-control">
                                        <option value="Casa">Casa</option>
                                        <option value="Departamento">Departamento</option>
                                        <option value="Quinta/Condominio">Condominio</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">¿Es propia o alquilada?</label>
                                    <select name="vivienda_propia" class="form-select form-control">
                                        <option value="Propia">Propia</option>
                                        <option value="Alquilada">Alquilada (Con permiso)</option>
                                        <option value="Alquilada (Sin Permiso)">Alquilada (Sin permiso)</option>
                                    </select>
                                </div>
                            </div>

                            <h4 style="color: var(--c-accent); margin-top: 2rem; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px dashed var(--c-border);">4. Experiencia y Compromiso</h4>
                            <div class="form-group">
                                <label class="form-label">¿Tienes otras mascotas actualmente?</label>
                                <input type="text" name="tiene_mascotas" class="form-control" placeholder="Ej: Sí, 2 gatos. / No, ninguna.">
                            </div>
                            <div class="form-group">
                                <label class="form-label">¿Cuál es tu experiencia previa cuidando mascotas?</label>
                                <textarea name="experiencia" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">¿Qué tiempo dispones al día para cuidarlo/pasearlo?</label>
                                <input type="text" name="tiempo_disponible" class="form-control" placeholder="Ej: 3 horas activas y fines de semana">
                            </div>
                            <div class="form-group">
                                <label class="form-label">¿Quiénes serán los responsables financieros de la mascota?</label>
                                <input type="text" name="responsables" class="form-control" placeholder="Ej: Yo y mi esposa">
                            </div>
                            <div class="form-group">
                                <label class="form-label">¿Por qué deseas adoptar a <?php echo htmlspecialchars($mascota['nombre']); ?>?</label>
                                <textarea name="motivo" class="form-control" rows="4" required></textarea>
                            </div>

                            <div style="margin-top: 3rem;">
                                <button type="submit" class="btn btn-primary btn-large btn-block" style="font-size: 1.2rem; padding: 1.2rem;">
                                    <i class="ri-send-plane-fill"></i> Enviar Solicitud de Adopción
                                </button>
                                <p style="text-align: center; margin-top: 1rem; color: var(--c-text-muted); font-size: 0.85rem;">
                                    Al enviar, aceptas que el albergue evalúe tu información.
                                </p>
                            </div>
                        </form>
                    </div>

                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
