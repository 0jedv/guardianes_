# CAMBIOS - Separación Frontend/Backend con API REST

## 📋 Resumen

Este documento describe los cambios realizados para transformar el proyecto **Guardianes** de una arquitectura monolítica PHP tradicional a una arquitectura moderna con **API REST**, separando completamente el frontend (HTML/CSS/JS) del backend (PHP/MySQL).

---

## 🎯 Objetivo

**Antes**: El código PHP y HTML estaban mezclados en los mismos archivos (`.php`), haciendo difícil mantener y escalar la aplicación.

**Después**: Frontend y backend están completamente separados. El frontend consume datos mediante peticiones AJAX a una API REST que responde en formato JSON.

---

## 🏗️ Arquitectura Nueva

```
┌─────────────────┐         JSON (AJAX)        ┌─────────────────┐
│   FRONTEND      │ ◄────────────────────────► │    BACKEND      │
│                 │                             │                 │
│  HTML + CSS     │    fetch() API calls        │  api_rest.php   │
│  JavaScript     │                             │  (REST API)     │
│  (api.js)       │                             │                 │
└─────────────────┘                             └────────┬────────┘
                                                         │
                                                         ▼
                                                  ┌─────────────┐
                                                  │   MySQL     │
                                                  │  Database   │
                                                  └─────────────┘
```

---

## 📁 Archivos Creados

### Backend

#### `api_rest.php` - API REST Principal
Archivo centralizado que maneja todas las operaciones CRUD mediante endpoints REST.

**Endpoints disponibles:**

| Método | Endpoint | Descripción | Requiere Admin |
|--------|----------|-------------|----------------|
| GET | `?action=getProfesores` | Lista todos los profesores activos | No |
| POST | `?action=createProfesor` | Crea un nuevo profesor | ✅ Sí |
| GET | `?action=getAusencias` | Lista ausencias (filtradas por rol) | No |
| POST | `?action=createAusencia` | Registra una nueva ausencia | No* |
| GET | `?action=getGuardias` | Lista guardias pendientes | No |
| POST | `?action=asignarGuardia` | Asigna profesor a una guardia | ✅ Sí |
| GET | `?action=getSession` | Obtiene información de sesión | No |

*Los profesores solo pueden crear ausencias para sí mismos.

---

### Frontend

#### `js/api.js` - Cliente JavaScript (~400 líneas)
Biblioteca JavaScript que consume la API REST. Incluye:

**Funciones principales:**
- `apiGet(action)` - Peticiones GET genéricas
- `apiPost(action, body)` - Peticiones POST genéricas
- `showMessage(message, type)` - Mensajes de feedback al usuario

**Módulo Profesores:**
- `fetchProfesores()` - Obtiene lista de profesores
- `createProfesor(formData)` - Crea nuevo profesor
- `renderProfesoresTable()` - Renderiza tabla dinámica
- `handleProfesorFormSubmit(event)` - Maneja envío de formulario

**Módulo Ausencias:**
- `fetchAusencias()` - Obtiene lista de ausencias
- `createAusencia(formData)` - Registra ausencia
- `renderAusenciasTable()` - Renderiza tabla dinámica
- `loadProfesoresSelect()` - Carga select de profesores
- `handleAusenciaFormSubmit(event)` - Maneja envío de formulario

**Módulo Guardias:**
- `fetchGuardias()` - Obtiene guardias pendientes
- `asignarGuardia(guardiaId, profesorId)` - Asigna sustituto
- `renderGuardiasTable()` - Renderiza tabla con selects
- `handleGuardiaAssignment()` - Maneja asignación

---

#### `profesores.html` - Gestión de Profesores
Página HTML pura para gestionar profesores.

**Características:**
- Formulario de creación (solo visible para admin)
- Tabla dinámica cargada mediante API
- Sin código PHP embebido
- Validación de permisos en JavaScript

---

#### `ausencias.html` - Gestión de Ausencias
Página HTML pura para registrar y ver ausencias.

**Características:**
- Formulario adaptativo según rol:
  - **Admin**: puede seleccionar cualquier profesor
  - **Profesor**: solo puede registrar sus propias ausencias
- Tabla de historial filtrada por permisos
- Generación automática de guardias al crear ausencia

---

#### `guardias.html` - Gestión de Guardias
Página HTML pura para asignar guardias.

**Características:**
- Lista de guardias pendientes
- Selects dinámicos para asignar sustitutos
- Actualización automática tras asignación
- Info box con instrucciones

---

## 🔄 Flujo de Datos

### Ejemplo: Crear un Profesor

**1. Usuario completa formulario en `profesores.html`**
```html
<form onsubmit="handleProfesorFormSubmit(event)">
  <input id="nombre" value="Juan">
  <input id="apellidos" value="Pérez">
  ...
</form>
```

**2. JavaScript captura el evento y envía datos**
```javascript
function handleProfesorFormSubmit(event) {
    event.preventDefault();
    
    const formData = {
        nombre: document.getElementById('nombre').value,
        apellidos: document.getElementById('apellidos').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        especialidad: document.getElementById('especialidad').value,
        rol: document.getElementById('rol').value
    };
    
    createProfesor(formData); // Llama a la API
}
```

