<?php
session_start();
require_once 'conexion.php';

// Redirigir si ya hay sesión activa
if (isset($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }
if (isset($_SESSION['user_id']))  { header('Location: mi-perfil.php'); exit; }

$error   = '';
$success = '';
$tab     = $_GET['tab'] ?? 'login'; // 'login' o 'registro'

// ─── REGISTRO ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registro') {
    $nombre    = trim($_POST['nombre']    ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $correo    = trim($_POST['correo']    ?? '');
    $pass      = $_POST['password']       ?? '';
    $pass2     = $_POST['password2']      ?? '';
    $telefono  = trim($_POST['telefono']  ?? '');

    if (!$nombre || !$apellidos || !$correo || !$pass) {
        $error = 'Por favor completa todos los campos obligatorios.';
        $tab = 'registro';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo no tiene un formato válido.';
        $tab = 'registro';
    } elseif (strlen($pass) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
        $tab = 'registro';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
        $tab = 'registro';
    } else {
        $esc_correo = $conn->real_escape_string($correo);
        // Verificar que el correo no exista ya
        $existe = $conn->query("SELECT id FROM usuarios WHERE correo='$esc_correo' LIMIT 1")->num_rows;
        if ($existe > 0) {
            $error = 'Ya existe una cuenta con ese correo. Inicia sesión.';
            $tab = 'registro';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $esc_nom  = $conn->real_escape_string($nombre);
            $esc_ape  = $conn->real_escape_string($apellidos);
            $esc_tel  = $conn->real_escape_string($telefono);
            $esc_hash = $conn->real_escape_string($hash);
            $conn->query("INSERT INTO usuarios (nombre, apellidos, correo, password_hash, telefono)
                          VALUES ('$esc_nom','$esc_ape','$esc_correo','$esc_hash','$esc_tel')");
            $success = '¡Cuenta creada! Ya puedes iniciar sesión.';
            $tab = 'login';
        }
    }
}

// ─── LOGIN ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login') {
    $correo = trim($_POST['correo']   ?? '');
    $pass   = trim($_POST['password'] ?? '');

    if (!$correo || !$pass) {
        $error = 'Completa correo y contraseña.';
    } else {
        $esc = $conn->real_escape_string($correo);

        // 1. ¿Es admin?
        $admin = $conn->query("SELECT * FROM admins WHERE correo='$esc' LIMIT 1")->fetch_assoc();
        if ($admin && password_verify($pass, $admin['password_hash'])) {
            $_SESSION['admin_id']     = $admin['id'];
            $_SESSION['admin_nombre'] = $admin['nombre'];
            $_SESSION['admin_rol']    = $admin['rol'];
            $_SESSION['albergue_id']  = $admin['albergue_id'];
            header('Location: dashboard.php');
            exit;
        }

        // 2. ¿Es usuario público?
        $user = $conn->query("SELECT * FROM usuarios WHERE correo='$esc' LIMIT 1")->fetch_assoc();
        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_correo'] = $user['correo'];
            $redir = $_GET['redir'] ?? 'mi-perfil.php';
            header("Location: $redir");
            exit;
        }

        $error = 'Correo o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceder - ConectaPet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { min-height:100vh; display:flex; font-family:'Inter',sans-serif; background:#0d1117; }

        /* Panel izquierdo */
        .left-panel {
            flex:1; background:linear-gradient(155deg, #1F4A38 0%, #2e6e52 40%, #1a3d2e 100%);
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            padding:4rem; position:relative; overflow:hidden;
        }
        .left-panel::before { content:''; position:absolute; width:500px; height:500px; border-radius:50%; background:rgba(255,255,255,0.04); top:-150px; left:-150px; }
        .left-panel::after  { content:''; position:absolute; width:300px; height:300px; border-radius:50%; background:rgba(255,255,255,0.04); bottom:-80px; right:-80px; }
        .left-content { position:relative; z-index:1; color:white; max-width:400px; text-align:center; }
        .left-logo { font-family:'Outfit',sans-serif; font-size:2.5rem; font-weight:800; margin-bottom:0.5rem; }
        .left-logo span { color:#B56143; }
        .left-paw { font-size:4rem; display:block; margin-bottom:1.5rem; }
        .left-content h1 { font-size:2rem; font-weight:800; line-height:1.25; margin-bottom:1rem; }
        .left-content h1 em { color:#fbbf24; font-style:normal; }
        .left-content p { opacity:0.8; line-height:1.7; margin-bottom:2rem; font-size:1rem; }
        .left-features { display:flex; flex-direction:column; gap:0.75rem; text-align:left; }
        .left-feat { display:flex; gap:0.75rem; align-items:center; font-size:0.9rem; }
        .left-feat-icon { width:32px; height:32px; background:rgba(255,255,255,0.15); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

        /* Panel derecho */
        .right-panel { width:500px; background:#161b22; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:3.5rem 3rem; }
        .form-box { width:100%; max-width:400px; }

        /* Tabs */
        .tabs { display:flex; background:#0d1117; border-radius:12px; padding:0.3rem; margin-bottom:2rem; }
        .tab-btn { flex:1; padding:0.75rem; border:none; background:transparent; color:#8b949e; font-family:'Inter',sans-serif; font-size:0.9rem; font-weight:600; cursor:pointer; border-radius:8px; transition:all 0.2s; }
        .tab-btn.active { background:#1F4A38; color:white; }

        /* Formulario */
        .form-title { color:#f0f6fc; font-family:'Outfit',sans-serif; font-size:1.6rem; font-weight:700; margin-bottom:0.3rem; }
        .form-sub { color:#8b949e; font-size:0.85rem; margin-bottom:2rem; }
        .fg { margin-bottom:1.25rem; }
        .fg label { display:block; color:#8b949e; font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.4rem; }
        .fg input {
            width:100%; padding:0.85rem 1.1rem;
            background:#0d1117; border:1px solid #30363d;
            border-radius:10px; color:#f0f6fc; font-size:0.95rem;
            font-family:'Inter',sans-serif; outline:none; transition:border-color 0.2s;
        }
        .fg input:focus { border-color:#1F4A38; box-shadow:0 0 0 3px rgba(31,74,56,0.25); }
        .fg-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .btn-submit {
            width:100%; padding:0.95rem; margin-top:0.5rem;
            background:linear-gradient(135deg,#1F4A38,#2e6e52); color:white;
            border:none; border-radius:10px; font-size:1rem; font-weight:700;
            font-family:'Inter',sans-serif; cursor:pointer; transition:all 0.2s;
        }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(31,74,56,0.4); }

        /* Alertas */
        .alert { padding:0.85rem 1.1rem; border-radius:8px; font-size:0.88rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem; }
        .alert-error   { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#f87171; }
        .alert-success { background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#6ee7b7; }

        .divider { text-align:center; color:#30363d; font-size:0.8rem; margin:1.25rem 0; position:relative; }
        .divider::before { content:''; position:absolute; left:0; top:50%; width:42%; height:1px; background:#30363d; }
        .divider::after  { content:''; position:absolute; right:0; top:50%; width:42%; height:1px; background:#30363d; }

        .back-link { text-align:center; margin-top:1.75rem; }
        .back-link a { color:#8b949e; font-size:0.85rem; text-decoration:none; }
        .back-link a:hover { color:#f0f6fc; }

        .demo-box { margin-top:2rem; padding:1rem; background:rgba(255,255,255,0.03); border:1px solid #30363d; border-radius:8px; font-size:0.78rem; color:#8b949e; text-align:center; }
        .demo-box strong { color:#f0f6fc; }

        @media(max-width:768px) { .left-panel { display:none; } .right-panel { width:100%; padding:2.5rem 1.5rem; } }
    </style>
</head>
<body>

<!-- Panel Izquierdo Decorativo -->
<div class="left-panel">
    <div class="left-content">
        <div class="left-logo">Conecta<span>Pet</span></div>
        <span class="left-paw">🐾</span>
        <h1>Tu comunidad de <em>adopción responsable</em></h1>
        <p>Únete y ayuda a miles de mascotas a encontrar un hogar. Con tu cuenta puedes adoptar, donar y seguir de cerca el impacto de tu ayuda.</p>
        <div class="left-features">
            <div class="left-feat">
                <div class="left-feat-icon"><i class="ri-file-list-3-fill" style="color:#fbbf24;"></i></div>
                Seguimiento de tus solicitudes de adopción
            </div>
            <div class="left-feat">
                <div class="left-feat-icon"><i class="ri-hand-coin-fill" style="color:#fbbf24;"></i></div>
                Historial completo de tus donaciones
            </div>
            <div class="left-feat">
                <div class="left-feat-icon"><i class="ri-heart-3-fill" style="color:#fbbf24;"></i></div>
                Guarda tus mascotas favoritas
            </div>
            <div class="left-feat">
                <div class="left-feat-icon"><i class="ri-mail-fill" style="color:#fbbf24;"></i></div>
                Recibe actualizaciones y comprobantes por email
            </div>
        </div>
    </div>
</div>

<!-- Panel Derecho - Formulario -->
<div class="right-panel">
    <div class="form-box">

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn <?php echo $tab === 'login' ? 'active' : ''; ?>" onclick="switchTab('login')">
                <i class="ri-login-box-line"></i> Iniciar Sesión
            </button>
            <button class="tab-btn <?php echo $tab === 'registro' ? 'active' : ''; ?>" onclick="switchTab('registro')">
                <i class="ri-user-add-line"></i> Registrarse
            </button>
        </div>

        <!-- Alertas -->
        <?php if ($error): ?>
        <div class="alert alert-error"><i class="ri-error-warning-fill"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><i class="ri-checkbox-circle-fill"></i> <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- ══ FORMULARIO LOGIN ══ -->
        <div id="panel-login" style="display:<?php echo $tab === 'login' ? 'block' : 'none'; ?>">
            <div class="form-title">¡Bienvenido de vuelta!</div>
            <div class="form-sub">Inicia sesión con tu cuenta de ConectaPet</div>
            <form method="POST" action="login.php">
                <input type="hidden" name="accion" value="login">
                <div class="fg">
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo" placeholder="tucorreo@ejemplo.com" required
                           value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>">
                </div>
                <div class="fg">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-submit"><i class="ri-login-box-fill"></i> Ingresar</button>
            </form>
            <div class="divider">¿No tienes cuenta?</div>
            <button onclick="switchTab('registro')" style="width:100%; padding:0.85rem; background:transparent; border:1px solid #30363d; border-radius:10px; color:#8b949e; font-family:'Inter',sans-serif; font-size:0.9rem; cursor:pointer; transition:all 0.2s;"
                    onmouseover="this.style.borderColor='#1F4A38'; this.style.color='white'"
                    onmouseout="this.style.borderColor='#30363d'; this.style.color='#8b949e'">
                Crear una cuenta nueva
            </button>
            <div class="demo-box">
                <strong>Admin:</strong> admin@conectapet.com / admin123
            </div>
        </div>

        <!-- ══ FORMULARIO REGISTRO ══ -->
        <div id="panel-registro" style="display:<?php echo $tab === 'registro' ? 'block' : 'none'; ?>">
            <div class="form-title">Crear una cuenta</div>
            <div class="form-sub">Es gratis y toma menos de un minuto</div>
            <form method="POST" action="login.php?tab=registro">
                <input type="hidden" name="accion" value="registro">
                <div class="fg-row">
                    <div class="fg">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" placeholder="José" required
                               value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                    </div>
                    <div class="fg">
                        <label>Apellidos *</label>
                        <input type="text" name="apellidos" placeholder="Ramírez" required
                               value="<?php echo htmlspecialchars($_POST['apellidos'] ?? ''); ?>">
                    </div>
                </div>
                <div class="fg">
                    <label>Correo Electrónico *</label>
                    <input type="email" name="correo" placeholder="tucorreo@ejemplo.com" required
                           value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>">
                </div>
                <div class="fg">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" placeholder="9XX XXX XXX"
                           value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                </div>
                <div class="fg-row">
                    <div class="fg">
                        <label>Contraseña * <small style="text-transform:none; color:#6b7280;">(mín. 6 caracteres)</small></label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="fg">
                        <label>Confirmar Contraseña *</label>
                        <input type="password" name="password2" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit"><i class="ri-user-add-fill"></i> Crear mi cuenta</button>
            </form>
        </div>

        <div class="back-link">
            <a href="index.php"><i class="ri-arrow-left-line"></i> Volver al sitio web</a>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.getElementById('panel-login').style.display    = (tab === 'login')    ? 'block' : 'none';
    document.getElementById('panel-registro').style.display = (tab === 'registro') ? 'block' : 'none';
    document.querySelectorAll('.tab-btn').forEach((btn, i) => {
        btn.classList.toggle('active', (i === 0 && tab === 'login') || (i === 1 && tab === 'registro'));
    });
}
</script>
</body>
</html>
