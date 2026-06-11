-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: bcj5pzf6lomricmiafi6-mysql.services.clever-cloud.com:3306
-- Tiempo de generación: 11-06-2026 a las 15:12:49
-- Versión del servidor: 8.0.22-13
-- Versión de PHP: 8.2.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bcj5pzf6lomricmiafi6`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int NOT NULL,
  `paciente_id` int NOT NULL,
  `nutricionista_id` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `precio` decimal(8,2) DEFAULT '120.00',
  `estado` enum('pendiente','pendiente_confirmacion','confirmada','rechazada','cancelada') DEFAULT 'pendiente_confirmacion',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `servicio_id` int DEFAULT NULL,
  `comprobante_pago` varchar(255) DEFAULT NULL,
  `metodo_pago` enum('QR','Transferencia','Deposito') DEFAULT NULL,
  `motivo_rechazo` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `paciente_id`, `nutricionista_id`, `fecha`, `hora`, `precio`, `estado`, `created_at`, `servicio_id`, `comprobante_pago`, `metodo_pago`, `motivo_rechazo`) VALUES
(1, 2, 1, '2026-06-20', '10:00:00', 150.00, 'confirmada', '2026-06-05 18:38:20', NULL, NULL, NULL, NULL),
(2, 2, 2, '2026-06-24', '14:00:00', 120.00, 'pendiente', '2026-06-05 18:38:20', NULL, NULL, NULL, NULL),
(3, 49, 3, '2026-06-27', '08:45:00', 200.00, 'confirmada', '2026-06-11 14:19:57', NULL, 'uploads/comprobantes/comprobantes_6a2ac40a2aa0a7.62893824.png', 'QR', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `disponibilidad`
--

CREATE TABLE `disponibilidad` (
  `id` int NOT NULL,
  `nutricionista_id` int NOT NULL,
  `dia_semana` tinyint NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `disponibilidad`
--

INSERT INTO `disponibilidad` (`id`, `nutricionista_id`, `dia_semana`, `hora_inicio`, `hora_fin`) VALUES
(1, 1, 0, '09:00:00', '17:00:00'),
(2, 1, 1, '09:00:00', '17:00:00'),
(3, 1, 2, '09:00:00', '17:00:00'),
(4, 1, 3, '09:00:00', '17:00:00'),
(5, 1, 4, '09:00:00', '17:00:00'),
(6, 2, 0, '09:00:00', '17:00:00'),
(7, 2, 1, '09:00:00', '17:00:00'),
(8, 2, 2, '09:00:00', '17:00:00'),
(9, 2, 3, '09:00:00', '17:00:00'),
(10, 2, 4, '09:00:00', '17:00:00'),
(11, 3, 0, '08:00:00', '16:00:00'),
(12, 3, 1, '08:00:00', '16:00:00'),
(13, 3, 2, '08:00:00', '16:00:00'),
(14, 3, 3, '08:00:00', '16:00:00'),
(15, 3, 4, '08:00:00', '16:00:00'),
(16, 4, 0, '08:00:00', '16:00:00'),
(17, 4, 1, '08:00:00', '16:00:00'),
(18, 4, 2, '08:00:00', '16:00:00'),
(19, 4, 3, '08:00:00', '16:00:00'),
(20, 4, 4, '08:00:00', '16:00:00'),
(21, 5, 0, '10:00:00', '18:00:00'),
(22, 5, 1, '10:00:00', '18:00:00'),
(23, 5, 2, '10:00:00', '18:00:00'),
(24, 5, 3, '10:00:00', '18:00:00'),
(25, 5, 4, '10:00:00', '18:00:00'),
(26, 6, 0, '09:00:00', '17:00:00'),
(27, 6, 1, '09:00:00', '17:00:00'),
(28, 6, 2, '09:00:00', '17:00:00'),
(29, 6, 3, '09:00:00', '17:00:00'),
(30, 6, 4, '09:00:00', '17:00:00'),
(31, 7, 0, '11:00:00', '19:00:00'),
(32, 7, 2, '11:00:00', '19:00:00'),
(33, 7, 4, '11:00:00', '19:00:00'),
(34, 8, 1, '08:00:00', '15:00:00'),
(35, 8, 2, '08:00:00', '15:00:00'),
(36, 8, 3, '08:00:00', '15:00:00'),
(37, 8, 4, '08:00:00', '15:00:00'),
(38, 8, 5, '08:00:00', '12:00:00'),
(39, 4, 0, '08:00:00', '16:00:00'),
(40, 4, 1, '08:00:00', '16:00:00'),
(41, 4, 2, '08:00:00', '16:00:00'),
(42, 4, 3, '08:00:00', '16:00:00'),
(43, 4, 4, '08:00:00', '16:00:00'),
(44, 5, 0, '10:00:00', '18:00:00'),
(45, 5, 1, '10:00:00', '18:00:00'),
(46, 5, 2, '10:00:00', '18:00:00'),
(47, 5, 3, '10:00:00', '18:00:00'),
(48, 5, 4, '10:00:00', '18:00:00'),
(49, 6, 0, '09:00:00', '17:00:00'),
(50, 6, 1, '09:00:00', '17:00:00'),
(51, 6, 2, '09:00:00', '17:00:00'),
(52, 6, 3, '09:00:00', '17:00:00'),
(53, 6, 4, '09:00:00', '17:00:00'),
(54, 7, 0, '11:00:00', '19:00:00'),
(55, 7, 2, '11:00:00', '19:00:00'),
(56, 7, 4, '11:00:00', '19:00:00'),
(57, 8, 1, '08:00:00', '15:00:00'),
(58, 8, 2, '08:00:00', '15:00:00'),
(59, 8, 3, '08:00:00', '15:00:00'),
(60, 8, 4, '08:00:00', '15:00:00'),
(61, 8, 5, '08:00:00', '12:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nutricionistas`
--

