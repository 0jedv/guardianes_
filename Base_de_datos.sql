-- ============================================
-- SCRIPT COMPLETO DE BASE DE DATOS - GUARDIANES
-- ============================================
-- 1. Borra la base de datos actual y la crea de nuevo
-- 2. Crea todas las tablas con la estructura final
-- 3. Inserta datos de prueba completos
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. BORRADO Y CREACIÓN DE LA BASE DE DATOS
-- --------------------------------------------------------

DROP DATABASE IF EXISTS `guardianes`;
CREATE DATABASE IF NOT EXISTS `guardianes` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `guardianes`;

-- --------------------------------------------------------
-- 2. CREACIÓN DE ESTRUCTURA (SIN DROP TABLE INDIVIDUAL)
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--
CREATE TABLE `profesores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL, -- Campo añadido
  `rol` enum('admin','profesor') DEFAULT 'profesor', -- Campo añadido
  `telefono` varchar(20) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `horas_contrato` int(11) DEFAULT NULL,
  `estado` enum('activo','baja','excedencia','inactivo') DEFAULT 'activo',
  `fecha_alta` date DEFAULT NULL,
  `fecha_baja` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`) -- Clave única añadida
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `aulas`
--
CREATE TABLE `aulas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `tipo` enum('normal','laboratorio','taller','informatica') DEFAULT 'normal',
  `planta` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `modulos`
--
CREATE TABLE `modulos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `curso` varchar(50) DEFAULT NULL,
  `horas_semanales` int(11) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `horario`
--
CREATE TABLE `horario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profesor_id` int(11) NOT NULL,
  `aula_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `grupo` varchar(50) DEFAULT NULL,
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `curso_escolar` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `profesor_id` (`profesor_id`),
  KEY `aula_id` (`aula_id`),
  KEY `modulo_id` (`modulo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `guardias`
--
CREATE TABLE `guardias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `horario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `profesor_sustituto_id` int(11) DEFAULT NULL, -- Campo añadido
  `estado` enum('pendiente','asignada','cubierta','cancelada') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL, -- Campo añadido
  PRIMARY KEY (`id`),
  KEY `horario_id` (`horario_id`),
  KEY `profesor_sustituto_id` (`profesor_sustituto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `ausencias`
--
CREATE TABLE `ausencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profesor_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `tipo` enum('enfermedad','permiso','formacion','personal') NOT NULL, -- Tipo 'personal' añadido
  `justificada` enum('si','no') DEFAULT 'si',
  `observaciones` text DEFAULT NULL,
  `estado` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente', -- Campo añadido
  PRIMARY KEY (`id`),
  KEY `profesor_id` (`profesor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `asignacion_guardias`
--
CREATE TABLE `asignacion_guardias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guardia_id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL,
  `confirmada` enum('si','no') DEFAULT 'no',
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guardia_id` (`guardia_id`),
  KEY `profesor_id` (`profesor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `centro`
--
CREATE TABLE `centro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `director` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `contador_guardias`
--
CREATE TABLE `contador_guardias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profesor_id` int(11) NOT NULL,
  `año_mes` char(7) NOT NULL,
  `total_guardias` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `profesor_id` (`profesor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `profesor_modulo`
--
CREATE TABLE `profesor_modulo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profesor_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `preferencia` enum('alta','media','baja') DEFAULT 'media',
  PRIMARY KEY (`id`),
  KEY `profesor_id` (`profesor_id`),
  KEY `modulo_id` (`modulo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 3. RESTRICCIONES (Foreign Keys)
-- --------------------------------------------------------

ALTER TABLE `horario`
  ADD CONSTRAINT `horario_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`),
  ADD CONSTRAINT `horario_ibfk_2` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`),
  ADD CONSTRAINT `horario_ibfk_3` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`);

ALTER TABLE `guardias`
  ADD CONSTRAINT `guardias_ibfk_1` FOREIGN KEY (`horario_id`) REFERENCES `horario` (`id`),
  ADD CONSTRAINT `guardias_ibfk_2` FOREIGN KEY (`profesor_sustituto_id`) REFERENCES `profesores` (`id`);

ALTER TABLE `ausencias`
  ADD CONSTRAINT `ausencias_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`);

ALTER TABLE `asignacion_guardias`
  ADD CONSTRAINT `asignacion_guardias_ibfk_1` FOREIGN KEY (`guardia_id`) REFERENCES `guardias` (`id`),
  ADD CONSTRAINT `asignacion_guardias_ibfk_2` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`);

ALTER TABLE `contador_guardias`
  ADD CONSTRAINT `contador_guardias_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`);

ALTER TABLE `profesor_modulo`
  ADD CONSTRAINT `profesor_modulo_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`),
  ADD CONSTRAINT `profesor_modulo_ibfk_2` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`);


-- --------------------------------------------------------
-- 4. INSERCIÓN DE DATOS DE PRUEBA
-- --------------------------------------------------------

