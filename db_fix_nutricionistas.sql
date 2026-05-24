-- ============================================================
--  NutriSucre — FIX: Nutricionistas visibles en búsqueda
--  Ejecutar en phpMyAdmin DESPUÉS de db_update2.sql
--
--  Problema: estado_verificacion = 'pendiente' hace que la API
--  no los devuelva. Este script los aprueba y añade 3 nuevos.
-- ============================================================
USE nutrisucre;

-- ─────────────────────────────────────────
--  1. Aprobar TODOS los nutricionistas existentes
-- ─────────────────────────────────────────
UPDATE nutricionistas SET estado_verificacion = 'aprobado' WHERE estado_verificacion != 'aprobado';

-- ─────────────────────────────────────────
--  2. Completar perfil de Diego Pérez (id=1) que faltaba
-- ─────────────────────────────────────────
UPDATE nutricionistas SET
    biografia          = 'Nutricionista clínico con 8 años de experiencia en hospitales públicos y privados de Chuquisaca. Especialista en evaluación nutricional integral, manejo de desnutrición hospitalaria y soporte nutricional enteral y parenteral. Miembro activo de la Sociedad Boliviana de Nutrición Clínica (SOBNUC). Ha participado como ponente en congresos nacionales sobre intervención nutricional en pacientes críticos.',
    universidad        = 'Universidad Mayor Real y Pontificia de San Francisco Xavier de Chuquisaca',
    titulo             = 'Licenciado en Nutrición y Dietética',
    anio_egreso        = 2015, anio_titulacion = 2016,
    registro_prof      = 'NUT-2016-0278',
    institucion_reg    = 'Colegio de Nutricionistas de Bolivia',
    experiencia_años   = 8, pacientes_exit = 420,
    modalidad          = 'Presencial', idiomas = 'Español, Quechua',
    duracion_consulta  = 50, max_pacientes_dia = 7,
    estado_verificacion= 'aprobado', puntaje_tecnico = 93
WHERE id = 1;

-- Mejorar biografía de Elena Vargas (id=2) con info más completa
UPDATE nutricionistas SET
    biografia = 'Nutricionista deportiva certificada por el ISSN (International Society of Sports Nutrition). Trabaja con atletas de alto rendimiento, equipos de fútbol profesional y corredores de maratón en Bolivia. Experta en periodización nutricional, suplementación basada en evidencia científica y composición corporal. Docente invitada en la cátedra de Nutrición Deportiva de la USFX. Ha publicado 3 artículos en revistas indexadas sobre rendimiento deportivo y alimentación.',
    institucion_reg = 'ISSN / Colegio de Nutricionistas de Bolivia',
    puntaje_tecnico = 95
WHERE id = 2;

-- Mejorar biografía de Marcos Soliz (id=3) con info más completa
UPDATE nutricionistas SET
    biografia = 'Especialista en el manejo nutricional de pacientes con diabetes tipo 2, obesidad mórbida y síndrome metabólico. Más de 9 años de experiencia en consulta privada y hospitalaria. Certificado en Educación en Diabetes por la Federación Internacional de Diabetes (IDF). Desarrolla programas de intervención nutricional comunitaria en zonas rurales de Chuquisaca, enfocado en prevención de enfermedades crónicas no transmisibles.',
    institucion_reg = 'IDF / Colegio de Nutricionistas de Bolivia',
    puntaje_tecnico = 92
WHERE id = 3;

