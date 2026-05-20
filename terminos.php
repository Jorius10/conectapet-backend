<?php session_start(); $active_nav = ''; include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Condiciones - ConectaPet</title>
    <meta name="description" content="Términos y Condiciones de uso de la plataforma ConectaPet.">
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .legal-hero { background:linear-gradient(135deg,var(--c-primary),#2e6e52); padding:5rem 0 3rem; text-align:center; color:white; }
        .legal-hero h1 { color:white; font-size:2.5rem; margin-bottom:0.5rem; }
        .legal-hero p { opacity:0.8; }
        .legal-body { max-width:800px; margin:0 auto; padding:3rem 2rem 5rem; }
        .legal-body h2 { color:var(--c-primary); font-size:1.3rem; margin:2.5rem 0 0.75rem; padding-bottom:0.5rem; border-bottom:2px solid var(--c-border); }
        .legal-body p, .legal-body li { color:var(--c-text-muted); line-height:1.8; font-size:0.95rem; }
        .legal-body ul { margin:0.5rem 0 1rem 1.5rem; }
        .legal-body li { margin-bottom:0.4rem; }
        .last-update { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--radius-md); padding:1rem 1.5rem; font-size:0.85rem; color:var(--c-text-muted); margin-bottom:2rem; }
    </style>
</head>
<body>
<?php $active_nav = ''; include 'header.php'; ?>

<div class="legal-hero" style="padding-top:130px;">
    <div class="container">
        <span style="background:rgba(255,255,255,0.15); padding:0.3rem 1rem; border-radius:20px; font-size:0.85rem; font-weight:700; display:inline-block; margin-bottom:1rem;">📄 Legal</span>
        <h1>Términos y Condiciones</h1>
        <p>Por favor lee estos términos antes de usar nuestra plataforma.</p>
    </div>
</div>

<main class="legal-body">
    <div class="last-update"><i class="ri-calendar-line"></i> Última actualización: Mayo 2026 &nbsp;|&nbsp; <i class="ri-map-pin-line"></i> Lima, Perú</div>

    <h2>1. Aceptación de los Términos</h2>
    <p>Al acceder y utilizar la plataforma ConectaPet, aceptas quedar vinculado por estos Términos y Condiciones. Si no estás de acuerdo con alguna parte de estos términos, no debes usar nuestros servicios.</p>

    <h2>2. Descripción del Servicio</h2>
    <p>ConectaPet es una plataforma digital que conecta albergues de animales con personas interesadas en adoptar mascotas o contribuir mediante donaciones. No somos dueños ni responsables de los animales publicados; actuamos como intermediarios tecnológicos.</p>

    <h2>3. Registro y Cuentas de Usuario</h2>
    <ul>
        <li>Debes proporcionar información verídica y actualizada al registrarte.</li>
        <li>Eres responsable de mantener la confidencialidad de tu contraseña.</li>
        <li>Nos reservamos el derecho de suspender cuentas que incumplan estas normas.</li>
        <li>Debes tener al menos 18 años para registrarte.</li>
    </ul>

    <h2>4. Proceso de Adopción</h2>
    <p>ConectaPet facilita el contacto entre adoptantes y albergues. El proceso final de adopción, incluyendo visitas, contratos y entrega del animal, es responsabilidad exclusiva del albergue correspondiente. ConectaPet no garantiza la aprobación de ninguna solicitud.</p>

    <h2>5. Donaciones</h2>
    <ul>
        <li>Las donaciones son voluntarias y no reembolsables.</li>
        <li>ConectaPet se compromete a destinar los fondos a los fines indicados (alimentos, medicinas, refugios o mascotas específicas).</li>
        <li>Se emitirá un comprobante digital por correo electrónico tras cada donación.</li>
        <li>No nos hacemos responsables por errores en datos bancarios proporcionados por el donante.</li>
    </ul>

    <h2>6. Conducta del Usuario</h2>
    <p>Queda prohibido:</p>
    <ul>
        <li>Publicar información falsa o engañosa.</li>
        <li>Usar la plataforma para fines comerciales no autorizados.</li>
        <li>Intentar acceder a cuentas ajenas o sistemas internos.</li>
        <li>Publicar contenido ofensivo, discriminatorio o ilegal.</li>
    </ul>

    <h2>7. Propiedad Intelectual</h2>
    <p>Todo el contenido de ConectaPet (logotipos, diseños, textos, código) es propiedad de ConectaPet. Queda prohibida su reproducción sin autorización escrita.</p>

    <h2>8. Limitación de Responsabilidad</h2>
    <p>ConectaPet no será responsable por daños directos, indirectos o consecuentes derivados del uso o imposibilidad de uso de la plataforma, incluyendo problemas surgidos entre adoptantes y albergues.</p>

    <h2>9. Modificaciones</h2>
    <p>Nos reservamos el derecho de modificar estos términos en cualquier momento. Las modificaciones entrarán en vigor al ser publicadas. El uso continuado de la plataforma implica aceptación de los nuevos términos.</p>

    <h2>10. Contacto</h2>
    <p>Para consultas sobre estos términos, escríbenos a <strong>legal@conectapet.com</strong>.</p>

    <div style="margin-top:3rem; text-align:center;">
        <a href="index.php" class="btn btn-primary"><i class="ri-arrow-left-line"></i> Volver al inicio</a>
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
