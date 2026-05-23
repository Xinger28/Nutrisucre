-- ============================================================
--  NutriSucre - Base de datos
--  Importar este archivo en phpMyAdmin
-- ============================================================


-- ─────────────────────────────────────────
--  Tabla de usuarios (pacientes, nutricionistas, admins)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,       -- guardamos el hash, nunca texto plano
    rol         ENUM('Paciente','Nutricionista','Administrador') DEFAULT 'Paciente',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────
--  Tabla de nutricionistas (info extendida)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS nutricionistas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT NOT NULL,
    especialidad    VARCHAR(150) DEFAULT 'Nutrición General',
    precio          DECIMAL(8,2) DEFAULT 120.00,
    rating          DECIMAL(3,1) DEFAULT 5.0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
--  Tabla de citas
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS citas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id         INT NOT NULL,
    nutricionista_id    INT NOT NULL,       -- ID de la tabla nutricionistas
    fecha               DATE NOT NULL,
    hora                TIME NOT NULL,
    precio              DECIMAL(8,2) DEFAULT 120.00,
    estado              ENUM('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id)      REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (nutricionista_id) REFERENCES nutricionistas(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
--  Datos de prueba
-- ─────────────────────────────────────────

-- Usuarios demo (password = "123456" hasheado con password_hash de PHP)
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Luis Gabriel',   'luis@nutrisucre.bo',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador'),
('Carla Soto',     'carla@nutrisucre.bo',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paciente'),
('Diego Pérez',    'diego@nutrisucre.bo',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista'),
('Elena Vargas',   'elena@nutrisucre.bo',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista'),
('Marcos Soliz',   'marcos@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista');

-- Perfil extendido de nutricionistas
INSERT INTO nutricionistas (usuario_id, especialidad, precio, rating) VALUES
(3, 'Nutrición Clínica',   150.00, 4.8),
(4, 'Nutrición Deportiva', 120.00, 4.9),
(5, 'Diabetes y Obesidad', 200.00, 5.0);

-- Citas de prueba
INSERT INTO citas (paciente_id, nutricionista_id, fecha, hora, precio, estado) VALUES
(2, 1, '2026-03-20', '10:00:00', 150.00, 'confirmada'),
(2, 2, '2026-03-24', '14:00:00', 120.00, 'pendiente');

-- ─────────────────────────────────────────
--  Tabla de seguimiento de progreso
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS seguimiento (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    fecha       DATE NOT NULL,
    peso        DECIMAL(5,2) DEFAULT NULL,   -- kg
    cintura     DECIMAL(5,2) DEFAULT NULL,   -- cm
    cadera      DECIMAL(5,2) DEFAULT NULL,   -- cm
    imc         DECIMAL(5,2) DEFAULT NULL,   -- calculado automáticamente si hay altura
    altura      DECIMAL(5,2) DEFAULT NULL,   -- cm, opcional
    notas       TEXT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY uq_paciente_fecha (paciente_id, fecha)   -- solo 1 registro por día
);

-- ─────────────────────────────────────────
--  Tabla de planes nutricionales
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS planes (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id      INT NOT NULL,
    nutricionista_id INT NOT NULL,           -- ID de tabla nutricionistas
    titulo           VARCHAR(200) NOT NULL,
    descripcion      TEXT DEFAULT NULL,
    calorias_dia     INT DEFAULT NULL,       -- kcal objetivo por día
    duracion_semanas INT DEFAULT 4,
    estado           ENUM('activo','pausado','completado') DEFAULT 'activo',
    fecha_inicio     DATE NOT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id)      REFERENCES usuarios(id)        ON DELETE CASCADE,
    FOREIGN KEY (nutricionista_id) REFERENCES nutricionistas(id)  ON DELETE CASCADE
);

-- ─────────────────────────────────────────
--  Tabla de reseñas
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS resenas (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id      INT NOT NULL,
    nutricionista_id INT NOT NULL,
    cita_id          INT DEFAULT NULL,
    calificacion     TINYINT NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
    comentario       TEXT DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_resenia_cita (cita_id),   -- una reseña por cita
    FOREIGN KEY (paciente_id)      REFERENCES usuarios(id)        ON DELETE CASCADE,
    FOREIGN KEY (nutricionista_id) REFERENCES nutricionistas(id)  ON DELETE CASCADE,
    FOREIGN KEY (cita_id)          REFERENCES citas(id)           ON DELETE SET NULL
);

-- Datos de seguimiento de prueba para Carla (id=2)
INSERT INTO seguimiento (paciente_id, fecha, peso, cintura, cadera, altura, notas) VALUES
(2, '2026-01-10', 72.5, 85.0, 98.0, 165.0, 'Inicio del tratamiento'),
(2, '2026-01-24', 71.2, 83.5, 97.0, 165.0, 'Buena adherencia al plan'),
(2, '2026-02-07', 70.0, 82.0, 96.0, 165.0, 'Progreso constante'),
(2, '2026-02-21', 69.3, 81.0, 95.5, 165.0, 'Semana difícil, pero se mantuvo'),
(2, '2026-03-07', 68.5, 80.0, 95.0, 165.0, 'Excelente progreso'),
(2, '2026-03-21', 67.8, 79.0, 94.5, 165.0, 'Meta casi alcanzada');

-- Plan de prueba para Carla
INSERT INTO planes (paciente_id, nutricionista_id, titulo, descripcion, calorias_dia, duracion_semanas, estado, fecha_inicio) VALUES
(2, 1, 'Plan Balance 1800 kcal', 'Dieta equilibrada para pérdida de peso gradual. Incluye 5 comidas al día con énfasis en proteínas magras y verduras.', 1800, 8, 'activo', '2026-01-10'),
(2, 2, 'Plan Hidratación Intensiva', 'Enfoque en hidratación adecuada y control de sodio. 2.5L de agua diaria mínima.', 1600, 4, 'completado', '2026-01-10');
