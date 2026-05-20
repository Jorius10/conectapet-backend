<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redir=mi-perfil.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$seccion = $_GET['seccion'] ?? 'actividad';
$success = ($_GET['ok'] ?? '') === 'foto' ? 'Foto de perfil actualizada correctamente.' : '';

$error   = '';


// ── Traer datos del usuario ──────────────────────────────────────────
$user = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$user->bind_param("i", $user_id);
$user->execute();
$u = $user->get_result()->fetch_assoc();

$foto_actual = !empty($u['foto']) && $u['foto'] !== 'default.png'
    ? 'public/img/perfiles/' . $u['foto']
    : null; // null = usamos inicial

// ── ACTUALIZAR DATOS PERSONALES ──────────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'datos') {
    $nom  = trim($_POST['nombre']    ?? '');
    $ape  = trim($_POST['apellidos'] ?? '');
    $cor  = trim($_POST['correo']    ?? '');

    $chk = $conn->prepare("SELECT id FROM usuarios WHERE correo=? AND id!=?");
    $chk->bind_param("si", $cor, $user_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $error = 'Ese correo ya está en uso por otra cuenta.';
    } else {
        $upd = $conn->prepare("UPDATE usuarios SET nombre=?, apellidos=?, correo=? WHERE id=?");
        $upd->bind_param("sssi", $nom, $ape, $cor, $user_id);
        if ($upd->execute()) {
            $_SESSION['user_nombre'] = $nom;
            $_SESSION['user_correo'] = $cor;
            $u['nombre'] = $nom; $u['apellidos'] = $ape; $u['correo'] = $cor;
            $success = 'Datos actualizados correctamente.';
        } else { $error = 'Error al guardar.'; }
    }
    $seccion = 'editar';
}

// ── GUARDAR PREFERENCIAS ─────────────────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'preferencias') {
    $tel = trim($_POST['telefono']  ?? '');
    $dir = trim($_POST['direccion'] ?? '');
    $bio = trim($_POST['biografia'] ?? '');
    $fb  = trim($_POST['facebook']  ?? '');
    $ig  = trim($_POST['instagram'] ?? '');
    $ciu = trim($_POST['ciudad']    ?? '');

    $upd = $conn->prepare("UPDATE usuarios SET telefono=?, direccion=?, biografia=?, facebook=?, instagram=?, ciudad=? WHERE id=?");
    $upd->bind_param("ssssssi", $tel, $dir, $bio, $fb, $ig, $ciu, $user_id);
    if ($upd->execute()) {
        $u['telefono']=$tel; $u['direccion']=$dir; $u['biografia']=$bio;
        $u['facebook']=$fb;  $u['instagram']=$ig;  $u['ciudad']=$ciu;
        $success = 'Preferencias guardadas.';
    } else { $error = 'Error al guardar.'; }
    $seccion = 'editar';
}

// ── ACTUALIZAR FOTO ──────────────────────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'foto') {
    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            if (!is_dir('public/img/perfiles')) mkdir('public/img/perfiles', 0755, true);
            $fname = 'user_' . $user_id . '_' . time() . '.' . $ext; // timestamp evita caché
            if (move_uploaded_file($_FILES['foto']['tmp_name'], 'public/img/perfiles/' . $fname)) {
                // Borrar foto anterior si existe
                if (!empty($u['foto']) && $u['foto'] !== 'default.png') {
                    @unlink('public/img/perfiles/' . $u['foto']);
                }
                $upd = $conn->prepare("UPDATE usuarios SET foto=? WHERE id=?");
                $upd->bind_param("si", $fname, $user_id);
                $upd->execute();
                // PRG: redirigir para evitar re-POST al refrescar
                header('Location: mi-perfil.php?seccion=config&ok=foto');
                exit;
            } else { $error = 'Error al subir el archivo.'; }
        } else { $error = 'Formato no permitido. Usa jpg, png o gif.'; }
    } else { $error = 'No se recibió ningún archivo.'; }
    $seccion = 'config';
}

// ── ELIMINAR FOTO ────────────────────────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_foto') {
    if (!empty($u['foto']) && $u['foto'] !== 'default.png') {
        @unlink('public/img/perfiles/' . $u['foto']);
    }
    $upd = $conn->prepare("UPDATE usuarios SET foto='default.png' WHERE id=?");
    $upd->bind_param("i", $user_id);
    $upd->execute();
    $u['foto'] = 'default.png'; $foto_actual = null;
    $success = 'Foto eliminada.'; $seccion = 'config';
}