-- ─────────────────────────────────────────
--  3. Agregar 3 nuevos usuarios nutricionistas
-- ─────────────────────────────────────────
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Gabriela Mendoza', 'gabriela@nutrisucre.bo',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista'),
('Roberto Quiroga',  'roberto@nutrisucre.bo',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista'),
('Ana Lucía Torrez', 'analucia@nutrisucre.bo',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista');

-- ─────────────────────────────────────────
--  4. Obtener IDs de los nuevos usuarios para las FK
--     Usamos subqueries con el email único
-- ─────────────────────────────────────────

-- Gabriela Mendoza — Nutrición Infantil y Pediátrica
INSERT INTO nutricionistas (usuario_id, especialidad, precio, rating,
    biografia, universidad, titulo, anio_egreso, anio_titulacion,
    registro_prof, institucion_reg, experiencia_años, pacientes_exit,
    modalidad, idiomas, duracion_consulta, max_pacientes_dia,
    estado_verificacion, puntaje_tecnico)
VALUES (
    (SELECT id FROM usuarios WHERE email = 'gabriela@nutrisucre.bo'),
    'Nutrición Infantil y Pediátrica', 180.00, 4.9,
    'Especialista en nutrición infantil con 6 años de experiencia en consulta pediátrica y centros de salud materno-infantil. Magíster en Nutrición Pediátrica por la Universidad de Buenos Aires (UBA). Experta en alimentación complementaria, manejo de alergias alimentarias en niños, selectividad alimentaria y desnutrición infantil aguda. Colaboradora del programa "Desnutrición Cero" del Ministerio de Salud de Bolivia. Autora de la guía práctica "Alimentación Saludable para los Primeros 1000 Días" distribuida en centros de salud de Chuquisaca.',
    'Universidad Andina Simón Bolívar',
    'Magíster en Nutrición Pediátrica — Lic. en Nutrición y Dietética',
    2017, 2018,
    'NUT-2018-0456',
    'Colegio de Nutricionistas de Bolivia',
    6, 280,
    'Ambas', 'Español, Quechua',
    45, 6,
    'aprobado', 89
);

-- Roberto Quiroga — Nutrición Geriátrica y Renal
INSERT INTO nutricionistas (usuario_id, especialidad, precio, rating,
    biografia, universidad, titulo, anio_egreso, anio_titulacion,
    registro_prof, institucion_reg, experiencia_años, pacientes_exit,
    modalidad, idiomas, duracion_consulta, max_pacientes_dia,
    estado_verificacion, puntaje_tecnico)
VALUES (
    (SELECT id FROM usuarios WHERE email = 'roberto@nutrisucre.bo'),
    'Nutrición Geriátrica y Renal', 220.00, 4.7,
    'Nutricionista con 12 años de experiencia especializado en el manejo dietético de pacientes geriátricos y con enfermedad renal crónica (ERC). Diplomado en Nutrición Renal por la Universidad de Chile y en Gerontología Clínica por la UMSA. Trabaja en coordinación con nefrólogos y geriatras del Hospital Santa Bárbara de Sucre. Experto en dietas hiposódicas, control de fósforo y potasio, y planes nutricionales para pacientes en hemodiálisis. Ha capacitado a más de 50 profesionales de salud en nutrición geriátrica a nivel departamental.',
    'Universidad Mayor de San Andrés (UMSA)',
    'Diplomado en Nutrición Renal — Lic. en Nutrición y Dietética',
    2012, 2013,
    'NUT-2013-0134',
    'Colegio de Nutricionistas de Bolivia / Sociedad Boliviana de Nefrología',
    12, 680,
    'Presencial', 'Español',
    60, 5,
    'aprobado', 94
);

-- Ana Lucía Torrez — Trastornos Alimenticios y Nutrición Emocional
INSERT INTO nutricionistas (usuario_id, especialidad, precio, rating,
    biografia, universidad, titulo, anio_egreso, anio_titulacion,
    registro_prof, institucion_reg, experiencia_años, pacientes_exit,
    modalidad, idiomas, duracion_consulta, max_pacientes_dia,
    estado_verificacion, puntaje_tecnico)
VALUES (
    (SELECT id FROM usuarios WHERE email = 'analucia@nutrisucre.bo'),
    'Trastornos Alimenticios y Nutrición Emocional', 250.00, 4.8,
    'Nutricionista clínica especializada en el abordaje integral de trastornos de la conducta alimentaria (TCA): anorexia nerviosa, bulimia y trastorno por atracón. Máster en Psicología de la Alimentación por la Universidad de Barcelona. Trabaja con enfoque interdisciplinario junto a psicólogos y psiquiatras. Pionera en Bolivia en implementar el modelo de "alimentación intuitiva" como herramienta terapéutica. Fundadora del programa "Paz con tu Cuerpo", que ha atendido a más de 200 jóvenes en Sucre. Ponente frecuente en universidades sobre prevención de TCA en adolescentes.',
    'Universidad Católica Boliviana San Pablo',
    'Máster en Psicología de la Alimentación — Lic. en Nutrición Humana',
    2014, 2015,
    'NUT-2015-0201',
    'Colegio de Nutricionistas de Bolivia / Asociación Boliviana de TCA',
    10, 450,
    'Virtual', 'Español, Inglés, Portugués',
    60, 4,
    'aprobado', 96
);

-- ─────────────────────────────────────────
--  5. Disponibilidad para los 3 nuevos nutricionistas
-- ─────────────────────────────────────────

-- Gabriela Mendoza (Lun-Vie 08:00-14:00 — horario matutino para niños)
INSERT IGNORE INTO disponibilidad (nutricionista_id, dia_semana, hora_inicio, hora_fin)
SELECT n.id, d.dia, '08:00', '14:00'
FROM nutricionistas n
JOIN usuarios u ON u.id = n.usuario_id
CROSS JOIN (SELECT 0 AS dia UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) d
WHERE u.email = 'gabriela@nutrisucre.bo';

-- Roberto Quiroga (Lun-Sáb 07:00-15:00 — incluye sábado por pacientes mayores)
INSERT IGNORE INTO disponibilidad (nutricionista_id, dia_semana, hora_inicio, hora_fin)
SELECT n.id, d.dia, '07:00', '15:00'
FROM nutricionistas n
JOIN usuarios u ON u.id = n.usuario_id
CROSS JOIN (SELECT 0 AS dia UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) d
WHERE u.email = 'roberto@nutrisucre.bo';

-- Ana Lucía Torrez (Lun-Jue 14:00-20:00 — horario vespertino)
INSERT IGNORE INTO disponibilidad (nutricionista_id, dia_semana, hora_inicio, hora_fin)
SELECT n.id, d.dia, '14:00', '20:00'
FROM nutricionistas n
JOIN usuarios u ON u.id = n.usuario_id
CROSS JOIN (SELECT 0 AS dia UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) d
WHERE u.email = 'analucia@nutrisucre.bo';

-- ─────────────────────────────────────────
--  6. Disponibilidad para Diego Pérez (id=1) que también faltaba
-- ─────────────────────────────────────────
INSERT IGNORE INTO disponibilidad (nutricionista_id, dia_semana, hora_inicio, hora_fin) VALUES
(1, 0, '08:00', '16:00'), (1, 1, '08:00', '16:00'), (1, 2, '08:00', '16:00'),
(1, 3, '08:00', '16:00'), (1, 4, '08:00', '16:00');

-- ─────────────────────────────────────────
--  7. Reseñas de ejemplo para los nuevos nutricionistas
-- ─────────────────────────────────────────
-- Usamos paciente_id = 2 (Carla Soto) como demo

INSERT INTO resenas (paciente_id, nutricionista_id, calificacion, comentario) VALUES
(2, (SELECT id FROM nutricionistas n JOIN usuarios u ON u.id = n.usuario_id WHERE u.email = 'gabriela@nutrisucre.bo'),
 5, 'Increíble profesional. Mi hijo de 3 años tenía selectividad alimentaria severa y en 2 meses logró aceptar nuevos alimentos. Muy paciente y empática.'),
(2, (SELECT id FROM nutricionistas n JOIN usuarios u ON u.id = n.usuario_id WHERE u.email = 'roberto@nutrisucre.bo'),
 5, 'Atiende a mi padre que está en diálisis. El plan nutricional que le hizo mejoró notablemente sus valores de fósforo y potasio. Muy profesional y humano.'),
(2, (SELECT id FROM nutricionistas n JOIN usuarios u ON u.id = n.usuario_id WHERE u.email = 'analucia@nutrisucre.bo'),
 5, 'Me ayudó a superar mi relación tóxica con la comida después de años de dietas restrictivas. El enfoque de alimentación intuitiva cambió mi vida. Altamente recomendada.');
