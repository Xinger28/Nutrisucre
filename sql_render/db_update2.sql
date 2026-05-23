-- ============================================================
--  NutriSucre — db_update2.sql
--  Importar en phpMyAdmin DESPUÉS de db_update.sql
-- ============================================================

-- ─────────────────────────────────────────
--  Ampliar tabla nutricionistas con info profesional
-- ─────────────────────────────────────────
ALTER TABLE nutricionistas
    ADD COLUMN IF NOT EXISTS foto            VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS biografia       TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS universidad     VARCHAR(200) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS titulo          VARCHAR(200) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS anio_egreso     INT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS anio_titulacion INT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS registro_prof   VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS institucion_reg VARCHAR(200) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS licencia_inicio DATE DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS licencia_vence  DATE DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS experiencia_años INT DEFAULT 0,
    ADD COLUMN IF NOT EXISTS pacientes_exit  INT DEFAULT 0,
    ADD COLUMN IF NOT EXISTS modalidad       ENUM('Virtual','Presencial','Ambas') DEFAULT 'Virtual',
    ADD COLUMN IF NOT EXISTS idiomas         VARCHAR(200) DEFAULT 'Español',
    ADD COLUMN IF NOT EXISTS duracion_consulta INT DEFAULT 60,
    ADD COLUMN IF NOT EXISTS max_pacientes_dia INT DEFAULT 8,
    ADD COLUMN IF NOT EXISTS estado_verificacion ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
    ADD COLUMN IF NOT EXISTS puntaje_tecnico INT DEFAULT 0,
    ADD COLUMN IF NOT EXISTS alertas_admin   TEXT DEFAULT NULL;

-- ─────────────────────────────────────────
--  Tabla de postulaciones (formulario completo)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS postulaciones (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT NOT NULL,
    -- Datos personales
    ci                  VARCHAR(30) DEFAULT NULL,
    fecha_nacimiento    DATE DEFAULT NULL,
    sexo                ENUM('Masculino','Femenino','Prefiero no decir') DEFAULT NULL,
    pais                VARCHAR(100) DEFAULT 'Bolivia',
    ciudad              VARCHAR(100) DEFAULT NULL,
    direccion_prof      VARCHAR(255) DEFAULT NULL,
    telefono            VARCHAR(30) DEFAULT NULL,
    -- Formación académica
    universidad         VARCHAR(200) DEFAULT NULL,
    carrera             VARCHAR(200) DEFAULT NULL,
    anio_egreso         INT DEFAULT NULL,
    anio_titulacion     INT DEFAULT NULL,
    titulo_prof         VARCHAR(200) DEFAULT NULL,
    -- Licencia
    registro_prof       VARCHAR(100) DEFAULT NULL,
    institucion_reg     VARCHAR(200) DEFAULT NULL,
    licencia_inicio     DATE DEFAULT NULL,
    licencia_vence      DATE DEFAULT NULL,
    -- Especialidades (JSON array)
    especialidades      JSON DEFAULT NULL,
    -- Experiencia (JSON array)
    experiencia         JSON DEFAULT NULL,
    -- Servicios
    tipo_consulta       VARCHAR(100) DEFAULT NULL,
    precio              DECIMAL(8,2) DEFAULT 120.00,
    duracion_consulta   INT DEFAULT 60,
    modalidad           ENUM('Virtual','Presencial','Ambas') DEFAULT 'Virtual',
    descripcion_serv    TEXT DEFAULT NULL,
    idiomas             VARCHAR(200) DEFAULT 'Español',
    horarios            JSON DEFAULT NULL,
    max_pacientes_dia   INT DEFAULT 8,
    -- Validación técnica (respuestas)
    resp_tecnica_1      TEXT DEFAULT NULL,
    resp_tecnica_2      TEXT DEFAULT NULL,
    resp_tecnica_3      TEXT DEFAULT NULL,
    resp_tecnica_4      TEXT DEFAULT NULL,
    resp_tecnica_5      TEXT DEFAULT NULL,
    puntaje_tecnico     INT DEFAULT 0,
    -- Estado
    estado              ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
    alertas             TEXT DEFAULT NULL,
    notas_admin         TEXT DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
--  Tabla de disponibilidad del nutricionista
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS disponibilidad (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    nutricionista_id  INT NOT NULL,
    dia_semana        TINYINT NOT NULL,   -- 0=Lun, 1=Mar ... 6=Dom
    hora_inicio       TIME NOT NULL,
    hora_fin          TIME NOT NULL,
    FOREIGN KEY (nutricionista_id) REFERENCES nutricionistas(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
--  Índice en citas para consulta rápida de disponibilidad
-- ─────────────────────────────────────────
CREATE INDEX IF NOT EXISTS idx_citas_disponibilidad
    ON citas(nutricionista_id, fecha, hora, estado);

-- ─────────────────────────────────────────
--  Actualizar nutricionistas de prueba con datos completos
-- ─────────────────────────────────────────
UPDATE nutricionistas SET
    biografia          = 'Especialista en nutrición deportiva con amplia experiencia trabajando con atletas de alto rendimiento.',
    universidad        = 'Universidad Mayor de San Francisco Xavier',
    titulo             = 'Licenciada en Nutrición y Dietética',
    anio_egreso        = 2016, anio_titulacion = 2017,
    registro_prof      = 'NUT-2017-0342',
    experiencia_años   = 7, pacientes_exit = 350,
    modalidad          = 'Ambas', idiomas = 'Español, Inglés',
    duracion_consulta  = 60, max_pacientes_dia = 6,
    estado_verificacion= 'aprobado', puntaje_tecnico = 91
WHERE id = 2;  -- Elena Vargas

UPDATE nutricionistas SET
    biografia          = 'Especialista en el manejo nutricional de pacientes con diabetes tipo 2 y obesidad.',
    universidad        = 'Universidad Autónoma Gabriel René Moreno',
    titulo             = 'Licenciado en Nutrición Clínica',
    anio_egreso        = 2014, anio_titulacion = 2015,
    registro_prof      = 'NUT-2015-0189',
    experiencia_años   = 9, pacientes_exit = 520,
    modalidad          = 'Virtual', idiomas = 'Español',
    duracion_consulta  = 45, max_pacientes_dia = 8,
    estado_verificacion= 'aprobado', puntaje_tecnico = 88
WHERE id = 3;  -- Marcos Soliz / Diego Pérez

-- Disponibilidad de prueba (Lun-Vie 09:00-17:00)
INSERT IGNORE INTO disponibilidad (nutricionista_id, dia_semana, hora_inicio, hora_fin) VALUES
(2, 0, '09:00', '17:00'), (2, 1, '09:00', '17:00'), (2, 2, '09:00', '17:00'),
(2, 3, '09:00', '17:00'), (2, 4, '09:00', '17:00'),
(3, 0, '08:00', '16:00'), (3, 1, '08:00', '16:00'), (3, 2, '08:00', '16:00'),
(3, 3, '08:00', '16:00'), (3, 4, '08:00', '16:00');
