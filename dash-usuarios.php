<?php
session_start();
require_once 'conexion.php';
include 'dash_layout.php';

// Solo superadmin
if (($_SESSION['admin_rol'] ?? '') !== 'superadmin') {
    header('Location: dashboard.php');
    exit;
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// ─────────────────────────────────────────────────────────────────────────────
// ELIMINAR USUARIO
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_usuario'])) {
    $uid = intval($_POST['usuario_id']);
    $conn->query("DELETE FROM usuarios WHERE id=$uid");
    $_SESSION['flash'] = '🗑️ Usuario eliminado.';
    header('Location: dash-usuarios.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// CREAR ADMIN
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    $nombre   = trim($_POST['nombre']      ?? '');
    $correo   = trim($_POST['correo']      ?? '');
    $pass     = trim($_POST['password']    ?? '');
    $rol      = trim($_POST['rol']         ?? 'albergue');
    $alb_id   = intval($_POST['albergue_id'] ?? 0) ?: null;

    if ($nombre && $correo && strlen($pass) >= 6) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $esc  = $conn->real_escape_string($correo);
        $existe = $conn->query("SELECT id FROM admins WHERE correo='$esc' LIMIT 1")->num_rows;
        if ($existe === 0) {
            $stmt = $conn->prepare("INSERT INTO admins (nombre, correo, password_hash, rol, albergue_id) VALUES (?,?,?,?,?)");
            $stmt->bind_param("ssssi", $nombre, $correo, $hash, $rol, $alb_id);
            $stmt->execute();
            $_SESSION['flash'] = '✅ Admin creado correctamente.';
        } else {
            $_SESSION['flash'] = '⚠️ Ya existe un admin con ese correo.';
        }
    } else {
        $_SESSION['flash'] = '⚠️ Completa todos los campos (contraseña mín. 6 chars).';
    }
    header('Location: dash-usuarios.php?tab=admins');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// ELIMINAR ADMIN
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_admin'])) {
    $aid = intval($_POST['admin_id']);
    // No eliminar el propio usuario admin activo
    if ($aid !== intval($_SESSION['admin_id'])) {
        $conn->query("DELETE FROM admins WHERE id=$aid");
        $_SESSION['flash'] = '🗑️ Admin eliminado.';
    } else {
        $_SESSION['flash'] = '⚠️ No puedes eliminarte a ti mismo.';
    }
    header('Location: dash-usuarios.php?tab=admins');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// DATOS
// ─────────────────────────────────────────────────────────────────────────────
$tab = $_GET['tab'] ?? 'usuarios';
$f_q = trim($_GET['q'] ?? '');

// Usuarios públicos
$where_u = $f_q ? "WHERE (nombre LIKE '%" . $conn->real_escape_string($f_q) . "%' OR apellidos LIKE '%" . $conn->real_escape_string($f_q) . "%' OR correo LIKE '%" . $conn->real_escape_string($f_q) . "%')" : '';
$usuarios = $conn->query("SELECT usuarios.*,
    (SELECT COUNT(*) FROM adopciones WHERE adopciones.usuario_id=usuarios.id) AS num_adopciones,
    (SELECT COALESCE(SUM(monto),0) FROM donaciones WHERE donaciones.id_usuario=usuarios.id) AS total_donado
    FROM usuarios $where_u ORDER BY fecha_registro DESC");

// Admins
$admins = $conn->query("SELECT admins.*, albergues.nombre AS albergue_nombre 
    FROM admins LEFT JOIN albergues ON admins.albergue_id=albergues.id ORDER BY admins.id ASC");

// Albergues para select
$albergues_list = $conn->query("SELECT id, nombre FROM albergues ORDER BY nombre");
?>

    <div class="dash-topbar">
        <h1><i class="ri-team-fill" style="color:#B56143;"></i> Gestión de Usuarios</h1>
        <div style="display:flex; gap:0.5rem;">
            <a href="?tab=usuarios" class="btn-dash <?php echo $tab === 'usuarios' ? 'btn-dash-primary' : 'btn-dash-outline'; ?>">
                <i class="ri-user-fill"></i> Usuarios
            </a>
            <a href="?tab=admins" class="btn-dash <?php echo $tab === 'admins' ? 'btn-dash-primary' : 'btn-dash-outline'; ?>">
                <i class="ri-shield-user-fill"></i> Administradores
            </a>
        </div>
    </div>

    <div class="dash-content">

        <?php if ($flash): ?>
        <div style="background:<?php echo strpos($flash, '⚠️') !== false ? '#fef3c7' : '#dcfce7'; ?>; border:1px solid <?php echo strpos($flash, '⚠️') !== false ? '#fde68a' : '#86efac'; ?>; color:<?php echo strpos($flash, '⚠️') !== false ? '#a16207' : '#15803d'; ?>; padding:0.85rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
            <?php echo htmlspecialchars($flash); ?>
        </div>
        <?php endif; ?>

        <?php if ($tab === 'usuarios'): ?>
        <!-- ══ USUARIOS PÚBLICOS ══ -->
        <form method="GET" style="display:flex; gap:0.75rem; margin-bottom:1.5rem;">
            <input type="hidden" name="tab" value="usuarios">
            <input type="text" name="q" value="<?php echo htmlspecialchars($f_q); ?>" placeholder="Buscar por nombre, apellido o correo..."
                   style="flex:1; padding:0.65rem 1rem; border:1px solid #e5e7eb; border-radius:8px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
            <button type="submit" class="btn-dash btn-dash-primary"><i class="ri-search-line"></i> Buscar</button>
            <?php if ($f_q): ?>
            <a href="?tab=usuarios" class="btn-dash btn-dash-outline"><i class="ri-close-line"></i></a>
            <?php endif; ?>
        </form>

        <div class="dash-table-wrap">
            <div class="dash-table-head">
                <h3><i class="ri-user-fill" style="color:#B56143;"></i> Usuarios registrados</h3>
                <span style="font-size:0.85rem; color:#9ca3af;"><?php echo $usuarios ? $usuarios->num_rows : 0; ?> usuarios</span>
            </div>
            <table class="dash-table">
                <thead>
                    <tr><th>Usuario</th><th>Correo</th><th>Teléfono</th><th>Solicitudes</th><th>Total Donado</th><th>Fecha Registro</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php if ($usuarios && $usuarios->num_rows > 0):
                        while ($u = $usuarios->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#1F4A38,#2e6e52); display:flex; align-items:center; justify-content:center; color:white; font-weight:800; font-family:'Outfit',sans-serif; font-size:1rem; flex-shrink:0;">
                                    <?php echo mb_strtoupper(mb_substr($u['nombre'], 0, 1)); ?>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']); ?></strong>
                                    <?php if (!empty($u['distrito'])): ?>
                                    <small style="display:block; color:#9ca3af;"><?php echo htmlspecialchars($u['distrito']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:0.85rem; color:#6b7280;"><?php echo htmlspecialchars($u['correo']); ?></td>
                        <td style="font-size:0.85rem; color:#6b7280;"><?php echo htmlspecialchars($u['telefono'] ?? '—'); ?></td>
                        <td><span class="badge badge-blue"><?php echo $u['num_adopciones']; ?></span></td>
                        <td><strong style="color:#15803d;">S/ <?php echo number_format($u['total_donado'], 2); ?></strong></td>
                        <td style="font-size:0.82rem; color:#9ca3af;"><?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Eliminar este usuario y todos sus datos?');" style="display:inline;">
                                <input type="hidden" name="usuario_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" name="eliminar_usuario" class="btn-dash btn-dash-danger" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                                    <i class="ri-delete-bin-line"></i> Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#9ca3af; padding:3rem;">No hay usuarios registrados aún.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
        <!-- ══ ADMINISTRADORES ══ -->
        <div style="display:grid; grid-template-columns:1fr 1.5fr; gap:1.5rem; align-items:start;">

            <!-- Formulario crear admin -->
            <div style="background:white; border-radius:14px; border:1px solid #e5e7eb; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div style="background:linear-gradient(135deg,#1F4A38,#2e6e52); padding:1.25rem 1.5rem;">
                    <h3 style="color:white; font-family:'Outfit',sans-serif; font-size:1.05rem;"><i class="ri-user-add-fill"></i> Crear Administrador</h3>
                </div>
                <form method="POST" action="dash-usuarios.php" style="padding:1.5rem;">
                    <div style="margin-bottom:1rem;">
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:#6b7280; text-transform:uppercase; margin-bottom:0.35rem;">Nombre completo</label>
                        <input type="text" name="nombre" required placeholder="Ej: María García"
                               style="width:100%; padding:0.75rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:#6b7280; text-transform:uppercase; margin-bottom:0.35rem;">Correo electrónico</label>
                        <input type="email" name="correo" required placeholder="admin@albergue.com"
                               style="width:100%; padding:0.75rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:#6b7280; text-transform:uppercase; margin-bottom:0.35rem;">Contraseña (mín. 6 caracteres)</label>
                        <input type="password" name="password" required placeholder="••••••••"
                               style="width:100%; padding:0.75rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:#6b7280; text-transform:uppercase; margin-bottom:0.35rem;">Rol</label>
                        <select name="rol" style="width:100%; padding:0.75rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
                            <option value="albergue">Admin de Albergue</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                    <div style="margin-bottom:1.5rem;">
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:#6b7280; text-transform:uppercase; margin-bottom:0.35rem;">Albergue asignado (opcional)</label>
                        <select name="albergue_id" style="width:100%; padding:0.75rem; border:1px solid #e5e7eb; border-radius:9px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
                            <option value="">— Sin asignar (Super Admin) —</option>
                            <?php while ($alb = $albergues_list->fetch_assoc()): ?>
                            <option value="<?php echo $alb['id']; ?>"><?php echo htmlspecialchars($alb['nombre']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="crear_admin" class="btn-dash btn-dash-primary" style="width:100%; padding:0.75rem; justify-content:center;">
                        <i class="ri-user-add-fill"></i> Crear Administrador
                    </button>
                </form>
            </div>

            <!-- Tabla admins -->
            <div class="dash-table-wrap">
                <div class="dash-table-head">
                    <h3><i class="ri-shield-user-fill" style="color:#B56143;"></i> Administradores activos</h3>
                    <span style="font-size:0.85rem; color:#9ca3af;"><?php echo $admins ? $admins->num_rows : 0; ?> admins</span>
                </div>
                <table class="dash-table">
                    <thead>
                        <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Albergue</th><th>Acción</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($admins && $admins->num_rows > 0):
                            while ($a = $admins->fetch_assoc()):
                                $es_yo = $a['id'] == $_SESSION['admin_id'];
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($a['nombre']); ?></strong> <?php if ($es_yo) echo '<span class="badge badge-green" style="font-size:0.65rem;">Tú</span>'; ?></td>
                            <td style="font-size:0.85rem; color:#6b7280;"><?php echo htmlspecialchars($a['correo']); ?></td>
                            <td><span class="badge <?php echo $a['rol'] === 'superadmin' ? 'badge-yellow' : 'badge-blue'; ?>"><?php echo $a['rol'] === 'superadmin' ? '⭐ Super Admin' : 'Albergue'; ?></span></td>
                            <td style="font-size:0.85rem; color:#6b7280;"><?php echo htmlspecialchars($a['albergue_nombre'] ?? '—'); ?></td>
                            <td>
                                <?php if (!$es_yo): ?>
                                <form method="POST" onsubmit="return confirm('¿Eliminar este administrador?');">
                                    <input type="hidden" name="admin_id" value="<?php echo $a['id']; ?>">
                                    <button type="submit" name="eliminar_admin" class="btn-dash btn-dash-danger" style="font-size:0.78rem; padding:0.35rem 0.75rem;">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span style="font-size:0.78rem; color:#9ca3af;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center; color:#9ca3af; padding:2rem;">Sin administradores.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
