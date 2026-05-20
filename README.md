# ConectaPet — Backend

Repositorio del servidor del proyecto **ConectaPet**, plataforma peruana de adopción responsable de mascotas y donaciones a albergues.

## 📁 Estructura

```
conectapet-backend/
├── index.php              # Página principal
├── albergues.php          # Listado de albergues
├── albergue-perfil.php    # Perfil individual de albergue
├── mascota-perfil.php     # Perfil individual de mascota
├── adoptar.php            # Formulario de solicitud de adopción
├── donaciones.php         # Página principal de donaciones
├── donar-general.php      # Donación por categoría / albergue
├── donar-mascota.php      # Donación dirigida a una mascota
├── procesar_donacion.php  # Procesa y guarda la donación en BD
├── noticias.php           # Listado de noticias
├── noticia.php            # Artículo individual
├── login.php              # Login + Registro unificado
├── mi-perfil.php          # Dashboard del usuario público
├── dashboard.php          # Panel de administración
├── dash-adopciones.php    # Gestión de adopciones (admin)
├── dash-donaciones.php    # Gestión de donaciones (admin)
├── dash_layout.php        # Layout compartido del dashboard
├── header.php             # Header compartido (todas las páginas)
├── logout.php             # Cierre de sesión admin
├── logout-user.php        # Cierre de sesión usuario
├── terminos.php           # Términos y condiciones
├── privacidad.php         # Política de privacidad
├── suscribir.php          # Suscripción al boletín
├── mailer.php             # ⚠️  NO incluido (datos sensibles)
├── conexion.php           # ⚠️  NO incluido (datos sensibles)
├── conexion.example.php   # ✅  Plantilla de conexión
├── database.sql           # Esquema completo de la BD
└── phpmailer/             # Librería PHPMailer
```

## ⚙️ Requisitos

- PHP 8.0+
- MySQL 5.7+ / MariaDB
- Apache (XAMPP recomendado en local)
- PHPMailer (incluido en `/phpmailer`)

## 🚀 Instalación local

```bash
# 1. Clona el repositorio dentro de htdocs
git clone https://github.com/TU-USUARIO/conectapet-backend.git "pagina proyecto"

# 2. Copia y configura la conexión
cp conexion.example.php conexion.php
# Edita conexion.php con tus credenciales MySQL

# 3. Importa la base de datos
# Abre phpMyAdmin → Importar → database.sql

# 4. Configura mailer.php con tus credenciales SMTP

# 5. Accede en: http://localhost/pagina%20proyecto/
```

## 🔐 Credenciales por defecto (solo desarrollo)

Ejecuta `setup-admin.php` una vez para crear el admin:
- **Email:** admin@conectapet.com
- **Pass:** admin123

> ⚠️ Elimina `setup-admin.php` después de usarlo.

## 🗄️ Base de datos

Las tablas principales son:

| Tabla | Descripción |
|---|---|
| `admins` | Administradores del sistema |
| `usuarios` | Usuarios públicos registrados |
| `albergues` | Albergues aliados |
| `mascotas` | Mascotas en adopción |
| `adopciones` | Solicitudes de adopción |
| `donaciones` | Donaciones realizadas |
| `noticias` | Artículos publicados |
| `suscriptores` | Correos del boletín |

## 🔗 Repositorio Frontend

> Los assets estáticos (CSS, JS, imágenes) están en el repositorio **conectapet-frontend**.
