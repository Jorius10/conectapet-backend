<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConectaPet - Adopción Responsable</title>
    <meta name="description" content="Plataforma de adopción responsable. Conecta con albergues, adopta mascotas, dona y mantente informado.">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
<?php $active_nav = 'inicio'; include 'header.php'; ?>

    <main id="inicio">
        <section class="hero">
            <div class="hero-background"></div>
            <div class="container hero-container">
                <div class="hero-text">
                    <span class="badge" data-i18n="hero_badge">Adopta, no compres</span>
                    <h1>Encuentra a tu <span>mejor amigo</span></h1>
                    <p data-i18n="hero_desc">Nuestra plataforma conecta albergues responsables con familias amorosas. Descubre mascotas esperando un hogar, realiza donaciones y sé parte del cambio.</p>
                    <div class="hero-actions">
                        <a href="albergues.php" class="btn btn-primary btn-large" data-i18n="hero_btn1">Ver Albergues</a>
                        <a href="donaciones.php" class="btn btn-secondary btn-large" data-i18n="hero_btn2">Donar <i class="ri-heart-3-fill" style="margin-left: 5px;"></i></a>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <h3 data-i18n="hero_stat1_n">+500</h3>
                            <p data-i18n="hero_stat1_l">Mascotas Adoptadas</p>
                        </div>
                        <div class="stat-item">
                            <h3 data-i18n="hero_stat2_n">24</h3>
                            <p data-i18n="hero_stat2_l">Albergues Aliados</p>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="glass-card main-glass">
                        <div class="glass-img-placeholder">
                            <img src="imagen-principal.jpg" alt="Mascota de la portada">
                        </div>
                        <div class="floating-badge fb-1">
                            <i class="ri-shield-check-fill"></i> Albergues Verificados
                        </div>
                        <div class="floating-badge fb-2">
                            <i class="ri-heart-pulse-fill"></i> Apoyo Médico
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="brand-col">
                    <a href="index.php" class="logo">
                        <div class="logo-bubble">
                            <img src="logo.png" alt="ConectaPet Logo">
                        </div>
                        <div class="logo-text">Conecta<span>Pet</span></div>
                    </a>
                    <p>Haciendo la diferencia, una patita a la vez. Únete a la red más grande de adopción éticamente responsable.</p>
                </div>
                <div class="footer-col">
                    <h4 data-i18n="footer_links">Enlaces</h4>
                    <ul>
                        <li><a href="index.php" data-i18n="nav_inicio">Inicio</a></li>
                        <li><a href="albergues.php" data-i18n="nav_albergues">Albergues</a></li>
                        <li><a href="donaciones.php" data-i18n="nav_donaciones">Donaciones</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4 data-i18n="footer_legal">Legales</h4>
                    <ul>
                        <li><a href="terminos.php" data-i18n="footer_terms">Términos y Condiciones</a></li>
                        <li><a href="privacidad.php" data-i18n="footer_privacy">Privacidad</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4 data-i18n="footer_news">Boletín</h4>
                    <p data-i18n="footer_news_sub">Recibe noticias de eventos de adopción</p>
                    <form class="newsletter-form" action="suscribir.php" method="POST">
                        <input type="hidden" name="redir" value="index.php">
                        <input type="email" name="correo" placeholder="Tu correo electrónico" required>
                        <button class="btn btn-primary" type="submit" data-i18n="footer_join">Unirme</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom" data-i18n="footer_copy">
                &copy; 2026 ConectaPet. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <script src="script.js"></script>

    <?php if (!empty($_SESSION['boletin_msg'])): ?>
    <div id="toast-boletin" style="
        position:fixed; bottom:2rem; right:2rem; z-index:9999;
        background:<?php echo $_SESSION['boletin_tipo']==='error' ? '#ef4444' : '#1F4A38'; ?>;
        color:white; padding:1rem 1.5rem; border-radius:12px;
        box-shadow:0 8px 24px rgba(0,0,0,0.2); font-weight:600;
        max-width:340px; display:flex; align-items:center; gap:0.75rem;
        animation: slideIn 0.3s ease;
    ">
        <?php echo $_SESSION['boletin_tipo']==='error' ? '❌' : '✅'; ?>
        <?php echo htmlspecialchars($_SESSION['boletin_msg']); unset($_SESSION['boletin_msg'], $_SESSION['boletin_tipo']); ?>
    </div>
    <style>@keyframes slideIn { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }</style>
    <script>setTimeout(() => { const t = document.getElementById('toast-boletin'); if(t) t.style.display='none'; }, 4000);</script>
    <?php endif; ?>
</body>
</html>
