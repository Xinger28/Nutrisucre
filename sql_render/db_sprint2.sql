-- ============================================================
--  NutriSucre — db_sprint2.sql
--  Sprint 2: Gestión de Productos/Servicios
--  Importar en phpMyAdmin DESPUÉS de db_update2.sql
-- ============================================================

-- ─────────────────────────────────────────
--  Tabla principal del Sprint 2
--  Representa el "Producto/Servicio" del Ofertante (Nutricionista)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS servicios (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nutricionista_id    INT NOT NULL,           -- FK al ofertante (tabla usuarios)
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
    incluye             TEXT DEFAULT NULL,       -- qué incluye el servicio
    -- Estado de validación (núcleo del Sprint 2)
    estado              ENUM('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
    motivo_rechazo      TEXT DEFAULT NULL,       -- nota del admin al rechazar
    -- Auditoría
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Relación con el ofertante
    FOREIGN KEY (nutricionista_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    -- Índice para búsquedas por estado
    INDEX idx_estado (estado),
    INDEX idx_nutricionista (nutricionista_id)
);

-- ─────────────────────────────────────────
--  Datos de prueba
--  IDs 3,4,5 = nutricionistas en usuarios demo
-- ─────────────────────────────────────────
INSERT INTO servicios
    (nutricionista_id, titulo, descripcion, categoria, precio, duracion_semanas, modalidad, incluye, estado)
VALUES
-- Servicios APROBADOS (visibles en plataforma)
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

-- Servicio PENDIENTE (esperando aprobación)
(3, 'Nutrición para Embarazo Saludable',
 'Plan integral de alimentación para mujeres embarazadas, cubriendo todos los trimestres con requerimientos nutricionales específicos.',
 'Embarazo y lactancia', 390.00, 9, 'Virtual',
 '5 consultas, suplementación recomendada, menú semanal, guía de alimentos a evitar',
 'Pendiente'),

-- Servicio RECHAZADO (con motivo)
(4, 'Dieta Keto Express 7 días',
 'Dieta cetogénica rápida para perder peso en una semana.',
 'Pérdida de peso', 50.00, 1, 'Virtual',
 'Un PDF genérico',
 'Rechazado');

-- Actualizar el motivo de rechazo del último
UPDATE servicios SET motivo_rechazo =
    'El servicio no cumple con los estándares mínimos de la plataforma: duración insuficiente (1 semana), precio no justificado y descripción genérica. Por favor amplíe el programa a mínimo 4 semanas con seguimiento profesional.'
WHERE titulo = 'Dieta Keto Express 7 días';
