<?php 
session_start();
require_once 'conexion.php';

$mascota_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Traemos los datos de la mascota y el nombre de su albergue uniendo las dos tablas
$sql = "SELECT mascotas.*, albergues.nombre AS albergue_nombre 
        FROM mascotas 
        LEFT JOIN albergues ON mascotas.albergue_id = albergues.id 
        WHERE mascotas.id = $mascota_id";
$resultado = $conn->query($sql);

if ($resultado->num_rows == 0) {
    die("Mascota no encontrada.");
}
$mascota = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conoce a <?php echo htmlspecialchars($mascota['nombre']); ?> - ConectaPet</title>
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
<?php $active_nav = 'albergues'; include 'header.php'; ?>

    <main style="padding-top: 150px; min-height: 80vh; padding-bottom: 5rem;">
        <div class="container">
            <!-- Botón para regresar al albergue -->
            <a href="albergue-perfil.php?id=<?php echo $mascota['albergue_id']; ?>" style="display: inline-block; margin-bottom: 2rem; color: var(--c-primary); font-weight: bold; text-decoration: none;">
                <i class="ri-arrow-left-line"></i> Volver a <?php echo htmlspecialchars($mascota['albergue_nombre']); ?>
            </a>

            <!-- Grilla Principal del Perfil -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: start;">
                
                <!-- Galería de fotos interactiva (Carrusel) -->
                <div class="mascota-gallery">
                    <div style="border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-lg); background: var(--c-surface); margin-bottom: 1rem;">
                        <?php if (!empty($mascota['foto_url'])): ?>
                            <!-- La foto principal en grande -->
                            <img id="main-pet-image" src="<?php echo htmlspecialchars($mascota['foto_url']); ?>" style="width: 100%; height: 500px; object-fit: cover; transition: opacity 0.2s;" alt="<?php echo htmlspecialchars($mascota['nombre']); ?>">
                        <?php else: ?>
                            <div style="width: 100%; height: 500px; display:flex; align-items:center; justify-content:center; background:var(--c-primary);">
                                <i class="ri-baidu-line" style="font-size: 8rem; color:white;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Carrusel de fotos pequeñas (Thumbnails) -->
                    <?php if (!empty($mascota['foto_url'])): 
                        // PHP es súper inteligente: Lee la carpeta donde está la foto principal y busca si guardaste más fotos
                        $carpeta_fotos = dirname($mascota['foto_url']);
                        $todas_las_fotos = glob($carpeta_fotos . "/*.{jpg,jpeg,png}", GLOB_BRACE);
                        
                        if(count($todas_las_fotos) > 1):
                    ?>
                    <div style="display: flex; gap: 0.8rem; overflow-x: auto; padding-bottom: 0.5rem;">
                        <?php foreach($todas_las_fotos as $foto): ?>
                            <!-- Al hacer clic, enviamos la foto clickeada al cuadro principal de arriba (JS nativo) -->
                            <img src="<?php echo htmlspecialchars($foto); ?>" 
                                 class="pet-thumbnail" 
                                 onclick="document.getElementById('main-pet-image').src = this.src; 
                                          document.querySelectorAll('.pet-thumbnail').forEach(n=>n.style.borderColor='transparent'); 
                                          this.style.borderColor='var(--c-primary)';" 
                                 style="width: 100px; height: 100px; object-fit: cover; border-radius: var(--radius-md); cursor: pointer; border: 3px solid transparent; transition: all 0.2s; <?php echo ($foto == $mascota['foto_url']) ? 'border-color: var(--c-primary);' : ''; ?>">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; endif; ?>
                </div>
                
                <!-- Detalles e Información -->
                <div class="mascota-details">
                    <h1 style="font-size: 3rem; color: var(--c-text); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($mascota['nombre']); ?></h1>
                    
                    <?php
                    // Lógica para color de estados
                    $tramite = strtolower(trim($mascota['estado_tramite']));
                    $color_badge = "var(--c-primary)";
                    $icon_badge = "ri-checkbox-circle-fill";
                    
                    if ($tramite == 'disponible') {
                        $color_badge = "#10b981"; // Verde esmeralda (Disponible)
                        $icon_badge = "ri-check-double-line";
                    } elseif ($tramite == 'con solicitud') {
                        $color_badge = "#3b82f6"; // Azul vibrante para destacar peticiones nuevas
                        $icon_badge = "ri-mail-send-fill";
                    } elseif ($tramite == 'en proceso') {
                        $color_badge = "#f59e0b"; // Naranja / Ámbar (En Proceso)
                        $icon_badge = "ri-time-line";
                    } elseif ($tramite == 'adoptado') {
                        $color_badge = "#ef4444"; // Rojo (Adoptado)
                        $icon_badge = "ri-heart-3-fill";
                    }
                    ?>
                    
                    <!-- Estado Trámite como Badge a Color -->
                    <div style="margin-bottom: 2rem;">
                        <span class="tag" style="background: <?php echo $color_badge; ?>; color: white; padding: 0.6rem 1.2rem; border-radius: var(--radius-pill); font-weight: bold; box-shadow: var(--shadow-sm); display: inline-block;">
                            <i class="<?php echo $icon_badge; ?>" style="margin-right: 5px;"></i> <?php echo htmlspecialchars($mascota['estado_tramite']); ?>
                        </span>
                    </div>
                    
                    <p style="font-size: 1.1rem; color: var(--c-text-muted); line-height: 1.8; margin-bottom: 2.5rem;">
                        <?php echo nl2br(htmlspecialchars($mascota['descripcion'])); ?>
                    </p>
                    
                    <!-- Tarjetitas de características (Stats) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 3rem;">
                        <div style="background: var(--c-surface); border: 1px solid var(--c-border); padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                            <p style="color: var(--c-text-muted); font-size: 0.9rem; margin-bottom: 0.3rem;"><i class="ri-guide-fill"></i> Especie / Sexo</p>
                            <h4 style="font-size: 1.1rem; color: var(--c-primary); margin:0;">
                                <?php echo htmlspecialchars($mascota['especie']) . ' - ' . htmlspecialchars($mascota['sexo']); ?>
                            </h4>
                        </div>
                        <div style="background: var(--c-surface); border: 1px solid var(--c-border); padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                            <p style="color: var(--c-text-muted); font-size: 0.9rem; margin-bottom: 0.3rem;"><i class="ri-calendar-event-fill"></i> Edad</p>
                            <h4 style="font-size: 1.1rem; color: var(--c-primary); margin:0;">
                                <?php echo htmlspecialchars($mascota['edad_texto']); ?>
                            </h4>
                        </div>
                        <div style="grid-column: 1 / -1; background: var(--c-surface); border: 1px solid var(--c-border); padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                            <p style="color: var(--c-text-muted); font-size: 0.9rem; margin-bottom: 0.3rem;"><i class="ri-heart-pulse-fill"></i> Estado Médico</p>
                            <h4 style="font-size: 1.1rem; color: var(--c-primary); margin:0;">
                                <?php echo htmlspecialchars($mascota['estado_medico']); ?>
                            </h4>
                        </div>
                    </div>
                    
                    <!-- Botones de Acción Solicitados -->
                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                        <?php if ($tramite == 'disponible' || $tramite == 'con solicitud'): ?>
                            <a href="adoptar.php?id=<?php echo $mascota_id; ?>" class="btn btn-primary btn-large" style="flex: 1; text-align: center; justify-content:center;">
                                <i class="ri-home-heart-fill"></i> Adoptar a <?php echo htmlspecialchars($mascota['nombre']); ?>
                            </a>
                        <?php else: ?>
                            <!-- Botón Gris Desactivado si NO está disponible -->
                            <div style="flex: 1; text-align: center; justify-content:center; padding: 1rem 2rem; background: var(--c-bg); color: var(--c-text-muted); font-weight: bold; border-radius: var(--radius-pill); border: 2px solid var(--c-border); cursor: not-allowed; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="ri-forbid-2-line"></i> Adopción Cerrada
                            </div>
                        <?php endif; ?>
                        
                        <a href="donar-mascota.php?mascota_id=<?php echo $mascota_id; ?>" class="btn btn-secondary btn-large" style="flex: 1; text-align: center; justify-content:center;">
                            <i class="ri-hand-coin-fill"></i> Donarle Específicamente
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="brand-col">
                    <a href="index.php" class="logo">
                        <div class="logo-bubble">
                            <img src="logo.png" alt="ConectaPet Logo">
                        </div>
                        <div class="logo-text">Conecta<span>Pet</span></div>
                    </a>
                    <p>Haciendo la diferencia, una patita a la vez.</p>
                </div>
                
                <!-- Cuadros explicativos del proceso de adopción -->
                <div style="grid-column: span 3; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: -1rem;">
                    <div style="background: var(--c-bg); border: 1px solid var(--c-border); padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(31, 74, 56, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                            <i class="ri-search-eye-line" style="font-size: 1.5rem; color: var(--c-primary);"></i>
                        </div>
                        <h4 style="color: var(--c-primary); margin-bottom: 0.5rem;">1. Encuentra</h4>
                        <p style="font-size: 0.85rem; color: var(--c-text-muted); line-height: 1.4;">Busca y conoce a tu mascota ideal.</p>
                    </div>
                    
                    <div style="background: var(--c-bg); border: 1px solid var(--c-border); padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(31, 74, 56, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                            <i class="ri-file-list-3-line" style="font-size: 1.5rem; color: var(--c-primary);"></i>
                        </div>
                        <h4 style="color: var(--c-primary); margin-bottom: 0.5rem;">2. Solicita</h4>
                        <p style="font-size: 0.85rem; color: var(--c-text-muted); line-height: 1.4;">Llena una solicitud de adopción online.</p>
                    </div>
                    
                    <div style="background: var(--c-bg); border: 1px solid var(--c-border); padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-top: 3px solid var(--c-accent);">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(181, 97, 67, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                            <i class="ri-home-heart-fill" style="font-size: 1.5rem; color: var(--c-accent);"></i>
                        </div>
                        <h4 style="color: var(--c-accent); margin-bottom: 0.5rem;">3. Adopta</h4>
                        <p style="font-size: 0.85rem; color: var(--c-text-muted); line-height: 1.4;">Dale un hogar para toda la vida.</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; 2026 ConectaPet. Todos los derechos reservados.
            </div>
        </div>
    </footer>
</body>
</html>
