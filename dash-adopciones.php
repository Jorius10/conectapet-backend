<?php
session_start();
require_once 'conexion.php';
include 'dash_layout.php';

$admin_rol = $_SESSION['admin_rol'] ?? 'albergue';

// Flash
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// ── FILTROS ──
$f_estado  = $_GET['estado'] ?? '';
$f_q       = trim($_GET['q'] ?? '');
$ver_id    = intval($_GET['ver'] ?? 0);

// ── CAMBIAR ESTADO ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $aid          = intval($_POST['adopcion_id']);
    $nuevo_estado = $conn->real_escape_string($_POST['nuevo_estado']);
    $nota_interna = $conn->real_escape_string(trim($_POST['nota_interna'] ?? ''));
    $mid          = intval($_POST['mascota_id']);

    // Actualizar estado y nota
    $conn->query("UPDATE adopciones SET estado='$nuevo_estado', nota_interna='$nota_interna' WHERE id=$aid");

    // Lógica de estado de mascota
    if ($nuevo_estado === 'Aprobado') {
        $conn->query("UPDATE mascotas SET estado_tramite='En Proceso' WHERE id=$mid");
    } elseif ($nuevo_estado === 'Adoptado Finalmente') {
        $conn->query("UPDATE mascotas SET estado_tramite='Adoptado' WHERE id=$mid");
    } elseif ($nuevo_estado === 'Rechazado') {
        $otras = $conn->query("SELECT COUNT(*) FROM adopciones WHERE mascota_id=$mid AND estado NOT IN ('Rechazado')")->fetch_row()[0];
        $nuevo_tramite = $otras > 0 ? 'Con Solicitud' : 'Disponible';
        $conn->query("UPDATE mascotas SET estado_tramite='$nuevo_tramite' WHERE id=$mid");
    }

    $_SESSION['flash'] = '✅ Estado actualizado correctamente.';
    header("Location: dash-adopciones.php?ver=$aid");
    exit;
}

