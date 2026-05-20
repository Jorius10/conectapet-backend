<?php
session_start();
require_once 'conexion.php';
include 'dash_layout.php';

// ── FILTRO DE BÚSQUEDA ──
$busqueda = trim($_GET['q'] ?? '');
$where = $busqueda ? "WHERE (adopciones.nombre LIKE '%$busqueda%' OR adopciones.apellidos LIKE '%$busqueda%' OR mascotas.nombre LIKE '%$busqueda%')" : '';

// ── VER DETALLE ──
$detalle = null;
if (isset($_GET['ver'])) {
    $vid = intval($_GET['ver']);
    $detalle = $conn->query("SELECT adopciones.*, mascotas.nombre AS mascota_nombre, mascotas.foto_url
        FROM adopciones LEFT JOIN mascotas ON adopciones.mascota_id = mascotas.id
        WHERE adopciones.id = $vid")->fetch_assoc();
}

// ── CAMBIAR ESTADO ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $aid = intval($_POST['adopcion_id']);
    $nuevo_estado = $conn->real_escape_string($_POST['nuevo_estado']);
    $conn->query("UPDATE adopciones SET estado='$nuevo_estado' WHERE id=$aid");

    // Si se aprueba → pasar mascota a "En Proceso"
    if ($nuevo_estado === 'Aprobado') {
        $mid = intval($_POST['mascota_id']);
        $conn->query("UPDATE mascotas SET estado_tramite='En Proceso' WHERE id=$mid");
    }
    // Si se rechaza → regresar a "Disponible" o "Con Solicitud" según si hay más solicitudes
    if ($nuevo_estado === 'Rechazado') {
        $mid = intval($_POST['mascota_id']);
        $otras = $conn->query("SELECT COUNT(*) FROM adopciones WHERE mascota_id=$mid AND estado='En Revisión'")->fetch_row()[0];
        $nuevo_tramite = $otras > 0 ? 'Con Solicitud' : 'Disponible';
        $conn->query("UPDATE mascotas SET estado_tramite='$nuevo_tramite' WHERE id=$mid");
    }
    header('Location: dash-adopciones.php');
    exit;
}

