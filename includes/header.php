<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Guardias</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="horario_profesor.php">Mi Horario</a></li>
                    <li><a href="profesores.html">Profesores</a></li>
                    <li><a href="guardias.html">Guardias</a></li>
                    <li><a href="ausencias.html">Ausencias</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                <?php endif; ?>

            </ul>
        </nav>
    </header>
    <main>
