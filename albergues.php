<?php 
session_start();
require_once 'conexion.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albergues - ConectaPet</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
<?php $active_nav = 'albergues'; include 'header.php'; ?>

    <main style="padding-top: 100px; min-height: 80vh;">
        <section id="albergues" class="section">
            <div class="container">
                <div class="section-title">
                    <h2 data-i18n="albergues_title">Nuestros Albergues</h2>
                    <p data-i18n="albergues_sub">Conoce los refugios con los que trabajamos y a sus maravillosos peludos listos para ser parte de tu familia.</p>
                </div>

                <div class="albergues-grid">
                    <?php
                    // 2. Hacemos la consulta para traer todos los albergues de la base de datos
                    $sql = "SELECT * FROM albergues";
                    $resultado = $conn->query($sql);

                    // 3. Verificamos si hay albergues registrados
                    if ($resultado->num_rows > 0) {
                        // 4. Imprimimos una tarjeta HTML por cada albergue que exista
                        while($row = $resultado->fetch_assoc()) {
                            ?>
                            <div class="albergue-card">
                                <div class="albergue-logo">
                                    <?php if (!empty($row["logo_url"])): ?>
                                        <img src="<?php echo htmlspecialchars($row["logo_url"]); ?>" alt="Logo <?php echo htmlspecialchars($row["nombre"]); ?>">
                                    <?php else: ?>
                                        <i class="ri-home-smile-2-fill"></i>
                                    <?php endif; ?>
                                </div>
                                <h3 class="albergue-name"><?php echo htmlspecialchars($row["nombre"]); ?></h3>
                                <div class="albergue-loc">
                                    <i class="ri-map-pin-line"></i> <?php echo htmlspecialchars($row["direccion"]); ?>
                                </div>
                                <p class="pet-desc" style="margin-bottom: 1.5rem;">
                                    <?php 
                                        // Recortamos la descripción si es muy larga
                                        $desc = htmlspecialchars($row["descripcion"]);
                                        echo strlen($desc) > 85 ? substr($desc, 0, 85) . "..." : $desc;
                                    ?>
                                </p>
                                <!-- Enviamos por URL el ID del albergue para cargarlo en la vista individual -->
                                <a href="albergue-perfil.php?id=<?php echo $row["id"]; ?>" class="btn btn-outline btn-block" data-i18n="albergues_btn">Ver Albergue y Mascotas</a>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div style='grid-column: 1 / -1; text-align: center;'><h3>No hay albergues registrados aún.</h3></div>";
                    }
                    ?>
                </div>
                
                <div class="section-title" style="margin-top: 6rem;">
                    <h2 data-i18n="vet_title">Veterinarias Aliadas</h2>
                    <p data-i18n="vet_sub">Las clínicas que trabajan de la mano con nosotros cuidando la salud de nuestras mascotas.</p>
                </div>

                <div class="albergues-grid">
                    <?php
                    $sql_vet = "SELECT * FROM veterinarias";
                    $resultado_vet = $conn->query($sql_vet);

                    if ($resultado_vet && $resultado_vet->num_rows > 0) {
                        while($vet = $resultado_vet->fetch_assoc()) {
                            ?>
                            <div class="albergue-card" style="border-top: 4px solid var(--c-primary);">
                                <div class="albergue-logo" style="border-color: var(--c-primary);">
                                    <?php if (!empty($vet["logo_url"])): ?>
                                        <img src="<?php echo htmlspecialchars($vet["logo_url"]); ?>" alt="Logo <?php echo htmlspecialchars($vet["nombre"]); ?>">
                                    <?php else: ?>
                                        <!-- Ícono genérico para clínica -->
                                        <i class="ri-hospital-fill"></i>
                                    <?php endif; ?>
                                </div>
                                <h3 class="albergue-name"><?php echo htmlspecialchars($vet["nombre"]); ?></h3>
                                <div class="albergue-loc" style="margin-bottom: 0.5rem;">
                                    <i class="ri-map-pin-line"></i> <?php echo htmlspecialchars($vet["direccion"]); ?>
                                </div>
                                <div class="albergue-loc" style="color: var(--c-primary); font-weight: bold;">
                                    <i class="ri-phone-fill"></i> <?php echo htmlspecialchars($vet["telefono"]); ?>
                                </div>
                                <p class="pet-desc">
                                    <?php 
                                        $desc = htmlspecialchars($vet["descripcion"]);
                                        echo strlen($desc) > 85 ? substr($desc, 0, 85) . "..." : $desc;
                                    ?>
                                </p>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div style='grid-column: 1 / -1; text-align: center;'><p>Cargando veterinarias próxiamente.</p></div>";
                    }
                    ?>
                </div>

                <div class="section-title" style="margin-top: 6rem;">
                    <h2>Mapa de Ubicaciones</h2>
                    <p>Encuentra rápidamente los albergues y clínicas veterinarias más cercanos a ti.</p>
                </div>

                <div class="map-container" style="width: 100%; border-radius: var(--radius-lg); overflow: hidden; height: 450px; box-shadow: var(--shadow-md); border: 1px solid var(--c-border); margin-bottom: 2rem; position: relative;">
                    <!-- Iframe de Google Maps genérico centrado en la ciudad -->
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15831.970336207614!2d-79.47067739999999!3d-7.2416813499999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x904d36a0e28cfa59%3A0x338dd138e9ed012c!2sGuadalupe%2013841!5e0!3m2!1ses!2spe!4v1776621993373!5m2!1ses!2spe" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

            </div>
        </section>
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
                    <p>Haciendo la diferencia, una patita a la vez. Únete a la red más grande de adopción éticamente responsable.</p>
                </div>
                <div class="footer-col">
                    <h4>Enlaces</h4>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="albergues.php">Albergues</a></li>
                        <li><a href="donaciones.php">Donaciones</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Legales</h4>
                    <ul>
                        <li><a href="#">Términos y Condiciones</a></li>
                        <li><a href="#">Privacidad</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Boletín</h4>
                    <p>Recibe noticias de eventos de adopción</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Tu correo electrónico">
                        <button class="btn btn-primary" type="button">Unirme</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; 2026 ConectaPet. Todos los derechos reservados.
            </div>
        </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>
