# 📘 Documentación Técnica del Proyecto Guardianes

Este documento detalla la arquitectura, estructura de archivos y, en especial, el funcionamiento interno de la API REST (`api_rest.php`), explicando el "por qué" y el "cómo" de cada decisión técnica.

## 🏗️ Filosofía y Arquitectura

El proyecto sigue una arquitectura **Cliente-Servidor Desacoplada**:

*   **Frontend (Cliente)**: HTML estático + JavaScript Puro (Vanilla). No hay PHP mezclado en el HTML. El cliente solo sabe "pintar" datos que recibe por JSON.
*   **Backend (Servidor)**: PHP actuando exclusivamente como API. No genera HTML, solo procesa datos y devuelve JSON.
*   **Comunicación**: Todas las interacciones se hacen vía peticiones HTTP (GET/POST) asíncronas (`fetch`).

---

## 📂 Estructura de Archivos

*   `index.html`: Punto de entrada (Login).
*   `dashboard.html`, `profesores.html`, etc.: Vistas de la aplicación.
*   `js/`: Contiene toda la lógica del cliente.
    *   `api.js`: **Centraliza** la comunicación. Piense en esto como el "teléfono" del frontend para llamar al backend.
*   **`api_rest.php`**: El cerebro del backend. (Analizado en profundidad abajo).
*   `conexion.php`: Archivo simple que devuelve el objeto `$pdo` para conectar a MySQL usando PDO (más seguro que mysqli).
*   `config.php`: Credenciales de base de datos.
*   `Base_de_datos.sql`: Script para crear la estructura + datos de prueba.

---

## 🧠 Deep Dive: `api_rest.php`

Este archivo es un **Front Controller**: maneja TODAS las peticiones que llegan al servidor.

### 1. Configuración de Cabeceras (Headers)

Lo primero que hace el archivo es definir cómo va a hablar.

```php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
```

*   **¿Por qué?**:
    *   `Content-Type: application/json`: Le dice al cliente "No te estoy enviando una web HTML, te estoy enviando datos puros".
    *   `Access-Control-Allow-Origin: *` (CORS): Permite que, si en el futuro tu frontend está en otro dominio (ej: app móvil), pueda conectarse.

### 2. Gestión de Sesiones

```php
session_start();
```

*   **¿Por qué?**: PHP por defecto es "sin estado" (stateless). Si Fulanito hace una petición y luego otra, PHP no recuerda quién es. `session_start()` crea/reanuda una "memoria" en el servidor asociada al navegador del usuario. Aquí guardamos `user_id` y `rol`.

### 3. Funciones Helper (`sendResponse`)

En lugar de escribir `echo json_encode(...)` y `exit` 50 veces, usamos una función.

```php
function sendResponse($success, $data = null, $message = '', $httpCode = 200) { ... }
```