// ── ESTADÍSTICAS ─────────────────────────────────────────────────────
$total_adopciones = $conn->prepare("SELECT COUNT(*) FROM adopciones WHERE usuario_id=?");
$total_adopciones->bind_param("i", $user_id); $total_adopciones->execute();
$num_adopciones = $total_adopciones->get_result()->fetch_row()[0];

$total_donado_stmt = $conn->prepare("SELECT COALESCE(SUM(monto),0) FROM donaciones WHERE id_usuario=?");
$total_donado_stmt->bind_param("i", $user_id); $total_donado_stmt->execute();
$total_donado = $total_donado_stmt->get_result()->fetch_row()[0];

$num_donaciones_stmt = $conn->prepare("SELECT COUNT(*) FROM donaciones WHERE id_usuario=?");
$num_donaciones_stmt->bind_param("i", $user_id); $num_donaciones_stmt->execute();
$num_donaciones = $num_donaciones_stmt->get_result()->fetch_row()[0];

// ── GRÁFICA DONACIONES ──────────────────────────────────────────────
$periodo = $_GET['periodo'] ?? 'mes';
$sql_chart = match($periodo) {
    'semana' => "SELECT YEARWEEK(fecha_donacion) AS p, SUM(monto) AS t FROM donaciones WHERE id_usuario=? GROUP BY p ORDER BY p ASC",
    'dia'    => "SELECT DATE(fecha_donacion) AS p, SUM(monto) AS t FROM donaciones WHERE id_usuario=? GROUP BY p ORDER BY p ASC",
    default  => "SELECT DATE_FORMAT(fecha_donacion,'%Y-%m') AS p, SUM(monto) AS t FROM donaciones WHERE id_usuario=? GROUP BY p ORDER BY p ASC",
};
$stmt_c = $conn->prepare($sql_chart);
$stmt_c->bind_param("i", $user_id); $stmt_c->execute();
$res_c = $stmt_c->get_result();
$chart_labels = []; $chart_data = [];
while ($r = $res_c->fetch_assoc()) { $chart_labels[] = $r['p']; $chart_data[] = $r['t']; }

