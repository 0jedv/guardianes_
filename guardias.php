<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'conexion.php';
include 'includes/header.php';

// Fetch Guardias with absent teacher info
$sql = "SELECT g.*, h.dia_semana, h.hora_inicio, h.hora_fin, 
        a.nombre as aula, m.nombre as modulo,
        p.nombre as profesor_nombre, p.apellidos as profesor_apellidos,
        ag.profesor_id as asignado_a,
        p2.nombre as asignado_nombre, p2.apellidos as asignado_apellidos
        FROM guardias g
        JOIN horario h ON g.horario_id = h.id
        LEFT JOIN aulas a ON h.aula_id = a.id
        LEFT JOIN modulos m ON h.modulo_id = m.id
        LEFT JOIN profesores p ON h.profesor_id = p.id
        LEFT JOIN asignacion_guardias ag ON g.id = ag.guardia_id
        LEFT JOIN profesores p2 ON ag.profesor_id = p2.id
        ORDER BY g.fecha DESC, h.hora_inicio";
$stmt = $pdo->query($sql);
$guardias = $stmt->fetchAll();
?>

<h1>Gestión de Guardias</h1>

<div style="margin-bottom: 20px;">
    <p>Total de guardias: <strong><?php echo count($guardias); ?></strong></p>
</div>

<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Día</th>
            <th>Horario</th>
            <th>Aula</th>
            <th>Módulo</th>
            <th>Profesor Ausente</th>
            <th>Estado</th>
            <th>Asignado a</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($guardias)): ?>
        <tr>
            <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                No hay guardias registradas. Las guardias se generan automáticamente al registrar ausencias.
            </td>
        </tr>
        <?php else: ?>
        <?php foreach ($guardias as $guardia): ?>
        <tr>
            <td><?php echo date('d/m/Y', strtotime($guardia['fecha'])); ?></td>
            <td><?php echo ucfirst($guardia['dia_semana']); ?></td>
            <td><?php echo substr($guardia['hora_inicio'], 0, 5) . ' - ' . substr($guardia['hora_fin'], 0, 5); ?></td>
            <td><?php echo htmlspecialchars($guardia['aula']); ?></td>
            <td><?php echo htmlspecialchars($guardia['modulo']); ?></td>
            <td>
                <strong><?php echo htmlspecialchars($guardia['profesor_apellidos'] . ', ' . $guardia['profesor_nombre']); ?></strong>
            </td>
            <td>
                <?php
                $estado_colors = [
                    'pendiente' => '#fff3cd',
                    'asignada' => '#cce5ff',
                    'cubierta' => '#d4edda',
                    'cancelada' => '#f8d7da'
                ];
                $color = $estado_colors[$guardia['estado']] ?? '#e2e3e5';
                ?>
                <span style="padding: 4px 8px; border-radius: 4px; background: <?php echo $color; ?>;">
                    <?php echo ucfirst($guardia['estado']); ?>
                </span>
            </td>
            <td>
                <?php if ($guardia['asignado_a']): ?>
                    <?php echo htmlspecialchars($guardia['asignado_apellidos'] . ', ' . $guardia['asignado_nombre']); ?>
                <?php else: ?>
                    <span style="color: #999;">Sin asignar</span>
                <?php endif; ?>
            </td>
            <td>
                <?php 
                // Only admins can generate and assign guardias
                $is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                if ($is_admin):
                ?>
                    <?php if ($guardia['estado'] == 'pendiente'): ?>
                        <button onclick="generarGuardias(<?php echo $guardia['id']; ?>)" class="btn" style="padding: 6px 12px; font-size: 0.85rem;">
                            Generar Guardias
                        </button>
                    <?php elseif ($guardia['estado'] == 'asignada'): ?>
                        <button onclick="confirmarGuardia(<?php echo $guardia['id']; ?>)" class="btn btn-success" style="padding: 6px 12px; font-size: 0.85rem;">
                            Confirmar
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color: #999; font-size: 0.85rem;">Solo admin</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Modal for substitute teacher suggestions -->
<div id="substituteModal" class="modal">
    <div class="modal-content" style="max-width: 1000px;">
        <div class="modal-header">
            <h2 class="modal-title">Seleccionar Profesor Sustituto</h2>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        
        <div id="guardiaInfo" class="guardia-info">
            <!-- Guardia details will be inserted here -->
        </div>
        
        <div id="substituteList">
            <div class="loading">Cargando profesores disponibles</div>
        </div>
    </div>
