<?php
session_start();
require_once 'conexion.php';
include 'dash_layout.php';

// ── ESTADÍSTICAS GENERALES ──
$total_mascotas    = $conn->query("SELECT COUNT(*) FROM mascotas")->fetch_row()[0];
$disponibles       = $conn->query("SELECT COUNT(*) FROM mascotas WHERE estado_tramite='Disponible'")->fetch_row()[0];
$con_solicitud     = $conn->query("SELECT COUNT(*) FROM mascotas WHERE estado_tramite='Con Solicitud'")->fetch_row()[0];
$adoptadas         = $conn->query("SELECT COUNT(*) FROM mascotas WHERE estado_tramite='Adoptado'")->fetch_row()[0];
$total_donaciones  = $conn->query("SELECT COALESCE(SUM(monto),0) FROM donaciones")->fetch_row()[0];
$num_donaciones    = $conn->query("SELECT COUNT(*) FROM donaciones")->fetch_row()[0];
$solicitudes_pend  = $conn->query("SELECT COUNT(*) FROM adopciones WHERE estado='En Revisión'")->fetch_row()[0];
$total_albergues   = $conn->query("SELECT COUNT(*) FROM albergues")->fetch_row()[0];

// ── ÚLTIMAS SOLICITUDES DE ADOPCIÓN ──
$ultimas_adopciones = $conn->query("SELECT adopciones.*, mascotas.nombre AS mascota_nombre 
    FROM adopciones 
    LEFT JOIN mascotas ON adopciones.mascota_id = mascotas.id 
    ORDER BY adopciones.id DESC LIMIT 8");

// ── ÚLTIMAS DONACIONES ──
$ultimas_donaciones = $conn->query("SELECT donaciones.*, 
    mascotas.nombre AS mascota_nombre, albergues.nombre AS albergue_nombre
    FROM donaciones
    LEFT JOIN mascotas ON donaciones.mascota_id = mascotas.id
    LEFT JOIN albergues ON donaciones.albergue_id = albergues.id
    ORDER BY donaciones.id DESC LIMIT 8");
?>

    <!-- TOP BAR -->
    <div class="dash-topbar">
        <h1><i class="ri-dashboard-3-fill" style="color:#B56143;"></i> Resumen General</h1>
        <div class="dash-topbar-actions">
            <span style="font-size:0.85rem; color:#9ca3af;"><?php echo date('l, d \d\e F Y'); ?></span>
            <a href="index.php" target="_blank" class="view-site"><i class="ri-external-link-line"></i> Ver Sitio</a>
        </div>
    </div>

    <div class="dash-content">

        <!-- ══ STAT CARDS ══ -->
        <div class="dash-grid-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;"><i class="ri-paw-fill" style="color:#15803d;"></i></div>
                <div class="stat-num"><?php echo $total_mascotas; ?></div>
                <div class="stat-label">Total Mascotas</div>
                <div class="stat-trend" style="color:#15803d;">✓ <?php echo $disponibles; ?> disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;"><i class="ri-file-list-3-fill" style="color:#1d4ed8;"></i></div>
                <div class="stat-num"><?php echo $solicitudes_pend; ?></div>
                <div class="stat-label">Solicitudes Pendientes</div>
                <div class="stat-trend" style="color:#f59e0b;"><?php echo $con_solicitud; ?> mascotas con solicitud</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef9c3;"><i class="ri-hand-coin-fill" style="color:#a16207;"></i></div>
                <div class="stat-num">S/ <?php echo number_format($total_donaciones, 0); ?></div>
                <div class="stat-label">Total Donado</div>
                <div class="stat-trend" style="color:#6b7280;"><?php echo $num_donaciones; ?> donaciones registradas</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;"><i class="ri-heart-3-fill" style="color:#b91c1c;"></i></div>
                <div class="stat-num"><?php echo $adoptadas; ?></div>
                <div class="stat-label">Mascotas Adoptadas</div>
                <div class="stat-trend" style="color:#15803d;">🐾 <?php echo $total_albergues; ?> albergues activos</div>
            </div>
        </div>

        <!-- ══ ACCIONES RÁPIDAS ══ -->
        <div style="background:white; border-radius:12px; padding:1.5rem; border:1px solid #e5e7eb; margin-bottom:2rem; display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
            <span style="font-weight:700; color:#1F4A38; margin-right:0.5rem;">Acciones rápidas:</span>
            <a href="dash-mascotas.php?accion=nueva" class="btn-dash btn-dash-primary"><i class="ri-add-circle-fill"></i> Nueva Mascota</a>
            <a href="dash-noticias.php?accion=nueva" class="btn-dash btn-dash-primary" style="background:#6366f1;"><i class="ri-add-circle-fill"></i> Nueva Noticia</a>
            <a href="dash-adopciones.php" class="btn-dash btn-dash-outline"><i class="ri-file-list-3-line"></i> Ver Solicitudes</a>
            <a href="dash-donaciones.php" class="btn-dash btn-dash-outline"><i class="ri-hand-coin-line"></i> Ver Donaciones</a>
        </div>

        <div class="dash-grid-2">

            <!-- ── TABLA SOLICITUDES ── -->
            <div class="dash-table-wrap">
                <div class="dash-table-head">
                    <h3><i class="ri-file-list-3-fill" style="color:#B56143;"></i> Últimas Solicitudes de Adopción</h3>
                    <a href="dash-adopciones.php" class="btn-dash btn-dash-outline" style="font-size:0.8rem;">Ver todas</a>
                </div>
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Solicitante</th>
                            <th>Mascota</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ultimas_adopciones && $ultimas_adopciones->num_rows > 0):
                            while ($sol = $ultimas_adopciones->fetch_assoc()):
                                $est = strtolower(trim($sol['estado'] ?? 'en_revision'));
                                $badge = $est === 'aprobado' ? 'badge-green' : ($est === 'rechazado' ? 'badge-red' : 'badge-yellow');
                                $label = ucfirst(str_replace('_', ' ', $sol['estado'] ?? 'En Revisión'));
                        ?>
                        <tr>
                            <td>
                                <strong style="display:block;"><?php echo htmlspecialchars($sol['nombre'] . ' ' . $sol['apellidos']); ?></strong>
                                <small style="color:#9ca3af;"><?php echo htmlspecialchars($sol['correo']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($sol['mascota_nombre'] ?? '—'); ?></td>
                            <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($label); ?></span></td>
                            <td>
                                <a href="dash-adopciones.php?ver=<?php echo $sol['id']; ?>" class="btn-dash btn-dash-outline" style="font-size:0.78rem; padding:0.3rem 0.7rem;">Ver</a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" style="text-align:center; color:#9ca3af; padding:2rem;">Sin solicitudes aún.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── TABLA DONACIONES ── -->
            <div class="dash-table-wrap">
                <div class="dash-table-head">
                    <h3><i class="ri-hand-coin-fill" style="color:#f59e0b;"></i> Últimas Donaciones</h3>
                    <a href="dash-donaciones.php" class="btn-dash btn-dash-outline" style="font-size:0.8rem;">Ver todas</a>
                </div>
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Donante</th>
                            <th>Destino</th>
                            <th>Monto</th>
                            <th>Método</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ultimas_donaciones && $ultimas_donaciones->num_rows > 0):
                            while ($don = $ultimas_donaciones->fetch_assoc()):
                                $destino = $don['mascota_nombre'] ?? ($don['albergue_nombre'] ?? ucfirst($don['tipo']));
                        ?>
                        <tr>
                            <td>
                                <strong style="display:block;"><?php echo htmlspecialchars($don['nombre']); ?></strong>
                                <small style="color:#9ca3af;"><?php echo htmlspecialchars($don['correo']); ?></small>
                            </td>
                            <td style="color:#6b7280; font-size:0.85rem;"><?php echo htmlspecialchars($destino); ?></td>
                            <td><strong style="color:#15803d;">S/ <?php echo number_format($don['monto'], 2); ?></strong></td>
                            <td><span class="badge badge-blue"><?php echo htmlspecialchars($don['metodo_pago']); ?></span></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" style="text-align:center; color:#9ca3af; padding:2rem;">Sin donaciones aún.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- ── MINI-RESUMEN MASCOTAS POR ESTADO ── -->
        <div style="background:white; border-radius:12px; border:1px solid #e5e7eb; padding:1.75rem; margin-top:2rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="font-size:1rem; color:#1F4A38; font-weight:700; margin-bottom:1.5rem;"><i class="ri-pie-chart-2-fill" style="color:#B56143;"></i> Estado del Inventario de Mascotas</h3>
            <div style="display:flex; gap:1.5rem; flex-wrap:wrap;">
                <?php
                $estados = [
                    ['label'=>'Disponibles',   'val'=>$disponibles,   'color'=>'#10b981', 'bg'=>'#dcfce7'],
                    ['label'=>'Con Solicitud', 'val'=>$con_solicitud,  'color'=>'#3b82f6', 'bg'=>'#dbeafe'],
                    ['label'=>'En Proceso',    'val'=>$conn->query("SELECT COUNT(*) FROM mascotas WHERE estado_tramite='En Proceso'")->fetch_row()[0], 'color'=>'#f59e0b', 'bg'=>'#fef9c3'],
                    ['label'=>'Adoptados',     'val'=>$adoptadas,     'color'=>'#ef4444', 'bg'=>'#fee2e2'],
                ];
                foreach ($estados as $e): ?>
                <div style="flex:1; min-width:150px; background:<?php echo $e['bg']; ?>; border-radius:10px; padding:1.25rem; text-align:center;">
                    <div style="font-size:2rem; font-weight:800; color:<?php echo $e['color']; ?>; font-family:'Outfit',sans-serif;"><?php echo $e['val']; ?></div>
                    <div style="font-size:0.8rem; color:<?php echo $e['color']; ?>; font-weight:600; margin-top:0.25rem;"><?php echo $e['label']; ?></div>
                    <?php if ($total_mascotas > 0): ?>
                    <div style="font-size:0.75rem; color:<?php echo $e['color']; ?>; opacity:0.7;"><?php echo round(($e['val']/$total_mascotas)*100); ?>%</div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div><!-- end dash-content -->

    <div style="padding:1rem 2rem; border-top:1px solid #e5e7eb; background:white; font-size:0.8rem; color:#9ca3af; text-align:right;">
        &copy; 2026 ConectaPet Dashboard &mdash; Sesión activa como <strong><?php echo htmlspecialchars($admin_nombre); ?></strong>
    </div>
</div><!-- end dash-main -->
</body>
</html>
