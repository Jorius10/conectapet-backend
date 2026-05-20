<?php 
session_start();
require_once 'conexion.php';

// Recibimos el ID desde la URL (ej: albergue-perfil.php?id=1)
$albergue_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Traemos los datos de ese albergue en específico
$sql_albergue = "SELECT * FROM albergues WHERE id = $albergue_id";
$resultado_albergue = $conn->query($sql_albergue);

if ($resultado_albergue->num_rows == 0) {
    die("Albergue no encontrado.");
}
$albergue = $resultado_albergue->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Albergue - ConectaPet</title>
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
<?php $active_nav = 'albergues'; include 'header.php'; ?>

    <main style="padding-top: 150px; min-height: 80vh;">
        <div class="container">
            
            <a href="albergues.php" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem; color: var(--c-primary); font-weight: bold; text-decoration: none; padding: 0.5rem 1rem; border: 1px solid var(--c-border); border-radius: var(--radius-pill); background: var(--c-surface); box-shadow: var(--shadow-sm); transition: all 0.2s;">
                <i class="ri-arrow-left-line"></i> Volver a Todos los Albergues
            </a>

            <!-- Encabezado del Albergue -->
            <div class="albergue-header">
                <div class="albergue-profile-logo">
                    <?php if (!empty($albergue["logo_url"])): ?>
                        <img src="<?php echo htmlspecialchars($albergue["logo_url"]); ?>" alt="Logo">
                    <?php else: ?>
                        <i class="ri-home-smile-2-fill"></i>
                    <?php endif; ?>
                </div>
                <div class="albergue-info">
                    <h1>Albergue "<?php echo htmlspecialchars($albergue['nombre']); ?>"</h1>
                    <div class="albergue-loc">
                        <i class="ri-map-pin-line"></i> <?php echo htmlspecialchars($albergue['direccion']); ?>
                    </div>
                    <p class="desc">
                        <?php echo nl2br(htmlspecialchars($albergue['descripcion'])); ?>
                    </p>
                    <div class="albergue-stats">
                        <div class="astat">
                            <span class="num"><?php echo $albergue['rescates']; ?></span>
                            <span class="label">Rescates</span>
                        </div>
                        <div class="astat">
                            <span class="num"><?php echo $albergue['adopciones']; ?></span>
                            <span class="label">Adopciones</span>
                        </div>
                        <div class="astat">
                            <span class="num"><?php echo $albergue['anios_trayectoria']; ?></span>
                            <span class="label">Años de Trayectoria</span>
                        </div>
                        <div class="astat" style="margin-left: auto;">
                            <a href="donar-general.php?tipo=albergue&albergue_id=<?php echo $albergue_id; ?>" class="btn btn-secondary">
                                <i class="ri-hand-coin-fill"></i> Donar a este Albergue
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title" style="text-align: left; margin-inline: 0; margin-bottom: 1.5rem;">
                <h2>Mascotas Esperando Hogar</h2>
                <p>Descubre a los perritos y gatitos de este albergue.</p>
            </div>

            <!-- Menú de Filtros Inteligentes -->
            <form method="GET" action="albergue-perfil.php" class="filter-bar" style="justify-content: center;">
                <input type="hidden" name="id" value="<?php echo $albergue_id; ?>">
                
                <select name="especie" class="form-select">
                    <option value="">🐶 Todas las Especies</option>
                    <option value="Perro" <?php echo (isset($_GET['especie']) && $_GET['especie']=='Perro')?'selected':'';?>>Perros</option>
                    <option value="Gato" <?php echo (isset($_GET['especie']) && $_GET['especie']=='Gato')?'selected':'';?>>Gatos</option>
                    <option value="Otro" <?php echo (isset($_GET['especie']) && $_GET['especie']=='Otro')?'selected':'';?>>Otros (Exóticos)</option>
                </select>
                
                <select name="sexo" class="form-select">
                    <option value="">⚧ Cualquier Sexo</option>
                    <option value="Macho" <?php echo (isset($_GET['sexo']) && $_GET['sexo']=='Macho')?'selected':'';?>>Machos</option>
                    <option value="Hembra" <?php echo (isset($_GET['sexo']) && $_GET['sexo']=='Hembra')?'selected':'';?>>Hembras</option>
                </select>
                
                <select name="estado_tramite" class="form-select">
                    <option value="">📝 Todos los Trámites</option>
                    <option value="Disponible" <?php echo (isset($_GET['estado_tramite']) && $_GET['estado_tramite']=='Disponible')?'selected':'';?>>Solo Disponibles</option>
                    <option value="Con Solicitud" <?php echo (isset($_GET['estado_tramite']) && $_GET['estado_tramite']=='Con Solicitud')?'selected':'';?>>Con Solicitud</option>
                    <option value="En Proceso" <?php echo (isset($_GET['estado_tramite']) && $_GET['estado_tramite']=='En Proceso')?'selected':'';?>>En Proceso</option>
                    <option value="Adoptado" <?php echo (isset($_GET['estado_tramite']) && $_GET['estado_tramite']=='Adoptado')?'selected':'';?>>Ya Adoptados</option>
                </select>
                
                <select name="estado_medico" class="form-select">
                    <option value="">🏥 Estado Médico</option>
                    <option value="Vacunad" <?php echo (isset($_GET['estado_medico']) && $_GET['estado_medico']=='Vacunad')?'selected':'';?>>Con Vacunas</option>
                    <option value="Desparasitad" <?php echo (isset($_GET['estado_medico']) && $_GET['estado_medico']=='Desparasitad')?'selected':'';?>>Desparasitados/as</option>
                    <option value="Esterilizad" <?php echo (isset($_GET['estado_medico']) && $_GET['estado_medico']=='Esterilizad')?'selected':'';?>>Esterilizados/as</option>
                    <option value="Castrad" <?php echo (isset($_GET['estado_medico']) && $_GET['estado_medico']=='Castrad')?'selected':'';?>>Castrados</option>
                </select>

                <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem; box-shadow: var(--shadow-sm);"><i class="ri-filter-3-fill"></i> Filtrar</button>
                <?php if(isset($_GET['especie']) || isset($_GET['sexo']) || isset($_GET['estado_tramite']) || isset($_GET['estado_medico'])): ?>
                    <a href="albergue-perfil.php?id=<?php echo $albergue_id; ?>" class="btn btn-outline" style="padding: 0.8rem 1.5rem;">Cerrar Filtros</a>
                <?php endif; ?>
            </form>

            <!-- Grilla Dinámica de Mascotas (con Paginación) -->
            <?php
            // ─── Configuración de paginación ────────────────────────
            $por_pagina = 3;
            $pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
            $offset = ($pagina_actual - 1) * $por_pagina;

            // Construimos la consulta SQL Dinámica basada en los filtros
            $where_clauses = ["albergue_id = $albergue_id"];
            
            if (!empty($_GET['especie'])) {
                $esp = $conn->real_escape_string($_GET['especie']);
                $where_clauses[] = "especie = '$esp'";
            }
            if (!empty($_GET['sexo'])) {
                $sex = $conn->real_escape_string($_GET['sexo']);
                $where_clauses[] = "sexo = '$sex'";
            }
            if (!empty($_GET['estado_tramite'])) {
                $est = $conn->real_escape_string($_GET['estado_tramite']);
                $where_clauses[] = "estado_tramite = '$est'";
            }
            if (!empty($_GET['estado_medico'])) {
                $med = $conn->real_escape_string($_GET['estado_medico']);
                $where_clauses[] = "estado_medico LIKE '%$med%'";
            }
            
            $where_sql = implode(' AND ', $where_clauses);

            // Contamos el total para calcular páginas
            $total_res = $conn->query("SELECT COUNT(*) as total FROM mascotas WHERE $where_sql");
            $total_mascotas = $total_res->fetch_assoc()['total'];
            $total_paginas  = max(1, ceil($total_mascotas / $por_pagina));

            // Aseguramos que la página no se pase del máximo
            if ($pagina_actual > $total_paginas) $pagina_actual = $total_paginas;
            $offset = ($pagina_actual - 1) * $por_pagina;

            // Query con LIMIT y OFFSET para traer solo 6
            $sql_mascotas = "SELECT * FROM mascotas WHERE $where_sql LIMIT $por_pagina OFFSET $offset";
            $resultado_mascotas = $conn->query($sql_mascotas);

            // Construir la URL base preservando todos los filtros activos (sin pagina)
            $params_filtro = array_filter([
                'id'             => $albergue_id,
                'especie'        => $_GET['especie']        ?? '',
                'sexo'           => $_GET['sexo']           ?? '',
                'estado_tramite' => $_GET['estado_tramite'] ?? '',
                'estado_medico'  => $_GET['estado_medico']  ?? '',
            ]);
            $url_base = 'albergue-perfil.php?' . http_build_query($params_filtro) . '&pagina=';
            ?>

            <div class="pets-grid">
                <?php
                if ($resultado_mascotas && $resultado_mascotas->num_rows > 0) {
                    while($mascota = $resultado_mascotas->fetch_assoc()) {
                        $isDog = $mascota["especie"] == "Perro";
                        $tagClass = $isDog ? "tag-dog" : "tag-cat";
                        ?>
                        <div class="pet-card">
                            <div class="pet-image">
                                <?php if (!empty($mascota["foto_url"])): ?>
                                    <img src="<?php echo htmlspecialchars($mascota["foto_url"]); ?>" style="width:100%; height:100%; object-fit:cover;" alt="Mascota">
                                <?php else: ?>
                                    <i class="ri-<?php echo $isDog ? 'baidu' : 'ghost-smile'; ?>-line" style="font-size:3rem; color:white;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="pet-info">
                                <div class="pet-tags">
                                    <span class="tag <?php echo $tagClass; ?>"><?php echo htmlspecialchars($mascota["especie"]); ?></span>
                                    <span class="tag tag-gender"><?php echo htmlspecialchars($mascota["sexo"]); ?></span>
                                </div>
                                <h3 class="pet-name"><?php echo htmlspecialchars($mascota["nombre"]); ?></h3>
                                <p class="pet-desc"><?php echo htmlspecialchars($mascota["descripcion"]); ?></p>
                                <div class="pet-meta">
                                    <span><i class="ri-calendar-line"></i> Trámite: <?php echo htmlspecialchars($mascota["estado_tramite"]); ?></span>
                                    <span><i class="ri-heart-pulse-fill"></i> <?php echo htmlspecialchars($mascota["estado_medico"]); ?></span>
                                </div>
                                <a href="mascota-perfil.php?id=<?php echo $mascota["id"]; ?>" class="btn btn-primary btn-block">Conocer a <?php echo htmlspecialchars($mascota["nombre"]); ?></a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color:var(--c-text-muted);'>No se encontraron mascotas con estos filtros.</p>";
                }
                ?>
            </div>

            <!-- ══ PAGINACIÓN ══ -->
            <?php if ($total_paginas > 1): ?>
            <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin: 3rem 0 1rem; flex-wrap: wrap;">

                <!-- Flecha Anterior -->
                <?php if ($pagina_actual > 1): ?>
                    <a href="<?php echo $url_base . ($pagina_actual - 1); ?>"
                       style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; border-radius:50%; border:1px solid var(--c-border); background:var(--c-surface); color:var(--c-primary); text-decoration:none; box-shadow:var(--shadow-sm); transition:all 0.2s; font-size:1.1rem;">
                        <i class="ri-arrow-left-s-line"></i>
                    </a>
                <?php else: ?>
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; border-radius:50%; border:1px solid var(--c-border); background:var(--c-bg); color:var(--c-text-muted); cursor:not-allowed; font-size:1.1rem;">
                        <i class="ri-arrow-left-s-line"></i>
                    </span>
                <?php endif; ?>

                <!-- Números de página -->
                <?php for ($p = 1; $p <= $total_paginas; $p++): ?>
                    <?php if ($p === $pagina_actual): ?>
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; border-radius:50%; background:var(--c-primary); color:white; font-weight:700; box-shadow:var(--shadow-sm);">
                            <?php echo $p; ?>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo $url_base . $p; ?>"
                           style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; border-radius:50%; border:1px solid var(--c-border); background:var(--c-surface); color:var(--c-text); text-decoration:none; font-weight:600; box-shadow:var(--shadow-sm); transition:all 0.2s;">
                            <?php echo $p; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Flecha Siguiente -->
                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="<?php echo $url_base . ($pagina_actual + 1); ?>"
                       style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; border-radius:50%; border:1px solid var(--c-border); background:var(--c-surface); color:var(--c-primary); text-decoration:none; box-shadow:var(--shadow-sm); transition:all 0.2s; font-size:1.1rem;">
                        <i class="ri-arrow-right-s-line"></i>
                    </a>
                <?php else: ?>
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; border-radius:50%; border:1px solid var(--c-border); background:var(--c-bg); color:var(--c-text-muted); cursor:not-allowed; font-size:1.1rem;">
                        <i class="ri-arrow-right-s-line"></i>
                    </span>
                <?php endif; ?>

            </div>
            <!-- Indicador de página -->
            <p style="text-align:center; color:var(--c-text-muted); font-size:0.9rem; margin-bottom: 3rem;">
                Mostrando página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?> 
                (<?php echo $total_mascotas; ?> mascotas en total)
            </p>
            <?php endif; ?>

        </div>
    </main>
    <footer class="footer">...</footer>
    <script src="script.js"></script>
</body>
</html>
