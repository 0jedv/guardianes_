/**
 * Cliente JavaScript para consumir la API REST
 * Maneja todas las peticiones AJAX al backend
 */

const API_URL = 'api_rest.php';
let profesoresList = [];
let ausenciasList = [];
let isEditing = false;
let currentEditId = null;

// ==================== UTILIDADES ====================

/**
 * Realiza una petición GET a la API
 */
/**
 * Realiza una petición GET a la API
 */
async function apiGet(action) {
    try {
        console.log(`📡 apiGet: Solicitando acción '${action}'...`);
        const response = await fetch(`${API_URL}?action=${action}`);

        // Verificar si la respuesta es válida (status 200-299)
        if (!response.ok) {
            const textHTML = await response.text();
            console.error(`❌ Error HTTP ${response.status}:`, textHTML);
            throw new Error(`Error del servidor: ${response.status}`);
        }

        const data = await response.json();
        console.log(`✅ apiGet ('${action}'): Respuesta recibida`, data);

        if (!data.success) {
            console.warn(`⚠️ apiGet ('${action}'): La API devolvió success=false`, data.message);
            throw new Error(data.message || 'Error en la petición');
        }

        return data;
    } catch (error) {
        console.error(`❌ apiGet ('${action}') Falló:`, error);
        showMessage(error.message, 'error');
        throw error;
    }
}

/**
 * Realiza una petición POST a la API
 */
async function apiPost(action, body) {
    try {
        console.log(`📡 apiPost: Enviando acción '${action}'...`, body);
        const response = await fetch(`${API_URL}?action=${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });

        // Verificar si la respuesta es válida
        if (!response.ok) {
            const textHTML = await response.text();
            console.error(`❌ Error HTTP ${response.status}:`, textHTML);
            throw new Error(`Error del servidor: ${response.status}`);
        }

        const data = await response.json();
        console.log(`✅ apiPost ('${action}'): Respuesta recibida`, data);

        if (!data.success) {
            console.warn(`⚠️ apiPost ('${action}'): La API devolvió success=false`, data.message);
            throw new Error(data.message || 'Error en la petición');
        }

        return data;
    } catch (error) {
        console.error(`❌ apiPost ('${action}') Falló:`, error);
        showMessage(error.message, 'error');
        throw error;
    }
}

/**
 * Realiza una petición PUT a la API
 */
async function apiPut(action, body) {
    try {
        console.log(`📡 apiPut: Enviando acción '${action}'...`, body);
        const response = await fetch(`${API_URL}?action=${action}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            const textHTML = await response.text();
            console.error(`❌ Error HTTP ${response.status}:`, textHTML);
            throw new Error(`Error del servidor: ${response.status}`);
        }

        const data = await response.json();
        console.log(`✅ apiPut ('${action}'): Respuesta recibida`, data);

        if (!data.success) {
            console.warn(`⚠️ apiPut ('${action}'): La API devolvió success=false`, data.message);
            throw new Error(data.message || 'Error en la petición');
        }

        return data;
    } catch (error) {
        console.error(`❌ apiPut ('${action}') Falló:`, error);
        showMessage(error.message, 'error');
        throw error;
    }
}

/**
 * Realiza una petición DELETE a la API
 */
