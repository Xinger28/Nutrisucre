-- ============================================================
--  NutriSucre — SQL Consolidado Completo (Sprint 1, 2 y 3)
--  Ejecutar este archivo para inicializar o restablecer la BD
-- ============================================================


-- ─────────────────────────────────────────
--  Tabla usuarios (Pacientes, Nutricionistas, Administradores)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    rol         ENUM('Paciente','Nutricionista','Administrador') DEFAULT 'Paciente',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────
--  Tabla nutricionistas (Información profesional extendida)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS nutricionistas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT NOT NULL,
    especialidad    VARCHAR(150) DEFAULT 'Nutrición General',
    precio          DECIMAL(8,2) DEFAULT 120.00,
    rating          DECIMAL(3,1) DEFAULT 5.0,
    foto            VARCHAR(255) DEFAULT NULL,
    biografia       TEXT DEFAULT NULL,
    universidad     VARCHAR(200) DEFAULT NULL,
    titulo          VARCHAR(200) DEFAULT NULL,
    anio_egreso     INT DEFAULT NULL,
    anio_titulacion INT DEFAULT NULL,
    registro_prof   VARCHAR(100) DEFAULT NULL,
    institucion_reg VARCHAR(200) DEFAULT NULL,
    licencia_inicio DATE DEFAULT NULL,
    licencia_vence  DATE DEFAULT NULL,
    experiencia_años INT DEFAULT 0,
    pacientes_exit  INT DEFAULT 0,
    modalidad       ENUM('Virtual','Presencial','Ambas') DEFAULT 'Virtual',
    idiomas         VARCHAR(200) DEFAULT 'Español',
    duracion_consulta INT DEFAULT 60,
    max_pacientes_dia INT DEFAULT 8,
    estado_verificacion ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
    puntaje_tecnico INT DEFAULT 0,
    alertas_admin   TEXT DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
--  Tabla de citas
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS citas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id         INT NOT NULL,
    nutricionista_id    INT NOT NULL,
    fecha               DATE NOT NULL,
    hora                TIME NOT NULL,
    precio              DECIMAL(8,2) DEFAULT 120.00,
    estado              ENUM('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id)      REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (nutricionista_id) REFERENCES nutricionistas(id) ON DELETE CASCADE
);

CREATE INDEX idx_citas_disponibilidad
    ON citas(nutricionista_id, fecha, hora, estado);

-- ─────────────────────────────────────────
--  Tabla de seguimiento de progreso
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS seguimiento (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    fecha       DATE NOT NULL,
    peso        DECIMAL(5,2) DEFAULT NULL,
    cintura     DECIMAL(5,2) DEFAULT NULL,
    cadera      DECIMAL(5,2) DEFAULT NULL,
    grasa       DECIMAL(5,2) DEFAULT NULL,
    altura      DECIMAL(5,2) DEFAULT NULL,
    imc         DECIMAL(5,2) DEFAULT NULL,
    nota        TEXT DEFAULT NULL,
    notas       TEXT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY uq_paciente_fecha (paciente_id, fecha)
);

