<?php
session_start();
require_once 'conexion.php';
include 'dash_layout.php';

// ── PERMISOS: solo admins ────────────────────────────────────────────────────
// (dash_layout.php ya verifica $_SESSION['admin_id'])

$admin_rol      = $_SESSION['admin_rol']    ?? 'albergue';
$admin_albergue = $_SESSION['albergue_id'] ?? null;

// ── ACCIÓN ───────────────────────────────────────────────────────────────────
$accion = $_GET['accion'] ?? 'lista';

// ── FILTROS ──────────────────────────────────────────────────────────────────
$f_especie = $_GET['especie'] ?? '';
$f_estado  = $_GET['estado']  ?? '';
$f_q       = trim($_GET['q']  ?? '');

// ── MENSAJE FLASH ────────────────────────────────────────────────────────────
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// ─────────────────────────────────────────────────────────────────────────────
// GUARDAR MASCOTA (nueva o edición)
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_mascota'])) {
    $mid        = intval($_POST['mascota_id'] ?? 0);
    $nombre     = trim($_POST['nombre']       ?? '');
    $especie    = trim($_POST['especie']      ?? '');
    $sexo       = trim($_POST['sexo']         ?? '');
    $edad       = trim($_POST['edad_texto']   ?? '');
    $desc       = trim($_POST['descripcion']  ?? '');
    $est_tram   = trim($_POST['estado_tramite'] ?? 'Disponible');
    $est_med    = trim($_POST['estado_medico']  ?? '');
    $alb_id     = intval($_POST['albergue_id'] ?? ($admin_albergue ?? 1));

    // Upload foto
    $foto_url = trim($_POST['foto_actual'] ?? '');
    if (!empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $dir = 'mascotas/subidas/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fname = 'msc_' . time() . '_' . rand(100,999) . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $fname)) {
                $foto_url = $dir . $fname;
            }
        }
    }

    if ($mid > 0) {
        // Editar
        $stmt = $conn->prepare("UPDATE mascotas SET nombre=?, especie=?, sexo=?, edad_texto=?, descripcion=?, estado_tramite=?, estado_medico=?, foto_url=?, albergue_id=? WHERE id=?");
        $stmt->bind_param("ssssssssii", $nombre, $especie, $sexo, $edad, $desc, $est_tram, $est_med, $foto_url, $alb_id, $mid);
        $stmt->execute();
        $_SESSION['flash'] = '✅ Mascota actualizada correctamente.';
    } else {
        // Nueva
        $stmt = $conn->prepare("INSERT INTO mascotas (nombre, especie, sexo, edad_texto, descripcion, estado_tramite, estado_medico, foto_url, albergue_id) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssssi", $nombre, $especie, $sexo, $edad, $desc, $est_tram, $est_med, $foto_url, $alb_id);
        $stmt->execute();
        $_SESSION['flash'] = '✅ Mascota registrada correctamente.';
    }
    header('Location: dash-mascotas.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// ELIMINAR MASCOTA
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_mascota'])) {
    $mid = intval($_POST['mascota_id']);
    $conn->query("DELETE FROM mascotas WHERE id=$mid");
    $_SESSION['flash'] = '🗑️ Mascota eliminada.';
    header('Location: dash-mascotas.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// CARGAR DATOS PARA FORMULARIO (editar)
// ─────────────────────────────────────────────────────────────────────────────
$mascota_edit = null;
if ($accion === 'editar' && isset($_GET['id'])) {
    $eid = intval($_GET['id']);
    $mascota_edit = $conn->query("SELECT * FROM mascotas WHERE id=$eid")->fetch_assoc();
}

// ─────────────────────────────────────────────────────────────────────────────
// LISTADO
// ─────────────────────────────────────────────────────────────────────────────
$where_parts = [];
if ($f_especie) $where_parts[] = "mascotas.especie='" . $conn->real_escape_string($f_especie) . "'";
if ($f_estado)  $where_parts[] = "mascotas.estado_tramite='" . $conn->real_escape_string($f_estado) . "'";
if ($f_q)       $where_parts[] = "(mascotas.nombre LIKE '%" . $conn->real_escape_string($f_q) . "%' OR albergues.nombre LIKE '%" . $conn->real_escape_string($f_q) . "%')";
if ($admin_rol === 'albergue' && $admin_albergue) {
    $where_parts[] = "mascotas.albergue_id=" . intval($admin_albergue);
}
$where_sql = $where_parts ? "WHERE " . implode(" AND ", $where_parts) : "";

$mascotas = $conn->query("SELECT mascotas.*, albergues.nombre AS albergue_nombre 
    FROM mascotas LEFT JOIN albergues ON mascotas.albergue_id=albergues.id
    $where_sql ORDER BY mascotas.id DESC");

// Albergues para el select
$albergues_list = $conn->query("SELECT id, nombre FROM albergues ORDER BY nombre");
?>

    <div class="dash-topbar">
        <h1><i class="ri-home-heart-fill" style="color:#B56143;"></i> 
            <?php echo ($accion === 'nueva' || $accion === 'editar') ? ($accion === 'editar' ? 'Editar Mascota' : 'Nueva Mascota') : 'Gestión de Mascotas'; ?>
        </h1>
        <div style="display:flex; gap:0.5rem;">
            <?php if ($accion !== 'nueva' && $accion !== 'editar'): ?>
            <a href="dash-mascotas.php?accion=nueva" class="btn-dash btn-dash-primary">
                <i class="ri-add-circle-fill"></i> Nueva Mascota
            </a>
            <?php else: ?>
            <a href="dash-mascotas.php" class="btn-dash btn-dash-outline">
                <i class="ri-arrow-left-line"></i> Volver al listado
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dash-content">

        <?php if ($flash): ?>
        <div style="background:#dcfce7; border:1px solid #86efac; color:#15803d; padding:0.85rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem; font-weight:600;">
            <?php echo htmlspecialchars($flash); ?>
        </div>
        <?php endif; ?>

<?php if ($accion === 'nueva' || $accion === 'editar'): ?>
        <!-- ══ FORMULARIO ══ -->
        <div style="background:white; border-radius:14px; border:1px solid #e5e7eb; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="background:linear-gradient(135deg,#1F4A38,#2e6e52); padding:1.5rem 2rem;">
                <h2 style="color:white; font-family:'Outfit',sans-serif; font-size:1.2rem;">
                    <i class="ri-paw-fill"></i> 
                    <?php echo $accion === 'editar' ? 'Editar datos de ' . htmlspecialchars($mascota_edit['nombre'] ?? 'mascota') : 'Registrar nueva mascota'; ?>
                </h2>
            </div>
            <form method="POST" action="dash-mascotas.php" enctype="multipart/form-data" style="padding:2rem;">
                <input type="hidden" name="mascota_id" value="<?php echo $mascota_edit['id'] ?? 0; ?>">
                <input type="hidden" name="foto_actual" value="<?php echo htmlspecialchars($mascota_edit['foto_url'] ?? ''); ?>">

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Nombre *</label>
                        <input type="text" name="nombre" required placeholder="Ej: Max"
                               value="<?php echo htmlspecialchars($mascota_edit['nombre'] ?? ''); ?>"
                               style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Especie *</label>
                        <select name="especie" required style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                            <option value="">Seleccionar...</option>
                            <?php foreach (['Perro','Gato','Otro'] as $esp): ?>
                            <option value="<?php echo $esp; ?>" <?php echo ($mascota_edit['especie'] ?? '') === $esp ? 'selected' : ''; ?>><?php echo $esp; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Sexo *</label>
                        <select name="sexo" required style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                            <option value="">Seleccionar...</option>
                            <option value="Macho" <?php echo ($mascota_edit['sexo'] ?? '') === 'Macho' ? 'selected' : ''; ?>>Macho</option>
                            <option value="Hembra" <?php echo ($mascota_edit['sexo'] ?? '') === 'Hembra' ? 'selected' : ''; ?>>Hembra</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Edad</label>
                        <input type="text" name="edad_texto" placeholder="Ej: 2 Años"
                               value="<?php echo htmlspecialchars($mascota_edit['edad_texto'] ?? ''); ?>"
                               style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Estado Trámite</label>
                        <select name="estado_tramite" style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                            <?php foreach (['Disponible','Con Solicitud','En Proceso','Adoptado'] as $est): ?>
                            <option value="<?php echo $est; ?>" <?php echo ($mascota_edit['estado_tramite'] ?? 'Disponible') === $est ? 'selected' : ''; ?>><?php echo $est; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Estado Médico</label>
                        <select name="estado_medico" style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                            <?php foreach (['Vacunado','Vacunada','Esterilizado','Esterilizada','Castrado','Desparasitado','Desparasitada','En chequeos médicos'] as $em): ?>
                            <option value="<?php echo $em; ?>" <?php echo ($mascota_edit['estado_medico'] ?? '') === $em ? 'selected' : ''; ?>><?php echo $em; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if ($admin_rol === 'superadmin'): ?>
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Albergue</label>
                    <select name="albergue_id" style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                        <?php while ($alb = $albergues_list->fetch_assoc()): ?>
                        <option value="<?php echo $alb['id']; ?>" <?php echo ($mascota_edit['albergue_id'] ?? '') == $alb['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($alb['nombre']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php else: ?>
                <input type="hidden" name="albergue_id" value="<?php echo intval($admin_albergue); ?>">
                <?php endif; ?>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="Describe la personalidad y características de la mascota..."
                              style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem; resize:vertical;"><?php echo htmlspecialchars($mascota_edit['descripcion'] ?? ''); ?></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; align-items:start; margin-bottom:1.5rem;">
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Foto de la Mascota</label>
                        <input type="file" name="foto" accept="image/*" onchange="previewImg(this)"
                               style="width:100%; padding:0.7rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; font-size:0.85rem; background:white;">
                        <p style="font-size:0.75rem; color:#9ca3af; margin-top:0.3rem;">jpg, png, webp. Deja vacío para mantener la actual.</p>
                    </div>
                    <div style="text-align:center;">
                        <?php if (!empty($mascota_edit['foto_url'])): ?>
                        <img src="<?php echo htmlspecialchars($mascota_edit['foto_url']); ?>" id="img-preview"
                             style="width:120px; height:120px; object-fit:cover; border-radius:12px; border:3px solid #1F4A38; display:block; margin:0 auto;">
                        <?php else: ?>
                        <img id="img-preview" src="" style="width:120px; height:120px; object-fit:cover; border-radius:12px; border:3px solid #e5e7eb; display:none; margin:0 auto;">
                        <div id="img-placeholder" style="width:120px; height:120px; border-radius:12px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; font-size:3rem; margin:0 auto;">🐾</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display:flex; gap:1rem; padding-top:1rem; border-top:1px solid #f3f4f6;">
                    <button type="submit" name="guardar_mascota" class="btn-dash btn-dash-primary" style="padding:0.75rem 2rem;">
                        <i class="ri-save-fill"></i> <?php echo $accion === 'editar' ? 'Guardar Cambios' : 'Registrar Mascota'; ?>
                    </button>
                    <a href="dash-mascotas.php" class="btn-dash btn-dash-outline">Cancelar</a>
                </div>
            </form>
        </div>

<?php else: ?>
        <!-- ══ FILTROS ══ -->
        <form method="GET" style="background:white; padding:1.25rem 1.5rem; border-radius:12px; border:1px solid #e5e7eb; margin-bottom:1.5rem; display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
            <div style="flex:1; min-width:200px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#9ca3af; text-transform:uppercase; margin-bottom:0.3rem;">Buscar</label>
                <input type="text" name="q" value="<?php echo htmlspecialchars($f_q); ?>" placeholder="Nombre o albergue..."
                       style="width:100%; padding:0.6rem 1rem; border:1px solid #e5e7eb; border-radius:8px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
            </div>
            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#9ca3af; text-transform:uppercase; margin-bottom:0.3rem;">Especie</label>
                <select name="especie" style="padding:0.6rem 1rem; border:1px solid #e5e7eb; border-radius:8px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
                    <option value="">Todas</option>
                    <?php foreach (['Perro','Gato','Otro'] as $esp): ?>
                    <option value="<?php echo $esp; ?>" <?php echo $f_especie === $esp ? 'selected' : ''; ?>><?php echo $esp; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#9ca3af; text-transform:uppercase; margin-bottom:0.3rem;">Estado</label>
                <select name="estado" style="padding:0.6rem 1rem; border:1px solid #e5e7eb; border-radius:8px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
                    <option value="">Todos</option>
                    <?php foreach (['Disponible','Con Solicitud','En Proceso','Adoptado'] as $est): ?>
                    <option value="<?php echo $est; ?>" <?php echo $f_estado === $est ? 'selected' : ''; ?>><?php echo $est; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="btn-dash btn-dash-primary"><i class="ri-filter-3-fill"></i> Filtrar</button>
                <?php if ($f_q || $f_especie || $f_estado): ?>
                <a href="dash-mascotas.php" class="btn-dash btn-dash-outline"><i class="ri-close-line"></i> Limpiar</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- ══ TABLA MASCOTAS ══ -->
        <div class="dash-table-wrap">
            <div class="dash-table-head">
                <h3><i class="ri-paw-fill" style="color:#B56143;"></i> Mascotas registradas</h3>
                <span style="font-size:0.85rem; color:#9ca3af;"><?php echo $mascotas ? $mascotas->num_rows : 0; ?> mascotas</span>
            </div>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mascota</th>
                        <th>Especie / Sexo</th>
                        <th>Albergue</th>
                        <th>Estado Trámite</th>
                        <th>Estado Médico</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($mascotas && $mascotas->num_rows > 0):
                        while ($m = $mascotas->fetch_assoc()):
                            $badge_tram = match ($m['estado_tramite']) {
                                'Disponible'    => 'badge-green',
                                'Con Solicitud' => 'badge-blue',
                                'En Proceso'    => 'badge-yellow',
                                'Adoptado'      => 'badge-gray',
                                default         => 'badge-gray'
                            };
                    ?>
                    <tr>
                        <td style="color:#9ca3af;">#<?php echo $m['id']; ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <?php if (!empty($m['foto_url'])): ?>
                                <img src="<?php echo htmlspecialchars($m['foto_url']); ?>"
                                     style="width:44px; height:44px; border-radius:8px; object-fit:cover; border:2px solid #f3f4f6;">
                                <?php else: ?>
                                <div style="width:44px; height:44px; border-radius:8px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">🐾</div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($m['nombre']); ?></strong>
                                    <small style="display:block; color:#9ca3af;"><?php echo htmlspecialchars($m['edad_texto'] ?? '—'); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($m['especie']); ?> · <?php echo htmlspecialchars($m['sexo']); ?></td>
                        <td style="font-size:0.85rem; color:#6b7280;"><?php echo htmlspecialchars($m['albergue_nombre'] ?? '—'); ?></td>
                        <td><span class="badge <?php echo $badge_tram; ?>"><?php echo htmlspecialchars($m['estado_tramite']); ?></span></td>
                        <td style="font-size:0.85rem; color:#6b7280;"><?php echo htmlspecialchars($m['estado_medico'] ?? '—'); ?></td>
                        <td>
                            <div style="display:flex; gap:0.4rem;">
                                <a href="dash-mascotas.php?accion=editar&id=<?php echo $m['id']; ?>" class="btn-dash btn-dash-outline" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                                    <i class="ri-edit-line"></i> Editar
                                </a>
                                <a href="mascota-perfil.php?id=<?php echo $m['id']; ?>" target="_blank" class="btn-dash btn-dash-outline" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                                    <i class="ri-eye-line"></i>
                                </a>
                                <form method="POST" action="dash-mascotas.php" onsubmit="return confirm('¿Eliminar a <?php echo addslashes($m['nombre']); ?>? Esta acción no se puede deshacer.');" style="display:inline;">
                                    <input type="hidden" name="mascota_id" value="<?php echo $m['id']; ?>">
                                    <button type="submit" name="eliminar_mascota" class="btn-dash btn-dash-danger" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#9ca3af; padding:3rem;">
                        <i class="ri-paw-line" style="font-size:2rem; display:block; margin-bottom:0.5rem; opacity:0.3;"></i>
                        No hay mascotas que coincidan con el filtro.
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
<?php endif; ?>

    </div><!-- end dash-content -->
</div><!-- end dash-main -->
</body>
</html>

<script>
function previewImg(input) {
    const prev = document.getElementById('img-preview');
    const ph   = document.getElementById('img-placeholder');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            prev.src = e.target.result;
            prev.style.display = 'block';
            if (ph) ph.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
