<?php
session_start();
require_once 'conexion.php';
include 'dash_layout.php';

$accion = $_GET['accion'] ?? 'lista';

// Flash
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// ─────────────────────────────────────────────────────────────────────────────
// GUARDAR NOTICIA
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_noticia'])) {
    $nid        = intval($_POST['noticia_id'] ?? 0);
    $titulo     = trim($_POST['titulo']       ?? '');
    $resumen    = trim($_POST['resumen']      ?? '');
    $contenido  = trim($_POST['contenido']    ?? '');
    $categoria  = trim($_POST['categoria']    ?? 'General');
    $autor      = trim($_POST['autor']        ?? 'ConectaPet');
    $destacada  = isset($_POST['destacada']) ? 1 : 0;

    // Upload imagen
    $imagen_url = trim($_POST['imagen_actual'] ?? '');
    if (!empty($_FILES['imagen']['name'])) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $dir = 'fotos noticias/subidas/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fname = 'noticia_' . time() . '_' . rand(100,999) . '.' . $ext;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $fname)) {
                $imagen_url = $dir . $fname;
            }
        }
    }

    if ($nid > 0) {
        $stmt = $conn->prepare("UPDATE noticias SET titulo=?, resumen=?, contenido=?, imagen_url=?, categoria=?, autor=?, destacada=? WHERE id=?");
        $stmt->bind_param("ssssssii", $titulo, $resumen, $contenido, $imagen_url, $categoria, $autor, $destacada, $nid);
        $stmt->execute();
        $_SESSION['flash'] = '✅ Noticia actualizada correctamente.';
    } else {
        $stmt = $conn->prepare("INSERT INTO noticias (titulo, resumen, contenido, imagen_url, categoria, autor, destacada) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssi", $titulo, $resumen, $contenido, $imagen_url, $categoria, $autor, $destacada);
        $stmt->execute();
        $_SESSION['flash'] = '✅ Noticia publicada correctamente.';
    }
    header('Location: dash-noticias.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// ELIMINAR NOTICIA
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_noticia'])) {
    $nid = intval($_POST['noticia_id']);
    $conn->query("DELETE FROM noticias WHERE id=$nid");
    $_SESSION['flash'] = '🗑️ Noticia eliminada.';
    header('Location: dash-noticias.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// TOGGLE DESTACADA
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_destacada'])) {
    $nid   = intval($_POST['noticia_id']);
    $nuevo = intval($_POST['nuevo_valor']);
    $conn->query("UPDATE noticias SET destacada=$nuevo WHERE id=$nid");
    header('Location: dash-noticias.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// CARGAR PARA EDITAR
// ─────────────────────────────────────────────────────────────────────────────
$noticia_edit = null;
if ($accion === 'editar' && isset($_GET['id'])) {
    $eid = intval($_GET['id']);
    $noticia_edit = $conn->query("SELECT * FROM noticias WHERE id=$eid")->fetch_assoc();
}

// ─────────────────────────────────────────────────────────────────────────────
// LISTADO
// ─────────────────────────────────────────────────────────────────────────────
$f_cat = trim($_GET['cat'] ?? '');
$where = $f_cat ? "WHERE categoria='" . $conn->real_escape_string($f_cat) . "'" : '';
$noticias = $conn->query("SELECT * FROM noticias $where ORDER BY id DESC");

$categorias = ['General','Eventos','Alianzas','Salud Animal','Consejos','Campañas'];
?>

    <div class="dash-topbar">
        <h1><i class="ri-newspaper-fill" style="color:#B56143;"></i>
            <?php echo ($accion === 'nueva' || $accion === 'editar') ? ($accion === 'editar' ? 'Editar Noticia' : 'Nueva Noticia / Campaña') : 'Gestión de Noticias'; ?>
        </h1>
        <div style="display:flex; gap:0.5rem;">
            <?php if ($accion !== 'nueva' && $accion !== 'editar'): ?>
            <a href="dash-noticias.php?accion=nueva" class="btn-dash btn-dash-primary">
                <i class="ri-add-circle-fill"></i> Nueva Noticia
            </a>
            <?php else: ?>
            <a href="dash-noticias.php" class="btn-dash btn-dash-outline">
                <i class="ri-arrow-left-line"></i> Volver al listado
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
        <!-- ══ FORMULARIO ══ -->
        <div style="background:white; border-radius:14px; border:1px solid #e5e7eb; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="background:linear-gradient(135deg,#6366f1,#4f46e5); padding:1.5rem 2rem;">
                <h2 style="color:white; font-family:'Outfit',sans-serif; font-size:1.2rem;">
                    <i class="ri-newspaper-fill"></i>
                    <?php echo $accion === 'editar' ? 'Editar: ' . htmlspecialchars($noticia_edit['titulo'] ?? '') : 'Publicar nueva noticia o campaña'; ?>
                </h2>
            </div>
            <form method="POST" action="dash-noticias.php" enctype="multipart/form-data" style="padding:2rem;">
                <input type="hidden" name="noticia_id" value="<?php echo $noticia_edit['id'] ?? 0; ?>">
                <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($noticia_edit['imagen_url'] ?? ''); ?>">

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Título *</label>
                    <input type="text" name="titulo" required placeholder="Título de la noticia o campaña"
                           value="<?php echo htmlspecialchars($noticia_edit['titulo'] ?? ''); ?>"
                           style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.95rem;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Categoría</label>
                        <select name="categoria" style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($noticia_edit['categoria'] ?? 'General') === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Autor</label>
                        <input type="text" name="autor" placeholder="Ej: Equipo ConectaPet"
                               value="<?php echo htmlspecialchars($noticia_edit['autor'] ?? 'ConectaPet'); ?>"
                               style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem;">
                    </div>
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Resumen (subtítulo / preview)</label>
                    <textarea name="resumen" rows="2" placeholder="Breve descripción que aparece en el listado de noticias..."
                              style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.9rem; resize:vertical;"><?php echo htmlspecialchars($noticia_edit['resumen'] ?? ''); ?></textarea>
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Contenido completo (HTML permitido)</label>
                    <textarea name="contenido" rows="8" placeholder="<p>Escribe el contenido completo aquí...</p>"
                              style="width:100%; padding:0.8rem 1rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; outline:none; font-size:0.88rem; resize:vertical; font-size:0.85rem; font-family:monospace;"><?php echo htmlspecialchars($noticia_edit['contenido'] ?? ''); ?></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem; align-items:start;">
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem;">Imagen Principal</label>
                        <input type="file" name="imagen" accept="image/*" onchange="prevNoticia(this)"
                               style="width:100%; padding:0.7rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; font-size:0.85rem; background:white;">
                        <p style="font-size:0.75rem; color:#9ca3af; margin-top:0.3rem;">Deja vacío para mantener la imagen actual.</p>
                    </div>
                    <div style="text-align:center;">
                        <?php if (!empty($noticia_edit['imagen_url'])): ?>
                        <img id="noticia-preview" src="<?php echo htmlspecialchars($noticia_edit['imagen_url']); ?>"
                             style="width:100%; max-height:120px; object-fit:cover; border-radius:10px; border:2px solid #e5e7eb;">
                        <?php else: ?>
                        <img id="noticia-preview" src="" style="display:none; width:100%; max-height:120px; object-fit:cover; border-radius:10px;">
                        <div style="background:#f9fafb; border-radius:10px; padding:2rem; color:#d1d5db; text-align:center; font-size:0.85rem;">Sin imagen</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.5rem; padding:1rem; background:#fef9c3; border-radius:9px; border:1px solid #fde68a;">
                    <input type="checkbox" name="destacada" id="chk-dest" style="width:18px; height:18px; accent-color:#a16207;" <?php echo ($noticia_edit['destacada'] ?? 0) ? 'checked' : ''; ?>>
                    <label for="chk-dest" style="font-weight:600; color:#a16207; cursor:pointer;">⭐ Marcar como noticia destacada (aparece primero en el sitio)</label>
                </div>

                <div style="display:flex; gap:1rem; padding-top:1rem; border-top:1px solid #f3f4f6;">
                    <button type="submit" name="guardar_noticia" class="btn-dash btn-dash-primary" style="padding:0.75rem 2rem; background:#6366f1;">
                        <i class="ri-send-plane-fill"></i> <?php echo $accion === 'editar' ? 'Guardar Cambios' : 'Publicar Noticia'; ?>
                    </button>
                    <a href="dash-noticias.php" class="btn-dash btn-dash-outline">Cancelar</a>
                </div>
            </form>
        </div>

<?php else: ?>
        <!-- ══ FILTRO CATEGORÍA ══ -->
        <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
            <a href="dash-noticias.php" class="btn-dash <?php echo !$f_cat ? 'btn-dash-primary' : 'btn-dash-outline'; ?>">Todas</a>
            <?php foreach ($categorias as $cat): ?>
            <a href="dash-noticias.php?cat=<?php echo urlencode($cat); ?>" class="btn-dash <?php echo $f_cat === $cat ? 'btn-dash-primary' : 'btn-dash-outline'; ?>"><?php echo $cat; ?></a>
            <?php endforeach; ?>
        </div>

        <!-- ══ GRID NOTICIAS ══ -->
        <?php if ($noticias && $noticias->num_rows > 0): ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:1.25rem;">
            <?php while ($n = $noticias->fetch_assoc()): ?>
            <div style="background:white; border-radius:14px; border:1px solid #e5e7eb; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05); display:flex; flex-direction:column;">
                <!-- Imagen -->
                <?php if (!empty($n['imagen_url'])): ?>
                <div style="height:180px; overflow:hidden; position:relative;">
                    <img src="<?php echo htmlspecialchars($n['imagen_url']); ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php if ($n['destacada']): ?>
                    <span style="position:absolute; top:10px; right:10px; background:#fbbf24; color:white; font-size:0.72rem; font-weight:800; padding:0.25rem 0.6rem; border-radius:20px;">⭐ DESTACADA</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <!-- Contenido -->
                <div style="padding:1.25rem; flex:1; display:flex; flex-direction:column;">
                    <div style="display:flex; gap:0.5rem; margin-bottom:0.75rem; flex-wrap:wrap;">
                        <span class="badge badge-blue" style="font-size:0.7rem;"><?php echo htmlspecialchars($n['categoria']); ?></span>
                        <span style="font-size:0.75rem; color:#9ca3af;"><?php echo date('d/m/Y', strtotime($n['fecha_publicacion'])); ?></span>
                    </div>
                    <h3 style="font-size:0.95rem; font-weight:700; color:#1F4A38; margin-bottom:0.5rem; line-height:1.4;"><?php echo htmlspecialchars($n['titulo']); ?></h3>
                    <p style="font-size:0.82rem; color:#6b7280; line-height:1.5; flex:1; margin-bottom:1rem;"><?php echo htmlspecialchars(mb_strimwidth($n['resumen'] ?? '', 0, 120, '...')); ?></p>
                    
                    <!-- Acciones -->
                    <div style="display:flex; gap:0.4rem; flex-wrap:wrap; border-top:1px solid #f3f4f6; padding-top:0.75rem;">
                        <a href="dash-noticias.php?accion=editar&id=<?php echo $n['id']; ?>" class="btn-dash btn-dash-outline" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                            <i class="ri-edit-line"></i> Editar
                        </a>
                        <a href="noticia.php?id=<?php echo $n['id']; ?>" target="_blank" class="btn-dash btn-dash-outline" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                            <i class="ri-eye-line"></i> Ver
                        </a>
                        <!-- Toggle destacada -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="noticia_id" value="<?php echo $n['id']; ?>">
                            <input type="hidden" name="nuevo_valor" value="<?php echo $n['destacada'] ? 0 : 1; ?>">
                            <button type="submit" name="toggle_destacada" class="btn-dash btn-dash-outline" style="font-size:0.78rem; padding:0.35rem 0.75rem; color:<?php echo $n['destacada'] ? '#a16207' : '#6b7280'; ?>;" title="<?php echo $n['destacada'] ? 'Quitar destacada' : 'Marcar como destacada'; ?>">
                                <?php echo $n['destacada'] ? '⭐' : '☆'; ?>
                            </button>
                        </form>
                        <!-- Eliminar -->
                        <form method="POST" onsubmit="return confirm('¿Eliminar esta noticia?');" style="display:inline;">
                            <input type="hidden" name="noticia_id" value="<?php echo $n['id']; ?>">
                            <button type="submit" name="eliminar_noticia" class="btn-dash btn-dash-danger" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div style="background:white; border-radius:14px; border:1px solid #e5e7eb; padding:4rem; text-align:center;">
            <i class="ri-newspaper-line" style="font-size:3rem; color:#d1d5db; display:block; margin-bottom:1rem;"></i>
            <p style="color:#9ca3af; margin-bottom:1.5rem;">No hay noticias publicadas aún.</p>
            <a href="dash-noticias.php?accion=nueva" class="btn-dash btn-dash-primary"><i class="ri-add-circle-fill"></i> Crear primera noticia</a>
        </div>
        <?php endif; ?>
<?php endif; ?>

    </div>
</div>
</body>
</html>

<script>
function prevNoticia(input) {
    const prev = document.getElementById('noticia-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { prev.src = e.target.result; prev.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