**3. Función `createProfesor()` hace petición POST**
```javascript
async function createProfesor(formData) {
    const response = await apiPost('createProfesor', formData);
    showMessage(response.message, 'success');
    renderProfesoresTable(); // Actualiza tabla
}
```

**4. API REST procesa la petición**
```php
case 'createProfesor':
    validateAdmin(); // Solo admin
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validaciones...
    
    $sql = "INSERT INTO profesores (...) VALUES (...)";
    $stmt->execute([...]);
    
    sendResponse(true, ['id' => $newId], 'Profesor creado', 201);
```

**5. JavaScript recibe respuesta JSON**
```json
{
  "success": true,
  "data": { "id": 42 },
  "message": "Profesor creado correctamente"
}
```

**6. Se muestra mensaje y actualiza tabla**
- Mensaje de éxito aparece en pantalla
- Tabla se recarga automáticamente
- Todo sin recargar la página

---

## 📊 Ejemplos de Requests/Responses

### GET - Listar Profesores

**Request:**
```http
GET /Guardianes/api_rest.php?action=getProfesores
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Juan",
      "apellidos": "Pérez",
      "email": "juan@example.com",
      "especialidad": "Matemáticas",
      "rol": "profesor",
      "estado": "activo"
    },
    {
      "id": 2,
      "nombre": "María",
      "apellidos": "García",
      "email": "maria@example.com",
      "especialidad": "Lengua",
      "rol": "admin",
      "estado": "activo"
    }
  ],
  "message": "Profesores obtenidos correctamente"
}
```

---

### POST - Crear Ausencia

**Request:**
```http
POST /Guardianes/api_rest.php?action=createAusencia
Content-Type: application/json

{
  "profesor_id": 1,
  "fecha_inicio": "2024-12-10",
  "fecha_fin": "2024-12-12",
  "tipo": "enfermedad",
  "motivo": "Gripe"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "ausencia_id": 15,
    "guardias_creadas": 6
  },
  "message": "Ausencia registrada y guardias generadas correctamente"
}
```

---

### POST - Asignar Guardia

**Request:**
```http
POST /Guardianes/api_rest.php?action=asignarGuardia
Content-Type: application/json

{
  "guardia_id": 42,
  "profesor_sustituto_id": 3
}
```

**Response:**
```json
{
  "success": true,
  "data": null,
  "message": "Guardia asignada correctamente"
}
```

---

## 🚀 Cómo Usar

### 1. Acceder a las nuevas páginas

Actualiza los enlaces en tu navegación para usar los archivos `.html`:

```html
<!-- Antes -->
<a href="profesores.php">Profesores</a>

<!-- Después -->
<a href="profesores.html">Profesores</a>
```

### 2. Las sesiones PHP siguen funcionando

La autenticación sigue usando sesiones PHP. Debes iniciar sesión normalmente en `index.php`.

### 3. Permisos automáticos

El JavaScript detecta automáticamente si eres admin o profesor y ajusta la interfaz:
- **Admin**: ve todos los formularios y datos
- **Profesor**: solo ve sus propios datos

---

## ✅ Ventajas de la Nueva Arquitectura

### Separación de Responsabilidades
- **Frontend**: Solo se encarga de la presentación
- **Backend**: Solo se encarga de la lógica de negocio

### Escalabilidad
- Puedes crear una app móvil que consuma la misma API
- Puedes cambiar el frontend sin tocar el backend

### Mantenibilidad
- Código más limpio y organizado
- Más fácil de debuggear
- Más fácil de testear

### Experiencia de Usuario
- No hay recargas de página
- Feedback inmediato
- Interfaz más fluida y moderna

---

## 🔧 Migración desde Archivos Antiguos

Los archivos PHP originales **NO se han eliminado**:
- `profesores.php` ➜ Ahora usa `profesores.html`
- `ausencias.php` ➜ Ahora usa `ausencias.html`
- `guardias.php` ➜ Ahora usa `guardias.html`

Puedes mantener ambas versiones o eliminar las antiguas cuando estés seguro.

---

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+, PDO, MySQL
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **API**: REST, JSON
- **Comunicación**: Fetch API (AJAX)

---

## 📝 Notas Importantes

1. **Sesiones PHP**: La API valida sesiones PHP en cada request
2. **CORS**: Headers configurados para desarrollo local
3. **Seguridad**: Validación de permisos en backend (no confiar en frontend)
4. **Errores**: Todos los errores se manejan con códigos HTTP apropiados
5. **Feedback**: Mensajes de éxito/error se muestran automáticamente

---

## 🎓 Conclusión

Has transformado exitosamente tu aplicación monolítica en una arquitectura moderna con API REST. Ahora tienes:

✅ Frontend y backend completamente separados  
✅ Comunicación mediante JSON  
✅ Código más limpio y mantenible  
✅ Base para futuras expansiones (apps móviles, etc.)  
✅ Mejor experiencia de usuario (sin recargas)  

**¡Felicidades! 🎉**
