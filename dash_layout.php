<?php
// layout/dash_layout.php — incluir en todas las páginas del dashboard
// Uso: include 'dash_layout.php'; al inicio, luego el contenido, luego cierre

// Verificar sesión activa
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_nombre = $_SESSION['admin_nombre'] ?? 'Admin';
$admin_rol    = $_SESSION['admin_rol']    ?? 'albergue';

// Detectar página activa para el menú
$pagina_actual = basename($_SERVER['PHP_SELF']);

function dash_menu_item($icon, $label, $href, $actual) {
    $activo = (basename($href) === $actual) ? 'style="background:rgba(255,255,255,0.12); color:white;"' : '';
    echo "<a href='$href' class='dash-nav-link' $activo><i class='$icon'></i><span>$label</span></a>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ConectaPet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { display:flex; min-height:100vh; font-family:'Inter',sans-serif; background:#f4f6f9; color:#1a1a2e; }

        /* ── SIDEBAR ── */
        .dash-sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1F4A38 0%, #163827 100%);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            overflow-y: auto;
        }
        .dash-logo {
            display: flex; align-items: center; gap: 0.8rem;
            padding: 1.75rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08);
            text-decoration: none;
        }
        .dash-logo-icon { font-size: 1.5rem; }
        .dash-logo-text { font-family:'Outfit',sans-serif; font-size:1.4rem; font-weight:800; color:white; }
        .dash-logo-text span { color: #B56143; }

        .dash-admin-info {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .dash-admin-info .avatar {
            width: 40px; height: 40px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: white;
            margin-bottom: 0.5rem;
        }
        .dash-admin-info .name { color: white; font-weight: 600; font-size: 0.9rem; }
        .dash-admin-info .role { color: rgba(255,255,255,0.5); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }

        .dash-nav { padding: 1rem 0; flex-grow: 1; }
        .dash-nav-section { color: rgba(255,255,255,0.35); font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 1rem 1.5rem 0.5rem; }
        .dash-nav-link {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.8rem 1.5rem;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            border-radius: 0;
        }
        .dash-nav-link:hover { background: rgba(255,255,255,0.08); color: white; }
        .dash-nav-link i { font-size: 1.15rem; width: 20px; }

        .dash-nav-link.danger { color: rgba(239,68,68,0.7); }
        .dash-nav-link.danger:hover { background: rgba(239,68,68,0.1); color: #f87171; }

        /* ── MAIN CONTENT ── */
        .dash-main {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        /* Top bar */
        .dash-topbar {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .dash-topbar h1 { font-size: 1.3rem; color: #1F4A38; font-family: 'Outfit', sans-serif; font-weight: 700; }
        .dash-topbar-actions { display:flex; align-items:center; gap:1rem; }
        .dash-topbar .view-site { display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1rem; border-radius:20px; background:#f3f4f6; color:#6b7280; font-size:0.85rem; text-decoration:none; transition:all 0.2s; font-weight:500; }
        .dash-topbar .view-site:hover { background:#1F4A38; color:white; }

        /* Área de contenido */
        .dash-content { padding: 2rem; flex: 1; }

        /* Stat cards */
        .stat-card { background:white; border-radius:12px; padding:1.5rem; border:1px solid #e5e7eb; display:flex; flex-direction:column; gap:0.75rem; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
        .stat-card .stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; }
        .stat-card .stat-num { font-size:2rem; font-weight:800; font-family:'Outfit',sans-serif; color:#1F4A38; }
        .stat-card .stat-label { font-size:0.85rem; color:#6b7280; font-weight:500; }
        .stat-card .stat-trend { font-size:0.8rem; font-weight:600; }

        /* Tables */
        .dash-table-wrap { background:white; border-radius:12px; border:1px solid #e5e7eb; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
        .dash-table-head { padding:1.25rem 1.5rem; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f3f4f6; }
        .dash-table-head h3 { font-size:1rem; color:#1F4A38; font-weight:700; }
        table.dash-table { width:100%; border-collapse:collapse; }
        table.dash-table th { padding:0.75rem 1.25rem; font-size:0.75rem; color:#9ca3af; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; text-align:left; background:#f9fafb; border-bottom:1px solid #f3f4f6; }
        table.dash-table td { padding:1rem 1.25rem; font-size:0.9rem; color:#374151; border-bottom:1px solid #f9fafb; }
        table.dash-table tr:last-child td { border-bottom:none; }
        table.dash-table tr:hover td { background:#fafafa; }

        /* Badges */
        .badge { display:inline-block; padding:0.25rem 0.75rem; border-radius:20px; font-size:0.75rem; font-weight:700; }
        .badge-green  { background:#dcfce7; color:#15803d; }
        .badge-blue   { background:#dbeafe; color:#1d4ed8; }
        .badge-yellow { background:#fef9c3; color:#a16207; }
        .badge-red    { background:#fee2e2; color:#b91c1c; }
        .badge-gray   { background:#f3f4f6; color:#6b7280; }

        /* Botones acción */
        .btn-dash { display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1.1rem; border-radius:8px; font-size:0.85rem; font-weight:600; cursor:pointer; text-decoration:none; border:none; transition:all 0.2s; font-family:'Inter',sans-serif; }
        .btn-dash-primary { background:#1F4A38; color:white; }
        .btn-dash-primary:hover { background:#163827; }
        .btn-dash-outline { background:transparent; border:1px solid #e5e7eb; color:#374151; }
        .btn-dash-outline:hover { border-color:#1F4A38; color:#1F4A38; }
        .btn-dash-danger { background:transparent; border:1px solid #fecaca; color:#ef4444; }
        .btn-dash-danger:hover { background:#fee2e2; }

        /* Grid utilidades */
        .dash-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:1.5rem; margin-bottom:2rem; }
        .dash-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
        @media(max-width:1200px) { .dash-grid-4 { grid-template-columns:repeat(2,1fr); } }
        @media(max-width:900px)  { .dash-grid-2 { grid-template-columns:1fr; } .dash-sidebar { width:220px; } .dash-main { margin-left:220px; } }
    </style>
</head>
<body>

<!-- ═══════════ SIDEBAR ═══════════ -->
<aside class="dash-sidebar">
    <a href="dashboard.php" class="dash-logo">
        <span class="dash-logo-icon">🐾</span>
        <span class="dash-logo-text">Conecta<span>Pet</span></span>
    </a>

    <div class="dash-admin-info">
        <div class="avatar"><i class="ri-user-3-fill"></i></div>
        <div class="name"><?php echo htmlspecialchars($admin_nombre); ?></div>
        <div class="role"><?php echo $admin_rol === 'superadmin' ? '⭐ Super Admin' : 'Admin de Albergue'; ?></div>
    </div>

    <nav class="dash-nav">
        <div class="dash-nav-section">Principal</div>
        <?php dash_menu_item('ri-dashboard-3-fill', 'Resumen', 'dashboard.php', $pagina_actual); ?>
        <?php dash_menu_item('ri-home-heart-fill', 'Mascotas', 'dash-mascotas.php', $pagina_actual); ?>
        <?php dash_menu_item('ri-file-list-3-fill', 'Solicitudes Adopción', 'dash-adopciones.php', $pagina_actual); ?>

        <div class="dash-nav-section">Finanzas</div>
        <?php dash_menu_item('ri-hand-coin-fill', 'Donaciones', 'dash-donaciones.php', $pagina_actual); ?>

        <div class="dash-nav-section">Contenido</div>
        <?php dash_menu_item('ri-newspaper-fill', 'Noticias', 'dash-noticias.php', $pagina_actual); ?>
        <?php if ($admin_rol === 'superadmin'): ?>
        <?php dash_menu_item('ri-hospital-fill', 'Albergues', 'dash-albergues.php', $pagina_actual); ?>
        <?php endif; ?>

        <div class="dash-nav-section">Cuenta</div>
        <a href="index.php" class="dash-nav-link" target="_blank"><i class="ri-external-link-fill"></i><span>Ver Sitio Web</span></a>
        <a href="logout.php" class="dash-nav-link danger"><i class="ri-logout-box-r-fill"></i><span>Cerrar Sesión</span></a>
    </nav>
</aside>

<!-- ═══════════ MAIN ═══════════ -->
<div class="dash-main">