</div>

<script>
let currentGuardiaId = null;

function generarGuardias(guardiaId) {
    currentGuardiaId = guardiaId;
    
    // Show modal
    document.getElementById('substituteModal').classList.add('active');
    
    // Reset content
    document.getElementById('substituteList').innerHTML = '<div class="loading">Cargando profesores disponibles</div>';
    
    // Fetch suggestions
    fetch(`api/generar_guardias.php?guardia_id=${guardiaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('substituteList').innerHTML = 
                    `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }
            
            // Display guardia info
            const guardiaInfo = data.guardia;
            document.getElementById('guardiaInfo').innerHTML = `
                <h3>Clase a Cubrir</h3>
                <p><strong>Fecha:</strong> ${guardiaInfo.fecha} (${guardiaInfo.dia})</p>
                <p><strong>Horario:</strong> ${guardiaInfo.horario}</p>
                <p><strong>Aula:</strong> ${guardiaInfo.aula}</p>
                <p><strong>Módulo:</strong> ${guardiaInfo.modulo}</p>
                <p><strong>Profesor Ausente:</strong> ${guardiaInfo.profesor_ausente}</p>
            `;
            
            // Display suggestions
            if (data.suggestions.length === 0) {
                document.getElementById('substituteList').innerHTML = 
                    '<p style="text-align: center; color: #999; padding: 20px;">No hay profesores disponibles</p>';
                return;
            }
            
            let html = '<div class="substitute-suggestions">';
            data.suggestions.forEach(teacher => {
                const qualifiedClass = teacher.qualified !== 'none' ? 'qualified' : '';
                const notAvailableClass = !teacher.available ? 'not-available' : '';
                const availableBadge = teacher.available ? 
                    '<span class="substitute-badge badge-available">Disponible</span>' :
                    '<span class="substitute-badge badge-busy">Ocupado</span>';
                
                let qualificationBadge = '';
                if (teacher.qualified !== 'none') {
                    const qualText = teacher.qualified === 'alta' ? 'Alta cualificación' : 
                                    (teacher.qualified === 'media' ? 'Media cualificación' : 'Baja cualificación');
                    qualificationBadge = `<span class="substitute-badge badge-qualified">${qualText}</span>`;
                }
                
                html += `
                    <div class="substitute-card ${qualifiedClass} ${notAvailableClass}" 
                         onclick="${teacher.available ? `asignarGuardia(${teacher.id}, '${teacher.apellidos}, ${teacher.nombre}')` : ''}">
                        <div class="substitute-header">
                            <div class="substitute-name">${teacher.apellidos}, ${teacher.nombre}</div>
                            ${availableBadge}
                        </div>
                        <div class="substitute-info">
                            <strong>Departamento:</strong> ${teacher.departamento || 'N/A'}
                        </div>
                        ${qualificationBadge}
                        <div class="substitute-stats">
                            <div class="stat-item">
                                <span class="stat-label">Guardias este mes</span>
                                <span class="stat-value">${teacher.guardia_count}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Prioridad</span>
                                <span class="stat-value">${teacher.priority_score}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            document.getElementById('substituteList').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('substituteList').innerHTML = 
                '<div class="alert alert-danger">Error al cargar los profesores</div>';
        });
}

function asignarGuardia(profesorId, profesorNombre) {
    if (!confirm(`¿Asignar esta guardia a ${profesorNombre}?`)) {
        return;
    }
    
    fetch('api/asignar_guardia.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            guardia_id: currentGuardiaId,
            profesor_id: profesorId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        alert('Guardia asignada correctamente');
        closeModal();
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al asignar la guardia');
    });
}

function confirmarGuardia(guardiaId) {
    if (!confirm('¿Confirmar que esta guardia ha sido cubierta?')) {
        return;
    }
    
    fetch('api/asignar_guardia.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            guardia_id: guardiaId,
            confirmar: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        alert('Guardia confirmada');
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al confirmar la guardia');
    });
}

function closeModal() {
    document.getElementById('substituteModal').classList.remove('active');
    currentGuardiaId = null;
}

// Close modal when clicking outside
document.getElementById('substituteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
