<?php
session_start();
require_once 'conexion.php';
include 'dash_layout.php';

// Solo superadmin puede acceder
if (($_SESSION['admin_rol'] ?? '') !== 'superadmin') {
    header('Location: dashboard.php');
    exit;
}

$accion = $_GET['accion'] ?? 'lista';
$flash  = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// ─────────────────────────────────────────────────────────────────────────────
// GUARDAR ALBERGUE
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_albergue'])) {
    $aid     = intval($_POST['albergue_id'] ?? 0);
    $nombre  = trim($_POST['nombre']        ?? '');
    $dir     = trim($_POST['direccion']     ?? '');
    $desc    = trim($_POST['descripcion']   ?? '');
    $resc    = intval($_POST['rescates']    ?? 0);
    $adop    = intval($_POST['adopciones']  ?? 0);
    $anios   = intval($_POST['anios']       ?? 0);

    // Logo upload
    $logo = trim($_POST['logo_actual'] ?? '');
    if (!empty($_FILES['logo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $ddir = 'albergues fotos/subidas/';
            if (!is_dir($ddir)) mkdir($ddir, 0755, true);
            $fname = 'alb_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $ddir . $fname)) {
                $logo = $ddir . $fname;
            }
        }
    }

    if ($aid > 0) {
        $stmt = $conn->prepare("UPDATE albergues SET nombre=?, direccion=?, descripcion=?, logo_url=?, rescates=?, adopciones=?, anios_trayectoria=? WHERE id=?");
        $stmt->bind_param("ssssiii", $nombre, $dir, $desc, $logo, $resc, $adop, $anios, $aid);
        $stmt->execute();
        $_SESSION['flash'] = '✅ Albergue actualizado.';
    } else {
        $stmt = $conn->prepare("INSERT INTO albergues (nombre, direccion, descripcion, logo_url, rescates, adopciones, anios_trayectoria) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssii", $nombre, $dir, $desc, $logo, $resc, $adop, $anios);
        $stmt->execute();
        $_SESSION['flash'] = '✅ Albergue registrado.';
    }
    header('Location: dash-albergues.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// ELIMINAR
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_albergue'])) {
    $aid = intval($_POST['albergue_id']);
    $conn->query("DELETE FROM albergues WHERE id=$aid");
    $_SESSION['flash'] = '🗑️ Albergue eliminado.';
    header('Location: dash-albergues.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// CARGAR PARA EDITAR
// ─────────────────────────────────────────────────────────────────────────────
$alb_edit = null;
if ($accion === 'editar' && isset($_GET['id'])) {
    $eid = intval($_GET['id']);
    $alb_edit = $conn->query("SELECT * FROM albergues WHERE id=$eid")->fetch_assoc();
}

// ─────────────────────────────────────────────────────────────────────────────
// LISTADO
// ─────────────────────────────────────────────────────────────────────────────
$albergues = $conn->query("SELECT albergues.*, 
    (SELECT COUNT(*) FROM mascotas WHERE mascotas.albergue_id=albergues.id) AS total_mascotas,
    (SELECT COUNT(*) FROM admins WHERE admins.albergue_id=albergues.id) AS total_admins
    FROM albergues ORDER BY albergues.id ASC");
?>

    <div class="dash-topbar">
        <h1><i class="ri-hospital-fill" style="color:#B56143;"></i>
            <?php echo ($accion === 'nueva' || $accion === 'editar') ? ($accion === 'editar' ? 'Editar Albergue' : 'Nuevo Albergue') : 'Gestión de Albergues'; ?>
        </h1>
        <div style="display:flex; gap:0.5rem;">
            <?php if ($accion !== 'nueva' && $accion !== 'editar'): ?>
            <a href="dash-albergues.php?accion=nueva" class="btn-dash btn-dash-primary">
                <i class="ri-add-circle-fill"></i> Nuevo Albergue
            </a>
            <?php else: ?>
            <a href="dash-albergues.php" class="btn-dash btn-dash-outline">
                <i class="ri-arrow-left-line"></i> Volver
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dash-content">

        <?php if ($flash): ?>
        <div style="background:#dcfce7; border:1px solid #86efac; color:#15803d; padding:0.85rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
            <?php echo htmlspecialchars($flash); ?>
        </div>
        <?php endif; ?>

<?php if ($accion === 'nueva' || $accion === 'editar'): ?>
        <div style="background:white; border-radius:14px; border:1px solid #e5e7eb; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="background:linear-gradient(135deg,#1F4A38,#2e6e52); padding:1.5rem 2rem;">
                <h2 style="color:white; font-family:'Outfit',sans-serif; font-size:1.2rem;">
                    <i class="ri-hospital-fill"></i>
                    <?php echo $accion === 'editar' ? 'Editar: ' . htmlspecialchars($alb_edit['nombre'] ?? '') : 'Registrar nuevo albergue'; ?>
                </h2>
            </div>
            <form method="POST" action="dash-albergues.php" enctype="multipart/form-data" style="padding:2rem;">
                <input type="hidden" name="albergue_id" value="<?php echo $alb_edit['id'] ?? 0; ?>">
                <input type="hidden" name="logo_actual"  value="<?php echo htmlspecialchars($alb_edit['logo_url'] ?? ''); ?>">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Nombre del Albergue *</label>
                        <input type="text" name="nombre" required placeholder="Ej: Patitas Con Futuro"
                               value="<?php echo htmlspecialchars($alb_edit['nombre'] ?? ''); ?>"
                               style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Dirección</label>
                        <input type="text" name="direccion" placeholder="Av. Ejemplo 123, Lima"
                               value="<?php echo htmlspecialchars($alb_edit['direccion'] ?? ''); ?>"
                               style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                    </div>
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="Cuéntanos sobre el albergue..."
                              style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem; resize:vertical;"><?php echo htmlspecialchars($alb_edit['descripcion'] ?? ''); ?></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Total Rescates</label>
                        <input type="number" name="rescates" min="0" value="<?php echo intval($alb_edit['rescates'] ?? 0); ?>"
                               style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Total Adopciones</label>
                        <input type="number" name="adopciones" min="0" value="<?php echo intval($alb_edit['adopciones'] ?? 0); ?>"
                               style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Años de Trayectoria</label>
                        <input type="number" name="anios" min="0" value="<?php echo intval($alb_edit['anios_trayectoria'] ?? 0); ?>"
                               style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                    </div>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Logo / Foto del Albergue</label>
                    <?php if (!empty($alb_edit['logo_url'])): ?>
                    <img src="<?php echo htmlspecialchars($alb_edit['logo_url']); ?>" style="width:80px; height:80px; border-radius:8px; object-fit:cover; border:2px solid #e5e7eb; margin-bottom:0.5rem; display:block;">
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/*"
                           style="width:100%; padding:0.7rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; font-size:0.85rem; background:white;">
                </div>

                <div style="display:flex; gap:1rem; padding-top:1rem; border-top:1px solid #f3f4f6;">
                    <button type="submit" name="guardar_albergue" class="btn-dash btn-dash-primary" style="padding:0.75rem 2rem;">
                        <i class="ri-save-fill"></i> <?php echo $accion === 'editar' ? 'Guardar Cambios' : 'Registrar Albergue'; ?>
                    </button>
                    <a href="dash-albergues.php" class="btn-dash btn-dash-outline">Cancelar</a>
                </div>
            </form>
        </div>

<?php else: ?>
        <!-- ══ TABLA ALBERGUES ══ -->
        <div class="dash-table-wrap">
            <div class="dash-table-head">
                <h3><i class="ri-hospital-fill" style="color:#B56143;"></i> Albergues registrados</h3>
                <span style="font-size:0.85rem; color:#9ca3af;"><?php echo $albergues ? $albergues->num_rows : 0; ?> albergues</span>
            </div>
            <table class="dash-table">
                <thead>
                    <tr><th>Albergue</th><th>Dirección</th><th>Rescates</th><th>Adopciones</th><th>Mascotas</th><th>Admins</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if ($albergues && $albergues->num_rows > 0):
                        while ($a = $albergues->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <?php if (!empty($a['logo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($a['logo_url']); ?>" style="width:40px; height:40px; border-radius:8px; object-fit:cover;">
                                <?php else: ?>
                                <div style="width:40px; height:40px; border-radius:8px; background:#f3f4f6; display:flex; align-items:center; justify-content:center;">🏠</div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($a['nombre']); ?></strong>
                                    <small style="display:block; color:#9ca3af;"><?php echo $a['anios_trayectoria']; ?> años</small>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:0.85rem; color:#6b7280;"><?php echo htmlspecialchars($a['direccion'] ?? '—'); ?></td>
                        <td><strong style="color:#1F4A38;"><?php echo number_format($a['rescates']); ?></strong></td>
                        <td><strong style="color:#B56143;"><?php echo number_format($a['adopciones']); ?></strong></td>
                        <td><span class="badge badge-green"><?php echo $a['total_mascotas']; ?> mascotas</span></td>
                        <td><span class="badge badge-blue"><?php echo $a['total_admins']; ?> admins</span></td>
                        <td>
                            <div style="display:flex; gap:0.4rem;">
                                <a href="dash-albergues.php?accion=editar&id=<?php echo $a['id']; ?>" class="btn-dash btn-dash-outline" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                                    <i class="ri-edit-line"></i> Editar
                                </a>
                                <a href="albergue-perfil.php?id=<?php echo $a['id']; ?>" target="_blank" class="btn-dash btn-dash-outline" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                                    <i class="ri-eye-line"></i>
                                </a>
                                <form method="POST" onsubmit="return confirm('¿Eliminar este albergue? Se eliminarán también sus mascotas.');" style="display:inline;">
                                    <input type="hidden" name="albergue_id" value="<?php echo $a['id']; ?>">
                                    <button type="submit" name="eliminar_albergue" class="btn-dash btn-dash-danger" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#9ca3af; padding:3rem;">No hay albergues registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
<?php endif; ?>

    </div>
</div>
</body>
</html>