async function apiDelete(action, body) {
    try {
        console.log(`📡 apiDelete: Enviando acción '${action}'...`, body);
        const response = await fetch(`${API_URL}?action=${action}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            const textHTML = await response.text();
            console.error(`❌ Error HTTP ${response.status}:`, textHTML);
            throw new Error(`Error del servidor: ${response.status}`);
        }

        const data = await response.json();
        console.log(`✅ apiDelete ('${action}'): Respuesta recibida`, data);

        if (!data.success) {
            console.warn(`⚠️ apiDelete ('${action}'): La API devolvió success=false`, data.message);
            throw new Error(data.message || 'Error en la petición');
        }

        return data;
    } catch (error) {
        console.error(`❌ apiDelete ('${action}') Falló:`, error);
        showMessage(error.message, 'error');
        throw error;
    }
}

/**
 * Muestra un mensaje al usuario
 */
function showMessage(message, type = 'success') {
    // Buscar contenedor de mensajes o crearlo
    let messageContainer = document.getElementById('message-container');

    if (!messageContainer) {
        messageContainer = document.createElement('div');
        messageContainer.id = 'message-container';
        messageContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(messageContainer);
    }

    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type}`;
    messageDiv.style.cssText = `
        padding: 15px 20px;
        margin-bottom: 10px;
        border-radius: 4px;
        background-color: ${type === 'success' ? '#d4edda' : '#f8d7da'};
        color: ${type === 'success' ? '#155724' : '#721c24'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease-out;
    `;
    messageDiv.textContent = message;

    messageContainer.appendChild(messageDiv);

    // Auto-eliminar después de 5 segundos
    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => messageDiv.remove(), 300);
    }, 5000);
}

// ==================== PROFESORES ====================

/**
 * Obtiene la lista de profesores
 */
async function fetchProfesores() {
    try {
        const response = await apiGet('getProfesores');
        profesoresList = response.data || [];
        return profesoresList;
    } catch (error) {
        return [];
    }
}

/**
 * Crea un nuevo profesor
 */
async function createProfesor(formData) {
    try {
        const response = await apiPost('createProfesor', formData);
        showMessage(response.message, 'success');
        return response.data;
    } catch (error) {
        return null;
    }
}

/**
 * Renderiza la tabla de profesores
 */
