# Quick Reference - API Endpoints 🚀

## 🔗 Base URL
```
Local: http://127.0.0.1:8000/api/
Production: http://[YOUR_VM_IP]:8000/api/
```

## 📋 Essential Endpoints

### Status Check ✅
```
GET /api/status/
```

### Departments 🏢
```
GET    /api/departamentos/           # List all departments
POST   /api/departamentos/           # Create department
GET    /api/departamentos/{id}/      # Get department + employees
PUT    /api/departamentos/{id}/      # Update department
DELETE /api/departamentos/{id}/      # Delete department
```

### Employees 👥
```
GET    /api/empleados/               # List all employees
POST   /api/empleados/               # Create employee
GET    /api/empleados/{id}/          # Get specific employee
PUT    /api/empleados/{id}/          # Update employee
DELETE /api/empleados/{id}/          # Deactivate employee
GET    /api/empleados/buscar/?q=name # Search employees
```

### Roles 🎭
```
GET /api/roles/                           # List all roles
GET /api/roles/departamento/{dept_id}/    # Roles by department
```

### Users 🔐
```
GET  /api/usuarios/                  # List all users
POST /api/usuarios/                  # Create user
```

### Notifications 📢
```
GET /api/notificaciones/usuario/{user_id}/           # User notifications
PUT /api/notificaciones/{id}/marcar-leida/           # Mark as read
```

### Statistics 📊
```
GET /api/estadisticas/generales/        # General stats
GET /api/estadisticas/departamentos/    # Department stats
```

## 🎯 Quick Examples

### Get All Employees
```javascript
fetch('http://127.0.0.1:8000/api/empleados/')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Employees:', data.data);
    }
  });
```

### Create New Employee
```javascript
const newEmployee = {
  "numero_documento": "12345678",
  "nombres": "Juan",
  "apellidos": "Pérez",
  "email": "juan@company.com",
  "id_departamento": 1,
  "id_rol": 2,
  "fecha_ingreso": "2025-01-29"
};

fetch('http://127.0.0.1:8000/api/empleados/', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(newEmployee)
});
```

### Search Employees
```javascript
fetch('http://127.0.0.1:8000/api/empleados/buscar/?q=Juan')
  .then(response => response.json())
  .then(data => console.log('Search results:', data.data));
```

## 📱 Response Format

### Success Response
```json
{
  "success": true,
  "data": [...],
  "count": 10
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {}
}
```

## 🚀 Ready to Use!

1. **Start Django**: `python manage.py runserver`
2. **Test Status**: Visit `http://127.0.0.1:8000/api/status/`
3. **Connect Frontend**: Use the endpoints above
4. **Check Admin**: `http://127.0.0.1:8000/admin/`

## 🔧 For VM Deployment

Replace `127.0.0.1` with your VM IP address in all URLs.

**Backend VM**: Configure `ALLOWED_HOSTS` in `settings.py`  
**Frontend**: Update API base URL to backend VM IP
