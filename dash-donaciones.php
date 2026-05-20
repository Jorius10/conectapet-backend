<?php
session_start();
require_once 'conexion.php';
include 'dash_layout.php';

// ── RESUMEN ──
$total_monto = $conn->query("SELECT COALESCE(SUM(monto),0) FROM donaciones")->fetch_row()[0];
$total_num   = $conn->query("SELECT COUNT(*) FROM donaciones")->fetch_row()[0];
$por_metodo  = $conn->query("SELECT metodo_pago, COUNT(*) as num, SUM(monto) as total FROM donaciones GROUP BY metodo_pago");
$por_tipo    = $conn->query("SELECT tipo, COUNT(*) as num, SUM(monto) as total FROM donaciones GROUP BY tipo");

// ── FILTRO ──
$busqueda = trim($_GET['q'] ?? '');
$where = $busqueda ? "WHERE (d.nombre LIKE '%$busqueda%' OR d.correo LIKE '%$busqueda%')" : '';

$donaciones = $conn->query("SELECT d.*, m.nombre AS mascota_nombre, a.nombre AS albergue_nombre
    FROM donaciones d
    LEFT JOIN mascotas m ON d.mascota_id = m.id
    LEFT JOIN albergues a ON d.albergue_id = a.id
    $where
    ORDER BY d.id DESC");
?>

    <div class="dash-topbar">
        <h1><i class="ri-hand-coin-fill" style="color:#f59e0b;"></i> Reporte de Donaciones</h1>
        <form method="GET" style="display:flex; gap:0.5rem;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar donante..."
                   style="padding:0.5rem 1rem; border:1px solid #e5e7eb; border-radius:8px; font-family:'Inter',sans-serif; font-size:0.9rem; outline:none;">
            <button class="btn-dash btn-dash-primary" type="submit"><i class="ri-search-line"></i></button>
        </form>
    </div>

    <div class="dash-content">

        <!-- ══ RESUMEN FINANCIERO ══ -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1.5rem; margin-bottom:2rem;">
            <div class="stat-card" style="border-top:4px solid #f59e0b;">
                <div class="stat-icon" style="background:#fef9c3;"><i class="ri-money-dollar-circle-fill" style="color:#a16207;"></i></div>
                <div class="stat-num" style="color:#a16207;">S/ <?php echo number_format($total_monto, 2); ?></div>
                <div class="stat-label">Total recaudado</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;"><i class="ri-group-fill" style="color:#1d4ed8;"></i></div>
                <div class="stat-num" style="color:#1d4ed8;"><?php echo $total_num; ?></div>
                <div class="stat-label">Donaciones totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;"><i class="ri-bar-chart-fill" style="color:#15803d;"></i></div>
                <div class="stat-num" style="color:#15803d; font-size:1.5rem;">S/ <?php echo $total_num > 0 ? number_format($total_monto/$total_num, 2) : '0.00'; ?></div>
                <div class="stat-label">Monto promedio</div>
            </div>
            <?php while ($row = $por_metodo->fetch_assoc()): ?>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f3f4f6;"><i class="ri-smartphone-fill" style="color:#6b7280;"></i></div>
                <div class="stat-num" style="font-size:1.4rem;">S/ <?php echo number_format($row['total'],2); ?></div>
                <div class="stat-label"><?php echo htmlspecialchars($row['metodo_pago']); ?> (<?php echo $row['num']; ?>)</div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- ══ TABLA ══ -->
        <div class="dash-table-wrap">
            <div class="dash-table-head">
                <h3><i class="ri-receipt-fill" style="color:#f59e0b;"></i> Historial de Donaciones</h3>
                <span style="font-size:0.85rem; color:#9ca3af;"><?php echo $donaciones ? $donaciones->num_rows : 0; ?> registros</span>
            </div>
            <table class="dash-table">
                <thead>
                    <tr><th>#</th><th>Donante</th><th>Destino</th><th>Tipo</th><th>Monto</th><th>Método</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    <?php if ($donaciones && $donaciones->num_rows > 0):
                        while ($d = $donaciones->fetch_assoc()):
                            $destino = $d['mascota_nombre'] ?: ($d['albergue_nombre'] ?: '—');
                    ?>
                    <tr>
                        <td style="color:#9ca3af;">#<?php echo $d['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($d['nombre']); ?></strong>
                            <small style="display:block; color:#9ca3af;"><?php echo htmlspecialchars($d['correo']); ?></small>
                        </td>
                        <td style="color:#6b7280;"><?php echo htmlspecialchars($destino); ?></td>
                        <td><span class="badge badge-gray"><?php echo htmlspecialchars(ucfirst($d['tipo'])); ?></span></td>
                        <td><strong style="color:#15803d;">S/ <?php echo number_format($d['monto'],2); ?></strong></td>
                        <td><span class="badge badge-blue"><?php echo htmlspecialchars($d['metodo_pago']); ?></span></td>
                        <td style="color:#9ca3af; font-size:0.85rem;"><?php echo date('d/m/Y H:i', strtotime($d['fecha_donacion'])); ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#9ca3af; padding:3rem;">No hay donaciones registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
