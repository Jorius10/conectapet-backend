DROP DATABASE IF EXISTS conectapet_db;
CREATE DATABASE conectapet_db;
USE conectapet_db;

-- Tabla de Albergues
CREATE TABLE albergues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    direccion VARCHAR(200),
    descripcion TEXT,
    logo_url VARCHAR(255),
    rescates INT DEFAULT 0,
    adopciones INT DEFAULT 0,
    anios_trayectoria INT DEFAULT 0
);

-- Tabla de Mascotas
CREATE TABLE mascotas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    albergue_id INT,
    nombre VARCHAR(100) NOT NULL,
    especie ENUM('Perro', 'Gato', 'Otro') NOT NULL,
    sexo ENUM('Macho', 'Hembra') NOT NULL,
    edad_texto VARCHAR(50),
    descripcion TEXT,
    foto_url VARCHAR(255),
    estado_tramite VARCHAR(100) DEFAULT 'Disponible',
    estado_medico VARCHAR(100) DEFAULT 'Vacunado',
    FOREIGN KEY (albergue_id) REFERENCES albergues(id) ON DELETE CASCADE
);

-- 4 Albergues Oficiales
INSERT INTO albergues (nombre, direccion, descripcion, logo_url, rescates, adopciones, anios_trayectoria) VALUES
('Patitas Con Futuro', 'Av. Del Sol 123, Lima', 'Rescatamos y rehabilitamos perritos de la calle desde 2018. Creemos en una segunda oportunidad.', 'albergues fotos/albergue-patitas.jpg', 850, 620, 8),
('Refugio Los Michis', 'Jr. Gatos 456, Arequipa', 'Especializados en el rescate y cuidado ético de felinos desamparados. Hogar de gatos increíbles.', 'albergues fotos/albergue-gatos.jpg', 300, 200, 5),
('Albergue Central', 'Plaza Centro 789, Cusco', 'El refugio más antiguo ayudando a todos los animales en situación de riesgo en la región.', 'albergues fotos/albergue amor y rescate.png', 1200, 950, 15),
('Huellitas Felices', 'Calle Luna 101, Trujillo', 'Cuidamos a los peluditos más vulnerables y les conseguimos un hogar digno, sin importar su especie.', 'albergues fotos/albergue-cuddle.jpg', 410, 312, 4);

-- Mascotas para "Patitas Sur" (1) -> perros
INSERT INTO mascotas (albergue_id, nombre, especie, sexo, edad_texto, descripcion, foto_url, estado_tramite, estado_medico) VALUES
(1, 'Max', 'Perro', 'Macho', '2 Años', 'Perrito muy juguetón y lleno de energía. Ideal para familias con patio.', 'mascotas/perro1/perro1_1.jpg', 'Adoptado', 'Vacunado'),
(1, 'Bella', 'Perro', 'Hembra', '1 Año', 'Cariñosa, un poco tímida, pero será tu fiel compañera por siempre.', 'mascotas/perro2/perro2_1.jpg', 'Disponible', 'Esterilizada'),
(1, 'Rocky', 'Perro', 'Macho', '8 Meses', 'Cachorro en etapa de aprendizaje, curioso y amigable con todos.', 'mascotas/perro3/perro3_1.jpg', 'Disponible', 'Desparasitado'),
(1, 'Luna', 'Perro', 'Hembra', '3 Años', 'Educada y tranquila. Perfecta para hacerte compañía leyendo fente a la ventana.', 'mascotas/perro4/perro4_1.jpg', 'En Proceso', 'Esterilizada');

-- Mascotas para "Refugio Los Michis" (2) -> gatos
INSERT INTO mascotas (albergue_id, nombre, especie, sexo, edad_texto, descripcion, foto_url, estado_tramite, estado_medico) VALUES
(2, 'Michi', 'Gato', 'Hembra', '1.5 Años', 'Independiente, ama dormir en cajas de cartón. Sumamente limpia.', 'mascotas/gato1/gato1_1.jpg', 'Disponible', 'Esterilizada'),
(2, 'Pelusa', 'Gato', 'Hembra', '6 Meses', 'Curiosa e inquieta, está descubriendo el mundo de las lanas.', 'mascotas/gato2/gato2_1.jpg', 'Adoptado', 'Vacunada'),
(2, 'Salem', 'Gato', 'Macho', '2 Años', 'Negro elegante y misterioso, pero cuando toma confianza es súper mimoso.', 'mascotas/gato3/gato3_1.jpg', 'En Proceso', 'Desparasitado'),
(2, 'Blanquita', 'Gato', 'Hembra', '4 Meses', 'Chiquitita, le encantan los premios de atún.', 'mascotas/gato4/gato4_1.jpg', 'Disponible', 'En chequeos médicos');