// ── VER DETALLE ──
$detalle = null;
if ($ver_id > 0) {
    $detalle = $conn->query("SELECT adopciones.*, mascotas.nombre AS mascota_nombre, mascotas.foto_url, mascotas.especie, mascotas.sexo, mascotas.edad_texto
        FROM adopciones LEFT JOIN mascotas ON adopciones.mascota_id = mascotas.id
        WHERE adopciones.id = $ver_id")->fetch_assoc();
}

// ── LISTADO ──
$where_parts = [];
if ($f_estado) $where_parts[] = "adopciones.estado='" . $conn->real_escape_string($f_estado) . "'";
if ($f_q)      $where_parts[] = "(adopciones.nombre LIKE '%" . $conn->real_escape_string($f_q) . "%' OR adopciones.apellidos LIKE '%" . $conn->real_escape_string($f_q) . "%' OR mascotas.nombre LIKE '%" . $conn->real_escape_string($f_q) . "%')";
$where_sql = $where_parts ? "WHERE " . implode(" AND ", $where_parts) : "";

$adopciones = $conn->query("SELECT adopciones.*, mascotas.nombre AS mascota_nombre 
    FROM adopciones 
    LEFT JOIN mascotas ON adopciones.mascota_id = mascotas.id 
    $where_sql
    ORDER BY adopciones.id DESC");

// Contadores por estado
$cnt_revision = $conn->query("SELECT COUNT(*) FROM adopciones WHERE estado='En Revisión'")->fetch_row()[0];
$cnt_aprobado = $conn->query("SELECT COUNT(*) FROM adopciones WHERE estado='Aprobado'")->fetch_row()[0];
$cnt_rechazado= $conn->query("SELECT COUNT(*) FROM adopciones WHERE estado='Rechazado'")->fetch_row()[0];

$estados_config = [
    ''            => ['label' => 'Todas',       'count' => ($adopciones ? $conn->query("SELECT COUNT(*) FROM adopciones")->fetch_row()[0] : 0), 'badge' => 'badge-gray'],
    'En Revisión' => ['label' => 'En Revisión', 'count' => $cnt_revision, 'badge' => 'badge-yellow'],
    'Aprobado'    => ['label' => 'Aprobados',   'count' => $cnt_aprobado, 'badge' => 'badge-green'],
    'Rechazado'   => ['label' => 'Rechazados',  'count' => $cnt_rechazado,'badge' => 'badge-red'],
];
?>

    <div class="dash-topbar">
        <h1><i class="ri-file-list-3-fill" style="color:#B56143;"></i> Solicitudes de Adopción</h1>
        <form method="GET" style="display:flex; gap:0.5rem;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($f_q); ?>" placeholder="Buscar solicitante o mascota..."
                   style="padding:0.5rem 1rem; border:1px solid #e5e7eb; border-radius:8px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none; min-width:240px;">
            <input type="hidden" name="estado" value="<?php echo htmlspecialchars($f_estado); ?>">
            <button class="btn-dash btn-dash-primary" type="submit"><i class="ri-search-line"></i></button>
            <?php if ($f_q): ?>
            <a href="dash-adopciones.php?estado=<?php echo urlencode($f_estado); ?>" class="btn-dash btn-dash-outline"><i class="ri-close-line"></i></a>
            <?php endif; ?>
        </form>
    </div>

    <div class="dash-content">

        <?php if ($flash): ?>
        <div style="background:#dcfce7; border:1px solid #86efac; color:#15803d; padding:0.85rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
            <?php echo htmlspecialchars($flash); ?>
        </div>
        <?php endif; ?>

        <!-- ══ TABS DE ESTADO ══ -->
        <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
            <?php foreach ($estados_config as $key => $cfg): ?>
            <a href="dash-adopciones.php?estado=<?php echo urlencode($key); ?><?php echo $f_q ? '&q='.urlencode($f_q) : ''; ?>"
               style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.55rem 1.1rem; border-radius:20px; font-size:0.85rem; font-weight:600; text-decoration:none; border:2px solid <?php echo $f_estado === $key ? '#1F4A38' : '#e5e7eb'; ?>; background:<?php echo $f_estado === $key ? '#1F4A38' : 'white'; ?>; color:<?php echo $f_estado === $key ? 'white' : '#374151'; ?>; transition:all 0.2s;">
                <?php echo $cfg['label']; ?>
                <span style="background:<?php echo $f_estado === $key ? 'rgba(255,255,255,0.25)' : '#f3f4f6'; ?>; color:<?php echo $f_estado === $key ? 'white' : '#6b7280'; ?>; padding:0.1rem 0.5rem; border-radius:20px; font-size:0.75rem;"><?php echo $cfg['count']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($detalle): ?>
        <!-- ══ PANEL DE DETALLE ══ -->
        <div style="background:white; border-radius:14px; border:1px solid #e5e7eb; overflow:hidden; margin-bottom:2rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="background:linear-gradient(135deg,#1F4A38,#2e6e52); padding:1.25rem 2rem; display:flex; align-items:center; justify-content:space-between;">
                <h2 style="color:white; font-size:1.1rem; font-family:'Outfit',sans-serif;">
                    <i class="ri-file-list-3-fill"></i> Solicitud #<?php echo $detalle['id']; ?>
                    &mdash; <?php echo htmlspecialchars($detalle['nombre'] . ' ' . $detalle['apellidos']); ?>
                </h2>
                <a href="dash-adopciones.php?estado=<?php echo urlencode($f_estado); ?>" class="btn-dash" style="background:rgba(255,255,255,0.15); color:white;">
                    <i class="ri-close-line"></i> Cerrar
                </a>
            </div>

            <div style="display:grid; grid-template-columns:280px 1fr; gap:0;">
                <!-- Panel izquierdo: mascota + acciones -->
                <div style="padding:2rem; border-right:1px solid #f3f4f6; text-align:center; background:#fafafa;">
                    <?php if (!empty($detalle['foto_url'])): ?>
                    <img src="<?php echo htmlspecialchars($detalle['foto_url']); ?>" style="width:130px; height:130px; object-fit:cover; border-radius:50%; border:4px solid #1F4A38; margin-bottom:1rem;">
                    <?php else: ?>
                    <div style="width:130px; height:130px; border-radius:50%; background:#f3f4f6; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:4rem;"><?php echo $detalle['especie'] === 'Gato' ? '🐱' : '🐶'; ?></div>
                    <?php endif; ?>
                    <h3 style="color:#1F4A38; margin-bottom:0.25rem;"><?php echo htmlspecialchars($detalle['mascota_nombre'] ?? '—'); ?></h3>
                    <p style="color:#9ca3af; font-size:0.82rem; margin-bottom:0.25rem;"><?php echo htmlspecialchars($detalle['especie'] ?? ''); ?> · <?php echo htmlspecialchars($detalle['sexo'] ?? ''); ?></p>
                    <p style="color:#9ca3af; font-size:0.82rem; margin-bottom:1.5rem;"><?php echo htmlspecialchars($detalle['edad_texto'] ?? ''); ?></p>

                    <?php
                    $est = $detalle['estado'] ?? 'En Revisión';
                    $bc  = match ($est) { 'Aprobado','Adoptado Finalmente' => 'badge-green', 'Rechazado' => 'badge-red', default => 'badge-yellow' };
                    ?>
                    <span class="badge <?php echo $bc; ?>" style="margin-bottom:1.5rem; font-size:0.85rem; padding:0.35rem 1rem;"><?php echo htmlspecialchars($est); ?></span>

                    <!-- Formulario cambio de estado -->
                    <form method="POST" style="margin-top:1rem; text-align:left;">
                        <input type="hidden" name="adopcion_id" value="<?php echo $detalle['id']; ?>">
                        <input type="hidden" name="mascota_id" value="<?php echo $detalle['mascota_id']; ?>">
                        <div style="margin-bottom:0.75rem;">
                            <label style="display:block; font-size:0.72rem; font-weight:700; color:#6b7280; text-transform:uppercase; margin-bottom:0.3rem;">Nuevo Estado</label>
                            <select name="nuevo_estado" style="width:100%; padding:0.65rem; border:1px solid #e5e7eb; border-radius:8px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
                                <?php foreach (['En Revisión','Aprobado','Rechazado','Adoptado Finalmente'] as $opt): ?>
                                <option value="<?php echo $opt; ?>" <?php echo $est === $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="margin-bottom:1rem;">
                            <label style="display:block; font-size:0.72rem; font-weight:700; color:#6b7280; text-transform:uppercase; margin-bottom:0.3rem;">Nota Interna (solo visible para admins)</label>
                            <textarea name="nota_interna" rows="3" placeholder="Ej: Familia visitó el albergue el 15/06. Aprobada por evaluación social."
                                      style="width:100%; padding:0.65rem; border:1px solid #e5e7eb; border-radius:8px; font-family:'Inter',sans-serif; font-size:0.85rem; resize:vertical; outline:none;"><?php echo htmlspecialchars($detalle['nota_interna'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="cambiar_estado" class="btn-dash btn-dash-primary" style="width:100%; justify-content:center;">
                            <i class="ri-save-fill"></i> Guardar Estado
                        </button>
                    </form>
                </div>

                <!-- Panel derecho: datos del solicitante -->
                <div style="padding:2rem; overflow-y:auto; max-height:80vh;">
                    <h4 style="color:#1F4A38; margin-bottom:1.25rem; font-size:0.88rem; text-transform:uppercase; letter-spacing:0.5px; border-bottom:2px solid #f3f4f6; padding-bottom:0.75rem;">
                        <i class="ri-user-fill"></i> Datos del Solicitante
                    </h4>
                    <?php
                    $campos = [
                        'Nombre Completo'   => $detalle['nombre'] . ' ' . $detalle['apellidos'],
                        'DNI'               => $detalle['dni'],
                        'Fecha Nacimiento'  => $detalle['fecha_nacimiento'] ? date('d/m/Y', strtotime($detalle['fecha_nacimiento'])) : '—',
                        'Correo'            => $detalle['correo'],
                        'Teléfono'          => $detalle['telefono'],
                        'Teléfono Alt.'     => $detalle['telefono_alt'] ?: '—',
                        'Dirección'         => ($detalle['direccion'] ?? '') . ', ' . ($detalle['distrito'] ?? '') . ', ' . ($detalle['ciudad'] ?? ''),
                        'Departamento'      => $detalle['departamento'] ?: '—',
                        'Tipo de Vivienda'  => ($detalle['tipo_vivienda'] ?? '') . ' (' . ($detalle['vivienda_propia'] ?? '') . ')',
                        'Mascotas actuales' => $detalle['tiene_mascotas'] ?: '—',
                        'Tiempo disponible' => $detalle['tiempo_disponible'] ?: '—',
                        'Responsables'      => $detalle['responsables'] ?: '—',
                    ];
                    foreach ($campos as $label => $val): ?>
                    <div style="display:flex; gap:1rem; padding:0.55rem 0; border-bottom:1px solid #f9fafb; font-size:0.88rem;">
                        <span style="min-width:170px; color:#9ca3af; font-weight:600;"><?php echo $label; ?></span>
                        <span style="color:#374151;"><?php echo htmlspecialchars($val ?? '—'); ?></span>
                    </div>
                    <?php endforeach; ?>

                    <?php if (!empty($detalle['experiencia'])): ?>
                    <div style="margin-top:1.25rem;">
                        <div style="color:#9ca3af; font-weight:700; font-size:0.78rem; text-transform:uppercase; margin-bottom:0.5rem; letter-spacing:0.5px;">Experiencia previa</div>
                        <div style="background:#f9fafb; border-radius:8px; padding:1rem; color:#374151; font-size:0.88rem; line-height:1.6;"><?php echo htmlspecialchars($detalle['experiencia']); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($detalle['motivo'])): ?>
                    <div style="margin-top:1.25rem;">
                        <div style="color:#1F4A38; font-weight:700; font-size:0.78rem; text-transform:uppercase; margin-bottom:0.5rem; letter-spacing:0.5px;">Motivo de adopción</div>
                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:1rem; color:#374151; font-size:0.88rem; line-height:1.6;"><?php echo htmlspecialchars($detalle['motivo']); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($detalle['nota_interna'])): ?>
                    <div style="margin-top:1.25rem;">
                        <div style="color:#a16207; font-weight:700; font-size:0.78rem; text-transform:uppercase; margin-bottom:0.5rem; letter-spacing:0.5px;">🔒 Nota interna del equipo</div>
                        <div style="background:#fef9c3; border:1px solid #fde68a; border-radius:8px; padding:1rem; color:#a16207; font-size:0.88rem; line-height:1.6;"><?php echo htmlspecialchars($detalle['nota_interna']); ?></div>
                    </div>
                    <?php endif; ?>

                    <div style="margin-top:1.5rem; font-size:0.8rem; color:#9ca3af;">
                        Solicitud enviada el <?php echo date('d/m/Y H:i', strtotime($detalle['fecha_solicitud'] ?? 'now')); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ══ TABLA SOLICITUDES ══ -->
        <div class="dash-table-wrap">
            <div class="dash-table-head">
                <h3><i class="ri-file-list-3-fill" style="color:#B56143;"></i> Solicitudes</h3>
                <span style="font-size:0.85rem; color:#9ca3af;"><?php echo $adopciones ? $adopciones->num_rows : 0; ?> registros</span>
            </div>
            <table class="dash-table">
                <thead>
                    <tr><th>#</th><th>Solicitante</th><th>Mascota</th><th>Estado</th><th>Nota</th><th>Fecha</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if ($adopciones && $adopciones->num_rows > 0):
                        while ($s = $adopciones->fetch_assoc()):
                            $est = $s['estado'] ?? 'En Revisión';
                            $badge_class = match ($est) {
                                'Aprobado','Adoptado Finalmente' => 'badge-green',
                                'Rechazado' => 'badge-red',
                                default     => 'badge-yellow'
                            };
                    ?>
                    <tr style="<?php echo $ver_id == $s['id'] ? 'background:#f0fdf4;' : ''; ?>">
                        <td style="color:#9ca3af;">#<?php echo $s['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($s['nombre'].' '.$s['apellidos']); ?></strong>
                            <small style="display:block; color:#9ca3af;"><?php echo htmlspecialchars($s['correo']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($s['mascota_nombre'] ?? '—'); ?></td>
                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($est); ?></span></td>
                        <td>
                            <?php if (!empty($s['nota_interna'])): ?>
                            <span title="<?php echo htmlspecialchars($s['nota_interna']); ?>" style="color:#a16207; font-size:0.8rem; cursor:help;">📝 Tiene nota</span>
                            <?php else: ?>
                            <span style="color:#d1d5db; font-size:0.8rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:#9ca3af; font-size:0.85rem;"><?php echo date('d/m/Y', strtotime($s['fecha_solicitud'] ?? 'now')); ?></td>
                        <td>
                            <a href="dash-adopciones.php?ver=<?php echo $s['id']; ?>&estado=<?php echo urlencode($f_estado); ?>" 
                               class="btn-dash btn-dash-outline" style="font-size:0.8rem;">
                                <i class="ri-eye-line"></i> Ver detalle
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#9ca3af; padding:3rem;">
                        <i class="ri-file-list-3-line" style="font-size:2rem; display:block; margin-bottom:0.5rem; opacity:0.3;"></i>
                        No hay solicitudes<?php echo $f_estado ? " con estado «$f_estado»" : ''; ?>.
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- end dash-content -->
</div><!-- end dash-main -->
</body>
</html>