-- ─────────────────────────────────────────
--  Tabla de planes nutricionales
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS planes (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id         INT NOT NULL,
    nutricionista_id    INT NOT NULL,
    titulo              VARCHAR(200) NOT NULL,
    descripcion         TEXT DEFAULT NULL,
    calorias            INT DEFAULT NULL,
    calorias_dia        INT DEFAULT NULL,
    proteinas           INT DEFAULT NULL,
    carbohidratos       INT DEFAULT NULL,
    grasas              INT DEFAULT NULL,
    duracion_semanas    INT DEFAULT 4,
    estado              ENUM('activo','finalizado','pausado','completado') DEFAULT 'activo',
    fecha_inicio        DATE NOT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id)      REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (nutricionista_id) REFERENCES nutricionistas(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
--  Tabla de reseñas
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS resenas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id         INT NOT NULL,
    nutricionista_id    INT NOT NULL,
    cita_id             INT DEFAULT NULL,
    calificacion        TINYINT NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
    comentario          TEXT DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_resenia_cita (cita_id),
    FOREIGN KEY (paciente_id)      REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (nutricionista_id) REFERENCES nutricionistas(id) ON DELETE CASCADE,
    FOREIGN KEY (cita_id)          REFERENCES citas(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────
--  Tabla de postulaciones de nutricionistas
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS postulaciones (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT NOT NULL,
    ci                  VARCHAR(30) DEFAULT NULL,
    fecha_nacimiento    DATE DEFAULT NULL,
    sexo                ENUM('Masculino','Femenino','Prefiero no decir') DEFAULT NULL,
    pais                VARCHAR(100) DEFAULT 'Bolivia',
    ciudad              VARCHAR(100) DEFAULT NULL,
    direccion_prof      VARCHAR(255) DEFAULT NULL,
    telefono            VARCHAR(30) DEFAULT NULL,
    universidad         VARCHAR(200) DEFAULT NULL,
    carrera             VARCHAR(200) DEFAULT NULL,
    anio_egreso         INT DEFAULT NULL,
    anio_titulacion     INT DEFAULT NULL,
    titulo_prof         VARCHAR(200) DEFAULT NULL,
    registro_prof       VARCHAR(100) DEFAULT NULL,
    institucion_reg     VARCHAR(200) DEFAULT NULL,
    licencia_inicio     DATE DEFAULT NULL,
    licencia_vence      DATE DEFAULT NULL,
    especialidades      JSON DEFAULT NULL,
    experiencia         JSON DEFAULT NULL,
    tipo_consulta       VARCHAR(100) DEFAULT NULL,
    precio              DECIMAL(8,2) DEFAULT 120.00,
    duracion_consulta   INT DEFAULT 60,
    modalidad           ENUM('Virtual','Presencial','Ambas') DEFAULT 'Virtual',
    descripcion_serv    TEXT DEFAULT NULL,
    idiomas             VARCHAR(200) DEFAULT 'Español',
    horarios            JSON DEFAULT NULL,
    max_pacientes_dia   INT DEFAULT 8,
    resp_tecnica_1      TEXT DEFAULT NULL,
    resp_tecnica_2      TEXT DEFAULT NULL,
    resp_tecnica_3      TEXT DEFAULT NULL,
    resp_tecnica_4      TEXT DEFAULT NULL,
    resp_tecnica_5      TEXT DEFAULT NULL,
    puntaje_tecnico     INT DEFAULT 0,
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
    dia_semana        TINYINT NOT NULL,
    hora_inicio       TIME NOT NULL,
    hora_fin          TIME NOT NULL,
    FOREIGN KEY (nutricionista_id) REFERENCES nutricionistas(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
--  Tabla de servicios (Sprint 2)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS servicios (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nutricionista_id    INT NOT NULL,
    titulo              VARCHAR(200) NOT NULL,
    descripcion         TEXT DEFAULT NULL,
    categoria           ENUM(
                            'Pérdida de peso',
                            'Ganancia muscular',
                            'Control de diabetes',
                            'Nutrición deportiva',
                            'Nutrición infantil',
                            'Nutrición clínica',
                            'Nutrición geriátrica',
                            'Trastornos alimenticios',
                            'Embarazo y lactancia',
                            'Otro'
                        ) DEFAULT 'Otro',
    precio              DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    duracion_semanas    INT DEFAULT 4,
    modalidad           ENUM('Virtual','Presencial','Ambas') DEFAULT 'Virtual',
    incluye             TEXT DEFAULT NULL,
    estado              ENUM('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
    motivo_rechazo      TEXT DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nutricionista_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_estado (estado),
    INDEX idx_nutricionista (nutricionista_id)
);

-- ─────────────────────────────────────────
--  Tabla de solicitudes (Sprint 3)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS solicitudes (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id         INT NOT NULL,
    servicio_id         INT NOT NULL,
    precio_historico    DECIMAL(8,2) NOT NULL,
    motivo_consulta     TEXT NOT NULL,
    peso_actual         DECIMAL(5,2) DEFAULT NULL,
    altura_actual       DECIMAL(5,2) DEFAULT NULL,
    condiciones_medicas TEXT DEFAULT NULL,
    estado              ENUM('Pendiente', 'Aceptada', 'Rechazada') DEFAULT 'Pendiente',
    respuesta_ofertante TEXT DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    INDEX idx_solicitud_estado (estado),
    INDEX idx_solicitud_paciente (paciente_id)
);

-- ============================================================
--  DATOS DE PRUEBA / SEMILLA
--  Contraseña por defecto para todos: "123456"
-- ============================================================

INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Luis Gabriel',   'luis@nutrisucre.bo',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador'),
('Carla Soto',     'carla@nutrisucre.bo',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paciente'),
('Diego Pérez',    'diego@nutrisucre.bo',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista'),
('Elena Vargas',   'elena@nutrisucre.bo',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista'),
('Marcos Soliz',   'marcos@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista');

INSERT INTO nutricionistas (usuario_id, especialidad, precio, rating,
    biografia, universidad, titulo, anio_egreso, anio_titulacion,
    registro_prof, experiencia_años, pacientes_exit, modalidad, idiomas,
    duracion_consulta, max_pacientes_dia, estado_verificacion, puntaje_tecnico) VALUES
(3, 'Nutrición Clínica',   150.00, 4.8,
 'Especialista en nutrición clínica con enfoque en enfermedades crónicas.',
 'Universidad Mayor de San Francisco Xavier', 'Licenciado en Nutrición', 2015, 2016,
 'NUT-2016-0189', 8, 420, 'Ambas', 'Español', 60, 8, 'aprobado', 88),
(4, 'Nutrición Deportiva', 120.00, 4.9,
 'Especialista en nutrición deportiva para atletas de alto rendimiento.',
 'Universidad Mayor de San Francisco Xavier', 'Licenciada en Nutrición y Dietética', 2016, 2017,
 'NUT-2017-0342', 7, 350, 'Ambas', 'Español, Inglés', 60, 6, 'aprobado', 91),
(5, 'Diabetes y Obesidad', 200.00, 5.0,
 'Especialista en manejo nutricional de diabetes tipo 2 y obesidad.',
 'Universidad Autónoma Gabriel René Moreno', 'Licenciado en Nutrición Clínica', 2014, 2015,
 'NUT-2015-0199', 9, 520, 'Virtual', 'Español', 45, 8, 'aprobado', 94);

INSERT INTO citas (paciente_id, nutricionista_id, fecha, hora, precio, estado) VALUES
(2, 1, '2026-06-20', '10:00:00', 150.00, 'confirmada'),
(2, 2, '2026-06-24', '14:00:00', 120.00, 'pendiente');

INSERT INTO seguimiento (paciente_id, fecha, peso, cintura, cadera, grasa, nota) VALUES
(2, '2026-01-10', 78.5, 92.0, 104.0, 28.5, 'Inicio del plan'),
(2, '2026-01-24', 77.2, 90.5, 103.0, 27.8, 'Buena adherencia'),
(2, '2026-02-07', 76.0, 89.0, 102.0, 27.1, 'Se nota la diferencia'),
(2, '2026-02-21', 74.8, 87.5, 101.0, 26.5, 'Motivada'),
(2, '2026-03-07', 73.5, 86.0, 100.0, 25.9, 'Excelente progreso'),
(2, '2026-03-21', 72.1, 84.5, 99.0,  25.2, 'Meta casi alcanzada');

INSERT INTO planes (paciente_id, nutricionista_id, titulo, descripcion, calorias, proteinas, carbohidratos, grasas, duracion_semanas, estado, fecha_inicio) VALUES
(2, 1, 'Plan de descenso de peso - Fase 1',
 'Plan hipocalórico moderado con énfasis en proteínas magras y vegetales.',
 1600, 120, 150, 45, 8, 'activo', '2026-01-10'),
(2, 2, 'Plan de hidratación intensiva',
 'Mínimo 2.5 litros de agua diarios. Reducir bebidas gaseosas.',
 0, 0, 0, 0, 4, 'finalizado', '2026-01-10');

INSERT INTO resenas (paciente_id, nutricionista_id, cita_id, calificacion, comentario) VALUES
(2, 1, 1, 5, 'Excelente profesional, muy puntual y clara en sus explicaciones.'),
(2, 2, 2, 4, 'Muy buena consulta, me dio consejos prácticos.');

INSERT INTO disponibilidad (nutricionista_id, dia_semana, hora_inicio, hora_fin) VALUES
(1, 0, '09:00', '17:00'), (1, 1, '09:00', '17:00'), (1, 2, '09:00', '17:00'),
(1, 3, '09:00', '17:00'), (1, 4, '09:00', '17:00'),
(2, 0, '09:00', '17:00'), (2, 1, '09:00', '17:00'), (2, 2, '09:00', '17:00'),
(2, 3, '09:00', '17:00'), (2, 4, '09:00', '17:00'),
(3, 0, '08:00', '16:00'), (3, 1, '08:00', '16:00'), (3, 2, '08:00', '16:00'),
(3, 3, '08:00', '16:00'), (3, 4, '08:00', '16:00');

-- Servicios registrados (Sprint 2)
INSERT INTO servicios (nutricionista_id, titulo, descripcion, categoria, precio, duracion_semanas, modalidad, incluye, estado) VALUES
(3, 'Plan de Control de Peso Intensivo',
 'Programa estructurado de 8 semanas enfocado en reducción de peso saludable mediante alimentación balanceada y seguimiento semanal.',
 'Pérdida de peso', 350.00, 8, 'Ambas',
 '4 consultas virtuales, plan alimenticio personalizado, recetario PDF, seguimiento por WhatsApp',
 'Aprobado'),
(4, 'Plan Nutrición Deportiva de Alto Rendimiento',
 'Diseñado para atletas y deportistas que buscan optimizar su rendimiento mediante una alimentación estratégica pre y post entreno.',
 'Nutrición deportiva', 480.00, 6, 'Virtual',
 '3 consultas, análisis de composición corporal, plan de hidratación, suplementación guiada',
 'Aprobado'),
(5, 'Control Nutricional para Diabetes Tipo 2',
 'Intervención nutricional especializada para pacientes con diabetes tipo 2, orientada a control glucémico mediante dieta terapéutica.',
 'Control de diabetes', 420.00, 12, 'Ambas',
 '6 consultas, monitoreo glucémico, guía de alimentos permitidos, coordinación con médico tratante',
 'Aprobado'),
(3, 'Nutrición para Embarazo Saludable',
 'Plan integral de alimentación para mujeres embarazadas, cubriendo todos los trimestres con requerimientos nutricionales específicos.',
 'Embarazo y lactancia', 390.00, 9, 'Virtual',
 '5 consultas, suplementación recomendada, menú semanal, guía de alimentos a evitar',
 'Pendiente'),
(4, 'Dieta Keto Express 7 días',
 'Dieta cetogénica rápida para perder peso en una semana.',
 'Pérdida de peso', 50.00, 1, 'Virtual',
 'Un PDF genérico',
 'Rechazado');

UPDATE servicios SET motivo_rechazo =
    'El servicio no cumple con los estándares mínimos: duración insuficiente (1 semana), precio no justificado y descripción genérica. Amplíe el programa a mínimo 4 semanas con seguimiento profesional.'
WHERE titulo = 'Dieta Keto Express 7 días';

-- Solicitudes de prueba (Sprint 3)
INSERT INTO solicitudes (paciente_id, servicio_id, precio_historico, motivo_consulta, peso_actual, altura_actual, condiciones_medicas, estado, respuesta_ofertante) VALUES
(2, 1, 350.00, 'Quiero reducir grasa abdominal y mejorar mis hábitos de alimentación diarios.', 72.50, 165.00, 'Ninguna', 'Pendiente', NULL),
(2, 2, 480.00, 'Necesito preparar mi dieta para una maratón de 10K el próximo mes.', 70.00, 165.00, 'Alergia al maní', 'Aceptada', '¡Excelente! Vamos a trabajar en tu rendimiento y a planificar tu hidratación.'),
(2, 3, 420.00, 'Controlar mis niveles de glucemia ya que fui diagnosticada recientemente.', 71.00, 165.00, 'Diabetes Tipo 2, hipertensión leve', 'Rechazada', 'Por el momento no tengo disponibilidad para nuevos pacientes clínicos con estas patologías. Te sugiero contactar al Dr. Soliz.');