// ── MIS ADOPCIONES ───────────────────────────────────────────────────
$mis_adopciones = $conn->prepare("SELECT adopciones.*, mascotas.nombre AS mnombre, mascotas.foto_url, mascotas.especie
    FROM adopciones LEFT JOIN mascotas ON adopciones.mascota_id=mascotas.id
    WHERE adopciones.usuario_id=? ORDER BY adopciones.fecha_solicitud DESC");
$mis_adopciones->bind_param("i", $user_id); $mis_adopciones->execute();
$res_adopciones = $mis_adopciones->get_result();

// ── MIS DONACIONES ───────────────────────────────────────────────────
$mis_donaciones = $conn->prepare("SELECT d.*, m.nombre AS mnombre, a.nombre AS anombre
    FROM donaciones d
    LEFT JOIN mascotas m ON d.mascota_id=m.id
    LEFT JOIN albergues a ON d.albergue_id=a.id
    WHERE d.id_usuario=? ORDER BY d.fecha_donacion DESC");
$mis_donaciones->bind_param("i", $user_id); $mis_donaciones->execute();
$res_donaciones = $mis_donaciones->get_result();

// ── INICIAL DEL AVATAR ──────────────────────────────────────────────
$inicial = mb_strtoupper(mb_substr($u['nombre'], 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - ConectaPet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { display:flex; min-height:100vh; font-family:'Inter',sans-serif; background:#f4f6f9; color:#1a1a2e; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 270px; flex-shrink: 0;
            background: linear-gradient(180deg,#1F4A38 0%,#163827 100%);
            display: flex; flex-direction: column;
            position: fixed; top:0; left:0; bottom:0;
            overflow-y: auto; z-index: 100;
        }
        .sb-header { padding:2rem 1.5rem 1.25rem; border-bottom:1px solid rgba(255,255,255,0.1); text-align:center; }
        .sb-avatar {
            width:90px; height:90px; border-radius:50%; margin:0 auto 0.75rem;
            border:3px solid rgba(255,255,255,0.4);
            display:flex; align-items:center; justify-content:center;
            font-size:2.2rem; font-weight:800; color:white;
            font-family:'Outfit',sans-serif; overflow:hidden;
            background:rgba(255,255,255,0.15);
        }
        .sb-avatar img { width:100%; height:100%; object-fit:cover; }
        .sb-name { color:white; font-weight:700; font-size:1rem; margin-bottom:0.2rem; }
        .sb-email { color:rgba(255,255,255,0.55); font-size:0.78rem; word-break:break-all; }

        /* Mini stats en sidebar */
        .sb-stats { display:flex; gap:0.75rem; padding:1rem 1.5rem; border-bottom:1px solid rgba(255,255,255,0.08); }
        .sb-stat { flex:1; text-align:center; background:rgba(255,255,255,0.08); border-radius:8px; padding:0.5rem; }
        .sb-stat strong { display:block; color:#fbbf24; font-family:'Outfit',sans-serif; font-size:1.1rem; font-weight:800; }
        .sb-stat span { color:rgba(255,255,255,0.6); font-size:0.7rem; }

        .sb-nav { padding:1rem 0; flex-grow:1; }
        .sb-section { color:rgba(255,255,255,0.3); font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; padding:0.75rem 1.5rem 0.4rem; }
        .sb-link {
            display:flex; align-items:center; gap:0.75rem;
            padding:0.75rem 1.5rem; color:rgba(255,255,255,0.65);
            text-decoration:none; font-size:0.875rem; font-weight:500; transition:all 0.2s;
        }
        .sb-link i { font-size:1.1rem; width:18px; }
        .sb-link:hover { background:rgba(255,255,255,0.08); color:white; }
        .sb-link.active { background:rgba(255,255,255,0.14); color:white; border-left:3px solid #fbbf24; padding-left:calc(1.5rem - 3px); }
        .sb-link.danger:hover { background:rgba(239,68,68,0.12); color:#f87171; }

        /* ── MAIN ── */
        .main { margin-left:270px; flex:1; display:flex; flex-direction:column; min-height:100vh; }

        /* Topbar */
        .topbar {
            background:white; padding:1rem 2rem;
            border-bottom:1px solid #e5e7eb;
            display:flex; align-items:center; justify-content:space-between;
            position:sticky; top:0; z-index:50;
        }
        .topbar h1 { font-size:1.2rem; color:#1F4A38; font-family:'Outfit',sans-serif; font-weight:700; }
        .topbar a { color:#6b7280; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem; }
        .topbar a:hover { color:#1F4A38; }

        .content { padding:2rem; flex:1; }

        /* Panel / Card */
        .panel { background:white; border-radius:14px; padding:2rem; margin-bottom:1.5rem; border:1px solid #e5e7eb; box-shadow:0 1px 4px rgba(0,0,0,0.05); }
        .panel-title { font-size:1.1rem; font-weight:700; color:#1F4A38; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem; }
        .panel-title i { color:#B56143; }

        /* Formularios */
        .fg { margin-bottom:1.1rem; }
        .fg label { display:block; font-size:0.8rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.4rem; }
        .fg input, .fg textarea, .fg select {
            width:100%; padding:0.8rem 1rem;
            border:1px solid #e5e7eb; border-radius:9px;
            font-family:'Inter',sans-serif; font-size:0.9rem; color:#374151;
            background:#f9fafb; outline:none; transition:border-color 0.2s;
        }
        .fg input:focus, .fg textarea:focus, .fg select:focus { border-color:#1F4A38; background:white; box-shadow:0 0 0 3px rgba(31,74,56,0.08); }
        .fg textarea { resize:vertical; min-height:80px; }
        .fg-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }

        /* Botones */
        .btn-g { display:inline-flex; align-items:center; gap:0.4rem; padding:0.7rem 1.4rem; border-radius:9px; font-family:'Inter',sans-serif; font-size:0.88rem; font-weight:600; cursor:pointer; border:none; transition:all 0.2s; }
        .btn-g-primary { background:#1F4A38; color:white; }
        .btn-g-primary:hover { background:#163827; transform:translateY(-1px); }
        .btn-g-outline { background:transparent; border:1px solid #e5e7eb; color:#374151; }
        .btn-g-outline:hover { border-color:#1F4A38; color:#1F4A38; }
        .btn-g-danger { background:transparent; border:1px solid #fecaca; color:#ef4444; }
        .btn-g-danger:hover { background:#fee2e2; }

        /* Alertas */
        .alert { padding:0.85rem 1.1rem; border-radius:9px; font-size:0.88rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem; }
        .alert-ok  { background:#dcfce7; border:1px solid #bbf7d0; color:#15803d; }
        .alert-err { background:#fee2e2; border:1px solid #fecaca; color:#b91c1c; }

        /* Stat cards */
        .stat-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:1rem; margin-bottom:1.5rem; }
        .sc { background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; padding:1.25rem; text-align:center; }
        .sc .num { font-size:1.9rem; font-weight:800; font-family:'Outfit',sans-serif; color:#1F4A38; }
        .sc .lbl { font-size:0.78rem; color:#9ca3af; font-weight:600; margin-top:0.2rem; }

        /* Badge */
        .bdg { display:inline-block; padding:0.2rem 0.7rem; border-radius:20px; font-size:0.72rem; font-weight:700; }
        .bdg-yellow { background:#fef9c3; color:#a16207; }
        .bdg-green  { background:#dcfce7; color:#15803d; }
        .bdg-red    { background:#fee2e2; color:#b91c1c; }
        .bdg-blue   { background:#dbeafe; color:#1d4ed8; }

        /* Tabla simple */
        .mini-table { width:100%; border-collapse:collapse; font-size:0.88rem; }
        .mini-table th { padding:0.6rem 1rem; background:#f9fafb; text-align:left; color:#9ca3af; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.4px; border-bottom:1px solid #f3f4f6; }
        .mini-table td { padding:0.9rem 1rem; border-bottom:1px solid #f9fafb; color:#374151; }
        .mini-table tr:last-child td { border-bottom:none; }
        .mini-table tr:hover td { background:#fafafa; }

        /* Avatar upload preview */
        .avatar-preview { width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid #e5e7eb; display:block; margin-bottom:1rem; }

        /* Chart selector */
        .chart-controls { display:flex; gap:0.5rem; margin-bottom:1rem; }
        .period-btn { padding:0.45rem 1rem; border-radius:var(--radius-pill, 20px); border:1px solid #e5e7eb; background:white; font-size:0.82rem; font-weight:600; cursor:pointer; color:#6b7280; transition:all 0.2s; font-family:'Inter',sans-serif; }
        .period-btn.active { background:#1F4A38; color:white; border-color:#1F4A38; }

        /* Empty state */
        .empty { text-align:center; padding:3.5rem 0; color:#9ca3af; }
        .empty i { font-size:3rem; display:block; margin-bottom:0.75rem; opacity:0.3; }

        @media(max-width:900px) {
            .sidebar { position:relative; width:100%; height:auto; }
            .main { margin-left:0; }
            .fg-row { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

<!-- ═══════════ SIDEBAR ═══════════ -->
<div class="sidebar">
    <div class="sb-header">
        <div class="sb-avatar">
            <?php if ($foto_actual): ?>
                <img src="<?php echo htmlspecialchars($foto_actual); ?>?t=<?php echo time(); ?>" alt="foto">
            <?php else: ?>
                <?php echo $inicial; ?>
            <?php endif; ?>
        </div>
        <div class="sb-name"><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']); ?></div>
        <div class="sb-email"><?php echo htmlspecialchars($u['correo']); ?></div>
    </div>

    <div class="sb-stats">
        <div class="sb-stat"><strong><?php echo $num_adopciones; ?></strong><span>Solicitudes</span></div>
        <div class="sb-stat"><strong><?php echo $num_donaciones; ?></strong><span>Donaciones</span></div>
        <div class="sb-stat"><strong>S/<?php echo number_format($total_donado, 0); ?></strong><span>Donado</span></div>
    </div>

    <nav class="sb-nav">
        <div class="sb-section">Mi cuenta</div>
        <a href="?seccion=actividad"  class="sb-link <?php echo $seccion==='actividad'  ? 'active':''; ?>"><i class="ri-bar-chart-fill"></i> Mi Actividad</a>
        <a href="?seccion=adopciones" class="sb-link <?php echo $seccion==='adopciones' ? 'active':''; ?>"><i class="ri-file-list-3-fill"></i> Mis Adopciones</a>
        <a href="?seccion=donaciones" class="sb-link <?php echo $seccion==='donaciones' ? 'active':''; ?>"><i class="ri-hand-coin-fill"></i> Mis Donaciones</a>
        <div class="sb-section">Configuración</div>
        <a href="?seccion=editar"     class="sb-link <?php echo $seccion==='editar'     ? 'active':''; ?>"><i class="ri-user-settings-fill"></i> Editar Perfil</a>
        <a href="?seccion=config"     class="sb-link <?php echo $seccion==='config'     ? 'active':''; ?>"><i class="ri-image-edit-fill"></i> Foto de Perfil</a>
        <div class="sb-section">Sitio</div>
        <a href="albergues.php" class="sb-link"><i class="ri-search-eye-fill"></i> Ver Mascotas</a>
        <a href="index.php"     class="sb-link"><i class="ri-home-2-fill"></i> Inicio</a>
        <a href="logout-user.php" class="sb-link danger"><i class="ri-logout-box-r-fill"></i> Cerrar Sesión</a>
    </nav>
</div>

<!-- ═══════════ MAIN ═══════════ -->
<div class="main">
    <!-- Topbar -->
    <div class="topbar">
        <h1>
            <i class="ri-user-3-fill" style="color:#B56143;"></i>
            <?php
            $titulos = ['actividad'=>'Mi Actividad', 'adopciones'=>'Mis Solicitudes de Adopción', 'donaciones'=>'Mis Donaciones', 'editar'=>'Editar Perfil', 'config'=>'Foto de Perfil'];
            echo $titulos[$seccion] ?? 'Mi Perfil';
            ?>
        </h1>
        <a href="index.php"><i class="ri-arrow-left-line"></i> Volver al sitio</a>
    </div>

    <div class="content">

        <?php if ($success): ?><div class="alert alert-ok"><i class="ri-checkbox-circle-fill"></i> <?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-err"><i class="ri-error-warning-fill"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <?php /* ════════════════════════ MI ACTIVIDAD ════════════════════════ */ ?>
        <?php if ($seccion === 'actividad'): ?>

        <div class="stat-cards">
            <div class="sc"><div class="num"><?php echo $num_adopciones; ?></div><div class="lbl">Solicitudes de adopción</div></div>
            <div class="sc"><div class="num"><?php echo $num_donaciones; ?></div><div class="lbl">Donaciones realizadas</div></div>
            <div class="sc"><div class="num" style="font-size:1.5rem;">S/ <?php echo number_format($total_donado, 2); ?></div><div class="lbl">Total donado</div></div>
        </div>

        <div class="panel">
            <div class="panel-title"><i class="ri-line-chart-fill"></i> Historial de Donaciones</div>
            <div class="chart-controls">
                <?php foreach (['dia'=>'Por Día','semana'=>'Por Semana','mes'=>'Por Mes'] as $k=>$lbl): ?>
                <a href="?seccion=actividad&periodo=<?php echo $k; ?>" class="period-btn <?php echo $periodo===$k?'active':''; ?>"><?php echo $lbl; ?></a>
                <?php endforeach; ?>
            </div>
            <?php if (empty($chart_labels)): ?>
            <div class="empty"><i class="ri-bar-chart-2-line"></i><p>Aún no tienes donaciones registradas.</p></div>
            <?php else: ?>
            <canvas id="grafico" height="120"></canvas>
            <?php endif; ?>
        </div>

        <?php /* ════════════════════════ MIS ADOPCIONES ════════════════════════ */ ?>
        <?php elseif ($seccion === 'adopciones'): ?>

        <div class="panel" style="padding:0; overflow:hidden;">
            <?php if ($res_adopciones->num_rows > 0): ?>
            <table class="mini-table">
                <thead><tr><th>Mascota</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
                <tbody>
                <?php while ($ad = $res_adopciones->fetch_assoc()):
                    $est = $ad['estado'] ?? 'En Revisión';
                    $bc = match($est) { 'Aprobado'=>'bdg-green','Rechazado'=>'bdg-red','En Proceso'=>'bdg-blue',default=>'bdg-yellow' };
                ?>
                <tr>
                    <td style="display:flex; align-items:center; gap:0.75rem;">
                        <?php if (!empty($ad['foto_url'])): ?>
                            <img src="<?php echo htmlspecialchars($ad['foto_url']); ?>" style="width:44px;height:44px;border-radius:8px;object-fit:cover;">
                        <?php else: ?>
                            <div style="width:44px;height:44px;border-radius:8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:1.4rem;"><?php echo $ad['especie']==='Gato'?'🐱':'🐶'; ?></div>
                        <?php endif; ?>
                        <div>
                            <strong><?php echo htmlspecialchars($ad['mnombre'] ?? '—'); ?></strong>
                            <div style="font-size:0.78rem;color:#9ca3af;"><?php echo htmlspecialchars($ad['especie'] ?? ''); ?></div>
                        </div>
                    </td>
                    <td><span class="bdg <?php echo $bc; ?>"><?php echo htmlspecialchars($est); ?></span></td>
                    <td style="color:#9ca3af;font-size:0.82rem;"><?php echo date('d/m/Y', strtotime($ad['fecha_solicitud'])); ?></td>
                    <td><a href="mascota-perfil.php?id=<?php echo $ad['mascota_id']; ?>" class="btn-g btn-g-outline" style="font-size:0.78rem;padding:0.4rem 0.8rem;">Ver</a></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty"><i class="ri-file-list-3-line"></i><p>No has enviado solicitudes de adopción aún.</p>
                <a href="albergues.php" class="btn-g btn-g-primary" style="margin-top:1rem;display:inline-flex;">Buscar mascotas</a></div>
            <?php endif; ?>
        </div>

        <?php /* ════════════════════════ MIS DONACIONES ════════════════════════ */ ?>
        <?php elseif ($seccion === 'donaciones'): ?>

        <div class="panel" style="padding:0; overflow:hidden;">
            <?php if ($res_donaciones->num_rows > 0): ?>
            <table class="mini-table">
                <thead><tr><th>Destino</th><th>Tipo</th><th>Monto</th><th>Método</th><th>Fecha</th></tr></thead>
                <tbody>
                <?php while ($d = $res_donaciones->fetch_assoc()):
                    $dest = $d['mnombre'] ?: ($d['anombre'] ?: ucfirst($d['tipo']));
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($dest); ?></strong></td>
                    <td><span class="bdg bdg-blue"><?php echo htmlspecialchars(ucfirst($d['tipo'])); ?></span></td>
                    <td><strong style="color:#15803d;">S/ <?php echo number_format($d['monto'],2); ?></strong></td>
                    <td><?php echo htmlspecialchars($d['metodo_pago']); ?></td>
                    <td style="color:#9ca3af;font-size:0.82rem;"><?php echo date('d/m/Y H:i', strtotime($d['fecha_donacion'])); ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty"><i class="ri-hand-coin-line"></i><p>No has realizado donaciones aún.</p>
                <a href="donaciones.php" class="btn-g btn-g-primary" style="margin-top:1rem;display:inline-flex;">Hacer una donación</a></div>
            <?php endif; ?>
        </div>

        <?php /* ════════════════════════ EDITAR PERFIL ════════════════════════ */ ?>
        <?php elseif ($seccion === 'editar'): ?>

        <div class="panel">
            <div class="panel-title"><i class="ri-user-fill"></i> Datos Personales</div>
            <form method="POST" action="?seccion=editar">
                <input type="hidden" name="accion" value="datos">
                <div class="fg-row">
                    <div class="fg"><label>Nombre *</label><input type="text" name="nombre" value="<?php echo htmlspecialchars($u['nombre']); ?>" required></div>
                    <div class="fg"><label>Apellidos *</label><input type="text" name="apellidos" value="<?php echo htmlspecialchars($u['apellidos']); ?>" required></div>
                </div>
                <div class="fg"><label>Correo Electrónico *</label><input type="email" name="correo" value="<?php echo htmlspecialchars($u['correo']); ?>" required></div>
                <button type="submit" class="btn-g btn-g-primary"><i class="ri-save-fill"></i> Guardar Datos</button>
            </form>
        </div>

        <div class="panel">
            <div class="panel-title"><i class="ri-settings-3-fill"></i> Preferencias y Contacto</div>
            <form method="POST" action="?seccion=editar">
                <input type="hidden" name="accion" value="preferencias">
                <div class="fg-row">
                    <div class="fg"><label>Teléfono</label><input type="tel" name="telefono" placeholder="9XX XXX XXX" value="<?php echo htmlspecialchars($u['telefono']??''); ?>"></div>
                    <div class="fg"><label>Ciudad</label><input type="text" name="ciudad" placeholder="Lima, Arequipa..." value="<?php echo htmlspecialchars($u['ciudad']??''); ?>"></div>
                </div>
                <div class="fg"><label>Dirección</label><input type="text" name="direccion" placeholder="Calle y número" value="<?php echo htmlspecialchars($u['direccion']??''); ?>"></div>
                <div class="fg"><label>Biografía / Sobre mí</label><textarea name="biografia" placeholder="Cuéntanos un poco sobre ti..."><?php echo htmlspecialchars($u['biografia']??''); ?></textarea></div>
                <div class="fg-row">
                    <div class="fg"><label><i class="ri-facebook-fill"></i> Facebook</label><input type="text" name="facebook" placeholder="@usuario" value="<?php echo htmlspecialchars($u['facebook']??''); ?>"></div>
                    <div class="fg"><label><i class="ri-instagram-fill"></i> Instagram</label><input type="text" name="instagram" placeholder="@usuario" value="<?php echo htmlspecialchars($u['instagram']??''); ?>"></div>
                </div>
                <button type="submit" class="btn-g btn-g-primary"><i class="ri-save-fill"></i> Guardar Preferencias</button>
            </form>
        </div>

        <?php /* ════════════════════════ FOTO DE PERFIL ════════════════════════ */ ?>
        <?php elseif ($seccion === 'config'): ?>

        <div class="panel" style="max-width:480px;">
            <div class="panel-title"><i class="ri-image-edit-fill"></i> Foto de Perfil</div>
            <!-- Preview actual -->
            <div style="margin-bottom:1.5rem; text-align:center;">
                <?php if ($foto_actual): ?>
                    <img src="<?php echo htmlspecialchars($foto_actual); ?>?t=<?php echo time(); ?>" class="avatar-preview" style="width:100px;height:100px;margin:0 auto;">
                <?php else: ?>
                    <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#1F4A38,#2e6e52);display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:800;color:white;font-family:'Outfit',sans-serif;margin:0 auto;"><?php echo $inicial; ?></div>
                <?php endif; ?>
            </div>
            <form method="POST" action="?seccion=config" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="foto">
                <div class="fg"><label>Nueva foto (jpg, png, gif, webp)</label>
                    <input type="file" name="foto" accept="image/*" onchange="previewFoto(this)" required style="background:white;">
                </div>
                <img id="foto-preview" src="" style="display:none; width:80px; height:80px; border-radius:50%; object-fit:cover; margin-bottom:1rem; border:2px solid #1F4A38;">
                <button type="submit" class="btn-g btn-g-primary"><i class="ri-upload-cloud-fill"></i> Subir Foto</button>
            </form>
            <?php if ($foto_actual): ?>
            <form method="POST" action="?seccion=config" style="margin-top:1rem;">
                <input type="hidden" name="accion" value="eliminar_foto">
                <button type="submit" class="btn-g btn-g-danger"><i class="ri-delete-bin-fill"></i> Eliminar foto actual</button>
            </form>
            <?php endif; ?>
        </div>

        <?php endif; ?>

    </div><!-- end content -->

    <div style="padding:1rem 2rem; border-top:1px solid #e5e7eb; background:white; font-size:0.8rem; color:#9ca3af;">
        &copy; 2026 ConectaPet &mdash; Sesión activa como <strong><?php echo htmlspecialchars($u['nombre']); ?></strong>
    </div>
</div><!-- end main -->

<script>
<?php if ($seccion === 'actividad' && !empty($chart_labels)): ?>
const ctx = document.getElementById('grafico').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Donaciones (S/)',
            data: <?php echo json_encode($chart_data); ?>,
            backgroundColor: 'rgba(31,74,56,0.12)',
            borderColor: '#1F4A38',
            borderWidth: 2.5,
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#B56143',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'S/ ' + v } } }
    }
});
<?php endif; ?>

function previewFoto(input) {
    const preview = document.getElementById('foto-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
