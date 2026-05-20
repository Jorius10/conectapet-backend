<?php
session_start();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { header('Location: noticias.php'); exit; }

$noticia = $conn->query("SELECT * FROM noticias WHERE id = $id")->fetch_assoc();
if (!$noticia) { header('Location: noticias.php'); exit; }

// Noticias relacionadas (misma categoría, excluyendo ésta)
$cat_esc = $conn->real_escape_string($noticia['categoria']);
$relacionadas = $conn->query("SELECT * FROM noticias WHERE categoria = '$cat_esc' AND id != $id ORDER BY fecha_publicacion DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($noticia['titulo']); ?> - ConectaPet</title>
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .article-body { font-size:1.05rem; line-height:1.9; color:var(--c-text); }
        .article-body p { margin-bottom:1.5rem; }
        .article-body ul, .article-body ol { margin:0 0 1.5rem 1.5rem; }
        .article-body li { margin-bottom:0.5rem; }
        .article-body h3 { color:var(--c-primary); margin:2rem 0 1rem; }
        .article-body strong { color:var(--c-primary); }
        .related-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--radius-md); padding:1.25rem; text-decoration:none; display:block; transition:all 0.2s; }
        .related-card:hover { border-color:var(--c-primary); transform:translateY(-3px); box-shadow:var(--shadow-sm); }
        .related-card h4 { color:var(--c-primary); font-size:0.95rem; line-height:1.4; margin-bottom:0.4rem; }
        .related-card span { color:var(--c-text-muted); font-size:0.8rem; }
    </style>
</head>
<body>
<?php $active_nav = 'noticias'; include 'header.php'; ?>

    <main style="padding-top:130px; padding-bottom:5rem;">
        <div class="container" style="max-width:860px;">

            <!-- Volver -->
            <a href="noticias.php" style="display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:2rem; color:var(--c-primary); font-weight:bold; text-decoration:none;">
                <i class="ri-arrow-left-line"></i> Volver a Noticias
            </a>

            <!-- Categoría -->
            <span style="background:rgba(31,74,56,0.1); color:var(--c-primary); padding:0.4rem 1rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                <?php echo htmlspecialchars($noticia['categoria']); ?>
            </span>

            <!-- Título -->
            <h1 style="font-size:2.4rem; color:var(--c-primary); margin:1.25rem 0 0.75rem; line-height:1.25;">
                <?php echo htmlspecialchars($noticia['titulo']); ?>
            </h1>

            <!-- Meta -->
            <div style="display:flex; gap:1.5rem; color:var(--c-text-muted); font-size:0.9rem; margin-bottom:2.5rem; flex-wrap:wrap;">
                <span><i class="ri-user-3-line" style="color:var(--c-accent);"></i> <?php echo htmlspecialchars($noticia['autor']); ?></span>
                <span><i class="ri-calendar-line" style="color:var(--c-accent);"></i> <?php echo date('d \d\e F \d\e Y', strtotime($noticia['fecha_publicacion'])); ?></span>
            </div>

            <!-- Imagen principal (o placeholder) -->
            <div style="width:100%; height:400px; border-radius:var(--radius-lg); overflow:hidden; margin-bottom:3rem; box-shadow:var(--shadow-md);">
                <?php if (!empty($noticia['imagen_url'])): ?>
                    <img src="<?php echo htmlspecialchars($noticia['imagen_url']); ?>" style="width:100%;height:100%;object-fit:cover;" alt="<?php echo htmlspecialchars($noticia['titulo']); ?>">
                <?php else: ?>
                    <div style="width:100%; height:100%; background:linear-gradient(135deg, var(--c-primary), #2e6e52); display:flex; align-items:center; justify-content:center;">
                        <i class="ri-newspaper-fill" style="font-size:7rem; color:rgba(255,255,255,0.15);"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Contenido del artículo -->
            <div class="article-body">
                <?php echo $noticia['contenido']; // Contenido ya es HTML desde la BD ?>
            </div>

            <!-- Compartir -->
            <div style="border-top:1px solid var(--c-border); margin-top:3rem; padding-top:2rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                <span style="font-weight:600; color:var(--c-primary);">Compartir:</span>
                <a href="https://wa.me/?text=<?php echo urlencode($noticia['titulo'] . ' - ' . 'https://conectapet.com/noticia.php?id=' . $id); ?>" target="_blank"
                   style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.2rem; border-radius:var(--radius-pill); background:#25D366; color:white; text-decoration:none; font-weight:600; font-size:0.9rem;">
                    <i class="ri-whatsapp-fill"></i> WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://conectapet.com/noticia.php?id=' . $id); ?>" target="_blank"
                   style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.2rem; border-radius:var(--radius-pill); background:#1877F2; color:white; text-decoration:none; font-weight:600; font-size:0.9rem;">
                    <i class="ri-facebook-fill"></i> Facebook
                </a>
            </div>

            <!-- Noticias relacionadas -->
            <?php if ($relacionadas && $relacionadas->num_rows > 0): ?>
            <div style="margin-top:4rem;">
                <h3 style="font-size:1.4rem; color:var(--c-primary); margin-bottom:1.5rem; border-bottom:2px solid var(--c-border); padding-bottom:0.75rem;">
                    <i class="ri-article-line"></i> Artículos relacionados
                </h3>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:1.25rem;">
                    <?php while ($r = $relacionadas->fetch_assoc()): ?>
                    <a href="noticia.php?id=<?php echo $r['id']; ?>" class="related-card">
                        <h4><?php echo htmlspecialchars($r['titulo']); ?></h4>
                        <span><i class="ri-calendar-line"></i> <?php echo date('d M Y', strtotime($r['fecha_publicacion'])); ?></span>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">&copy; 2026 ConectaPet. Todos los derechos reservados.</div>
        </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>
