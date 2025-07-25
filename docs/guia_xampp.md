# 🚀 Guía de Configuración para XAMPP
## Figger Energy SAS - Actualización de Contraseñas

---

## 📝 Instrucciones Paso a Paso

### **1. Abrir phpMyAdmin en XAMPP**
1. Inicia **XAMPP Control Panel**
2. Asegúrate de que **Apache** y **MySQL** estén ejecutándose
3. Haz clic en **"Admin"** junto a MySQL
4. Se abrirá phpMyAdmin en tu navegador

### **2. Seleccionar la Base de Datos**
1. En la barra lateral izquierda, busca `figger_energy_db`
2. Haz clic en el nombre de la base de datos
3. Verás las tablas: `usuarios`, `contactos`, `alertas_mineria`, etc.

### **3. Ejecutar el Script SQL**
1. Haz clic en la pestaña **"SQL"** (parte superior)
2. En el área de texto, haz clic en **"Elegir archivo"**
3. Navega hasta tu proyecto y selecciona:
   ```
   /ruta/a/tu/proyecto/scripts/actualizar_passwords.sql
   ```
4. Haz clic en **"Continuar"** para ejecutar

### **4. Verificar Resultados**
Deberías ver algo como esto:

```
✅ Estado actual de usuarios mostrado
✅ 3 filas afectadas por UPDATE
✅ Contraseñas actualizadas correctamente
```

### **5. Comprobar en la Tabla Usuarios**
1. Haz clic en la tabla **"usuarios"**
2. Haz clic en **"Examinar"**
3. En la columna `password` deberías ver hashes como:
   ```
   $2y$10$TKh8H1.PfQx...
   ```
   ⚠️ **IMPORTANTE**: Es normal ver estos strings extraños

---

## 🔑 Credenciales Actualizadas

Después de ejecutar el script, usa estas credenciales:

| Usuario | Email | Contraseña Real |
|---------|-------|----------------|
| **Admin** | `admin@figgerenergy.gov.co` | `admin123` |
| **Empleado** | `empleado@figgerenergy.gov.co` | `empleado123` |
| **Auditor** | `auditor@figgerenergy.gov.co` | `auditor123` |

---

## ✅ Prueba del Sistema

### **1. Probar Login**
1. Ve a: `http://localhost/tu-proyecto/login.php`
2. Usa cualquiera de las credenciales de arriba
3. Deberías poder acceder sin problemas

### **2. Si NO funciona el login**
Revisa estos puntos:

**A. Verificar Base de Datos:**
```sql
SELECT email, LEFT(password, 15) as hash FROM usuarios;
```

**B. Verificar Servidor:**
- Apache y MySQL corriendo en XAMPP
- No hay errores en los logs de PHP

**C. Verificar Archivos:**
- `includes/db.php` con configuración correcta
- Credenciales de BD en XAMPP (usuario: `root`, password: vacía)

---

## 🛠️ Solución de Problemas

### **Error: "Base de datos no encontrada"**
```sql
-- Ejecutar primero:
CREATE DATABASE IF NOT EXISTS figger_energy_db;
USE figger_energy_db;
-- Luego importar: data/usuarios.sql
```

### **Error: "Tabla usuarios no existe"**
1. Importa primero: `data/usuarios.sql`
2. Luego ejecuta: `scripts/actualizar_passwords.sql`

### **Login falla con "Credenciales incorrectas"**
- Verifica que ejecutaste `actualizar_passwords.sql`
- Usa las credenciales exactas (copia y pega)
- Revisa que no hay espacios extra

---

## 📋 Checklist Final

- [ ] XAMPP Apache y MySQL ejecutándose
- [ ] Base de datos `figger_energy_db` existe
- [ ] Tabla `usuarios` tiene datos
- [ ] Script `actualizar_passwords.sql` ejecutado
- [ ] Contraseñas aparecen como hashes en phpMyAdmin
- [ ] Login funciona con credenciales actualizadas

---

## 💡 Recordatorio

**Las contraseñas en phpMyAdmin DEBEN aparecer como hashes extraños.** 
Si ves las contraseñas en texto plano, el sistema NO es seguro.

✅ **Correcto**: `$2y$10$TKh8H1.PfQx...`  
❌ **Incorrecto**: `admin123`