*   **¿Por qué?**:
    *   **Estandarización**: Asegura que TODAS las respuestas tengan la misma estructura: `success` (bool), `data` (payload), `message` (texto).
    *   **DRY (Don't Repeat Yourself)**: Menos código propenso a errores.

### 4. Seguridad y Middleware (`validateSession`, `validateAdmin`)

Antes de procesar datos, ponemos "porteros".

```php
function validateSession() {
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, null, 'No autorizado...', 401);
    }
}
```

*   **¿Por qué?**: Protege los endpoints. Si alguien intenta llamar a `?action=getProfesores` escribiendo la URL directamente sin loguearse, esta función lo detiene inmediatamente. `validateAdmin()` añade una capa extra verificando `$_SESSION['user_role'] === 'admin'`.

### 5. Enrutamiento (El `switch`)

El corazón lógico. Decide qué código ejecutar basándose en el parámetro `action`.

```php
$action = $_GET['action'] ?? $_POST['action'] ?? null;
switch ($action) { ... }
```

#### A. Login (`case 'login'`)
Recibe JSON crudo (`php://input`) porque los formularios estándar envían `x-www-form-urlencoded`.

**¿Dónde se asigna el Rol (`user_role`)?**
Justo aquí, dentro del bloque `if ($user && password_verify(...))`. Es el momento crítico donde pasamos de "visitante anónimo" a "usuario con permisos".

```php
// 1. Buscamos al usuario en la BD por su email
$stmt = $pdo->prepare("SELECT * FROM profesores WHERE email = ? ...");
$stmt->execute([$email]);
$user = $stmt->fetch(); // $user ahora contiene todos los campos de la tabla (id, rol, password...)

// 2. Verificamos la contraseña
if ($user && password_verify($password, $user['password'])) {
    
    // 3. ¡AQUÍ ES! Asignación de variables de sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellidos'];
    
    // Tomamos el campo 'rol' que vino de la base de datos ('admin' o 'profesor')
    // y lo guardamos en la sesión del servidor.
    $_SESSION['user_role'] = $user['rol']; 

    // A partir de esta línea, PHP recordará el rol del usuario en futuras peticiones.
    // ...
}
```

*   **Clave**: `password_verify`. Nunca comparamos contraseñas con `==` porque en la BD están encriptadas.

#### B. Crear Ausencia y Lógica de Negocio (`case 'createAusencia'`)
Este es el punto más complejo. No solo guarda la ausencia, tiene **efectos secundarios automáticos**.

1.  **Guarda la Ausencia**:
    ```php
    INSERT INTO ausencias ...
    ```
2.  **Busca el Horario**:
    Consulta qué clases tiene ese profesor (`SELECT ... FROM horario`).
3.  **Genera Guardias (Bucle Inteligente)**:
    Recorre cada día desde `fecha_inicio` hasta `fecha_fin`.
    ```php
    while ($fecha_actual <= $fecha_final) {
        $dia_num = ...; // 1=Lunes, etc.
        // Si el profesor tiene clase ese día (según su horario), crea una 'guardia' nueva
        if ($tiene_clase) {
            INSERT INTO guardias (estado='pendiente', ...)
        }
    }
    ```
*   **¿Por qué esto aquí?**: Automatización. El jefe de estudios no tiene que crear manualmente las huecos a cubrir. El sistema sabe el horario y crea las tareas ("guardias") automáticamente para que luego alguien las coja.

#### C. Asignar Guardia (`case 'asignarGuardia'`)
Solo para admins. Actualiza una guardia existente poniendo un ID de sustituto.

```php
UPDATE guardias SET profesor_sustituto_id = ?, estado = 'asignada' ...
```

### 6. Obtención de Datos (Queries Complejas)

En `getGuardias`, usamos `JOINs` masivos.

```php
SELECT g.*, p_ausente.nombre, p_sustituto.nombre ...
FROM guardias g
JOIN horario h ...
JOIN profesores p_ausente ...
LEFT JOIN profesores p_sustituto ...
```

*   **¿Por qué?**: En la tabla `guardias` solo tenemos IDs (`profesor_sustituto_id`: 5). El frontend no sabe quién es "5". La SQL hace el trabajo sucio de buscar esos nombres y entregarlos listos para mostrar, ahorrando al frontend tener que hacer 10 peticiones extra.

### 7. Manejo de Errores (Try-Catch)

Todo el `switch` está envuelto en un `try { ... } catch (PDOException $e)`.

*   **¿Por qué?**: Si la base de datos falla (se cae el servidor, error de sintaxis SQL), no queremos que la página se quede en blanco o muestre un error feo de PHP en pantalla. Capturamos el error y devolvemos un JSON limpio con `success: false` y el mensaje de error.

---

## 🛠️ Tecnologías Clave Utilizadas

1.  **PDO (PHP Data Objects)**:
    *   Uso de `prepare()` y `execute([$var])`.
    *   **¿Por qué?**: Previene **Inyección SQL**. Nunca concatenamos variables directamente en la cadena SQL.

2.  **JSON (JavaScript Object Notation)**:
    *   Formato ligero de intercambio de datos.
    *   Es el estándar de la industria hoy en día, mucho más legible que XML.

3.  **Bcrypt (password_hash)**:
    *   Algoritmo robusto para guardar contraseñas. Incluso si roban la base de datos, no pueden saber cuál es la contraseña real.