-- Mascotas para "Albergue Central" (3) -> mixto
INSERT INTO mascotas (albergue_id, nombre, especie, sexo, edad_texto, descripcion, foto_url, estado_tramite, estado_medico) VALUES
(3, 'Toby', 'Perro', 'Macho', '4 Años', 'Perro leal y protector. Ya sabe dar la pata y sentarse.', 'mascotas/perro5/perro5_1.jpg', 'Disponible', 'Castrado'),
(3, 'Garfield', 'Gato', 'Macho', '3 Años', 'Dormilón experto. No hace mucho ruido, sólo le gusta comer.', 'mascotas/gato5/gato5_1.jpg', 'Adoptado', 'Vacunado'),
(3, 'Pupy', 'Perro', 'Hembra', '2 Meses', 'Mucha energía, necesita un hogar que le enseñe modales desde chiquita.', 'mascotas/perro6/perro6_1.jpg', 'Disponible', 'Desparasitada');

-- Mascotas para "Huellitas Felices" (4) -> exóticos y más
INSERT INTO mascotas (albergue_id, nombre, especie, sexo, edad_texto, descripcion, foto_url, estado_tramite, estado_medico) VALUES
(4, 'Nina', 'Gato', 'Hembra', '8 Meses', 'Le encanta escalar la biblioteca. Tiene excelentes habilidades de salto.', 'mascotas/gato6/gato6_1.jpg', 'En Proceso', 'Esterilizada');

-- Tabla de Veterinarias Aliadas
CREATE TABLE veterinarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    direccion VARCHAR(200),
    descripcion TEXT,
    logo_url VARCHAR(255),
    telefono VARCHAR(50)
);

INSERT INTO veterinarias (nombre, direccion, descripcion, logo_url, telefono) VALUES
('VetCenter', 'Av. Las Palmas 345, Lima', 'Clínica veterinaria 24 horas con atención especializada en rescates y emergencias.', 'contactos/albergue1.jpg', '+51 987 654 321'),
('Salud Animal', 'Av. Arequipa 1000, Arequipa', 'Especialistas en cirugía y rehabilitación. Apoyamos a los albergues con chequeos mensuales.', 'contactos/encargado1.jpg', '+51 999 888 777'),
('Pet Care Clinic', 'Calle Los Jazmines 56, Cusco', 'Brindamos servicios de esterilización a bajo costo y campañas gratuitas de vacunación.', 'contactos/vet1.jpg', '+51 911 222 333');

-- Tabla de Solicitudes de Adopción
CREATE TABLE adopciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id INT,
    usuario_id INT NULL,
    nombre VARCHAR(100),
    apellidos VARCHAR(100),
    dni VARCHAR(20),
    fecha_nacimiento DATE,
    correo VARCHAR(100),
    telefono VARCHAR(50),
    telefono_alt VARCHAR(50),
    direccion VARCHAR(200),
    ciudad VARCHAR(100),
    distrito VARCHAR(100),
    departamento VARCHAR(100),
    tipo_vivienda VARCHAR(50),
    vivienda_propia VARCHAR(50),
    tiene_mascotas VARCHAR(50),
    experiencia VARCHAR(50),
    motivo TEXT,
    tiempo_disponible VARCHAR(100),
    responsables VARCHAR(100),
    estado VARCHAR(50) DEFAULT 'En Proceso',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mascota_id) REFERENCES mascotas(id)
);

-- Tabla de Usuarios Públicos
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    distrito VARCHAR(100),
    avatar_url VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Administradores
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    albergue_id INT NULL,
    rol ENUM('superadmin','albergue') DEFAULT 'albergue',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (albergue_id) REFERENCES albergues(id) ON DELETE SET NULL
);