--
-- PROFESORES
--
-- Passwords: 
-- admin/profesores: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi (Hash para 'profesor123')
--
INSERT INTO `profesores` (`nombre`, `apellidos`, `email`, `password`, `rol`, `telefono`, `departamento`, `horas_contrato`, `estado`, `fecha_alta`) VALUES
('Admin', 'Sistema', 'admin@guardianes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '600000000', 'Administración', 40, 'activo', '2024-09-01'),
('Juan', 'García López', 'juan.garcia@guardianes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '600111111', 'Matemáticas', 25, 'activo', '2024-09-01'),
('María', 'Rodríguez Pérez', 'maria.rodriguez@guardianes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '600222222', 'Lengua', 25, 'activo', '2024-09-01'),
('Carlos', 'Martínez Sánchez', 'carlos.martinez@guardianes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '600333333', 'Informática', 30, 'activo', '2024-09-01'),
('Ana', 'López Fernández', 'ana.lopez@guardianes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '600444444', 'Inglés', 20, 'activo', '2024-09-01'),
('Pedro', 'Sánchez Ruiz', 'pedro.sanchez@guardianes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '600555555', 'Ciencias', 25, 'activo', '2024-09-01'),
('Laura', 'Fernández Gómez', 'laura.fernandez@guardianes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '600666666', 'Historia', 20, 'activo', '2024-09-01');

--
-- AULAS
--
INSERT INTO `aulas` (`nombre`, `capacidad`, `tipo`, `planta`, `observaciones`) VALUES
('A101', 30, 'normal', 1, 'Aula estándar planta 1'),
('A102', 30, 'normal', 1, 'Aula estándar planta 1'),
('A103', 30, 'normal', 1, 'Aula estándar planta 1'),
('A201', 30, 'normal', 2, 'Aula estándar planta 2'),
('LAB-INFO1', 25, 'informatica', 1, 'Laboratorio de informática 1'),
('LAB-INFO2', 25, 'informatica', 1, 'Laboratorio de informática 2'),
('LAB-CIENCIAS', 20, 'laboratorio', 2, 'Laboratorio de ciencias'),
('TALLER1', 20, 'taller', 0, 'Taller de tecnología'),
('BIBLIOTECA', 50, 'normal', 1, 'Sala de lectura');

--
-- MÓDULOS
--
INSERT INTO `modulos` (`nombre`, `curso`, `horas_semanales`, `departamento`) VALUES
('Matemáticas I', '1º ESO', 4, 'Matemáticas'),
('Matemáticas II', '2º ESO', 4, 'Matemáticas'),
('Lengua Castellana', '1º ESO', 3, 'Lengua'),
('Literatura', '2º ESO', 3, 'Lengua'),
('Programación', '1º DAW', 8, 'Informática'),
('Bases de Datos', '2º DAW', 6, 'Informática'),
('Inglés I', '1º ESO', 3, 'Inglés'),
('Inglés II', '2º ESO', 3, 'Inglés'),
('Física', '1º ESO', 3, 'Ciencias'),
('Química', '2º ESO', 3, 'Ciencias'),
('Historia Universal', '1º ESO', 2, 'Historia'),
('Historia de España', '2º ESO', 2, 'Historia');

--
-- HORARIOS
--
INSERT INTO `horario` (`profesor_id`, `aula_id`, `modulo_id`, `grupo`, `dia_semana`, `hora_inicio`, `hora_fin`, `curso_escolar`) VALUES
-- Lunes
(2, 1, 1, '1º ESO A', 'lunes', '08:00:00', '09:00:00', '2024-2025'),
(2, 1, 1, '1º ESO A', 'lunes', '09:00:00', '10:00:00', '2024-2025'),
(2, 2, 2, '2º ESO B', 'lunes', '10:30:00', '11:30:00', '2024-2025'),
(3, 3, 3, '1º ESO A', 'lunes', '08:00:00', '09:00:00', '2024-2025'),
(3, 3, 4, '2º ESO A', 'lunes', '11:30:00', '12:30:00', '2024-2025'),
(4, 5, 5, '1º DAW', 'lunes', '08:00:00', '10:00:00', '2024-2025'),
(4, 5, 6, '2º DAW', 'lunes', '10:30:00', '12:30:00', '2024-2025'),
(5, 4, 7, '1º ESO A', 'lunes', '09:00:00', '10:00:00', '2024-2025'),
(5, 4, 8, '2º ESO A', 'lunes', '12:30:00', '13:30:00', '2024-2025'),
-- Martes
(2, 1, 1, '1º ESO B', 'martes', '08:00:00', '09:00:00', '2024-2025'),
(2, 2, 2, '2º ESO A', 'martes', '11:30:00', '12:30:00', '2024-2025'),
(3, 3, 3, '1º ESO B', 'martes', '09:00:00', '10:00:00', '2024-2025'),
(3, 3, 4, '2º ESO B', 'martes', '10:30:00', '11:30:00', '2024-2025'),
(4, 6, 5, '1º DAW', 'martes', '08:00:00', '10:00:00', '2024-2025'),
-- Miércoles
(2, 1, 1, '1º ESO A', 'miercoles', '09:00:00', '10:00:00', '2024-2025'),
(4, 5, 5, '1º DAW', 'miercoles', '08:00:00', '10:00:00', '2024-2025'),
-- Jueves
(2, 1, 1, '1º ESO A', 'jueves', '08:00:00', '09:00:00', '2024-2025'),
(4, 6, 6, '2º DAW', 'jueves', '08:00:00', '10:00:00', '2024-2025'),
-- Viernes
(2, 1, 1, '1º ESO B', 'viernes', '10:30:00', '11:30:00', '2024-2025'),
(4, 5, 5, '1º DAW', 'viernes', '08:00:00', '10:00:00', '2024-2025');

--
-- AUSENCIAS
--
INSERT INTO `ausencias` (`profesor_id`, `fecha_inicio`, `fecha_fin`, `motivo`, `tipo`, `justificada`, `estado`) VALUES
(2, '2025-12-09', '2025-12-11', 'Enfermedad - Gripe', 'enfermedad', 'si', 'aprobada'),
(5, '2025-12-10', '2025-12-10', 'Asuntos personales', 'personal', 'si', 'aprobada'),
(4, '2025-12-12', '2025-12-13', 'Curso de formación', 'formacion', 'si', 'pendiente');

COMMIT;