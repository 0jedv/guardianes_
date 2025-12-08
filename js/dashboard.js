/**
 * Script de dashboard para obtener estadísticas desde api_rest.php
 */

// Establecer el año actual en el footer
document.getElementById('year').textContent = new Date().getFullYear();

// Verificar sesión y cargar datos
async function loadDashboard() {
    try {
        const response = await fetch('api_rest.php?action=getDashboardStats');
        const data = await response.json();

        if (data.success) {
            // Actualizar información del usuario
            document.getElementById('user-name').textContent = data.data.user_name;
            document.getElementById('user-role').textContent = data.data.user_role;

            // Actualizar estadísticas
            document.getElementById('total-profesores').textContent = data.data.total_profesores;
            document.getElementById('guardias-hoy').textContent = data.data.guardias_hoy;
            document.getElementById('ausencias-hoy').textContent = data.data.ausencias_hoy;
        } else {
            // Si no hay sesión, redirigir al login
            if (response.status === 401) {
                window.location.href = 'index.html';
            } else {
                console.error('Error:', data.message);
                alert('Error al cargar el dashboard: ' + data.message);
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión con el servidor');
    }
}

// Cargar dashboard al cargar la página
loadDashboard();
