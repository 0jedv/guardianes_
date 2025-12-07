-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-11-2025 a las 12:47:51
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `guardianes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_guardias`
--

CREATE TABLE `asignacion_guardias` (
  `id` int(11) NOT NULL,
  `guardia_id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL,
  `confirmada` enum('si','no') DEFAULT 'no',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aulas`
--

CREATE TABLE `aulas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `tipo` enum('normal','laboratorio','taller','informatica') DEFAULT 'normal',
  `planta` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aulas`
--

INSERT INTO `aulas` (`id`, `nombre`, `capacidad`, `tipo`, `planta`, `observaciones`) VALUES
(1, 'A1', 30, 'normal', 1, NULL),
(2, 'A2', 30, 'normal', 1, NULL),
(3, 'A3', 25, 'normal', 1, 'Aula pequeña'),
(4, 'L1', 20, 'informatica', 0, 'Laboratorio principal'),
(5, 'L2', 20, 'informatica', 0, 'Laboratorio secundario'),
(6, 'T1', 15, 'taller', 0, 'Taller de hardware'),
(7, 'B1', 30, 'normal', 2, NULL),
(8, 'B2', 30, 'normal', 2, NULL),
(9, 'SUM', 100, 'normal', 0, 'Salón de usos múltiples');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ausencias`
--

CREATE TABLE `ausencias` (
  `id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `tipo` enum('enfermedad','permiso','formacion') NOT NULL,
  `justificada` enum('si','no') DEFAULT 'si',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro`
--

CREATE TABLE `centro` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `director` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `centro`
--

INSERT INTO `centro` (`id`, `nombre`, `direccion`, `telefono`, `email`, `director`) VALUES
(1, 'IES Sevilla Tech', 'Calle Ejemplo 123, Sevilla', '954123456', 'info@sevillatech.edu', 'Francisco Gómez Ramírez');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contador_guardias`
--

CREATE TABLE `contador_guardias` (
  `id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `año_mes` char(7) NOT NULL,
  `total_guardias` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guardias`
--

CREATE TABLE `guardias` (
  `id` int(11) NOT NULL,
  `horario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('pendiente','asignada','cubierta','cancelada') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horario`
--

CREATE TABLE `horario` (
  `id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `aula_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `grupo` varchar(50) DEFAULT NULL,
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `curso_escolar` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horario`
--

INSERT INTO `horario` (`id`, `profesor_id`, `aula_id`, `modulo_id`, `grupo`, `dia_semana`, `hora_inicio`, `hora_fin`, `curso_escolar`) VALUES
(1, 1, 4, 1, '1º DAM-A', 'lunes', '08:15:00', '09:15:00', '2024-2025'),
(2, 1, 4, 1, '1º DAM-A', 'lunes', '09:15:00', '10:15:00', '2024-2025'),
(3, 1, 4, 7, '2º DAM-A', 'lunes', '11:45:00', '12:45:00', '2024-2025'),
(4, 1, 4, 7, '2º DAM-A', 'lunes', '12:45:00', '13:45:00', '2024-2025'),
(5, 2, 5, 2, '1º DAM-B', 'martes', '08:15:00', '09:15:00', '2024-2025'),
(6, 2, 5, 2, '1º DAM-B', 'martes', '09:15:00', '10:15:00', '2024-2025'),
(7, 2, 5, 2, '1º DAM-B', 'martes', '10:15:00', '11:15:00', '2024-2025'),
(8, 2, 5, 9, '2º DAM-A', 'martes', '11:45:00', '12:45:00', '2024-2025'),
(9, 9, 4, 1, '1º DAM-B', 'miercoles', '08:15:00', '09:15:00', '2024-2025'),
(10, 9, 4, 2, '1º DAM-A', 'miercoles', '09:15:00', '10:15:00', '2024-2025'),
(11, 9, 4, 2, '1º DAM-A', 'miercoles', '10:15:00', '11:15:00', '2024-2025');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `curso` varchar(50) DEFAULT NULL,
  `horas_semanales` int(11) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modulos`
--

INSERT INTO `modulos` (`id`, `nombre`, `curso`, `horas_semanales`, `departamento`) VALUES
(1, 'Bases de Datos', '1º DAM', 6, 'Informática'),
(2, 'Programación', '1º DAM', 8, 'Informática'),
(3, 'Lenguajes de Marcas', '1º DAM', 4, 'Informática'),
(4, 'Sistemas Informáticos', '1º DAM', 6, 'Informática'),
(5, 'Entornos de Desarrollo', '1º DAM', 3, 'Informática'),
(6, 'FOL', '1º DAM', 3, 'FOL'),
(7, 'Acceso a Datos', '2º DAM', 6, 'Informática'),
(8, 'Desarrollo de Interfaces', '2º DAM', 6, 'Informática'),
(9, 'Programación de Servicios', '2º DAM', 5, 'Informática'),
(10, 'Sistemas de Gestión Empresarial', '2º DAM', 6, 'Informática'),
(11, 'Inglés Técnico', '2º DAM', 3, 'Inglés'),
(12, 'Empresa e Iniciativa Emprendedora', '2º DAM', 3, 'Empresariales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

CREATE TABLE `profesores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol` enum('admin','profesor') DEFAULT 'profesor',
  `telefono` varchar(20) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `horas_contrato` int(11) DEFAULT NULL,
  `estado` enum('activo','baja','excedencia','inactivo') DEFAULT 'activo',
  `fecha_alta` date DEFAULT NULL,
  `fecha_baja` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`id`, `nombre`, `apellidos`, `email`, `password`, `rol`, `telefono`, `departamento`, `horas_contrato`, `estado`, `fecha_alta`, `fecha_baja`, `observaciones`) VALUES
(1, 'Juan', 'García Pérez', 'juan.garcia@centro.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '666111222', 'Informática', 25, 'activo', '2023-09-01', NULL, 'Jefe de departamento'),
(2, 'María', 'López Sánchez', 'maria.lopez@centro.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '666222333', 'Informática', 20, 'activo', '2024-01-15', NULL, NULL),
(3, 'Pedro', 'Martínez Ruiz', 'pedro.martinez@centro.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '666333444', 'Matemáticas', 18, 'activo', '2022-09-01', NULL, NULL),
(4, 'Ana', 'Fernández Torres', 'ana.fernandez@centro.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '666444555', 'Lengua y Literatura', 22, 'activo', '2023-09-01', NULL, NULL),
(5, 'Carlos', 'Rodríguez Gómez', 'carlos.rodriguez@centro.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '666555666', 'Inglés', 20, 'activo', '2024-09-01', NULL, NULL),
(6, 'Laura', 'Jiménez Moreno', 'laura.jimenez@centro.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '666666777', 'Informática', 18, 'activo', '2023-02-01', NULL, NULL),
(7, 'Miguel', 'Sánchez Díaz', 'miguel.sanchez@centro.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '666777888', 'FOL', 15, 'activo', '2022-09-01', NULL, NULL),
(8, 'Carmen', 'Ruiz Álvarez', 'carmen.ruiz@centro.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '666888999', 'Matemáticas', 20, 'baja', '2021-09-01', NULL, 'Baja por maternidad'),
(9, 'José', 'Moreno Ortiz', 'jose.moreno@centro.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '666999000', 'Informática', 25, 'activo', '2020-09-01', NULL, 'Tutor 1º DAM'),
(10, 'Isabel', 'Navarro Castro', 'isabel.navarro@centro.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor', '666000111', 'Empresariales', 18, 'activo', '2023-09-01', NULL, NULL),
(11, 'Admin', 'Sistema', 'admin@guardianes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 'Administración', NULL, 'activo', '2025-11-26', NULL, NULL),
(12, 'Admin', 'Sistema', 'admin@guardianes.com', 'admin123', 'admin', NULL, 'Administración', NULL, 'activo', '2025-11-26', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor_modulo`
--

CREATE TABLE `profesor_modulo` (
  `id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `preferencia` enum('alta','media','baja') DEFAULT 'media'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesor_modulo`
--

INSERT INTO `profesor_modulo` (`id`, `profesor_id`, `modulo_id`, `preferencia`) VALUES
(1, 1, 1, 'alta'),
(2, 1, 4, 'media'),
(3, 1, 7, 'alta'),
(4, 2, 2, 'alta'),
(5, 2, 5, 'alta'),
(6, 2, 9, 'media'),
(7, 3, 2, 'baja'),
(8, 3, 3, 'media'),
(9, 5, 11, 'alta'),
(10, 6, 3, 'alta'),
(11, 6, 8, 'alta'),
(12, 6, 1, 'media'),
(13, 7, 6, 'alta'),
(14, 7, 12, 'alta'),
(15, 9, 1, 'alta'),
(16, 9, 2, 'alta'),
(17, 9, 7, 'media'),
(18, 9, 10, 'alta'),
(19, 10, 12, 'alta');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion_guardias`
--
ALTER TABLE `asignacion_guardias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guardia_id` (`guardia_id`),
  ADD KEY `profesor_id` (`profesor_id`);

--
-- Indices de la tabla `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ausencias`
--
ALTER TABLE `ausencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profesor_id` (`profesor_id`);

--
-- Indices de la tabla `centro`
--
ALTER TABLE `centro`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contador_guardias`
--
ALTER TABLE `contador_guardias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profesor_id` (`profesor_id`);

--
-- Indices de la tabla `guardias`
--
ALTER TABLE `guardias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `horario_id` (`horario_id`);

--
-- Indices de la tabla `horario`
--
ALTER TABLE `horario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profesor_id` (`profesor_id`),
  ADD KEY `aula_id` (`aula_id`),
  ADD KEY `modulo_id` (`modulo_id`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `profesor_modulo`
--
ALTER TABLE `profesor_modulo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profesor_id` (`profesor_id`),
  ADD KEY `modulo_id` (`modulo_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion_guardias`
--
ALTER TABLE `asignacion_guardias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `ausencias`
--
ALTER TABLE `ausencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `centro`
--
ALTER TABLE `centro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `contador_guardias`
--
ALTER TABLE `contador_guardias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `guardias`
--
ALTER TABLE `guardias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horario`
--
ALTER TABLE `horario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `profesores`
--
ALTER TABLE `profesores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `profesor_modulo`
--
ALTER TABLE `profesor_modulo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_guardias`
--
ALTER TABLE `asignacion_guardias`
  ADD CONSTRAINT `asignacion_guardias_ibfk_1` FOREIGN KEY (`guardia_id`) REFERENCES `guardias` (`id`),
  ADD CONSTRAINT `asignacion_guardias_ibfk_2` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`);

--
-- Filtros para la tabla `ausencias`
--
ALTER TABLE `ausencias`
  ADD CONSTRAINT `ausencias_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`);

--
-- Filtros para la tabla `contador_guardias`
--
ALTER TABLE `contador_guardias`
  ADD CONSTRAINT `contador_guardias_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`);

--
-- Filtros para la tabla `guardias`
--
ALTER TABLE `guardias`
  ADD CONSTRAINT `guardias_ibfk_1` FOREIGN KEY (`horario_id`) REFERENCES `horario` (`id`);

--
-- Filtros para la tabla `horario`
--
ALTER TABLE `horario`
  ADD CONSTRAINT `horario_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`),
  ADD CONSTRAINT `horario_ibfk_2` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`),
  ADD CONSTRAINT `horario_ibfk_3` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`);

--
-- Filtros para la tabla `profesor_modulo`
--
ALTER TABLE `profesor_modulo`
  ADD CONSTRAINT `profesor_modulo_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`),
  ADD CONSTRAINT `profesor_modulo_ibfk_2` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