CREATE TABLE `nutricionistas` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `especialidad` varchar(150) DEFAULT 'Nutrición General',
  `precio` decimal(8,2) DEFAULT '120.00',
  `rating` decimal(3,1) DEFAULT '5.0',
  `foto` varchar(255) DEFAULT NULL,
  `descripcion_serv` text,
  `universidad` varchar(200) DEFAULT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `anio_egreso` int DEFAULT NULL,
  `anio_titulacion` int DEFAULT NULL,
  `registro_prof` varchar(100) DEFAULT NULL,
  `institucion_reg` varchar(200) DEFAULT NULL,
  `licencia_inicio` date DEFAULT NULL,
  `licencia_vence` date DEFAULT NULL,
  `experiencia_anios` int DEFAULT '0',
  `pacientes_exit` int DEFAULT '0',
  `modalidad` enum('Virtual','Presencial','Ambas') DEFAULT 'Virtual',
  `idiomas` varchar(200) DEFAULT 'Español',
  `duracion_consulta` int DEFAULT '60',
  `max_pacientes_dia` int DEFAULT '8',
  `estado_verificacion` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `puntaje_tecnico` int DEFAULT '0',
  `alertas_admin` text,
  `telefono` varchar(30) DEFAULT NULL,
  `whatsapp` varchar(30) DEFAULT NULL,
  `mostrar_correo` tinyint(1) DEFAULT '1',
  `qr_code` varchar(255) DEFAULT NULL,
  `titular_cuenta` varchar(150) DEFAULT NULL,
  `banco` varchar(150) DEFAULT NULL,
  `nro_cuenta` varchar(100) DEFAULT NULL,
  `datos_transferencia_adicional` text,
  `pago_qr_habilitado` tinyint(1) DEFAULT '0',
  `pago_transferencia_habilitado` tinyint(1) DEFAULT '0',
  `pago_deposito_habilitado` tinyint(1) DEFAULT '0',
  `fotos_adicionales` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `nutricionistas`
--

