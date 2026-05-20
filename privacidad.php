<?php session_start(); $active_nav = ''; include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad - ConectaPet</title>
    <meta name="description" content="Política de privacidad y tratamiento de datos personales en ConectaPet.">
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .legal-hero { background:linear-gradient(135deg,#1d4ed8,#2563eb); padding:5rem 0 3rem; text-align:center; color:white; }
        .legal-hero h1 { color:white; font-size:2.5rem; margin-bottom:0.5rem; }
        .legal-hero p { opacity:0.8; }
        .legal-body { max-width:800px; margin:0 auto; padding:3rem 2rem 5rem; }
        .legal-body h2 { color:#1d4ed8; font-size:1.3rem; margin:2.5rem 0 0.75rem; padding-bottom:0.5rem; border-bottom:2px solid var(--c-border); }
        .legal-body p, .legal-body li { color:var(--c-text-muted); line-height:1.8; font-size:0.95rem; }
        .legal-body ul { margin:0.5rem 0 1rem 1.5rem; }
        .legal-body li { margin-bottom:0.4rem; }
        .last-update { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--radius-md); padding:1rem 1.5rem; font-size:0.85rem; color:var(--c-text-muted); margin-bottom:2rem; }
        .highlight-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:var(--radius-md); padding:1.25rem 1.5rem; margin:1rem 0; }
        .highlight-box p { color:#1e40af; margin:0; font-weight:500; }
    </style>
</head>
<body>
<?php $active_nav = ''; include 'header.php'; ?>

<div class="legal-hero" style="padding-top:130px;">
    <div class="container">
        <span style="background:rgba(255,255,255,0.15); padding:0.3rem 1rem; border-radius:20px; font-size:0.85rem; font-weight:700; display:inline-block; margin-bottom:1rem;">🔒 Privacidad</span>
        <h1>Política de Privacidad</h1>
        <p>Tu privacidad es importante para nosotros. Aquí explicamos cómo usamos tus datos.</p>
    </div>
</div>

<main class="legal-body">
    <div class="last-update"><i class="ri-calendar-line"></i> Última actualización: Mayo 2026 &nbsp;|&nbsp; <i class="ri-map-pin-line"></i> Lima, Perú</div>

    <div class="highlight-box">
        <p>🐾 En ConectaPet nunca vendemos ni compartimos tus datos personales con terceros con fines comerciales. Tus datos solo se usan para hacer funcionar la plataforma y mejorar tu experiencia.</p>
    </div>

    <h2>1. Responsable del Tratamiento</h2>
    <p>ConectaPet es la entidad responsable del tratamiento de tus datos personales. Para cualquier consulta sobre privacidad, puedes contactarnos en <strong>privacidad@conectapet.com</strong>.</p>

    <h2>2. Datos que Recopilamos</h2>
    <p>Recopilamos los siguientes datos cuando usas nuestra plataforma:</p>
    <ul>
        <li><strong>Datos de registro:</strong> nombre, apellidos, correo electrónico y contraseña (cifrada).</li>
        <li><strong>Datos de perfil opcionales:</strong> teléfono, dirección, ciudad, foto de perfil.</li>
        <li><strong>Datos de adopción:</strong> información del formulario de solicitud (DNI, dirección, tipo de vivienda, etc.).</li>
        <li><strong>Datos de donación:</strong> nombre, correo y monto donado. No almacenamos datos bancarios completos.</li>
        <li><strong>Datos técnicos:</strong> dirección IP, tipo de navegador, páginas visitadas (para análisis interno).</li>
    </ul>

    <h2>3. Finalidad del Tratamiento</h2>
    <ul>
        <li>Gestionar tu cuenta y autenticar tu identidad.</li>
        <li>Procesar solicitudes de adopción y ponerlas en contacto con los albergues.</li>
        <li>Registrar y confirmar donaciones realizadas.</li>
        <li>Enviarte notificaciones sobre tus solicitudes (correo electrónico).</li>
        <li>Mejorar el funcionamiento de la plataforma.</li>
        <li>Enviarte el boletín si te has suscrito (puedes cancelar en cualquier momento).</li>
    </ul>

    <h2>4. Base Legal</h2>
    <p>El tratamiento de tus datos se basa en:</p>
    <ul>
        <li><strong>Consentimiento:</strong> al registrarte y usar los servicios.</li>
        <li><strong>Ejecución del contrato:</strong> para procesar adopciones y donaciones.</li>
        <li><strong>Interés legítimo:</strong> para análisis interno y mejora del servicio.</li>
    </ul>

    <h2>5. Seguridad de los Datos</h2>
    <p>Aplicamos medidas técnicas y organizativas para proteger tus datos:</p>
    <ul>
        <li>Contraseñas almacenadas con cifrado bcrypt.</li>
        <li>Comunicaciones mediante HTTPS.</li>
        <li>Acceso restringido a la base de datos solo para personal autorizado.</li>
        <li>Sin almacenamiento de datos bancarios completos.</li>
    </ul>

    <h2>6. Tus Derechos</h2>
    <p>Tienes derecho a:</p>
    <ul>
        <li><strong>Acceso:</strong> solicitar qué datos tenemos sobre ti.</li>
        <li><strong>Rectificación:</strong> corregir datos incorrectos desde tu perfil.</li>
        <li><strong>Eliminación:</strong> solicitar la eliminación de tu cuenta y datos.</li>
        <li><strong>Portabilidad:</strong> recibir tus datos en formato descargable.</li>
        <li><strong>Oposición:</strong> oponerte al tratamiento con fines de marketing.</li>
    </ul>
    <p>Para ejercer estos derechos escríbenos a <strong>privacidad@conectapet.com</strong>.</p>

    <h2>7. Cookies</h2>
    <p>Usamos únicamente cookies de sesión necesarias para el funcionamiento de la plataforma (login). No usamos cookies de rastreo ni publicidad de terceros.</p>

    <h2>8. Cambios en esta Política</h2>
    <p>Podemos actualizar esta política periódicamente. Te notificaremos por correo si los cambios son significativos.</p>

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