// ── LISTADO ──
$adopciones = $conn->query("SELECT adopciones.*, mascotas.nombre AS mascota_nombre 
    FROM adopciones 
    LEFT JOIN mascotas ON adopciones.mascota_id = mascotas.id 
    $where
    ORDER BY adopciones.id DESC");
?>

    <div class="dash-topbar">
        <h1><i class="ri-file-list-3-fill" style="color:#B56143;"></i> Solicitudes de Adopción</h1>
        <form method="GET" style="display:flex; gap:0.5rem;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar solicitante o mascota..."
                   style="padding:0.5rem 1rem; border:1px solid #e5e7eb; border-radius:8px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
            <button class="btn-dash btn-dash-primary" type="submit"><i class="ri-search-line"></i></button>
        </form>
    </div>

    <div class="dash-content">

        <?php if ($detalle): ?>
        <!-- ══ PANEL DE DETALLE ══ -->
        <div style="background:white; border-radius:12px; border:1px solid #e5e7eb; padding:0; overflow:hidden; margin-bottom:2rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="background:linear-gradient(135deg,#1F4A38,#2e6e52); padding:1.5rem 2rem; display:flex; align-items:center; justify-content:space-between;">
                <h2 style="color:white; font-size:1.2rem; font-family:'Outfit',sans-serif;">Detalle de Solicitud #<?php echo $detalle['id']; ?></h2>
                <a href="dash-adopciones.php" class="btn-dash" style="background:rgba(255,255,255,0.15); color:white;"><i class="ri-close-line"></i> Cerrar</a>
            </div>
            <div style="display:grid; grid-template-columns:1fr 2fr; gap:0;">
                <!-- Mascota info -->
                <div style="padding:2rem; border-right:1px solid #f3f4f6; text-align:center;">
                    <?php if (!empty($detalle['foto_url'])): ?>
                        <img src="<?php echo htmlspecialchars($detalle['foto_url']); ?>" style="width:140px; height:140px; object-fit:cover; border-radius:50%; margin-bottom:1rem; border:4px solid #1F4A38;">
                    <?php else: ?>
                        <div style="width:140px; height:140px; border-radius:50%; background:#f3f4f6; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:4rem;">🐾</div>
                    <?php endif; ?>
                    <h3 style="color:#1F4A38; margin-bottom:0.5rem;"><?php echo htmlspecialchars($detalle['mascota_nombre'] ?? 'Sin mascota'); ?></h3>
                    <p style="color:#9ca3af; font-size:0.85rem;">Mascota solicitada</p>

                    <!-- Cambiar estado -->
                    <form method="POST" style="margin-top:1.5rem;">
                        <input type="hidden" name="adopcion_id" value="<?php echo $detalle['id']; ?>">
                        <input type="hidden" name="mascota_id" value="<?php echo $detalle['mascota_id']; ?>">
                        <select name="nuevo_estado" style="width:100%; padding:0.6rem; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:0.75rem; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
                            <option value="En Revisión" <?php echo $detalle['estado']==='En Revisión' ? 'selected':'' ?>>En Revisión</option>
                            <option value="Aprobado"    <?php echo $detalle['estado']==='Aprobado' ? 'selected':'' ?>>✅ Aprobar</option>
                            <option value="Rechazado"   <?php echo $detalle['estado']==='Rechazado' ? 'selected':'' ?>>❌ Rechazar</option>
                        </select>
                        <button type="submit" name="cambiar_estado" class="btn-dash btn-dash-primary" style="width:100%;">Guardar Estado</button>
                    </form>
                </div>
                <!-- Datos del solicitante -->
                <div style="padding:2rem;">
                    <h4 style="color:#1F4A38; margin-bottom:1.25rem; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.5px;">Datos del Solicitante</h4>
                    <?php
                    $campos = [
                        'Nombre Completo' => $detalle['nombre'].' '.$detalle['apellidos'],
                        'DNI' => $detalle['dni'],
                        'Fecha Nacimiento' => $detalle['fecha_nacimiento'],
                        'Correo' => $detalle['correo'],
                        'Teléfono' => $detalle['telefono'],
                        'Teléfono Alt.' => $detalle['telefono_alt'] ?: '—',
                        'Dirección' => $detalle['direccion'].', '.$detalle['distrito'].', '.$detalle['ciudad'],
                        'Tipo Vivienda' => $detalle['tipo_vivienda'].' ('.$detalle['vivienda_propia'].')',
                        'Mascotas actuales' => $detalle['tiene_mascotas'],
                        'Tiempo disponible' => $detalle['tiempo_disponible'],
                        'Responsables' => $detalle['responsables'],
                    ];
                    foreach ($campos as $label => $val): ?>
                    <div style="display:flex; gap:1rem; padding:0.6rem 0; border-bottom:1px solid #f9fafb; font-size:0.9rem;">
                        <span style="min-width:160px; color:#9ca3af; font-weight:600;"><?php echo $label; ?></span>
                        <span style="color:#374151;"><?php echo htmlspecialchars($val ?? '—'); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!empty($detalle['motivo'])): ?>
                    <div style="margin-top:1.25rem;">
                        <div style="color:#9ca3af; font-weight:600; font-size:0.85rem; margin-bottom:0.5rem;">MOTIVO DE ADOPCIÓN</div>
                        <div style="background:#f9fafb; border-radius:8px; padding:1rem; color:#374151; font-size:0.9rem; line-height:1.6;"><?php echo htmlspecialchars($detalle['motivo']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ══ TABLA SOLICITUDES ══ -->
        <div class="dash-table-wrap">
            <div class="dash-table-head">
                <h3><i class="ri-file-list-3-fill" style="color:#B56143;"></i> Solicitudes registradas</h3>
                <span style="font-size:0.85rem; color:#9ca3af;"><?php echo $adopciones ? $adopciones->num_rows : 0; ?> registros</span>
            </div>
            <table class="dash-table">
                <thead>
                    <tr><th>#</th><th>Solicitante</th><th>Mascota</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if ($adopciones && $adopciones->num_rows > 0):
                        while ($s = $adopciones->fetch_assoc()):
                            $est = $s['estado'] ?? 'En Revisión';
                            $badge_class = match ($est) {
                                'Aprobado'  => 'badge-green',
                                'Rechazado' => 'badge-red',
                                default     => 'badge-yellow'
                            };
                    ?>
                    <tr>
                        <td style="color:#9ca3af;">#<?php echo $s['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($s['nombre'].' '.$s['apellidos']); ?></strong>
                            <small style="display:block; color:#9ca3af;"><?php echo htmlspecialchars($s['correo']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($s['mascota_nombre'] ?? '—'); ?></td>
                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($est); ?></span></td>
                        <td style="color:#9ca3af; font-size:0.85rem;"><?php echo date('d/m/Y', strtotime($s['fecha_solicitud'] ?? 'now')); ?></td>
                        <td><a href="dash-adopciones.php?ver=<?php echo $s['id']; ?>" class="btn-dash btn-dash-outline" style="font-size:0.8rem;">👁 Ver detalle</a></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" style="text-align:center; color:#9ca3af; padding:3rem;">No hay solicitudes registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
