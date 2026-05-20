<?php session_start(); require_once 'conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donaciones - ConectaPet</title>
    <link rel="stylesheet" href="style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
<?php $active_nav = 'donaciones'; include 'header.php'; ?>

    <main style="padding-top: 120px; padding-bottom: 5rem;">

        <!-- ══ HERO ══ -->
        <section style="background: linear-gradient(135deg, var(--c-primary) 0%, #2e6e52 100%); color: white; padding: 5rem 0; text-align: center; position: relative; overflow: hidden;">
            <div style="position:absolute; inset:0; background: radial-gradient(circle at 80% 50%, rgba(255,255,255,0.05), transparent 60%); pointer-events:none;"></div>
            <div class="container" style="position: relative; z-index: 1;">
                <span style="background: rgba(255,255,255,0.15); color:white; padding:0.4rem 1rem; border-radius:var(--radius-pill); font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; display:inline-block; margin-bottom:1.5rem;">
                    <i class="ri-hand-coin-line"></i> Donaciones
                </span>
                <h1 style="font-size:3rem; color:white; margin-bottom:1rem; line-height:1.2;" data-i18n="don_hero_title">
                    Tu ayuda puede <span style="color:#fbbf24;">salvar vidas</span> 💙
                </h1>
                <p style="font-size:1.15rem; opacity:0.9; max-width:600px; margin:0 auto 2.5rem; line-height:1.7;" data-i18n="don_hero_sub">
                    Con cada donación contribuyes a rescatar, alimentar y brindar atención médica a nuestras mascotas mientras esperan un hogar.
                </p>
                <div style="display:flex; gap:3rem; justify-content:center; flex-wrap:wrap; border-top:1px solid rgba(255,255,255,0.2); padding-top:2rem;">
                    <div style="text-align:center;"><div style="font-size:2rem; font-weight:800; color:#fbbf24;">2,760+</div><div style="font-size:0.9rem; opacity:0.8;">Rescates financiados</div></div>
                    <div style="text-align:center;"><div style="font-size:2rem; font-weight:800; color:#fbbf24;">4</div><div style="font-size:0.9rem; opacity:0.8;">Albergues beneficiados</div></div>
                    <div style="text-align:center;"><div style="font-size:2rem; font-weight:800; color:#fbbf24;">3 Clínicas</div><div style="font-size:0.9rem; opacity:0.8;">Veterinarias aliadas</div></div>
                </div>
            </div>
        </section>

        <!-- ══ 3 CATEGORÍAS ══ -->
        <section class="section">
            <div class="container">
                <div class="section-title">
                    <h2>¿A dónde va tu donación?</h2>
                    <p>Elige la causa que más te mueva. Cada categoría tiene un impacto real y medible. Tus donaciones serán distribuidas a donde más se necesite</p>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:2rem; margin-bottom:5rem;">

                    <!-- Alimentos -->
                    <div style="background:var(--c-surface); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm); border:1px solid var(--c-border); display:flex; flex-direction:column; transition:var(--transition);"
                         onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='var(--shadow-lg)'"
                         onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-sm)'">
                        <div style="height:200px; position:relative;">
                            <div style="position:absolute; inset:0; background:linear-gradient(135deg, #f59e0b, #d97706); display:flex; align-items:center; justify-content:center;">
                                <i class="ri-restaurant-fill" style="font-size:5rem; color:rgba(255,255,255,0.3);"></i>
                            </div>
                            <span style="position:absolute; bottom:1rem; left:1.5rem; background:rgba(0,0,0,0.3); color:white; padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:600;">Más solicitada</span>
                        </div>
                        <div style="padding:2rem; flex-grow:1; display:flex; flex-direction:column;">
                            <div style="width:50px; height:50px; background:rgba(245,158,11,0.12); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:1rem;">
                                <i class="ri-restaurant-fill" style="font-size:1.5rem; color:#f59e0b;"></i>
                            </div>
                            <h3 style="font-size:1.5rem; color:var(--c-primary); margin-bottom:0.75rem;">Alimentos</h3>
                            <p style="color:var(--c-text-muted); line-height:1.7; flex-grow:1;">Garantizamos comida diaria y de calidad para los rescatados. S/10 alimenta a 3 mascotas por un día.</p>
                            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin:1.25rem 0;">
                                <span style="background:rgba(245,158,11,0.1); color:#d97706; padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:600;">Balanceado</span>
                                <span style="background:rgba(245,158,11,0.1); color:#d97706; padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:600;">Snacks</span>
                                <span style="background:rgba(245,158,11,0.1); color:#d97706; padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:600;">Suplementos</span>
                            </div>
                            <a href="donar-general.php?tipo=alimentos" class="btn btn-block btn-large" style="background:#f59e0b; color:white; border-radius:var(--radius-pill);">
                                <i class="ri-hand-coin-fill"></i> Donar para Alimentos
                            </a>
                        </div>
                    </div>

                    <!-- Medicinas -->
                    <div style="background:var(--c-surface); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-md); border:2px solid var(--c-primary); display:flex; flex-direction:column; position:relative; transition:var(--transition);"
                         onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='var(--shadow-lg)'"
                         onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-md)'">
                        <div style="position:absolute; top:1rem; right:1rem; background:var(--c-primary); color:white; padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.75rem; font-weight:700; z-index:2;">⭐ Urgente</div>
                        <div style="height:200px; position:relative;">
                            <div style="position:absolute; inset:0; background:linear-gradient(135deg, var(--c-primary), #2e6e52); display:flex; align-items:center; justify-content:center;">
                                <i class="ri-first-aid-kit-fill" style="font-size:5rem; color:rgba(255,255,255,0.3);"></i>
                            </div>
                        </div>
                        <div style="padding:2rem; flex-grow:1; display:flex; flex-direction:column;">
                            <div style="width:50px; height:50px; background:rgba(31,74,56,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:1rem;">
                                <i class="ri-first-aid-kit-fill" style="font-size:1.5rem; color:var(--c-primary);"></i>
                            </div>
                            <h3 style="font-size:1.5rem; color:var(--c-primary); margin-bottom:0.75rem;">Medicinas</h3>
                            <p style="color:var(--c-text-muted); line-height:1.7; flex-grow:1;">Ayuda a cubrir vacunas, desparasitaciones, cirugías y emergencias veterinarias urgentes.</p>
                            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin:1.25rem 0;">
                                <span style="background:rgba(31,74,56,0.08); color:var(--c-primary); padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:600;">Vacunas</span>
                                <span style="background:rgba(31,74,56,0.08); color:var(--c-primary); padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:600;">Cirugías</span>
                                <span style="background:rgba(31,74,56,0.08); color:var(--c-primary); padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:600;">Desparasitación</span>
                            </div>
                            <a href="donar-general.php?tipo=medicinas" class="btn btn-primary btn-block btn-large">
                                <i class="ri-hand-coin-fill"></i> Donar para Medicinas
                            </a>
                        </div>
                    </div>

                    <!-- Refugios -->
                    <div style="background:var(--c-surface); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm); border:1px solid var(--c-border); display:flex; flex-direction:column; transition:var(--transition);"
                         onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='var(--shadow-lg)'"
                         onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-sm)'">
                        <div style="height:200px; position:relative;">
                            <div style="position:absolute; inset:0; background:linear-gradient(135deg, var(--c-accent), #c4774f); display:flex; align-items:center; justify-content:center;">
                                <i class="ri-home-heart-fill" style="font-size:5rem; color:rgba(255,255,255,0.3);"></i>
                            </div>
                        </div>
                        <div style="padding:2rem; flex-grow:1; display:flex; flex-direction:column;">
                            <div style="width:50px; height:50px; background:rgba(181,97,67,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:1rem;">
                                <i class="ri-home-heart-fill" style="font-size:1.5rem; color:var(--c-accent);"></i>
                            </div>
                            <h3 style="font-size:1.5rem; color:var(--c-primary); margin-bottom:0.75rem;">Refugios</h3>
                            <p style="color:var(--c-text-muted); line-height:1.7; flex-grow:1;">Apoya con mantas, mejoras de infraestructura y limpieza para mantener espacios dignos.</p>
                            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin:1.25rem 0;">
                                <span style="background:rgba(181,97,67,0.1); color:var(--c-accent); padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:600;">Mantas</span>
                                <span style="background:rgba(181,97,67,0.1); color:var(--c-accent); padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:600;">Limpieza</span>
                                <span style="background:rgba(181,97,67,0.1); color:var(--c-accent); padding:0.3rem 0.8rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:600;">Infraestructura</span>
                            </div>
                            <a href="donar-general.php?tipo=refugios" class="btn btn-secondary btn-block btn-large">
                                <i class="ri-hand-coin-fill"></i> Donar para Refugios
                            </a>
                        </div>
                    </div>

                </div>

                <!-- ══ DONAR A ALBERGUE ESPECÍFICO ══ -->
                <div class="section-title" style="margin-top:2rem;">
                    <h2>Donar a un Albergue Específico</h2>
                    <p>¿Tienes un albergue favorito? Selecciónalo y tu donación irá directamente a él.</p>
                </div>
                <div style="background:var(--c-surface); border-radius:var(--radius-lg); padding:2.5rem; box-shadow:var(--shadow-sm); border:1px solid var(--c-border); border-top:4px solid var(--c-secondary); margin-bottom:5rem;">
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.5rem;">
                        <?php
                        $albergues_lista = $conn->query("SELECT id, nombre, logo_url FROM albergues ORDER BY nombre ASC");
                        if ($albergues_lista && $albergues_lista->num_rows > 0):
                            while($alb = $albergues_lista->fetch_assoc()):
                        ?>
                        <a href="donar-general.php?tipo=albergue&albergue_id=<?php echo $alb['id']; ?>"
                           style="display:flex; align-items:center; gap:1rem; padding:1.2rem 1.5rem; background:var(--c-bg); border:1px solid var(--c-border); border-radius:var(--radius-md); text-decoration:none; color:var(--c-text); font-weight:600; transition:all 0.2s; box-shadow:var(--shadow-sm);"
                           onmouseover="this.style.borderColor='var(--c-primary)'; this.style.transform='translateY(-3px)'; this.style.color='var(--c-primary)';"
                           onmouseout="this.style.borderColor='var(--c-border)'; this.style.transform=''; this.style.color='var(--c-text)';">
                            <div style="width:48px; height:48px; border-radius:50%; overflow:hidden; flex-shrink:0; background:var(--c-surface); border:2px solid var(--c-border); display:flex; align-items:center; justify-content:center;">
                                <?php if(!empty($alb['logo_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($alb['logo_url']); ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
                                <?php else: ?>
                                    <i class="ri-home-smile-2-fill" style="color:var(--c-accent);"></i>
                                <?php endif; ?>
                            </div>
                            <span><?php echo htmlspecialchars($alb['nombre']); ?></span>
                            <i class="ri-arrow-right-s-line" style="margin-left:auto; color:var(--c-text-muted);"></i>
                        </a>
                        <?php endwhile; endif; ?>
                    </div>
                </div>

                <!-- ══ PELUDITO EN CONCRETO ══ -->
                <div style="background:linear-gradient(135deg, #fef3c7, #fde68a); border-radius:var(--radius-lg); padding:3rem; border:1px solid #fcd34d; display:grid; grid-template-columns:1fr auto; gap:2rem; align-items:center; flex-wrap:wrap;">
                    <div>
                        <div style="font-size:2.5rem; margin-bottom:0.75rem;">🐾</div>
                        <h3 style="font-size:1.6rem; color:#92400e; margin-bottom:0.75rem;">¿Deseas ayudar a un peludito en concreto?</h3>
                        <p style="color:#78350f; line-height:1.7; max-width:500px;">
                            Puedes donar directamente a una mascota específica. Tu aporte cubre sus necesidades médicas y de cuidado mientras espera ser adoptado.
                        </p>
                    </div>
                    <div style="text-align:center; flex-shrink:0;">
                        <a href="albergues.php" class="btn btn-large" style="background:#d97706; color:white; white-space:nowrap; display:inline-flex; align-items:center; gap:0.5rem;">
                            <i class="ri-search-eye-fill"></i> Ver Mascotas
                        </a>
                        <p style="color:#92400e; font-size:0.85rem; margin-top:0.75rem;">Entra al perfil de cualquier<br>mascota y dale a "Donar"</p>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="brand-col">
                    <a href="index.php" class="logo">
                        <div class="logo-bubble"><img src="logo.png" alt="ConectaPet Logo"></div>
                        <div class="logo-text">Conecta<span>Pet</span></div>
                    </a>
                    <p>Haciendo la diferencia, una patita a la vez.</p>
                </div>
                <div class="footer-col">
                    <h4>Enlaces</h4>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="albergues.php">Albergues</a></li>
                        <li><a href="donaciones.php">Donaciones</a></li>
                        <li><a href="noticias.php">Noticias</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Donaciones</h4>
                    <ul>
                        <li><a href="donar-general.php?tipo=alimentos">🍖 Alimentos</a></li>
                        <li><a href="donar-general.php?tipo=medicinas">💊 Medicinas</a></li>
                        <li><a href="donar-general.php?tipo=refugios">🏠 Refugios</a></li>
                    </ul>
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
