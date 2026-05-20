<?php
/**
 * header.php — Header compartido para todas las páginas públicas de ConectaPet.
 * Variables opcionales antes de incluir:
 *   $active_nav → 'inicio' | 'albergues' | 'donaciones' | 'noticias'
 */
if (session_status() === PHP_SESSION_NONE) session_start();

$_active      = $active_nav ?? '';
$_loggedUser  = isset($_SESSION['user_id']);
$_loggedAdmin = isset($_SESSION['admin_id']);
$_userName    = $_SESSION['user_nombre'] ?? '';
$_userInicial = mb_strtoupper(mb_substr($_userName, 0, 1));
?>
<style>
/* ── Selector de idioma ─────────────────────────── */
.lang-switcher {
    display: flex;
    align-items: center;
    gap: 4px;
    background: var(--c-surface, #fff);
    border: 1px solid var(--c-border, #e5e7eb);
    border-radius: 20px;
    padding: 3px 6px;
    margin-right: 0.5rem;
}
.lang-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 14px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--c-text-muted, #6b7280);
    font-family: 'Inter', sans-serif;
    transition: all 0.2s;
    letter-spacing: 0.3px;
    line-height: 1;
}
.lang-btn:hover { background: var(--c-bg, #f9fafb); color: var(--c-primary, #1F4A38); }
.lang-btn.lang-active {
    background: var(--c-primary, #1F4A38);
    color: white;
}
.lang-flag { font-size: 1rem; line-height: 1; }
.lang-divider { width: 1px; height: 14px; background: var(--c-border, #e5e7eb); }
</style>

<header class="header">
    <div class="container header-content">
        <a href="index.php" class="logo">
            <div class="logo-bubble"><img src="logo.png" alt="ConectaPet Logo"></div>
            <div class="logo-text">Conecta<span>Pet</span></div>
        </a>

        <nav class="nav-links">
            <a href="index.php"      <?php echo $_active==='inicio'     ? 'class="active"':''; ?> data-i18n="nav_inicio">Inicio</a>
            <a href="albergues.php"  <?php echo $_active==='albergues'  ? 'class="active"':''; ?> data-i18n="nav_albergues">Albergues</a>
            <a href="donaciones.php" <?php echo $_active==='donaciones' ? 'class="active"':''; ?> data-i18n="nav_donaciones">Donaciones</a>
            <a href="noticias.php"   <?php echo $_active==='noticias'   ? 'class="active"':''; ?> data-i18n="nav_noticias">Noticias</a>
        </nav>

        <div class="auth-buttons" style="display:flex; align-items:center; gap:0.5rem;">

            <!-- Selector de idioma -->
            <div class="lang-switcher" title="Idioma / Language">
                <button class="lang-btn" data-lang="es" aria-label="Español">
                    <span class="lang-flag">🇵🇪</span> ES
                </button>
                <div class="lang-divider"></div>
                <button class="lang-btn" data-lang="en" aria-label="English">
                    <span class="lang-flag">🇺🇸</span> EN
                </button>
            </div>

            <?php if ($_loggedAdmin): ?>
                <a href="dashboard.php" class="btn btn-outline" data-i18n="auth_dashboard">Dashboard</a>
                <a href="logout.php" class="btn btn-outline" style="padding:0.5rem 0.9rem;" title="Cerrar sesión">
                    <i class="ri-logout-box-r-line"></i>
                </a>

            <?php elseif ($_loggedUser): ?>
                <a href="mi-perfil.php" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;">
                    <span style="width:24px;height:24px;border-radius:50%;background:rgba(255,255,255,0.25);display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;">
                        <?php echo htmlspecialchars($_userInicial); ?>
                    </span>
                    <?php echo htmlspecialchars($_userName); ?>
                </a>
                <a href="logout-user.php" class="btn btn-outline" style="padding:0.5rem 0.9rem;" title="Cerrar sesión">
                    <i class="ri-logout-box-r-line"></i>
                </a>

            <?php else: ?>
                <a href="login.php" class="btn btn-outline" data-i18n="auth_login">
                    <i class="ri-login-box-line"></i> Iniciar Sesión
                </a>
                <a href="login.php?tab=registro" class="btn btn-primary" data-i18n="auth_register">
                    <i class="ri-user-add-line"></i> Registrarse
                </a>
            <?php endif; ?>
        </div>

        <button class="menu-toggle">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<!-- i18n script (carga una sola vez aunque se incluya varias veces) -->
<?php if (!defined('I18N_LOADED')): define('I18N_LOADED', true); ?>
<script src="i18n.js"></script>
<?php endif; ?>
