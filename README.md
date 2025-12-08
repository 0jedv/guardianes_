# Guardianes - Sistema de Gestión de Guardias

Este proyecto es un sistema web para la gestión de guardias, ausencias y horarios de profesores. Está diseñado con una arquitectura que separa claramente el **Frontend** del **Backend**, comunicándose a través de una **API REST**.

## 🏗️ Arquitectura del Proyecto

El proyecto sigue una arquitectura cliente-servidor desacoplada:

### 1. Frontend (Cliente)
- **Tecnologías**: HTML5, CSS3, JavaScript (Vanilla).
- **Ubicación**: Archivos `.html` en la raíz y scripts en la carpeta `js/`.
- **Funcionamiento**: La interfaz de usuario no contiene lógica de negocio ni conexiones a base de datos. Todo el contenido dinámico se carga mediante peticiones asíncronas (fetch) al backend.
- **Archivos Clave**:
  - `index.html`: Página de inicio / Login.
  - `dashboard.html`: Panel principal.
  - `js/api.js`: Contiene las funciones para comunicarse con el servidor.

### 2. Backend (Servidor)
- **Tecnologías**: PHP, MySQL.
- **Ubicación**: `api_rest.php` y archivos auxiliares (`conexion.php`, `config.php`).
- **Funcionamiento**: Actúa como una API RESTful que recibe peticiones JSON, procesa la lógica de negocio y devuelve respuestas JSON.
- **Seguridad**: Maneja sesiones de usuario (`session_start()`) y valida permisos (admin vs profesor).

---

## 🔌 API REST (`api_rest.php`)

El archivo `api_rest.php` es el núcleo del backend. Funciona como un "Front Controller" que maneja todas las solicitudes.

### Características Principales:
1.  **Respuestas JSON**: Todas las salidas son en formato JSON (`header('Content-Type: application/json')`).
2.  **CORS**: Configurado para permitir peticiones desde el frontend (`Access-Control-Allow-Origin`).
3.  **Enrutamiento**: Utiliza un parámetro `action` (GET o POST) para determinar qué función ejecutar (ej. `?action=getProfesores`).
4.  **Autenticación**: Verifica si el usuario tiene sesión iniciada antes de procesar solicitudes protegidas.

### Endpoints Principales:
- **`login`**: Autentica al usuario y crea la sesión.
- **`getProfesores`**: Devuelve la lista de profesores activos.
- **`createProfesor`**: (Admin) Registra un nuevo profesor.
- **`getAusencias` / `createAusencia`**: Gestión de ausencias. Al crear una ausencia, el sistema **genera automáticamente las guardias** correspondientes basándose en el horario del profesor.
- **`getGuardias` / `asignarGuardia`**: Gestión de sustituciones.
- **`getHorarioProfesor`**: Obtiene el horario de un profesor específico.

---

## 🔐 Variables de Sesión (`$_SESSION`)

El sistema utiliza sesiones de PHP para mantener la autenticación del usuario. Al iniciar sesión correctamente, se almacenan las siguientes variables globales, que son cruciales para el control de acceso y la personalización:

1.  **`$_SESSION['user_id']`**:
    *   **Contenido**: El ID numérico único del usuario (profesor) en la base de datos (e.g., `1`, `42`).
    *   **Uso**: Se utiliza para vincular acciones (como crear una ausencia) con el usuario autenticado y para filtrar consultas (e.g., "ver solo mis ausencias").

2.  **`$_SESSION['user_name']`**:
    *   **Contenido**: El nombre completo del usuario, concatenando `nombre` y `apellidos` (e.g., `"Juan Pérez"`).
    *   **Uso**: Principalmente cosmético, se utiliza para mostrar un mensaje de bienvenida personalizado en el dashboard o en la cabecera.

3.  **`$_SESSION['user_role']`**:
    *   **Contenido**: El rol del usuario, que puede ser `'profesor'` o `'admin'`.
    *   **Uso**: Crítico para el control de acceso (ACL). Define qué acciones puede realizar el usuario (e.g., solo 'admin' puede crear usuarios o asignar sustituciones).

Estas variables se inicializan en el endpoint `login` de `api_rest.php` y se verifican en cada petición protegida mediante la función `validateSession()` y, para acciones de administrador, `validateAdmin()`.

---

## 📂 Estructura de Carpetas

```
guardianes/
├── api_rest.php       # Punto de entrada de la API
├── conexion.php       # Conexión a la base de datos (PDO)
├── config.php         # Configuración de credenciales
├── Base_de_datos.sql  # Script de creación de la BBDD
├── assets/            # Imágenes y recursos estáticos
├── includes/          # Librerías externas (ej. FPDF)
├── js/                # Lógica del frontend
│   ├── api.js         # Funciones de comunicación con API
│   ├── login.js       # Lógica de login
│   └── ...
└── *.html             # Vistas de la aplicación
```

## 🚀 Instalación y Despliegue

1.  **Base de Datos**:
    - Crear una base de datos en MySQL.
    - Importar el archivo `Base_de_datos.sql`.
2.  **Configuración**:
    - Editar `config.php` con las credenciales de la base de datos.
3.  **Servidor Web**:
    - Desplegar los archivos en un servidor compatible con PHP (Apache/Nginx/XAMPP).
4.  **Uso**:
    - Acceder a `index.html` desde el navegador.

## 💡 Notas Relevantes

- **Generación Automática de Guardias**: Cuando un profesor registra una ausencia, el sistema consulta su horario (`horario_profesor`) y crea registros en la tabla `guardias` para cada clase que pierde, facilitando la asignación de sustitutos.
- **Seguridad**: Las contraseñas se almacenan hasheadas (`password_hash`).
- **Roles**: El sistema distingue entre 'admin' y 'profesor' para restringir acciones sensibles.
