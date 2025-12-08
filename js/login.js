/**
 * Script de login para conectar con api_rest.php
 */

// Establecer el año actual en el footer
document.getElementById('year').textContent = new Date().getFullYear();

// Manejar el formulario de login
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorMessage = document.getElementById('error-message');

    // Ocultar mensaje de error previo
    errorMessage.style.display = 'none';

    try {
        const response = await fetch('api_rest.php?action=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (data.success) {
            // Login exitoso, redirigir al dashboard
            window.location.href = 'dashboard.html';
        } else {
            // Mostrar error
            errorMessage.textContent = data.message || 'Error al iniciar sesión';
            errorMessage.style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        errorMessage.textContent = 'Error de conexión con el servidor';
        errorMessage.style.display = 'block';
    }
});
