# 🎨 Documentación Técnica del Cliente (Frontend)

Este documento detalla la arquitectura, tecnologías y decisiones de diseño tomadas para la parte visual y funcional del cliente web ("Frontend") del proyecto Guardianes.

## 🛠️ Tecnologías Utilizadas

*   **HTML5 Semántico**: Estructura limpia y accesible (`header`, `main`, `nav`, `footer`).
*   **CSS3 Vanilla**: Sin preprocesadores ni frameworks pesados (como Bootstrap) para mantener el proyecto ligero y educativo.
*   **JavaScript (ES6+)**: Uso de características modernas como `async/await`, `fetch API`, `arrow functions` y `template literals`.

---

## 📂 Organización de Archivos

La lógica del cliente está cuidadosamente separada de la estructura:

1.  **Vistas (`.html`)**: Solo contienen la estructura y contenedores vacíos con IDs (ej: `<tbody id="profesores-tbody">`) donde JavaScript inyectará los datos.
    *   `index.html`: Login.
    *   `dashboard.html`: Vista principal con estadísticas.
    *   `profesores.html`: Gestión de docentes.
    *   `guardias.html`: Panel de asignación de sustituciones.

2.  **Estilos (`assets/css/style.css`)**: Un único archivo de estilos global que mantiene la coherencia visual.

3.  **Lógica (`js/`)**:
    *   `api.js`: **EL NÚCLEO**. Contiene todas las funciones de comunicación con el servidor.
    *   `login.js`, `dashboard.js`, etc.: Scripts específicos que importan o usan funciones de `api.js` para dar vida a cada página html.

---

## 🎨 Estilos y Diseño (`style.css`)

El diseño se basa en **Variables CSS** para facilitar cambios de tema y mantenimiento.

### Variables Globales (`:root`)
```css
:root {
    --primary-color: #4a90e2;  /* Azul corporativo */
    --secondary-color: #f5f6fa; /* Fondo gris suave */
    --text-color: #2c3e50;      /* Gris oscuro para texto */
    --danger-color: #e74c3c;    /* Rojo para errores/borrar */
    --success-color: #2ecc71;   /* Verde para éxito/aprobado */
}
```

### Componentes Clave

1.  **Botones (`.btn`)**: Diseño plano con transiciones suaves en `hover`.
2.  **Tablas**: Estilizadas con bordes sutiles y efecto `hover` en filas para mejorar la legibilidad de grandes conjuntos de datos.
3.  **Alertas (`.alert`)**: Cajas de notificación flotantes (`alert-danger`, `alert-success`) para feedback al usuario.
4.  **Sistema de Grid**: Se usa `flexbox` para el layout general (Header, Main, Footer) y grids sencillos para formularios.

---

## 🧠 Lógica Javascript (`js/api.js`)

Este archivo es la pieza más importante del frontend. Actúa como una capa de servicio (Service Layer).

### 1. Wrapper de Fetch (`apiGet` y `apiPost`)

Para no repetir código de manejo de errores en cada llamada, creamos funciones envolventes.

**Ejemplo de cómo funciona:**
```javascript
// En lugar de escribir fetch() manualmente cada vez...
async function apiGet(action) {
    // 1. Construye la URL automáticamente
    const response = await fetch(`${API_URL}?action=${action}`);
    
    // 2. Maneja errores HTTP (404, 500)
    if (!response.ok) throw new Error(...);
    
    // 3. Maneja errores lógicos de nuestra API (success: false)
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    
    return data;
}
```
*   **Ventaja**: Si mañana cambiamos la URL de la API, solo tocamos una línea aquí.

### 2. Inyección de HTML Dinámico

El frontend no usa librerías de renderizado complejas (como React). Usa **Template Literals** de ES6 para generar HTML seguro y rápido.

```javascript
// Ejemplo de renderizado de tabla (Profesor)
tbody.innerHTML = profesores.map(p => `
    <tr>
        <td>${p.apellidos}, ${p.nombre}</td>
        <td>${p.email}</td>
        <td>
            <!-- Lógica condicional dentro del template -->
            <span class="${p.rol === 'admin' ? 'badge-admin' : 'badge-profe'}">
                ${p.rol}
            </span>
        </td>
    </tr>
`).join('');
```

### 3. Sistema de Notificaciones (`showMessage`)

Una función sencilla que crea dinámicamente elementos DOM para mostrar mensajes "toast" (burbujas) en la esquina superior derecha.

```javascript
function showMessage(message, type) {
    // Crea el div al vuelo, le pone estilos y lo añade al body
    // Se autodestruye a los 5 segundos con setTimeout()
}
```

---

## 🔄 Flujo de Datos Típico

1.  El usuario entra a `profesores.html`.
2.  El script `js/profesores.js` se carga.
3.  Llama a `apiGet('getProfesores')` (definida en `api.js`).
4.  La petición viaja a `api_rest.php`.
5.  Recibe JSON `[ {id:1, nombre:"Juan"...}, ... ]`.
6.  La función `renderProfesoresTable()` convierte ese JSON en filas `<tr>` y las inserta en el DOM.
