-- ============================================================
--  NutriSucre — Tablas nuevas (ejecutar en phpMyAdmin)
--  Importar DESPUÉS del db.sql original
-- ============================================================

USE nutrisucre;

-- ─────────────────────────────────────────
--  Seguimiento de progreso del paciente
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS seguimiento (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    fecha       DATE NOT NULL,
    peso        DECIMAL(5,2) DEFAULT NULL,   -- kg
    cintura     DECIMAL(5,2) DEFAULT NULL,   -- cm
    cadera      DECIMAL(5,2) DEFAULT NULL,   -- cm
    grasa       DECIMAL(5,2) DEFAULT NULL,   -- % grasa corporal
    nota        TEXT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
--  Planes nutricionales
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS planes (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id         INT NOT NULL,
    nutricionista_id    INT NOT NULL,           -- ID de tabla nutricionistas
    titulo              VARCHAR(200) NOT NULL,
    descripcion         TEXT DEFAULT NULL,
    calorias            INT DEFAULT NULL,       -- kcal objetivo diario
    proteinas           INT DEFAULT NULL,       -- gramos
    carbohidratos       INT DEFAULT NULL,       -- gramos
    grasas              INT DEFAULT NULL,       -- gramos
    duracion_semanas    INT DEFAULT 4,
    estado              ENUM('activo','finalizado','pausado') DEFAULT 'activo',
    fecha_inicio        DATE NOT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id)      REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (nutricionista_id) REFERENCES nutricionistas(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
--  Reseñas de consultas
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS resenas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id         INT NOT NULL,
    nutricionista_id    INT NOT NULL,
    cita_id             INT DEFAULT NULL,
    calificacion        TINYINT NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
    comentario          TEXT DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unica_resena (paciente_id, cita_id),
    FOREIGN KEY (paciente_id)      REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (nutricionista_id) REFERENCES nutricionistas(id) ON DELETE CASCADE,
    FOREIGN KEY (cita_id)          REFERENCES citas(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────
--  Datos de prueba — seguimiento
-- ─────────────────────────────────────────
INSERT INTO seguimiento (paciente_id, fecha, peso, cintura, cadera, grasa, nota) VALUES
(2, '2026-01-10', 78.5, 92.0, 104.0, 28.5, 'Inicio del plan'),
(2, '2026-01-24', 77.2, 90.5, 103.0, 27.8, 'Buena adherencia'),
(2, '2026-02-07', 76.0, 89.0, 102.0, 27.1, 'Se nota la diferencia'),
(2, '2026-02-21', 74.8, 87.5, 101.0, 26.5, 'Motivada'),
(2, '2026-03-07', 73.5, 86.0, 100.0, 25.9, 'Excelente progreso'),
(2, '2026-03-21', 72.1, 84.5, 99.0,  25.2, 'Meta casi alcanzada');

-- ─────────────────────────────────────────
--  Datos de prueba — planes
-- ─────────────────────────────────────────
INSERT INTO planes (paciente_id, nutricionista_id, titulo, descripcion, calorias, proteinas, carbohidratos, grasas, duracion_semanas, estado, fecha_inicio) VALUES
(2, 1, 'Plan de descenso de peso - Fase 1',
 'Plan hipocalórico moderado con énfasis en proteínas magras y vegetales. Evitar ultraprocesados y azúcares añadidos.',
 1600, 120, 150, 45, 8, 'activo', '2026-01-10'),
(2, 2, 'Plan de hidratación intensiva',
 'Mínimo 2.5 litros de agua diarios. Incluir infusiones sin azúcar. Reducir bebidas gaseosas.',
 0, 0, 0, 0, 4, 'finalizado', '2026-01-10');

-- ─────────────────────────────────────────
--  Datos de prueba — reseñas
-- ─────────────────────────────────────────
INSERT INTO resenas (paciente_id, nutricionista_id, cita_id, calificacion, comentario) VALUES
(2, 1, 1, 5, 'Excelente profesional, muy puntual y clara en sus explicaciones.'),
(2, 2, 2, 4, 'Muy buena consulta, me dio consejos prácticos.');
