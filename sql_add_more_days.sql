-- Script para añadir más días al horario
-- Este script añade horarios para jueves y viernes a los profesores existentes

-- Horarios para Jueves
INSERT INTO `horario` (`profesor_id`, `aula_id`, `modulo_id`, `grupo`, `dia_semana`, `hora_inicio`, `hora_fin`, `curso_escolar`) VALUES
-- Profesor 1 (Juan García) - Jueves
(1, 4, 1, '1º DAM-B', 'jueves', '08:15:00', '09:15:00', '2024-2025'),
(1, 4, 4, '1º DAM-A', 'jueves', '09:15:00', '10:15:00', '2024-2025'),
(1, 4, 4, '1º DAM-A', 'jueves', '10:15:00', '11:15:00', '2024-2025'),
(1, 4, 7, '2º DAM-B', 'jueves', '12:45:00', '13:45:00', '2024-2025'),

-- Profesor 2 (María López) - Jueves
(2, 5, 2, '1º DAM-A', 'jueves', '08:15:00', '09:15:00', '2024-2025'),
(2, 5, 5, '1º DAM-B', 'jueves', '10:15:00', '11:15:00', '2024-2025'),
(2, 5, 9, '2º DAM-A', 'jueves', '11:45:00', '12:45:00', '2024-2025'),

-- Profesor 9 (José Moreno) - Jueves
(9, 4, 2, '1º DAM-B', 'jueves', '11:45:00', '12:45:00', '2024-2025'),
(9, 4, 10, '2º DAM-A', 'jueves', '12:45:00', '13:45:00', '2024-2025'),

-- Profesor 6 (Laura Jiménez) - Jueves
(6, 5, 3, '1º DAM-A', 'jueves', '09:15:00', '10:15:00', '2024-2025'),
(6, 5, 8, '2º DAM-B', 'jueves', '10:15:00', '11:15:00', '2024-2025');

-- Horarios para Viernes
INSERT INTO `horario` (`profesor_id`, `aula_id`, `modulo_id`, `grupo`, `dia_semana`, `hora_inicio`, `hora_fin`, `curso_escolar`) VALUES
-- Profesor 1 (Juan García) - Viernes
(1, 4, 1, '1º DAM-A', 'viernes', '08:15:00', '09:15:00', '2024-2025'),
(1, 4, 7, '2º DAM-A', 'viernes', '09:15:00', '10:15:00', '2024-2025'),
(1, 4, 7, '2º DAM-A', 'viernes', '10:15:00', '11:15:00', '2024-2025'),

-- Profesor 2 (María López) - Viernes
(2, 5, 2, '1º DAM-B', 'viernes', '08:15:00', '09:15:00', '2024-2025'),
(2, 5, 2, '1º DAM-B', 'viernes', '09:15:00', '10:15:00', '2024-2025'),
(2, 5, 9, '2º DAM-B', 'viernes', '11:45:00', '12:45:00', '2024-2025'),
(2, 5, 9, '2º DAM-B', 'viernes', '12:45:00', '13:45:00', '2024-2025'),

-- Profesor 9 (José Moreno) - Viernes
(9, 4, 1, '1º DAM-B', 'viernes', '10:15:00', '11:15:00', '2024-2025'),
(9, 4, 2, '1º DAM-A', 'viernes', '11:45:00', '12:45:00', '2024-2025'),
(9, 4, 10, '2º DAM-A', 'viernes', '12:45:00', '13:45:00', '2024-2025'),

-- Profesor 6 (Laura Jiménez) - Viernes
(6, 5, 3, '1º DAM-B', 'viernes', '08:15:00', '09:15:00', '2024-2025'),
(6, 5, 8, '2º DAM-A', 'viernes', '09:15:00', '10:15:00', '2024-2025'),
(6, 5, 8, '2º DAM-A', 'viernes', '10:15:00', '11:15:00', '2024-2025'),

-- Profesor 7 (Miguel Sánchez - FOL) - Jueves y Viernes
(7, 1, 6, '1º DAM-A', 'jueves', '08:15:00', '09:15:00', '2024-2025'),
(7, 1, 6, '1º DAM-B', 'viernes', '11:45:00', '12:45:00', '2024-2025'),

-- Profesor 5 (Carlos Rodríguez - Inglés) - Jueves y Viernes
(5, 2, 11, '2º DAM-A', 'jueves', '08:15:00', '09:15:00', '2024-2025'),
(5, 2, 11, '2º DAM-B', 'viernes', '08:15:00', '09:15:00', '2024-2025');

-- Verificar los datos insertados
SELECT COUNT(*) as total_horarios FROM horario WHERE dia_semana IN ('jueves', 'viernes');
