# API Documentation for Frontend Developer 🚀

## Base URL y Configuración Inicial

### Local Development
```
Base URL: http://127.0.0.1:8000/api/
Admin Panel: http://127.0.0.1:8000/admin/
```

### Production/VM Deployment
```
Base URL: http://[BACKEND_VM_IP]:8000/api/
Admin Panel: http://[BACKEND_VM_IP]:8000/admin/
```

## Formato de Respuesta Estándar 📋

Todas las respuestas de la API siguen este formato:

### Respuesta Exitosa:
```json
{
    "success": true,
    "data": [...], // Array o objeto con los datos
    "message": "Mensaje descriptivo (opcional)",
    "count": 0 // Número de elementos (para listas)
}
```

### Respuesta de Error:
```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": {} // Detalles específicos del error
}
```

## Endpoints Disponibles 🛣️

### 1. Status y Configuración

#### `GET /api/status/`
**Descripción**: Verificar estado de la API y conexión a base de datos  
**Respuesta**:
```json
{
    "success": true,
    "data": {
        "status": "API is running",
        "message": "Django REST API backend is working",
        "database": "Connected to MariaDB",
        "stats": {
            "total_empleados": 25,
            "total_departamentos": 3,
            "timestamp": "2025-01-29T10:30:00Z"
        }
    }
}
```

#### `GET /api/config-vm/`
**Descripción**: Información de configuración para VMs

---

### 2. Departamentos 🏢

#### `GET /api/departamentos/`
**Descripción**: Obtener todos los departamentos  
**Respuesta**:
```json
{
    "success": true,
    "data": [
        {
            "id_departamento": 1,
            "nombre": "Administración",
            "descripcion": "Gestión financiera, recursos humanos y jurídica",
            "estado": "activo",
            "fecha_creacion": "2025-01-15T09:00:00Z"
        }
    ],
    "count": 3
}
```

#### `POST /api/departamentos/`
**Descripción**: Crear nuevo departamento  
**Payload**:
```json
{
    "nombre": "Marketing",
    "descripcion": "Departamento de marketing y ventas",
    "estado": "activo"
}
```

#### `GET /api/departamentos/{id}/`
**Descripción**: Obtener departamento específico con empleados  
**Respuesta**:
```json
{
    "success": true,
    "data": {
        "id_departamento": 1,
        "nombre": "IT",
        "descripcion": "Tecnología de la información",
        "estado": "activo",
        "empleados": [
            {
                "id_empleado": 1,
                "nombre_completo": "Juan Pérez",
                "email": "juan.perez@figgerenergy.com",
                "rol_nombre": "Desarrollador"
            }
        ],
        "total_empleados": 5
    }
}
```

#### `PUT /api/departamentos/{id}/`
**Descripción**: Actualizar departamento

#### `DELETE /api/departamentos/{id}/`
**Descripción**: Eliminar departamento

---

### 3. Roles 🎭

#### `GET /api/roles/`
**Descripción**: Obtener todos los roles  
**Respuesta**:
```json
{
    "success": true,
    "data": [
        {
            "id_rol": 1,
            "nombre": "CEO - Gerente General",
            "id_departamento": 1,
            "departamento_nombre": "Administración",
            "descripcion": "Dirección estratégica y representación legal",
            "nivel_acceso": "admin",
            "permisos_sistema": {
                "dashboard": true,
                "usuarios": true,
                "reportes": true
            },
            "estado": "activo"
        }
    ],
    "count": 8
}
```

#### `GET /api/roles/departamento/{id_departamento}/`
**Descripción**: Obtener roles de un departamento específico

---

### 4. Empleados 👥

#### `GET /api/empleados/`
**Descripción**: Obtener todos los empleados  
**Parámetros de Query**:
- `estado`: `activo`, `inactivo`, `suspendido`
- `departamento`: ID del departamento

**Ejemplo**: `GET /api/empleados/?estado=activo&departamento=2`

**Respuesta**:
```json
{
    "success": true,
    "data": [
        {
            "id_empleado": 1,
            "numero_documento": "12345678",
            "tipo_documento": "CC",
            "nombres": "Juan Carlos",
            "apellidos": "Pérez García",
            "nombre_completo": "Juan Carlos Pérez García",
            "email": "juan.perez@figgerenergy.com",
            "telefono": "+57 300 123 4567",
            "fecha_nacimiento": "1990-05-15",
            "direccion": "Calle 123 #45-67",
            "ciudad": "Bogotá",
            "id_departamento": 2,
            "departamento_nombre": "IT",
            "id_rol": 5,
            "rol_nombre": "Desarrollador",
            "fecha_ingreso": "2023-01-15",
            "fecha_salida": null,
            "salario": "3500000.00",
            "foto_perfil": null,
            "estado": "activo",
            "fecha_creacion": "2025-01-15T09:00:00Z",
            "fecha_modificacion": "2025-01-15T09:00:00Z"
        }
    ],
    "count": 25
}
```

