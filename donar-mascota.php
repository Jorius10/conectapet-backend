<?php
session_start();
require_once 'conexion.php';

// ── Guard: solo usuarios logueados ──────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    $mid = $_GET['mascota_id'] ?? '';
    header('Location: login.php?redir=' . urlencode('donar-mascota.php?mascota_id=' . $mid));
    exit;
}
$user_nombre = $_SESSION['user_nombre'] ?? '';
$user_correo = $_SESSION['user_correo'] ?? '';


$mascota_id = isset($_GET['mascota_id']) ? intval($_GET['mascota_id']) : 0;
$mascota = null;

if ($mascota_id > 0) {
    $res = $conn->query("SELECT mascotas.*, albergues.nombre AS albergue_nombre 
                         FROM mascotas 
                         LEFT JOIN albergues ON mascotas.albergue_id = albergues.id 
                         WHERE mascotas.id = $mascota_id");
    $mascota = $res->fetch_assoc();
}

if (!$mascota) {
    echo "<script>alert('Mascota no encontrada.'); window.location.href='albergues.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donar a <?php echo htmlspecialchars($mascota['nombre']); ?> - ConectaPet</title>
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* Monto Radios */
        .monto-opciones {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        .monto-opciones input[type="radio"] {
            display: none;
        }
        .monto-opciones label {
            cursor: pointer;
            padding: 0.8rem 1.5rem;
            border-radius: var(--radius-pill);
            border: 2px solid var(--c-border);
            background: var(--c-bg);
            font-weight: 600;
            color: var(--c-text-muted);
            transition: all 0.2s;
            display: inline-block;
            font-size: 1rem;
        }
        .monto-opciones label:hover {
            border-color: var(--c-primary);
            color: var(--c-primary);
        }
        .monto-opciones input[type="radio"]:checked + label {
            background: var(--c-primary);
            color: white;
            border-color: var(--c-primary);
            box-shadow: var(--shadow-sm);
        }

        /* Método de pago info box */
        #info-pago {
            background: rgba(31, 74, 56, 0.05);
            border: 1px solid var(--c-primary);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-top: 1rem;
            display: none;
        }
        #info-pago h3 {
            color: var(--c-primary);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        #info-pago img {
            display: block;
            margin: 1rem auto;
            border-radius: var(--radius-md);
            max-width: 200px;
            box-shadow: var(--shadow-md);
        }
        #info-pago p {
            color: var(--c-text-muted);
            font-size: 0.95rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        #info-pago strong {
            color: var(--c-text);
        }

        textarea.form-control {
            resize: vertical;
        }
    </style>
