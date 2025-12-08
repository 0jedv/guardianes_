/**
 * Script de horario de profesor para obtener datos desde api_rest.php
 */

// Establecer el año actual en el footer
document.getElementById('year').textContent = new Date().getFullYear();

// Obtener profesor_id de la URL si existe
const urlParams = new URLSearchParams(window.location.search);
const profesorId = urlParams.get('profesor_id');

// Cargar horario del profesor
async function loadHorario() {
    try {
        let url = 'api_rest.php?action=getHorarioProfesor';
        if (profesorId) {
            url += `&profesor_id=${profesorId}`;
        }

        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
            const { profesor, horarios, all_teachers, is_admin } = data.data;

            // Actualizar información del profesor
            document.getElementById('profesor-nombre').textContent = `${profesor.nombre} ${profesor.apellidos}`;
            document.getElementById('profesor-departamento').textContent = profesor.departamento || 'N/A';

            // Si es admin, mostrar selector de profesores
            if (is_admin && all_teachers.length > 0) {
                const teacherSelector = document.getElementById('teacher-selector');
                const select = document.getElementById('profesor_id');

                teacherSelector.style.display = 'block';

                // Llenar el select con los profesores
                all_teachers.forEach(teacher => {
                    const option = document.createElement('option');
                    option.value = teacher.id;
                    option.textContent = `${teacher.apellidos}, ${teacher.nombre}`;
                    if (profesorId && teacher.id == profesorId) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });

                // Manejar cambio de profesor
                select.addEventListener('change', (e) => {
                    window.location.href = `horario_profesor.html?profesor_id=${e.target.value}`;
                });
            }

            // Mostrar horario
            if (horarios.length === 0) {
                document.getElementById('no-schedule-message').style.display = 'block';
                document.getElementById('schedule-container').style.display = 'none';
            } else {
                renderSchedule(horarios);
            }
        } else {
            // Si no hay sesión, redirigir al login
            if (response.status === 401) {
                window.location.href = 'index.html';
            } else {
                console.error('Error:', data.message);
                alert('Error al cargar el horario: ' + data.message);
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión con el servidor');
    }
}

/**
 * Renderizar el horario en la tabla
 */
function renderSchedule(horarios) {
    const scheduleBody = document.getElementById('schedule-body');
    scheduleBody.innerHTML = '';

    // Organizar horarios por día y hora
    const schedule = {};
    const timeSlots = new Set();
    const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

    horarios.forEach(horario => {
        const dia = horario.dia_semana;
        const hora = horario.hora_inicio.substring(0, 5);

        if (!schedule[dia]) {
            schedule[dia] = {};
        }
        schedule[dia][hora] = horario;
        timeSlots.add(hora);
    });

    // Convertir Set a Array y ordenar
    const sortedTimeSlots = Array.from(timeSlots).sort();

    // Crear filas de la tabla
    sortedTimeSlots.forEach(hora => {
        const row = document.createElement('tr');

        // Columna de hora
        const timeCell = document.createElement('td');
        timeCell.className = 'time-slot';
        timeCell.innerHTML = `<strong>${hora}</strong>`;
        row.appendChild(timeCell);

        // Columnas de días
        dias.forEach(dia => {
            const cell = document.createElement('td');
            cell.className = 'schedule-cell';

            if (schedule[dia] && schedule[dia][hora]) {
                const clase = schedule[dia][hora];
                cell.innerHTML = `
                    <div class="class-block">
                        <div class="class-module">${clase.modulo || clase.asignatura || 'N/A'}</div>
                        <div class="class-details">
                            <span class="class-room">📍 ${clase.aula || 'N/A'}</span>
                            <span class="class-group">👥 ${clase.grupo || 'N/A'}</span>
                        </div>
                        <div class="class-time">
                            ${clase.hora_inicio.substring(0, 5)} - ${clase.hora_fin.substring(0, 5)}
                        </div>
                    </div>
                `;
            }

            row.appendChild(cell);
        });

        scheduleBody.appendChild(row);
    });
}

// Cargar horario al cargar la página
loadHorario();