#### `POST /api/empleados/`
**Descripción**: Crear nuevo empleado  
**Payload**:
```json
{
    "numero_documento": "87654321",
    "tipo_documento": "CC",
    "nombres": "María Elena",
    "apellidos": "González López",
    "email": "maria.gonzalez@figgerenergy.com",
    "telefono": "+57 301 987 6543",
    "fecha_nacimiento": "1988-03-22",
    "direccion": "Carrera 45 #23-89",
    "ciudad": "Medellín",
    "id_departamento": 1,
    "id_rol": 3,
    "fecha_ingreso": "2025-02-01",
    "salario": "2800000.00",
    "estado": "activo"
}
```

#### `GET /api/empleados/{id}/`
**Descripción**: Obtener empleado específico

#### `PUT /api/empleados/{id}/`
**Descripción**: Actualizar empleado (permite actualizaciones parciales)

#### `DELETE /api/empleados/{id}/`
**Descripción**: Dar de baja empleado (cambia estado a inactivo)

#### `GET /api/empleados/buscar/?q={término}`
**Descripción**: Buscar empleados por nombre, email o documento  
**Ejemplo**: `GET /api/empleados/buscar/?q=Juan`

---

### 5. Usuarios 🔐

#### `GET /api/usuarios/`
**Descripción**: Obtener todos los usuarios del sistema  
**Respuesta**:
```json
{
    "success": true,
    "data": [
        {
            "id_usuario": 1,
            "username": "juan.perez",
            "ultimo_acceso": "2025-01-28T14:30:00Z",
            "estado": "activo",
            "empleado": {
                "id_empleado": 1,
                "nombre_completo": "Juan Carlos Pérez García",
                "email": "juan.perez@figgerenergy.com",
                "departamento_nombre": "IT",
                "rol_nombre": "Desarrollador"
            }
        }
    ]
}
```

#### `POST /api/usuarios/`
**Descripción**: Crear nuevo usuario  
**Payload**:
```json
{
    "id_empleado": 5,
    "username": "maria.gonzalez",
    "password_hash": "hashed_password_here",
    "estado": "activo"
}
```

---

### 6. Notificaciones 📢

#### `GET /api/notificaciones/usuario/{id_usuario}/`
**Descripción**: Obtener notificaciones de un usuario  
**Parámetros de Query**:
- `leida`: `true` o `false`
- `tipo`: `info`, `warning`, `error`, `success`

**Ejemplo**: `GET /api/notificaciones/usuario/1/?leida=false&tipo=warning`

**Respuesta**:
```json
{
    "success": true,
    "data": [
        {
            "id_notificacion": 1,
            "titulo": "Actualización de Sistema",
            "mensaje": "El sistema se actualizará el próximo viernes",
            "tipo": "info",
            "categoria": "sistema",
            "leida": false,
            "fecha_creacion": "2025-01-28T10:00:00Z",
            "url_accion": "/configuracion"
        }
    ],
    "count": 3
}
```

#### `PUT /api/notificaciones/{id}/marcar-leida/`
**Descripción**: Marcar notificación como leída

---

### 7. Estadísticas 📊

#### `GET /api/estadisticas/generales/`
**Descripción**: Obtener estadísticas generales del sistema  
**Respuesta**:
```json
{
    "success": true,
    "data": {
        "total_empleados": 45,
        "empleados_activos": 42,
        "total_departamentos": 3,
        "total_roles": 8,
        "usuarios_activos": 38,
        "sesiones_activas": 12,
        "notificaciones_no_leidas": 25
    },
    "timestamp": "2025-01-29T10:30:00Z"
}
```

#### `GET /api/estadisticas/departamentos/`
**Descripción**: Obtener estadísticas por departamento  
**Respuesta**:
```json
{
    "success": true,
    "data": [
        {
            "id_departamento": 1,
            "nombre": "Administración",
            "total_empleados": 8,
            "empleados_activos": 7,
            "empleados_inactivos": 1,
            "empleados_suspendidos": 0
        },
        {
            "id_departamento": 2,
            "nombre": "IT",
            "total_empleados": 15,
            "empleados_activos": 14,
            "empleados_inactivos": 1,
            "empleados_suspendidos": 0
        }
    ],
    "count": 3
}
```

---

## Configuración de CORS 🌐

El backend está configurado para permitir requests desde diferentes orígenes. Para producción, asegúrate de configurar las IPs correctas en el backend.

### Headers Requeridos
```javascript
// Para requests con autenticación (cuando la implementes)
headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    // 'Authorization': 'Bearer your-token-here' // Para futuras implementaciones
}
```