INSERT INTO `nutricionistas` (`id`, `usuario_id`, `especialidad`, `precio`, `rating`, `foto`, `descripcion_serv`, `universidad`, `titulo`, `anio_egreso`, `anio_titulacion`, `registro_prof`, `institucion_reg`, `licencia_inicio`, `licencia_vence`, `experiencia_anios`, `pacientes_exit`, `modalidad`, `idiomas`, `duracion_consulta`, `max_pacientes_dia`, `estado_verificacion`, `puntaje_tecnico`, `alertas_admin`, `telefono`, `whatsapp`, `mostrar_correo`, `qr_code`, `titular_cuenta`, `banco`, `nro_cuenta`, `datos_transferencia_adicional`, `pago_qr_habilitado`, `pago_transferencia_habilitado`, `pago_deposito_habilitado`, `fotos_adicionales`) VALUES
(1, 3, 'Nutrición Clínica', 150.00, 4.8, 'https://tse4.mm.bing.net/th/id/OIP.cB-G-RLoESxTi_My-nNjmgHaLH?r=0&cb=thfvnextfalcon2&pid=ImgDet&w=179&h=268&c=7&dpr=1,3&o=7&rm=3', 'Especialista en nutrición clínica con enfoque en enfermedades metabólicas y crónicas.', 'Universidad Mayor de San Francisco Xavier', 'Licenciado en Nutrición', 2015, 2016, 'NUT-2016-0189', NULL, NULL, NULL, 8, 420, 'Ambas', 'Español', 60, 8, 'aprobado', 88, NULL, '68616087', '68616087', 1, NULL, 'Diego Pérez S.', 'Banco Nacional de Bolivia', '100-2938402', 'Caja de ahorro en Bs. Enviar comprobante al WhatsApp.', 1, 1, 1, NULL),
(2, 4, 'Nutrición Deportiva', 120.00, 4.9, 'https://th.bing.com/th/id/R.16c7505184c8b3d4865b0e8cd92a37c9?rik=3XkXTEWxKJ%2fL1A&pid=ImgRaw&r=0', 'Especialista en nutrición deportiva para atletas de alto rendimiento y aficionados.', 'Universidad Mayor de San Francisco Xavier', 'Licenciada en Nutrición y Dietética', 2016, 2017, 'NUT-2017-0342', NULL, NULL, NULL, 7, 350, 'Ambas', 'Español, Inglés', 60, 6, 'aprobado', 91, NULL, '68427194', '68427194', 1, NULL, 'Elena Vargas P.', 'Banco Mercantil Santa Cruz', '401-923849', 'Transferencia bancaria directa. Enviar comprobante al WhatsApp.', 0, 1, 0, NULL),
(3, 5, 'Diabetes y Obesidad', 200.00, 5.0, 'https://tse4.mm.bing.net/th/id/OIP.7r9MvP6ydKN0JCoRfhbfjwHaKc?r=0&cb=thfvnextfalcon2&rs=1&pid=ImgDetMain&o=7&rm=3', 'Especialista en manejo nutricional de diabetes tipo 2 y control de obesidad.', 'Universidad Autónoma Gabriel René Moreno', 'Licenciado en Nutrición Clínica', 2014, 2015, 'NUT-2015-0199', NULL, NULL, NULL, 9, 520, 'Virtual', 'Español', 45, 8, 'aprobado', 94, NULL, '68652664', '68652664', 0, NULL, 'Marcos Soliz O.', 'Banco Unión', '150-29384910', 'Depósito en cuenta fiscal. Enviar comprobante.', 0, 1, 1, NULL),
(4, 6, 'Nutrición Infantil y Pediátrica', 130.00, 4.7, 'https://tse2.mm.bing.net/th/id/OIP.5crDb6JXRa9_Lrmh8QRIPQHaHa?r=0&cb=thfvnextfalcon2&pid=ImgDet&w=179&h=179&c=7&dpr=1,3&o=7&rm=3', 'Especializada en alimentación infantil desde el destete hasta la adolescencia. Trabajé 4 años en el Hospital Pediátrico de Sucre.', 'Universidad Mayor de San Francisco Xavier', 'Licenciada en Nutrición y Dietética', 2017, 2018, 'NUT-2018-0411', NULL, NULL, NULL, 6, 280, 'Ambas', 'Español', 45, 7, 'aprobado', 87, NULL, '68427194', '68427194', 1, NULL, 'Elena Vargas P. (Ejemplo)', 'Banco Mercantil Santa Cruz', '401-923849', 'Caja de ahorro. Enviar comprobante al WhatsApp.', 1, 1, 1, NULL),
(5, 7, 'Nutrición Clínica y Oncológica', 180.00, 4.6, 'https://tse2.mm.bing.net/th/id/OIP.GyKimjETOtzHWy-FixHkjwHaJg?r=0&cb=thfvnextfalcon2&pid=ImgDet&w=179&h=229&c=7&dpr=1,3&o=7&rm=3', 'Experta en soporte nutricional para pacientes oncológicos y con enfermedades crónicas complejas.', 'Universidad Autónoma Gabriel René Moreno', 'Licenciada en Nutrición Clínica', 2015, 2016, 'NUT-2016-0512', NULL, NULL, NULL, 8, 310, 'Virtual', 'Español, Inglés', 60, 5, 'aprobado', 90, NULL, '68652664', '68652664', 1, NULL, 'Marcos Soliz O. (Ejemplo)', 'Banco Unión', '150-29384910', 'Caja de ahorro. Enviar comprobante al WhatsApp.', 1, 1, 1, NULL),
(6, 8, 'Nutrición Deportiva y Funcional', 160.00, 4.8, 'https://tse4.mm.bing.net/th/id/OIP.7r9MvP6ydKN0JCoRfhbfjwHaKc?r=0&cb=thfvnextfalcon2&rs=1&pid=ImgDetMain&o=7&rm=3\r\n', 'Ex nutricionista de la selección boliviana de ciclismo. Especializado en periodización nutricional para deportistas amateurs y profesionales.', 'Universidad Mayor de San Francisco Xavier', 'Licenciado en Nutrición y Actividad Física', 2016, 2017, 'NUT-2017-0388', NULL, NULL, NULL, 7, 390, 'Ambas', 'Español', 60, 8, 'aprobado', 92, NULL, '68616087', '68616087', 1, NULL, 'Diego Pérez S. (Ejemplo)', 'Banco Nacional de Bolivia', '100-2938402', 'Caja de ahorro. Enviar comprobante al WhatsApp.', 1, 1, 1, NULL),
(7, 9, 'Geriatría Nutricional', 110.00, 4.5, 'https://tse4.mm.bing.net/th/id/OIP.CzBMtSO4J2q2xLB-yQIIfQHaHa?r=0&cb=thfvnextfalcon2&pid=ImgDet&w=179&h=179&c=7&dpr=1,3&o=7&rm=3', 'Especialista en nutrición para adultos mayores, enfocada en sarcopenia, osteoporosis y calidad de vida.', 'Universidad Mayor de San Francisco Xavier', 'Licenciada en Nutrición Geriátrica', 2018, 2019, 'NUT-2019-0299', NULL, NULL, NULL, 5, 195, 'Presencial', 'Español', 50, 6, 'aprobado', 83, NULL, '68427194', '68427194', 1, NULL, 'Elena Vargas P. (Ejemplo)', 'Banco Mercantil Santa Cruz', '401-923849', 'Caja de ahorro. Enviar comprobante al WhatsApp.', 1, 1, 1, NULL),
(8, 10, 'Trastornos Alimenticios y Psico-nutrición', 170.00, 4.9, 'https://3.bp.blogspot.com/-KtwV08_iEO4/Ux7lG1iLWJI/AAAAAAAAEg0/3-mksZ9ddM0/s1600/IMG_2415.jpg', 'Abordaje integral de trastornos como anorexia, bulimia y comer emocional, trabajando en equipo con psicólogos.', 'Universidad Pontificia Bolivariana', 'Licenciado en Nutrición y Psicología Aplicada', 2014, 2015, 'NUT-2015-0177', NULL, NULL, NULL, 10, 440, 'Virtual', 'Español', 60, 4, 'aprobado', 95, NULL, '68652664', '68652664', 1, NULL, 'Marcos Soliz O. (Ejemplo)', 'Banco Unión', '150-29384910', 'Caja de ahorro. Enviar comprobante al WhatsApp.', 1, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

CREATE TABLE `planes` (
  `id` int NOT NULL,
  `paciente_id` int NOT NULL,
  `nutricionista_id` int NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text,
  `calorias` int DEFAULT NULL,
  `calorias_dia` int DEFAULT NULL,
  `proteinas` int DEFAULT NULL,
  `carbohidratos` int DEFAULT NULL,
  `grasas` int DEFAULT NULL,
  `duracion_semanas` int DEFAULT '4',
  `estado` enum('activo','finalizado','pausado','completado') DEFAULT 'activo',
  `fecha_inicio` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id`, `paciente_id`, `nutricionista_id`, `titulo`, `descripcion`, `calorias`, `calorias_dia`, `proteinas`, `carbohidratos`, `grasas`, `duracion_semanas`, `estado`, `fecha_inicio`, `created_at`) VALUES
(1, 2, 1, 'Plan de descenso de peso - Fase 1', 'Plan hipocalórico moderado con énfasis en proteínas magras y vegetales.', 1600, NULL, 120, 150, 45, 8, 'activo', '2026-01-10', '2026-06-05 18:38:20'),
(2, 2, 2, 'Plan de hidratación intensiva', 'Mínimo 2.5 litros de agua diarios. Reducir bebidas gaseosas.', 0, NULL, 0, 0, 0, 4, 'finalizado', '2026-01-10', '2026-06-05 18:38:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulaciones`
--

CREATE TABLE `postulaciones` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `ci` varchar(30) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `sexo` enum('Masculino','Femenino','Prefiero no decir') DEFAULT NULL,
  `pais` varchar(100) DEFAULT 'Bolivia',
  `ciudad` varchar(100) DEFAULT NULL,
  `direccion_prof` varchar(255) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `universidad` varchar(200) DEFAULT NULL,
  `carrera` varchar(200) DEFAULT NULL,
  `anio_egreso` int DEFAULT NULL,
  `anio_titulacion` int DEFAULT NULL,
  `titulo_prof` varchar(200) DEFAULT NULL,
  `registro_prof` varchar(100) DEFAULT NULL,
  `institucion_reg` varchar(200) DEFAULT NULL,
  `licencia_inicio` date DEFAULT NULL,
  `licencia_vence` date DEFAULT NULL,
  `especialidades` json DEFAULT NULL,
  `experiencia` json DEFAULT NULL,
  `tipo_consulta` varchar(100) DEFAULT NULL,
  `precio` decimal(8,2) DEFAULT '120.00',
  `duracion_consulta` int DEFAULT '60',
  `modalidad` enum('Virtual','Presencial','Ambas') DEFAULT 'Virtual',
  `descripcion_serv` text,
  `idiomas` varchar(200) DEFAULT 'Español',
  `horarios` json DEFAULT NULL,
  `max_pacientes_dia` int DEFAULT '8',
  `resp_tecnica_1` text,
  `resp_tecnica_2` text,
  `resp_tecnica_3` text,
  `resp_tecnica_4` text,
  `resp_tecnica_5` text,
  `puntaje_tecnico` int DEFAULT '0',
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `alertas` text,
  `notas_admin` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenas`
--

CREATE TABLE `resenas` (
  `id` int NOT NULL,
  `paciente_id` int NOT NULL,
  `nutricionista_id` int NOT NULL,
  `cita_id` int DEFAULT NULL,
  `calificacion` tinyint NOT NULL,
  `comentario` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Volcado de datos para la tabla `resenas`
--

INSERT INTO `resenas` (`id`, `paciente_id`, `nutricionista_id`, `cita_id`, `calificacion`, `comentario`, `created_at`) VALUES
(1, 2, 1, 1, 5, 'Excelente profesional, muy puntual y clara en sus explicaciones.', '2026-06-05 18:38:20'),
(2, 2, 2, 2, 4, 'Muy buena consulta, me dio consejos prácticos.', '2026-06-05 18:38:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento`
--

CREATE TABLE `seguimiento` (
  `id` int NOT NULL,
  `paciente_id` int NOT NULL,
  `fecha` date NOT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `cintura` decimal(5,2) DEFAULT NULL,
  `cadera` decimal(5,2) DEFAULT NULL,
  `grasa` decimal(5,2) DEFAULT NULL,
  `altura` decimal(5,2) DEFAULT NULL,
  `imc` decimal(5,2) DEFAULT NULL,
  `nota` text,
  `notas` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `seguimiento`
--

INSERT INTO `seguimiento` (`id`, `paciente_id`, `fecha`, `peso`, `cintura`, `cadera`, `grasa`, `altura`, `imc`, `nota`, `notas`, `created_at`) VALUES
(1, 2, '2026-01-10', 78.50, 92.00, 104.00, 28.50, NULL, NULL, 'Inicio del plan', NULL, '2026-06-05 18:38:20'),
(2, 2, '2026-01-24', 77.20, 90.50, 103.00, 27.80, NULL, NULL, 'Buena adherencia', NULL, '2026-06-05 18:38:20'),
(3, 2, '2026-02-07', 76.00, 89.00, 102.00, 27.10, NULL, NULL, 'Se nota la diferencia', NULL, '2026-06-05 18:38:20'),
(4, 2, '2026-02-21', 74.80, 87.50, 101.00, 26.50, NULL, NULL, 'Motivada', NULL, '2026-06-05 18:38:20'),
(5, 2, '2026-03-07', 73.50, 86.00, 100.00, 25.90, NULL, NULL, 'Excelente progreso', NULL, '2026-06-05 18:38:20'),
(6, 2, '2026-03-21', 72.10, 84.50, 99.00, 25.20, NULL, NULL, 'Meta casi alcanzada', NULL, '2026-06-05 18:38:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int NOT NULL,
  `nutricionista_id` int NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text,
  `categoria` enum('Pérdida de peso','Ganancia muscular','Control de diabetes','Nutrición deportiva','Nutrición infantil','Nutrición clínica','Nutrición geriátrica','Trastornos alimenticios','Embarazo y lactancia','Otro') DEFAULT 'Otro',
  `precio` decimal(8,2) NOT NULL DEFAULT '0.00',
  `duracion_semanas` int DEFAULT '4',
  `modalidad` enum('Virtual','Presencial','Ambas') DEFAULT 'Virtual',
  `incluye` text,
  `estado` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
  `motivo_rechazo` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nutricionista_id`, `titulo`, `descripcion`, `categoria`, `precio`, `duracion_semanas`, `modalidad`, `incluye`, `estado`, `motivo_rechazo`, `created_at`, `updated_at`) VALUES
(1, 3, 'Plan de Control de Peso Intensivo', 'Programa estructurado de 8 semanas enfocado en reducción de peso saludable mediante alimentación balanceada y seguimiento semanal.', 'Pérdida de peso', 350.00, 8, 'Ambas', '4 consultas virtuales, plan alimenticio personalizado, recetario PDF, seguimiento por WhatsApp', 'Aprobado', NULL, '2026-06-05 18:38:20', '2026-06-05 18:38:20'),
(2, 4, 'Plan Nutrición Deportiva de Alto Rendimiento', 'Diseñado para atletas y deportistas que buscan optimizar su rendimiento mediante una alimentación estratégica pre y post entreno.', 'Nutrición deportiva', 480.00, 6, 'Virtual', '3 consultas, análisis de composición corporal, plan de hidratación, suplementación guiada', 'Aprobado', NULL, '2026-06-05 18:38:20', '2026-06-05 18:38:20'),
(3, 5, 'Control Nutricional para Diabetes Tipo 2', 'Intervención nutricional especializada para pacientes con diabetes tipo 2, orientada a control glucémico mediante dieta terapéutica.', 'Control de diabetes', 420.00, 12, 'Ambas', '6 consultas, monitoreo glucémico, guía de alimentos permitidos, coordinación con médico tratante', 'Aprobado', NULL, '2026-06-05 18:38:20', '2026-06-05 18:38:20'),
(4, 3, 'Nutrición para Embarazo Saludable', 'Plan integral de alimentación para mujeres embarazadas, cubriendo todos los trimestres con requerimientos nutricionales específicos.', 'Embarazo y lactancia', 390.00, 9, 'Virtual', '5 consultas, suplementación recomendada, menú semanal, guía de alimentos a evitar', 'Aprobado', NULL, '2026-06-05 18:38:20', '2026-06-05 21:39:05'),
(5, 4, 'Dieta Keto Express 7 días', 'Dieta cetogénica rápida para perder peso en una semana.', 'Pérdida de peso', 50.00, 1, 'Virtual', 'Un PDF genérico', 'Rechazado', 'El servicio no cumple con los estándares mínimos: duración insuficiente (1 semana), precio no justificado y descripción genérica. Amplíe el programa a mínimo 4 semanas con seguimiento profesional.', '2026-06-05 18:38:20', '2026-06-05 18:38:20'),
(6, 4, 'Plan Nutricional Infantil 3-12 años', 'Programa diseñado para niños con problemas de alimentación, bajo peso, sobrepeso o simplemente mejorar sus hábitos alimenticios de forma divertida y efectiva.', 'Nutrición infantil', 280.00, 8, 'Ambas', '4 consultas, plan de menús semanales, guía para padres, estrategias de introducción de alimentos, recetario infantil PDF', 'Aprobado', NULL, '2026-06-05 21:51:45', '2026-06-05 21:51:45'),
(7, 4, 'Nutrición para Adolescentes con Acné y Problemas Hormonales', 'Intervención nutricional específica para adolescentes donde la alimentación influye directamente en la piel, el peso y el estado emocional.', 'Nutrición infantil', 220.00, 6, 'Virtual', '3 consultas, plan alimenticio, lista de alimentos anti-inflamatorios, seguimiento mensual', 'Aprobado', NULL, '2026-06-05 21:51:45', '2026-06-05 21:51:45'),
(8, 5, 'Soporte Nutricional Oncológico', 'Acompañamiento nutricional para pacientes en quimioterapia o radioterapia, orientado a mantener masa muscular, controlar efectos secundarios y mejorar calidad de vida.', 'Nutrición clínica', 520.00, 16, 'Virtual', '8 consultas, coordinación con oncólogo, suplementación especializada, plan anti-náuseas, monitoreo semanal', 'Aprobado', NULL, '2026-06-05 21:51:45', '2026-06-05 21:51:45'),
(9, 5, 'Plan Nutricional para Enfermedad Renal Crónica', 'Dieta específica para pacientes con ERC en diferentes estadios, controlando proteínas, fósforo, potasio y sodio según cada caso.', 'Nutrición clínica', 450.00, 12, 'Virtual', '6 consultas, análisis de laboratorio guiado, menú adaptado, coordinación médica', 'Aprobado', NULL, '2026-06-05 21:51:45', '2026-06-05 21:51:45'),
(10, 6, 'Periodización Nutricional para Ciclistas y Triatletas', 'Planificación nutricional estratégica sincronizada con el calendario de entrenamiento y competencias del deportista.', 'Nutrición deportiva', 550.00, 12, 'Virtual', '6 consultas, plan por fases de entrenamiento, estrategia de hidratación, análisis de composición corporal mensual', 'Aprobado', NULL, '2026-06-05 21:51:45', '2026-06-05 21:51:45'),
(11, 6, 'Nutrición para Gym y Musculación', 'Plan enfocado en ganancia de masa muscular limpia con suplementación basada en evidencia científica.', 'Ganancia muscular', 380.00, 10, 'Ambas', '5 consultas, plan de volumen y definición, guía de suplementos, análisis de composición corporal', 'Aprobado', NULL, '2026-06-05 21:51:45', '2026-06-05 21:51:45'),
(12, 7, 'Nutrición para Adultos Mayores con Sarcopenia', 'Programa especializado para frenar la pérdida de masa muscular en adultos mayores de 60 años, mejorando fuerza, movilidad y calidad de vida.', 'Nutrición geriátrica', 200.00, 10, 'Presencial', '5 consultas presenciales, plan de alimentación rico en proteínas, guía de ejercicio adaptado, seguimiento familiar', 'Aprobado', NULL, '2026-06-05 21:51:45', '2026-06-05 21:51:45'),
(13, 8, 'Programa de Recuperación de Trastornos Alimenticios', 'Abordaje multidisciplinario (nutrición + psicología) para personas con anorexia, bulimia o comer compulsivo, con enfoque compasivo y sin dietas restrictivas.', 'Trastornos alimenticios', 600.00, 20, 'Virtual', '10 consultas, coordinación con psicólogo, plan de normalización alimentaria, grupo de apoyo online, materiales de psico-nutrición', 'Aprobado', NULL, '2026-06-05 21:51:45', '2026-06-05 21:51:45'),
(14, 8, 'Plan Anti-Estrés y Bienestar Emocional', 'Programa para personas que comen de forma emocional o que el estrés afecta su alimentación, combinando nutrición y técnicas de mindful eating.', 'Trastornos alimenticios', 320.00, 8, 'Virtual', '4 consultas, plan de alimentación consciente, ejercicios de mindfulness, diario alimentario guiado', 'Aprobado', NULL, '2026-06-05 21:51:45', '2026-06-05 21:51:45'),
(15, 4, 'Plan Nutricional Infantil 3-12 años', 'Programa diseñado para niños con problemas de alimentación, bajo peso, sobrepeso o simplemente mejorar sus hábitos alimenticios de forma divertida y efectiva.', 'Nutrición infantil', 280.00, 8, 'Ambas', '4 consultas, plan de menús semanales, guía para padres, estrategias de introducción de alimentos, recetario infantil PDF', 'Aprobado', NULL, '2026-06-05 21:56:26', '2026-06-05 21:56:26'),
(16, 4, 'Nutrición para Adolescentes con Acné y Problemas Hormonales', 'Intervención nutricional específica para adolescentes donde la alimentación influye directamente en la piel, el peso y el estado emocional.', 'Nutrición infantil', 220.00, 6, 'Virtual', '3 consultas, plan alimenticio, lista de alimentos anti-inflamatorios, seguimiento mensual', 'Aprobado', NULL, '2026-06-05 21:56:26', '2026-06-05 21:56:26'),
(17, 5, 'Soporte Nutricional Oncológico', 'Acompañamiento nutricional para pacientes en quimioterapia o radioterapia, orientado a mantener masa muscular, controlar efectos secundarios y mejorar calidad de vida.', 'Nutrición clínica', 520.00, 16, 'Virtual', '8 consultas, coordinación con oncólogo, suplementación especializada, plan anti-náuseas, monitoreo semanal', 'Aprobado', NULL, '2026-06-05 21:56:26', '2026-06-05 21:56:26'),
(18, 5, 'Plan Nutricional para Enfermedad Renal Crónica', 'Dieta específica para pacientes con ERC en diferentes estadios, controlando proteínas, fósforo, potasio y sodio según cada caso.', 'Nutrición clínica', 450.00, 12, 'Virtual', '6 consultas, análisis de laboratorio guiado, menú adaptado, coordinación médica', 'Aprobado', NULL, '2026-06-05 21:56:26', '2026-06-05 21:56:26'),
(19, 6, 'Periodización Nutricional para Ciclistas y Triatletas', 'Planificación nutricional estratégica sincronizada con el calendario de entrenamiento y competencias del deportista.', 'Nutrición deportiva', 550.00, 12, 'Virtual', '6 consultas, plan por fases de entrenamiento, estrategia de hidratación, análisis de composición corporal mensual', 'Aprobado', NULL, '2026-06-05 21:56:26', '2026-06-05 21:56:26'),
(20, 6, 'Nutrición para Gym y Musculación', 'Plan enfocado en ganancia de masa muscular limpia con suplementación basada en evidencia científica.', 'Ganancia muscular', 380.00, 10, 'Ambas', '5 consultas, plan de volumen y definición, guía de suplementos, análisis de composición corporal', 'Aprobado', NULL, '2026-06-05 21:56:26', '2026-06-05 21:56:26'),
(21, 7, 'Nutrición para Adultos Mayores con Sarcopenia', 'Programa especializado para frenar la pérdida de masa muscular en adultos mayores de 60 años, mejorando fuerza, movilidad y calidad de vida.', 'Nutrición geriátrica', 200.00, 10, 'Presencial', '5 consultas presenciales, plan de alimentación rico en proteínas, guía de ejercicio adaptado, seguimiento familiar', 'Aprobado', NULL, '2026-06-05 21:56:26', '2026-06-05 21:56:26'),
(22, 8, 'Programa de Recuperación de Trastornos Alimenticios', 'Abordaje multidisciplinario (nutrición + psicología) para personas con anorexia, bulimia o comer compulsivo, con enfoque compasivo y sin dietas restrictivas.', 'Trastornos alimenticios', 600.00, 20, 'Virtual', '10 consultas, coordinación con psicólogo, plan de normalización alimentaria, grupo de apoyo online, materiales de psico-nutrición', 'Aprobado', NULL, '2026-06-05 21:56:26', '2026-06-05 21:56:26'),
(23, 8, 'Plan Anti-Estrés y Bienestar Emocional', 'Programa para personas que comen de forma emocional o que el estrés afecta su alimentación, combinando nutrición y técnicas de mindful eating.', 'Trastornos alimenticios', 320.00, 8, 'Virtual', '4 consultas, plan de alimentación consciente, ejercicios de mindfulness, diario alimentario guiado', 'Aprobado', NULL, '2026-06-05 21:56:26', '2026-06-05 21:56:26'),
(24, 48, 'Peso integral', 'Ayudo a llegar a peso', 'Nutrición deportiva', 200.00, 8, 'Ambas', 'Mucho', 'Aprobado', NULL, '2026-06-11 14:00:13', '2026-06-11 14:30:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id` int NOT NULL,
  `paciente_id` int NOT NULL,
  `servicio_id` int NOT NULL,
  `precio_historico` decimal(8,2) NOT NULL,
  `motivo_consulta` text NOT NULL,
  `peso_actual` decimal(5,2) DEFAULT NULL,
  `altura_actual` decimal(5,2) DEFAULT NULL,
  `condiciones_medicas` text,
  `estado` enum('Pendiente','Aceptada','Rechazada') DEFAULT 'Pendiente',
  `respuesta_ofertante` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`id`, `paciente_id`, `servicio_id`, `precio_historico`, `motivo_consulta`, `peso_actual`, `altura_actual`, `condiciones_medicas`, `estado`, `respuesta_ofertante`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 350.00, 'Quiero reducir grasa abdominal y mejorar mis hábitos de alimentación diarios.', 72.50, 165.00, 'Ninguna', 'Pendiente', NULL, '2026-06-05 18:38:20', '2026-06-05 18:38:20'),
(2, 2, 2, 480.00, 'Necesito preparar mi dieta para una maratón de 10K el próximo mes.', 70.00, 165.00, 'Alergia al maní', 'Aceptada', '¡Excelente! Vamos a trabajar en tu rendimiento y a planificar tu hidratación.', '2026-06-05 18:38:20', '2026-06-05 18:38:20'),
(3, 2, 3, 420.00, 'Controlar mis niveles de glucemia ya que fui diagnosticada recientemente.', 71.00, 165.00, 'Diabetes Tipo 2, hipertensión leve', 'Rechazada', 'Por el momento no tengo disponibilidad para nuevos pacientes clínicos con estas patologías. Te sugiero contactar al Dr. Soliz.', '2026-06-05 18:38:20', '2026-06-05 18:38:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('Paciente','Nutricionista','Administrador') DEFAULT 'Paciente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ci` varchar(30) DEFAULT NULL,
  `celular` varchar(30) DEFAULT NULL,
  `estado` enum('activo','bloqueado') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `created_at`, `ci`, `celular`, `estado`) VALUES
(1, 'Luis Gabriel', 'luis@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', '2026-06-05 18:38:20', NULL, NULL, 'activo'),
(2, 'Carla Soto', 'carla@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paciente', '2026-06-05 18:38:20', NULL, NULL, 'activo'),
(3, 'Diego Pérez', 'diego@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista', '2026-06-05 18:38:20', NULL, NULL, 'activo'),
(4, 'Elena Vargas', 'elena@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista', '2026-06-05 18:38:20', NULL, NULL, 'activo'),
(5, 'Marcos Soliz', 'marcos@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista', '2026-06-05 18:38:20', NULL, NULL, 'activo'),
(6, 'Ana Beltrán', 'ana@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paciente', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(7, 'Roberto Quispe', 'roberto@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paciente', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(8, 'Valentina Cruz', 'valentina@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paciente', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(9, 'Jhonatan Mamani', 'jhonatan@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paciente', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(10, 'Lucía Flores', 'lucia@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paciente', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(11, 'Miguel Torrico', 'miguel@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paciente', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(12, 'Sofía Mendoza', 'sofia@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(13, 'Camila Rojas', 'camila@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(14, 'Andrés Chávez', 'andres@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(15, 'Patricia Salinas', 'patricia@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(16, 'Fernando Aguilar', 'fernando@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(17, 'Daniela Rios', 'daniela.post@nutrisucre.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nutricionista', '2026-06-05 21:51:45', NULL, NULL, 'activo'),
(48, 'Juana del arco', 'juana@gmail.com', '$2y$10$BE1la0dqfFRSM1cQkLtd2.2LAyRkUWGVl1bkYrqYBHJm8cd1xXvTS', 'Nutricionista', '2026-06-11 13:28:31', NULL, NULL, 'activo'),
(49, 'Jorge Luis', 'jorgel@gmail.com', '$2y$10$KBjMynwstu7RBfZKXQZHDejyXA207SIlpaIyttQOpQbKOxi5ufrpi', 'Paciente', '2026-06-11 14:17:51', '10394587', '78945623', 'activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `idx_citas_disponibilidad` (`nutricionista_id`,`fecha`,`hora`,`estado`),
  ADD KEY `fk_citas_servicios` (`servicio_id`);

--
-- Indices de la tabla `disponibilidad`
--
ALTER TABLE `disponibilidad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nutricionista_id` (`nutricionista_id`);

--
-- Indices de la tabla `nutricionistas`
--
ALTER TABLE `nutricionistas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `nutricionista_id` (`nutricionista_id`);

--
-- Indices de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_resenia_cita` (`cita_id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `nutricionista_id` (`nutricionista_id`);

--
-- Indices de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_paciente_fecha` (`paciente_id`,`fecha`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_nutricionista` (`nutricionista_id`);

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servicio_id` (`servicio_id`),
  ADD KEY `idx_solicitud_estado` (`estado`),
  ADD KEY `idx_solicitud_paciente` (`paciente_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `disponibilidad`
--
ALTER TABLE `disponibilidad`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `nutricionistas`
--
ALTER TABLE `nutricionistas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resenas`
--
ALTER TABLE `resenas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`nutricionista_id`) REFERENCES `nutricionistas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_citas_servicios` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `disponibilidad`
--
ALTER TABLE `disponibilidad`
  ADD CONSTRAINT `disponibilidad_ibfk_1` FOREIGN KEY (`nutricionista_id`) REFERENCES `nutricionistas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `nutricionistas`
--
ALTER TABLE `nutricionistas`
  ADD CONSTRAINT `nutricionistas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `planes`
--
ALTER TABLE `planes`
  ADD CONSTRAINT `planes_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `planes_ibfk_2` FOREIGN KEY (`nutricionista_id`) REFERENCES `nutricionistas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD CONSTRAINT `postulaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `resenas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resenas_ibfk_2` FOREIGN KEY (`nutricionista_id`) REFERENCES `nutricionistas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resenas_ibfk_3` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD CONSTRAINT `seguimiento_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `servicios_ibfk_1` FOREIGN KEY (`nutricionista_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitudes_ibfk_2` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