-- Admin de prueba: password = "admin123"
INSERT INTO admins (nombre, correo, password_hash, rol) VALUES
('Super Admin', 'admin@conectapet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- Tabla de Donaciones
CREATE TABLE donaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id INT NULL,
    albergue_id INT NULL,
    tipo VARCHAR(50) DEFAULT 'general',
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    comentario TEXT,
    metodo_pago VARCHAR(50),
    id_usuario INT NULL,
    fecha_donacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE SET NULL,
    FOREIGN KEY (albergue_id) REFERENCES albergues(id) ON DELETE SET NULL
);

-- Tabla de Noticias
CREATE TABLE noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    resumen TEXT,
    contenido LONGTEXT,
    imagen_url VARCHAR(255),
    categoria VARCHAR(100) DEFAULT 'General',
    autor VARCHAR(100) DEFAULT 'ConectaPet',
    destacada TINYINT(1) DEFAULT 0,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Noticias de ejemplo
INSERT INTO noticias (titulo, resumen, contenido, imagen_url, categoria, autor, destacada) VALUES
('Gran jornada de adopción en Lima: 40 mascotas encontraron hogar', 
 'El pasado fin de semana se realizó la feria de adopción más grande del año en el Parque de la Exposición, con más de 200 visitantes.',
 '<p>El evento reunió a 5 albergues afiliados a ConectaPet y permitió que 40 mascotas fueran adoptadas en un solo día. Familias de todos los distritos de Lima llegaron a conocer a los peluditos disponibles.</p><p>Entre los adoptados se encontraban 22 perros y 18 gatos, varios de ellos con más de un año esperando en los albergues. Los organizadores destacaron la importancia de eventos presenciales para acelerar el proceso.</p><p>ConectaPet seguirá promoviendo estas jornadas cada trimestre gracias al apoyo de los albergues afiliados y los donantes.</p>',
 'fotos noticias/noticia5.jpg', 'Eventos', 'Equipo ConectaPet', 1),

('Nueva clínica veterinaria se une como aliada de ConectaPet',
 'VetSalud Arequipa formalizó su alianza con la plataforma, ofreciendo atención médica a precio reducido para mascotas rescatadas.',
 '<p>VetSalud Arequipa, con más de 10 años de trayectoria, se convierte en la cuarta clínica veterinaria aliada de ConectaPet. A partir de febrero ofrecerán descuentos del 40% en vacunas y esterilizaciones para animales provenientes de albergues afiliados.</p><p>La directora de la clínica, Dra. María Soto, señaló: "Es una responsabilidad social que asumimos con mucho orgullo. Cada animal rescatado merece atención médica de calidad."</p>',
 'fotos noticias/noticia3.jpg', 'Alianzas', 'Redacción ConectaPet', 0),

('Campaña de vacunación gratuita llega a 3 provincias',
 'Gracias a las donaciones recibidas, organizamos brigadas de vacunación que beneficiaron a más de 150 animales en Cusco, Puno e Ica.',
 '<p>Durante el mes de enero, equipos de voluntarios y veterinarios aliados viajaron a tres provincias para aplicar vacunas gratuitas a perros y gatos en situación de calle y en albergues municipales.</p><p>La campaña fue financiada íntegramente con las donaciones recibidas a través de la plataforma ConectaPet durante el último trimestre. Un total de 152 animales fueron atendidos.</p>',
 'fotos noticias/noticia4.jpg', 'Salud Animal', 'Voluntarios ConectaPet', 0),

('Cómo preparar tu hogar para recibir a una mascota adoptada',
 'Guía práctica con los pasos esenciales para que la llegada de tu nuevo compañero sea tranquila y feliz para toda la familia.',
 '<p>Adoptar es solo el primer paso. Preparar el hogar es igual de importante para garantizar una adaptación exitosa. Aquí te dejamos los consejos que comparten nuestros albergues aliados.</p><ul><li><strong>Espacio propio:</strong> Define una zona tranquila con cama, agua y juguetes.</li><li><strong>Primeras horas:</strong> Deja que el animal explore a su ritmo sin forzar el contacto.</li><li><strong>Rutinas:</strong> Establece horarios fijos de comida y paseos desde el primer día.</li><li><strong>Veterinario:</strong> Agenda una revisión en los primeros 7 días.</li></ul>',
 'fotos noticias/noticia1.jpeg', 'Consejos', 'Dr. Renzo Vargas', 0);