## Ejemplos de Implementación 💻

### JavaScript/Fetch API
```javascript
// Obtener todos los empleados
async function getEmpleados() {
    try {
        const response = await fetch('http://127.0.0.1:8000/api/empleados/');
        const data = await response.json();
        
        if (data.success) {
            console.log('Empleados:', data.data);
            return data.data;
        } else {
            console.error('Error:', data.message);
        }
    } catch (error) {
        console.error('Network error:', error);
    }
}

// Crear nuevo empleado
async function createEmpleado(empleadoData) {
    try {
        const response = await fetch('http://127.0.0.1:8000/api/empleados/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(empleadoData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Empleado creado:', data.data);
            return data.data;
        } else {
            console.error('Error:', data.errors);
        }
    } catch (error) {
        console.error('Network error:', error);
    }
}

// Buscar empleados
async function searchEmpleados(query) {
    try {
        const response = await fetch(`http://127.0.0.1:8000/api/empleados/buscar/?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success) {
            return data.data;
        }
    } catch (error) {
        console.error('Search error:', error);
    }
}
```

### React/Axios Example
```javascript
import axios from 'axios';

const API_BASE_URL = 'http://127.0.0.1:8000/api';

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Interceptor para manejar respuestas
api.interceptors.response.use(
    (response) => response,
    (error) => {
        console.error('API Error:', error.response?.data);
        return Promise.reject(error);
    }
);

// Servicios
export const empleadosService = {
    getAll: (params = {}) => api.get('/empleados/', { params }),
    getById: (id) => api.get(`/empleados/${id}/`),
    create: (data) => api.post('/empleados/', data),
    update: (id, data) => api.put(`/empleados/${id}/`, data),
    delete: (id) => api.delete(`/empleados/${id}/`),
    search: (query) => api.get(`/empleados/buscar/?q=${query}`),
};

export const departamentosService = {
    getAll: () => api.get('/departamentos/'),
    getById: (id) => api.get(`/departamentos/${id}/`),
    create: (data) => api.post('/departamentos/', data),
};
```

## Códigos de Estado HTTP 📈

- **200 OK**: Operación exitosa
- **201 Created**: Recurso creado exitosamente
- **204 No Content**: Eliminación exitosa
- **400 Bad Request**: Error en los datos enviados
- **404 Not Found**: Recurso no encontrado
- **500 Internal Server Error**: Error del servidor

## Configuración para Despliegue en VM 🖥️

### Variables de Entorno a Configurar:
```bash
# En el backend
BACKEND_HOST=0.0.0.0
BACKEND_PORT=8000
DATABASE_HOST=[IP_DE_VM_BASE_DATOS]
ALLOWED_HOSTS=[IP_DE_VM_FRONTEND],[IP_DE_VM_BACKEND]

# En el frontend
REACT_APP_API_URL=http://[IP_DE_VM_BACKEND]:8000/api
# o para Vue.js
VUE_APP_API_URL=http://[IP_DE_VM_BACKEND]:8000/api
```

### URLs de Producción:
```
Backend API: http://[IP_DE_VM_BACKEND]:8000/api/
Admin Panel: http://[IP_DE_VM_BACKEND]:8000/admin/
```

## Testing de Endpoints 🧪

### Con curl:
```bash
# Status check
curl -X GET http://127.0.0.1:8000/api/status/

# Get employees
curl -X GET http://127.0.0.1:8000/api/empleados/

# Search employees
curl -X GET "http://127.0.0.1:8000/api/empleados/buscar/?q=Juan"

# Create employee
curl -X POST http://127.0.0.1:8000/api/empleados/ \
  -H "Content-Type: application/json" \
  -d '{"numero_documento":"12345678","nombres":"Test","apellidos":"User","email":"test@test.com","id_departamento":1,"id_rol":1,"fecha_ingreso":"2025-01-29"}'
```

## Notas Importantes ⚠️

1. **CORS**: El backend está configurado para desarrollo. En producción, configurar IPs específicas.

2. **Autenticación**: Actualmente no hay autenticación implementada. Todos los endpoints son de acceso libre.

3. **Paginación**: No implementada aún. Todas las listas devuelven todos los elementos.

4. **Rate Limiting**: No implementado. Considerar para producción.

5. **Validaciones**: El backend valida datos según los modelos Django.

6. **Timestamps**: Todos los timestamps están en formato ISO 8601 UTC.

## Próximas Implementaciones 🚀

- Sistema de autenticación JWT
- Paginación de resultados
- Filtros avanzados
- Upload de archivos (fotos de perfil)
- WebSocket para notificaciones en tiempo real
- API versioning

---

¡Tu backend está listo para conectarse! Si necesitas alguna funcionalidad específica o tienes dudas sobre algún endpoint, no dudes en preguntar. 🎉