</head>
<body>
<?php $active_nav = 'albergues'; include 'header.php'; ?>

    <main style="padding-top: 150px; padding-bottom: 6rem; min-height: 80vh;">
        <div class="container">
            <!-- Botón volver -->
            <a href="mascota-perfil.php?id=<?php echo $mascota_id; ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem; color: var(--c-primary); font-weight: bold; text-decoration: none;">
                <i class="ri-arrow-left-line"></i> Volver al perfil de <?php echo htmlspecialchars($mascota['nombre']); ?>
            </a>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 3rem; align-items: start;">

                <!-- ====== COLUMNA IZQUIERDA: Tarjeta de la Mascota ====== -->
                <div style="position: sticky; top: 120px; background: white; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md); border: 1px solid var(--c-border);">
                    <!-- Foto circular con overlay de corazón -->
                    <div style="position: relative; height: 260px; overflow: hidden;">
                        <img src="<?php echo htmlspecialchars($mascota['foto_url']); ?>" 
                             style="width: 100%; height: 100%; object-fit: cover;" 
                             alt="<?php echo htmlspecialchars($mascota['nombre']); ?>">
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);"></div>
                        <div style="position: absolute; bottom: 1rem; left: 1.5rem; color: white;">
                            <h2 style="font-size: 1.8rem; margin: 0;"><?php echo htmlspecialchars($mascota['nombre']); ?></h2>
                            <span style="font-size: 0.9rem; opacity: 0.85;"><?php echo htmlspecialchars($mascota['albergue_nombre']); ?></span>
                        </div>
                    </div>
                    <div style="padding: 1.5rem;">
                        <p style="color: var(--c-text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem; text-align: center;">
                            <i class="ri-heart-fill" style="color: var(--c-accent);"></i>
                            Tu donación mejora directamente la vida de <strong><?php echo htmlspecialchars($mascota['nombre']); ?></strong> mientras espera su hogar.
                        </p>
                        <hr style="border: 0; border-top: 1px solid var(--c-border); margin-bottom: 1.5rem;">
                        <div style="display: flex; flex-direction: column; gap: 0.8rem; font-size: 0.9rem; color: var(--c-text-muted);">
                            <div><i class="ri-guide-fill" style="color:var(--c-accent); margin-right:5px;"></i><strong><?php echo htmlspecialchars($mascota['especie']); ?></strong> · <?php echo htmlspecialchars($mascota['sexo']); ?></div>
                            <div><i class="ri-calendar-event-line" style="color:var(--c-accent); margin-right:5px;"></i><strong><?php echo htmlspecialchars($mascota['edad_texto']); ?></strong></div>
                            <div><i class="ri-heart-pulse-line" style="color:var(--c-accent); margin-right:5px;"></i><?php echo htmlspecialchars($mascota['estado_medico']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- ====== COLUMNA DERECHA: Formulario de Donación ====== -->
                <div style="background: white; border-radius: var(--radius-lg); padding: 3rem; box-shadow: var(--shadow-md); border: 1px solid var(--c-border);">
                    <!-- Encabezado -->
                    <div style="margin-bottom: 2.5rem;">
                        <span style="background: rgba(181,97,67,0.12); color: var(--c-accent); padding: 0.4rem 1rem; border-radius: var(--radius-pill); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="ri-hand-coin-fill"></i> Donación Dirigida
                        </span>
                        <h1 style="font-size: 2rem; color: var(--c-primary); margin-top: 1rem; margin-bottom: 0.5rem;">
                            Donar a <?php echo htmlspecialchars($mascota['nombre']); ?>
                        </h1>
                        <p style="color: var(--c-text-muted);">Elige el monto y el método de pago. Cada sol cuenta.</p>
                    </div>

                    <form action="procesar_donacion.php" method="POST">
                        <input type="hidden" name="mascota_id" value="<?php echo $mascota['id']; ?>">
                        <input type="hidden" name="tipo" value="mascota">

                        <!-- Nombre y correo -->
                        <h4 style="color: var(--c-accent); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px dashed var(--c-border);">1. Tus datos</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Nombre completo *</label>
                                <input type="text" name="nombre" class="form-control" required placeholder="Tu nombre" value="<?php echo htmlspecialchars($user_nombre); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Correo electrónico *</label>
                                <input type="email" name="correo" class="form-control" required placeholder="correo@ejemplo.com" value="<?php echo htmlspecialchars($user_correo); ?>">
                            </div>
                        </div>

                        <!-- Monto -->
                        <h4 style="color: var(--c-accent); margin-top: 1rem; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px dashed var(--c-border);">2. Elige el monto (S/)</h4>
                        <div class="monto-opciones" id="monto-opciones">
                            <input type="radio" id="m10"  name="monto" value="10"  required>
                            <label for="m10">S/ 10</label>

                            <input type="radio" id="m25"  name="monto" value="25">
                            <label for="m25">S/ 25</label>

                            <input type="radio" id="m50"  name="monto" value="50">
                            <label for="m50">S/ 50</label>

                            <input type="radio" id="m100" name="monto" value="100">
                            <label for="m100">S/ 100</label>

                            <input type="radio" id="otro" name="monto" value="otro">
                            <label for="otro">Otro monto</label>
                        </div>

                        <!-- Campo "Otro monto" (se muestra dinámicamente) -->
                        <div class="form-group" id="campo-monto-otro" style="display:none;">
                            <label class="form-label">Ingresa el monto deseado *</label>
                            <input type="number" name="monto_otro" id="monto_otro" class="form-control" step="0.50" min="1" placeholder="Ej: 75">
                        </div>

                        <!-- Método de pago -->
                        <h4 style="color: var(--c-accent); margin-top: 1.5rem; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px dashed var(--c-border);">3. Método de pago</h4>
                        <div class="form-group">
                            <label class="form-label">¿Cómo deseas pagar?</label>
                            <select name="metodo_pago" id="metodo_pago" class="form-select form-control" required>
                                <option value="">Elige un método de pago...</option>
                                <option value="Yape">💜 Yape</option>
                                <option value="Plin">🔵 Plin</option>
                                <option value="Transferencia">🏦 Transferencia Bancaria</option>
                            </select>
                        </div>

                        <!-- Caja dinámica de info pago -->
                        <div id="info-pago"></div>

                        <!-- Comentario -->
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label class="form-label">Comentario o dedicatoria (opcional)</label>
                            <textarea name="comentario" class="form-control" rows="3" placeholder="Escríbele algo bonito a <?php echo htmlspecialchars($mascota['nombre']); ?>..."></textarea>
                        </div>

                        <!-- Botón enviar -->
                        <div style="margin-top: 2.5rem;">
                            <button type="submit" class="btn btn-primary btn-large btn-block" style="font-size: 1.15rem; padding: 1.2rem;">
                                <i class="ri-heart-fill"></i> Confirmar Donación para <?php echo htmlspecialchars($mascota['nombre']); ?>
                            </button>
                            <p style="text-align: center; margin-top: 1rem; color: var(--c-text-muted); font-size: 0.85rem;">
                                Todas las donaciones van directamente al cuidado de esta mascota en el albergue.
                            </p>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </main>

    <footer class="footer" style="margin-top: 0;">
        <div class="container">
            <div class="footer-bottom">
                &copy; 2026 ConectaPet. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <script>
        // ===== LÓGICA: Mostrar/ocultar campo "otro monto" =====
        const montoRadios = document.querySelectorAll('input[name="monto"]');
        const campoOtro = document.getElementById('campo-monto-otro');
        const inputOtro = document.getElementById('monto_otro');

        montoRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'otro' && radio.checked) {
                    campoOtro.style.display = 'block';
                    inputOtro.required = true;
                    inputOtro.focus();
                } else {
                    campoOtro.style.display = 'none';
                    inputOtro.required = false;
                    inputOtro.value = '';
                }
            });
        });

        // ===== LÓGICA: Mostrar info según método de pago =====
        const metodoSelect = document.getElementById('metodo_pago');
        const infoPagoDiv = document.getElementById('info-pago');

        const infoPorMetodo = {
            'Yape': `
                <h3><i class="ri-smartphone-fill"></i> Pago por Yape</h3>
                <p>Escanea el código QR o envía al número registrado:</p>
                <p style="font-size: 1.2rem;"><strong>📱 965 432 100</strong></p>
                <p style="font-size: 0.85rem; color: #999;">Titular: ConectaPet</p>
            `,
            'Plin': `
                <h3><i class="ri-smartphone-fill"></i> Pago por Plin</h3>
                <p>Escanea el código QR o transfiere al número:</p>
                <p style="font-size: 1.2rem;"><strong>📱 965 432 100</strong></p>
                <p style="font-size: 0.85rem; color: #999;">Titular: ConectaPet</p>
            `,
            'Transferencia': `
                <h3><i class="ri-bank-fill"></i> Transferencia Bancaria (BCP)</h3>
                <p><strong>N° Cuenta:</strong> 123-45678901-0-12</p>
                <p><strong>CCI:</strong> 002 1234 56789012 34567</p>
                <p><strong>Titular:</strong> ConectaPet Asociación</p>
                <p style="font-size: 0.8rem; color: #999; margin-top: 0.5rem;">Por favor incluye el nombre de la mascota en la descripción.</p>
            `
        };

        metodoSelect.addEventListener('change', function () {
            const metodo = this.value;
            if (infoPorMetodo[metodo]) {
                infoPagoDiv.innerHTML = infoPorMetodo[metodo];
                infoPagoDiv.style.display = 'block';
            } else {
                infoPagoDiv.style.display = 'none';
            }
        });
    </script>
</body>
</html>
