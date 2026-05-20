/**
 * i18n.js — Sistema de traducción ES / EN para ConectaPet
 * Basado en atributos data-i18n en el HTML.
 * La preferencia se guarda en localStorage.
 */

const translations = {
    es: {
        // NAV
        nav_inicio:     'Inicio',
        nav_albergues:  'Albergues',
        nav_donaciones: 'Donaciones',
        nav_noticias:   'Noticias',

        // AUTH HEADER
        auth_login:     'Iniciar Sesión',
        auth_register:  'Registrarse',
        auth_dashboard: 'Dashboard',
        auth_logout:    'Cerrar Sesión',

        // INDEX HERO
        hero_badge:    'Adopta, no compres',
        hero_title:    'Encuentra a tu <span>mejor amigo</span>',
        hero_desc:     'Nuestra plataforma conecta albergues responsables con familias amorosas. Descubre mascotas esperando un hogar, realiza donaciones y sé parte del cambio.',
        hero_btn1:     'Ver Albergues',
        hero_btn2:     'Donar',
        hero_stat1_n:  '+500',
        hero_stat1_l:  'Mascotas Adoptadas',
        hero_stat2_n:  '24',
        hero_stat2_l:  'Albergues Aliados',

        // FOOTER
        footer_links:    'Enlaces',
        footer_legal:    'Legales',
        footer_terms:    'Términos y Condiciones',
        footer_privacy:  'Privacidad',
        footer_news:     'Boletín',
        footer_news_sub: 'Recibe noticias de eventos de adopción',
        footer_join:     'Unirme',
        footer_copy:     '© 2026 ConectaPet. Todos los derechos reservados.',

        // ALBERGUES
        albergues_title: 'Nuestros Albergues',
        albergues_sub:   'Conoce los refugios con los que trabajamos y a sus maravillosos peludos listos para ser parte de tu familia.',
        albergues_btn:   'Ver Albergue y Mascotas',
        vet_title:       'Veterinarias Aliadas',
        vet_sub:         'Las clínicas que trabajan de la mano con nosotros cuidando la salud de nuestras mascotas.',
        map_title:       'Mapa de Ubicaciones',
        map_sub:         'Encuentra rápidamente los albergues y clínicas veterinarias más cercanos a ti.',

        // DONACIONES
        don_hero_title:  '¿Cómo quieres ayudar hoy?',
        don_hero_sub:    'Cada aporte, grande o pequeño, transforma vidas. Elige la forma que más te inspire.',

        // NOTICIAS
        news_hero_title: 'Noticias y Novedades',
        news_hero_sub:   'Mantente informado sobre adopciones, campañas y el impacto de la comunidad ConectaPet.',

        // GENÉRICO
        back_site:       'Volver al sitio web',
        see_pets:        'Ver Mascotas',
        home:            'Inicio',
    },

    en: {
        // NAV
        nav_inicio:     'Home',
        nav_albergues:  'Shelters',
        nav_donaciones: 'Donations',
        nav_noticias:   'News',

        // AUTH HEADER
        auth_login:     'Log In',
        auth_register:  'Sign Up',
        auth_dashboard: 'Dashboard',
        auth_logout:    'Log Out',

        // INDEX HERO
        hero_badge:    'Adopt, don\'t shop',
        hero_title:    'Find your <span>best friend</span>',
        hero_desc:     'Our platform connects responsible shelters with loving families. Discover pets waiting for a home, make donations and be part of the change.',
        hero_btn1:     'View Shelters',
        hero_btn2:     'Donate',
        hero_stat1_n:  '+500',
        hero_stat1_l:  'Pets Adopted',
        hero_stat2_n:  '24',
        hero_stat2_l:  'Partner Shelters',

        // FOOTER
        footer_links:    'Links',
        footer_legal:    'Legal',
        footer_terms:    'Terms & Conditions',
        footer_privacy:  'Privacy Policy',
        footer_news:     'Newsletter',
        footer_news_sub: 'Receive news about adoption events',
        footer_join:     'Join',
        footer_copy:     '© 2026 ConectaPet. All rights reserved.',

        // ALBERGUES
        albergues_title: 'Our Shelters',
        albergues_sub:   'Meet the shelters we work with and their wonderful furry friends ready to join your family.',
        albergues_btn:   'View Shelter & Pets',
        vet_title:       'Partner Veterinary Clinics',
        vet_sub:         'The clinics working alongside us to care for our pets\' health.',
        map_title:       'Location Map',
        map_sub:         'Quickly find the nearest shelters and veterinary clinics to you.',

        // DONACIONES
        don_hero_title:  'How would you like to help today?',
        don_hero_sub:    'Every contribution, big or small, transforms lives. Choose the way that inspires you most.',

        // NOTICIAS
        news_hero_title: 'News & Updates',
        news_hero_sub:   'Stay informed about adoptions, campaigns and the impact of the ConectaPet community.',

        // GENÉRICO
        back_site:       'Back to website',
        see_pets:        'View Pets',
        home:            'Home',
    }
};

// ── Idioma activo ─────────────────────────────────────────────────────
function getLang() {
    return localStorage.getItem('cp_lang') || 'es';
}

function setLang(lang) {
    localStorage.setItem('cp_lang', lang);
    applyLang(lang);
    updateLangUI(lang);
}

// ── Aplicar traducciones ──────────────────────────────────────────────
function applyLang(lang) {
    const t = translations[lang];
    if (!t) return;

    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (t[key] !== undefined) {
            if (el.tagName === 'INPUT' && el.placeholder) {
                el.placeholder = t[key];
            } else if (el.hasAttribute('data-i18n-html')) {
                el.innerHTML = t[key];
            } else {
                el.textContent = t[key];
            }
        }
    });

    // Actualizar atributo lang del html
    document.documentElement.lang = lang;
}

// ── Actualizar UI del selector ────────────────────────────────────────
function updateLangUI(lang) {
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.classList.toggle('lang-active', btn.dataset.lang === lang);
    });
}

// ── Init al cargar ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const lang = getLang();
    applyLang(lang);
    updateLangUI(lang);

    // Eventos en los botones del selector
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', () => setLang(btn.dataset.lang));
    });
});