async function renderProfesoresTable() {
    const tbody = document.getElementById('profesores-tbody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Cargando...</td></tr>';

    const profesores = await fetchProfesores();
    const session = await getSession();
    const isAdmin = session && session.is_admin;

    // Update table header if needed (optional, or just add column in HTML? prefer JS validation)
    // Actually, we should probably update the THEAD in HTML or here. 
    // Let's assume the user is okay with us modifying the HTML structure dynamically or we just add a column 
    // but the header might default to 4 columns.
    // Let's look at `view_file` output for `profesores.html` (Step 23).
    // It has 4 THs: Nombre, Email, Rol, Estado.
    // If admin, we need 5th column.

    const thead = document.querySelector('table thead tr');
    if (isAdmin && thead && thead.children.length === 4) {
        const th = document.createElement('th');
        th.textContent = 'Acciones';
        thead.appendChild(th);
    }

    if (profesores.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${isAdmin ? 5 : 4}" style="text-align:center;">No hay profesores registrados</td></tr>`;
        return;
    }

    tbody.innerHTML = profesores.map(p => `
        <tr>
            <td>${p.apellidos}, ${p.nombre}</td>
            <td>${p.email}</td>
            <td><span style="padding: 4px 8px; border-radius: 4px; background: ${p.rol === 'admin' ? '#fef3c7' : '#e0f2fe'};">${p.rol}</span></td>
            <td><span style="padding: 4px 8px; border-radius: 4px; background: #d4edda;">${p.estado}</span></td>
            ${isAdmin ? `
            <td>
                <button onclick="editProfesor(${p.id})" class="btn" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background: #f39c12;">Editar</button>
                <button onclick="deleteProfesor(${p.id})" class="btn" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background: #e74c3c;">Eliminar</button>
            </td>
            ` : ''}
        </tr>
    `).join('');
}

async function deleteProfesor(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este profesor?')) return;

    try {
        await apiDelete('deleteProfesor', { id });
        renderProfesoresTable();
    } catch (error) {
        console.error(error);
    }
}

function editProfesor(id) {
    const profesor = profesoresList.find(p => p.id == id);
    if (!profesor) return;

    // Populate form
    document.getElementById('nombre').value = profesor.nombre;
    document.getElementById('apellidos').value = profesor.apellidos;
    document.getElementById('email').value = profesor.email;
    document.getElementById('rol').value = profesor.rol;
    document.getElementById('password').value = ''; // Optional specific logic validation?
    document.getElementById('password').required = false; // Not required on edit

    // Change UI state
    isEditing = true;
    currentEditId = id;

    const btn = document.querySelector('#profesor-form button[type="submit"]');
    if (btn) btn.textContent = 'Actualizar Profesor';

    // Scroll to form
    document.getElementById('profesor-form').scrollIntoView({ behavior: 'smooth' });
}

async function updateProfesor(formData) {
    try {
        const response = await apiPut('updateProfesor', formData);
        showMessage(response.message, 'success');
        return response.data || true;
    } catch (error) {
        return null;
    }
}

/**
 * Maneja el envío del formulario de profesores
 */
function handleProfesorFormSubmit(event) {
    event.preventDefault();

    const formData = {
        nombre: document.getElementById('nombre').value,
        apellidos: document.getElementById('apellidos').value,
        email: document.getElementById('email').value,
        rol: document.getElementById('rol').value
    };

    const password = document.getElementById('password').value;
    if (password) {
        formData.password = password;
    }

    if (isEditing) {
        formData.id = currentEditId;
        updateProfesor(formData).then(result => {
            if (result) {
                resetProfesorForm(event.target);
                renderProfesoresTable();
            }
        });
    } else {
        // Create requires password
        if (!password) {
            showMessage('La contraseña es obligatoria para nuevos usuarios', 'error');
            return;
        }
        formData.password = password;
        createProfesor(formData).then(result => {
            if (result) {
                resetProfesorForm(event.target);
                renderProfesoresTable();
            }
        });
    }
}

function resetProfesorForm(form) {
    form.reset();
    isEditing = false;
    currentEditId = null;
    document.getElementById('password').required = true;
    const btn = form.querySelector('button[type="submit"]');
    if (btn) btn.textContent = 'Crear Profesor';
}

// ==================== AUSENCIAS ====================

/**
 * Obtiene la lista de ausencias
 */
async function fetchAusencias() {
    try {
        const response = await apiGet('getAusencias');
        ausenciasList = response.data || [];
        return ausenciasList;
    } catch (error) {
        return [];
    }
}

/**
 * Crea una nueva ausencia
 */
async function createAusencia(formData) {
    try {
        const response = await apiPost('createAusencia', formData);
        showMessage(response.message, 'success');
        return response.data;
    } catch (error) {
        return null;
    }
}

/**
 * Renderiza la tabla de ausencias
 */
async function renderAusenciasTable() {
    const tbody = document.getElementById('ausencias-tbody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Cargando...</td></tr>';

    const ausencias = await fetchAusencias();
    const session = await getSession();
    const isAdmin = session && session.is_admin;

    // Update header for actions
    const thead = document.querySelector('table thead tr');
    if (isAdmin && thead && thead.children.length === 6) {
        const th = document.createElement('th');
        th.textContent = 'Acciones';
        thead.appendChild(th);
    }

    if (ausencias.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${isAdmin ? 7 : 6}" style="text-align:center;">No hay ausencias registradas</td></tr>`;
        return;
    }

    tbody.innerHTML = ausencias.map(a => `
        <tr>
            <td>${a.apellidos}, ${a.nombre}</td>
            <td>${formatDate(a.fecha_inicio)}</td>
            <td>${formatDate(a.fecha_fin)}</td>
            <td>${capitalize(a.tipo)}</td>
            <td>${a.motivo || '-'}</td>
            <td><span style="padding: 4px 8px; border-radius: 4px; background: ${a.justificada === 'si' ? '#d4edda' : '#fff3cd'};">${a.justificada === 'si' ? 'Justificada' : 'Pendiente'}</span></td>
            ${isAdmin ? `
            <td>
                <button onclick="editAusencia(${a.id})" class="btn" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background: #f39c12;">Editar</button>
                <button onclick="deleteAusencia(${a.id})" class="btn" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background: #e74c3c;">Eliminar</button>
            </td>
            ` : ''}
        </tr>
    `).join('');
}

async function deleteAusencia(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta ausencia?')) return;

    try {
        await apiDelete('deleteAusencia', { id });
        renderAusenciasTable();
    } catch (error) {
        console.error(error);
    }
}

function editAusencia(id) {
    const ausencia = ausenciasList.find(a => a.id == id);
    if (!ausencia) return;

    // Populate form
    // Note: dates in input type=date need YYYY-MM-DD
    document.getElementById('profesor_id').value = ausencia.profesor_id;
    document.getElementById('fecha_inicio').value = ausencia.fecha_inicio; // assuming format is already YYYY-MM-DD from API? usually yes
    document.getElementById('fecha_fin').value = ausencia.fecha_fin;
    document.getElementById('tipo').value = ausencia.tipo;
    document.getElementById('motivo').value = ausencia.motivo;

    // Change UI state
    isEditing = true;
    currentEditId = id;

    const btn = document.querySelector('#ausencia-form button[type="submit"]');
    if (btn) btn.textContent = 'Actualizar Ausencia';

    // Scroll to form
    document.getElementById('ausencia-form').scrollIntoView({ behavior: 'smooth' });
}

async function updateAusencia(formData) {
    try {
        const response = await apiPut('updateAusencia', formData);
        showMessage(response.message, 'success');
        return response.data || true;
    } catch (error) {
        return null;
    }
}

/**
 * Carga profesores en el select de ausencias
 */
async function loadProfesoresSelect() {
    const select = document.getElementById('profesor_id');
    if (!select) return;

    const profesores = await fetchProfesores();

    select.innerHTML = '<option value="">Seleccione un profesor</option>' +
        profesores.map(p => `<option value="${p.id}">${p.apellidos}, ${p.nombre}</option>`).join('');
}

/**
 * Maneja el envío del formulario de ausencias
 */
function handleAusenciaFormSubmit(event) {
    event.preventDefault();

    const formData = {
        profesor_id: document.getElementById('profesor_id').value,
        fecha_inicio: document.getElementById('fecha_inicio').value,
        fecha_fin: document.getElementById('fecha_fin').value,
        tipo: document.getElementById('tipo').value,
        motivo: document.getElementById('motivo').value
    };

    if (isEditing) {
        formData.id = currentEditId;
        updateAusencia(formData).then(result => {
            if (result) {
                resetAusenciaForm(event.target);
                renderAusenciasTable();
            }
        });
    } else {
        createAusencia(formData).then(result => {
            if (result) {
                resetAusenciaForm(event.target);
                renderAusenciasTable();
            }
        });
    }
}

function resetAusenciaForm(form) {
    form.reset();
    isEditing = false;
    currentEditId = null;
    const btn = form.querySelector('button[type="submit"]');
    if (btn) btn.textContent = 'Registrar Ausencia';
}

// ==================== GUARDIAS ====================

/**
 * Obtiene la lista de guardias
 */
async function fetchGuardias() {
    try {
        const response = await apiGet('getGuardias');
        return response.data;
    } catch (error) {
        return { guardias: [], profesores_disponibles: [] };
    }
}

/**
 * Actualiza una guardia (asignar/desasignar)
 */
async function updateGuardia(guardiaId, profesorId) {
    try {
        const estado = profesorId ? 'asignada' : 'pendiente';
        const response = await apiPut('updateGuardia', {
            id: guardiaId,
            profesor_sustituto_id: profesorId,
            estado: estado
        });
        showMessage(response.message, 'success');
        return true;
    } catch (error) {
        return false;
    }
}

async function deleteGuardia(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta guardia?')) return;

    try {
        await apiDelete('deleteGuardia', { id });
        renderGuardiasTable();
    } catch (error) {
        console.error(error);
    }
}

/**
 * Renderiza la tabla de guardias
 */
async function renderGuardiasTable() {
    const tbody = document.getElementById('guardias-tbody');
    const emptyState = document.getElementById('empty-state');
    const table = tbody?.closest('table');

    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Cargando...</td></tr>';

    const data = await fetchGuardias();
    const { guardias, profesores_disponibles } = data;

    // Obtener sesión para verificar permisos
    const session = window.currentSession || await getSession();
    const isAdmin = session && session.is_admin;

    if (guardias.length === 0) {
        // Mostrar estado vacío
        if (table) table.style.display = 'none';
        if (emptyState) emptyState.style.display = 'block';
        return;
    }

    // Ocultar estado vacío y mostrar tabla
    if (table) table.style.display = 'table';
    if (emptyState) emptyState.style.display = 'none';

    // Header update for actions
    const thead = document.querySelector('table#guardias-table thead tr') || document.querySelector('table thead tr'); // Guardias table might not have id, try generic
    // Only add if not exists and correct table. 
    // Ideally we should use specific ID for table in HTML but let's try to detect column count

    // Better strategy: Since renderGuardiasTable is called multiple times, we need to be careful not to add multiple THs.
    // Let's assume the HTML structure is fixed and we just inject content. 
    // But if we add a column in TBODY, we MUST add it in THEAD.
    // Let's try to find the row.
    if (isAdmin && thead && thead.children.length === 6) {
        const th = document.createElement('th');
        th.textContent = 'Acciones';
        thead.appendChild(th);
    }

    tbody.innerHTML = guardias.map(g => `
        <tr>
            <td>${formatDate(g.fecha)}</td>
            <td>${capitalize(g.dia_semana)}</td>
            <td>${g.hora_inicio} - ${g.hora_fin}</td>
            <td>${g.modulo_nombre || g.grupo || '-'}</td>
            <td>${g.profesor_ausente_apellidos}, ${g.profesor_ausente_nombre}</td>
            <td>
                ${isAdmin ? `
                     <select onchange="handleGuardiaAssignment(${g.guardia_id}, this.value)" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; width: 100%;">
                        <option value="">-- Sin Asignar --</option>
                        ${profesores_disponibles.map(p => `
                            <option value="${p.id}" ${g.profesor_sustituto_id == p.id ? 'selected' : ''}>
                                ${p.apellidos}, ${p.nombre}
                            </option>
                        `).join('')}
                    </select>
                ` : (g.estado === 'pendiente' ? `
                    <span style="padding: 4px 8px; border-radius: 4px; background: #fff3cd; color: #856404;">
                        Pendiente
                    </span>
                ` : `
                    <span style="padding: 4px 8px; border-radius: 4px; background: #d4edda; color: #155724;">
                        ${g.sustituto_apellidos}, ${g.sustituto_nombre}
                    </span>
                `)}
            </td>
            ${isAdmin ? `
            <td>
                <button onclick="deleteGuardia(${g.guardia_id})" class="btn" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background: #e74c3c;">Eliminar</button>
            </td>
            ` : ''}
        </tr>
    `).join('');
}

/**
 * Maneja la asignación de guardia
 */
async function handleGuardiaAssignment(guardiaId, profesorId) {
    // If profesorId is empty string, it sends "" which updateGuardia handles as unassign
    const success = await updateGuardia(guardiaId, profesorId);
    if (success) {
        renderGuardiasTable();
    }
}

// ==================== UTILIDADES DE FORMATO ====================

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES');
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// ==================== SESIÓN ====================

/**
 * Obtiene información de la sesión actual
 */
async function getSession() {
    try {
        const response = await apiGet('getSession');
        return response.data;
    } catch (error) {
        // Si no hay sesión, redirigir al login
        window.location.href = 'index.php';
        return null;
    }
}

// Agregar estilos de animación
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
