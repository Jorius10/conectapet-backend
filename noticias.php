<?php session_start(); require_once 'conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticias - ConectaPet</title>
    <meta name="description" content="Últimas noticias, eventos de adopción, consejos veterinarios y todo lo que pasa en el mundo ConectaPet.">
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .cat-btn { display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1.2rem; border-radius:var(--radius-pill); border:1px solid var(--c-border); background:var(--c-surface); color:var(--c-text-muted); font-weight:600; font-size:0.9rem; text-decoration:none; transition:all 0.2s; }
        .cat-btn:hover, .cat-btn.active { background:var(--c-primary); color:white; border-color:var(--c-primary); }
        .news-card { background:var(--c-surface); border-radius:var(--radius-lg); overflow:hidden; border:1px solid var(--c-border); box-shadow:var(--shadow-sm); transition:all 0.25s; display:flex; flex-direction:column; }
        .news-card:hover { transform:translateY(-6px); box-shadow:var(--shadow-lg); }
        .news-card-img { height:220px; overflow:hidden; position:relative; }
        .news-card-img img { width:100%; height:100%; object-fit:cover; transition:transform 0.3s; }
        .news-card:hover .news-card-img img { transform:scale(1.05); }
        .news-card-body { padding:1.8rem; flex-grow:1; display:flex; flex-direction:column; }
        .news-badge { display:inline-block; padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.8rem; }
        .news-card h3 { font-size:1.15rem; color:var(--c-primary); margin-bottom:0.6rem; line-height:1.4; }
        .news-card p { color:var(--c-text-muted); font-size:0.9rem; line-height:1.6; flex-grow:1; }
        .news-meta { display:flex; gap:1rem; font-size:0.8rem; color:var(--c-text-muted); margin-top:1rem; padding-top:1rem; border-top:1px solid var(--c-border); flex-wrap:wrap; }
        .news-meta i { color:var(--c-accent); }
        .read-more { margin-top:1.2rem; color:var(--c-primary); font-weight:700; font-size:0.9rem; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; transition:gap 0.2s; }
        .read-more:hover { gap:0.8rem; }
        .news-featured { border-radius:var(--radius-lg); overflow:hidden; position:relative; min-height:460px; display:flex; align-items:flex-end; box-shadow:var(--shadow-lg); }
        .news-featured-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.1) 60%); }
        .news-featured-content { position:relative; z-index:1; padding:3rem; color:white; }
        .news-featured-content h2 { font-size:2rem; line-height:1.3; margin-bottom:1rem; }
        .news-featured-content p { opacity:0.85; max-width:600px; line-height:1.7; margin-bottom:1.5rem; }
        .cat-Eventos   { background:rgba(99,102,241,0.15); color:#6366f1; }
        .cat-Alianzas  { background:rgba(16,185,129,0.15); color:#10b981; }
        .cat-SaludAnimal { background:rgba(239,68,68,0.15); color:#ef4444; }
        .cat-Consejos  { background:rgba(245,158,11,0.15); color:#f59e0b; }
        .cat-General   { background:rgba(31,74,56,0.1); color:var(--c-primary); }
    </style>
</head>
<body>
<?php $active_nav = 'noticias'; include 'header.php'; ?>

    <main style="padding-top:120px; padding-bottom:5rem;">
        <?php
        $cat_activa = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';

        // Noticia destacada
        $noticia_dest = $conn->query("SELECT * FROM noticias WHERE destacada = 1 ORDER BY fecha_publicacion DESC LIMIT 1")->fetch_assoc();

        // Categorías para filtros
        $res_cats = $conn->query("SELECT DISTINCT categoria FROM noticias ORDER BY categoria ASC");
        $categorias = [];
        while ($c = $res_cats->fetch_assoc()) $categorias[] = $c['categoria'];

        // Grid de noticias
        $where_cat = $cat_activa ? "AND categoria = '" . $conn->real_escape_string($cat_activa) . "'" : '';
        $dest_id   = ($noticia_dest && !$cat_activa) ? "AND id != " . $noticia_dest['id'] : '';
        $res_noticias = $conn->query("SELECT * FROM noticias WHERE 1=1 $where_cat $dest_id ORDER BY fecha_publicacion DESC");
        ?>

        <!-- NOTICIA DESTACADA -->
        <?php if ($noticia_dest && !$cat_activa): ?>
        <div class="container" style="margin-bottom:3rem;">
            <div class="news-featured">
                <div style="position:absolute; inset:0;">
                    <?php if (!empty($noticia_dest['imagen_url'])): ?>
                        <img src="<?php echo htmlspecialchars($noticia_dest['imagen_url']); ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
                    <?php else: ?>
                        <div style="position:absolute; inset:0; background:linear-gradient(135deg, #1F4A38, #2e6e52); display:flex; align-items:center; justify-content:center;">
                            <i class="ri-newspaper-fill" style="font-size:10rem; color:rgba(255,255,255,0.08);"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="news-featured-overlay"></div>
                <div class="news-featured-content">
                    <span style="background:rgba(255,255,255,0.2); color:white; padding:0.3rem 1rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:700; display:inline-block; margin-bottom:1rem;">
                        <i class="ri-star-fill"></i> DESTACADA · <?php echo htmlspecialchars($noticia_dest['categoria']); ?>
                    </span>
                    <h2><?php echo htmlspecialchars($noticia_dest['titulo']); ?></h2>
                    <p><?php echo htmlspecialchars($noticia_dest['resumen']); ?></p>
                    <div style="display:flex; align-items:center; gap:2rem; flex-wrap:wrap;">
                        <a href="noticia.php?id=<?php echo $noticia_dest['id']; ?>" class="btn btn-white btn-large">
                            <i class="ri-book-open-fill"></i> Leer artículo completo
                        </a>
                        <span style="opacity:0.75; font-size:0.9rem;">
                            <i class="ri-user-3-line"></i> <?php echo htmlspecialchars($noticia_dest['autor']); ?> &nbsp;·&nbsp;
                            <i class="ri-calendar-line"></i> <?php echo date('d M Y', strtotime($noticia_dest['fecha_publicacion'])); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="container">
            <!-- FILTROS -->
            <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; margin-bottom:2.5rem;">
                <span style="font-weight:700; color:var(--c-primary); margin-right:0.5rem;">Filtrar:</span>
                <a href="noticias.php" class="cat-btn <?php echo !$cat_activa ? 'active' : ''; ?>">
                    <i class="ri-apps-2-line"></i> Todas
                </a>
                <?php
                $icons_cat = ['Eventos'=>'ri-calendar-event-fill','Alianzas'=>'ri-handshake-fill','Salud Animal'=>'ri-heart-pulse-fill','Consejos'=>'ri-lightbulb-fill','General'=>'ri-newspaper-fill'];
                foreach ($categorias as $cat):
                    $icon = $icons_cat[$cat] ?? 'ri-price-tag-3-fill';
                ?>
                <a href="noticias.php?categoria=<?php echo urlencode($cat); ?>" class="cat-btn <?php echo $cat_activa === $cat ? 'active' : ''; ?>">
                    <i class="<?php echo $icon; ?>"></i> <?php echo htmlspecialchars($cat); ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- GRID -->
            <?php if ($res_noticias && $res_noticias->num_rows > 0): ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:2rem; margin-bottom:4rem;">
                <?php while ($n = $res_noticias->fetch_assoc()):
                    $cat_key = str_replace(' ', '', $n['categoria']);
                    $badge_class = "cat-$cat_key";
                    $icon_map = ['Eventos'=>'ri-calendar-event-fill','Alianzas'=>'ri-handshake-fill','SaludAnimal'=>'ri-heart-pulse-fill','Consejos'=>'ri-lightbulb-fill'];
                    $card_icon = $icon_map[$cat_key] ?? 'ri-newspaper-fill';
                    $grads = ['Eventos'=>'#6366f1, #8b5cf6','Alianzas'=>'#10b981, #059669','SaludAnimal'=>'#ef4444, #dc2626','Consejos'=>'#f59e0b, #d97706'];
                    $grad = $grads[$cat_key] ?? 'var(--c-primary), #2e6e52';
                ?>
                <article class="news-card">
                    <div class="news-card-img">
                        <?php if (!empty($n['imagen_url'])): ?>
                            <img src="<?php echo htmlspecialchars($n['imagen_url']); ?>" alt="<?php echo htmlspecialchars($n['titulo']); ?>">
                        <?php else: ?>
                            <div style="position:absolute; inset:0; background:linear-gradient(135deg, <?php echo $grad; ?>); display:flex; align-items:center; justify-content:center;">
                                <i class="<?php echo $card_icon; ?>" style="font-size:4rem; color:rgba(255,255,255,0.2);"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="news-card-body">
                        <span class="news-badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($n['categoria']); ?></span>
                        <h3><?php echo htmlspecialchars($n['titulo']); ?></h3>
                        <p><?php echo htmlspecialchars($n['resumen']); ?></p>
                        <div class="news-meta">
                            <span><i class="ri-user-3-line"></i> <?php echo htmlspecialchars($n['autor']); ?></span>
                            <span><i class="ri-calendar-line"></i> <?php echo date('d M Y', strtotime($n['fecha_publicacion'])); ?></span>
                        </div>
                        <a href="noticia.php?id=<?php echo $n['id']; ?>" class="read-more">
                            Leer más <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div style="text-align:center; padding:5rem 0; color:var(--c-text-muted);">
                <i class="ri-newspaper-line" style="font-size:4rem; display:block; margin-bottom:1rem; opacity:0.3;"></i>
                <p>No hay noticias en esta categoría aún.</p>
                <a href="noticias.php" class="btn btn-primary" style="margin-top:1rem;">Ver todas</a>
            </div>
            <?php endif; ?>

            <!-- NEWSLETTER -->
            <div style="background:linear-gradient(135deg, var(--c-primary), #2e6e52); border-radius:var(--radius-lg); padding:4rem; color:white; text-align:center;">
                <i class="ri-mail-send-line" style="font-size:3rem; margin-bottom:1rem; display:block; opacity:0.8;"></i>
                <h2 style="color:white; font-size:1.8rem; margin-bottom:0.75rem;" data-i18n="footer_news">¿Quieres recibir noticias de ConectaPet?</h2>
                <p style="opacity:0.85; max-width:500px; margin:0 auto 2rem; line-height:1.7;" data-i18n="footer_news_sub">Suscríbete y entérate primero de eventos de adopción, jornadas de vacunación y más.</p>
                <div style="display:flex; gap:1rem; max-width:480px; margin:0 auto; flex-wrap:wrap;">
                    <input type="email" placeholder="tucorreo@email.com"
                           style="flex:1; padding:1rem 1.5rem; border-radius:var(--radius-pill); border:none; outline:none; font-size:1rem; font-family:var(--font-body); min-width:200px;">
                    <button class="btn btn-white" style="white-space:nowrap;"><i class="ri-send-plane-fill"></i> Suscribirme</button>
                </div>
                <p style="font-size:0.8rem; opacity:0.6; margin-top:1rem;">Sin spam. Cancela cuando quieras.</p>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="brand-col">
                    <a href="index.php" class="logo">
                        <div class="logo-bubble"><img src="logo.png" alt="ConectaPet Logo"></div>
                        <div class="logo-text">Conecta<span>Pet</span></div>
                    </a>
                    <p>Haciendo la diferencia, una patita a la vez.</p>
                </div>
                <div class="footer-col">
                    <h4>Categorías</h4>
                    <ul>
                        <li><a href="noticias.php?categoria=Eventos">📅 Eventos</a></li>
                        <li><a href="noticias.php?categoria=Alianzas">🤝 Alianzas</a></li>
                        <li><a href="noticias.php?categoria=Salud+Animal">❤️ Salud Animal</a></li>
                        <li><a href="noticias.php?categoria=Consejos">💡 Consejos</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Plataforma</h4>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="albergues.php">Albergues</a></li>
                        <li><a href="donaciones.php">Donaciones</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">&copy; 2026 ConectaPet. Todos los derechos reservados.</div>
        </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>
