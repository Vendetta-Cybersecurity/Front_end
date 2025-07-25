# 🔐 Documentación de Seguridad de Contraseñas
## Figger Energy SAS - Sistema de Gestión

---

## ¿Por qué las contraseñas aparecen como strings extraños en phpMyAdmin?

### ✅ **ESTO ES NORMAL Y CORRECTO**

Las contraseñas que ves en phpMyAdmin como strings de letras, números y símbolos son **hashes de seguridad**, no errores del sistema.

#### Ejemplo de lo que ves en la base de datos:
```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

#### Contraseña real del usuario:
```
admin123
```

---

## 🛡️ ¿Cómo funciona la seguridad?

### 1. **Registro de Usuario** (`register.php`)
```php
// La contraseña real se convierte en hash
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Se guarda SOLO el hash en la base de datos
INSERT INTO usuarios (password) VALUES ('$2y$10$...')
```

### 2. **Verificación en Login** (`login.php`)
```php
// Se compara la contraseña ingresada con el hash guardado
if (password_verify($password_ingresada, $hash_guardado)) {
    // Login exitoso
}
```

### 3. **¿Por qué usar hashes?**
- 🔒 **Seguridad**: Nadie puede ver las contraseñas reales
- 🛡️ **Protección**: Si alguien accede a la DB, no obtiene contraseñas
- ✅ **Estándar**: Es la práctica recomendada internacionalmente

---

## 👥 Usuarios Demo del Sistema

| Email | Contraseña Real | Hash en DB |
|-------|----------------|------------|
| `admin@figgerenergy.gov.co` | `admin123` | `$2y$10$...` |
| `empleado@figgerenergy.gov.co` | `empleado123` | `$2y$10$...` |
| `auditor@figgerenergy.gov.co` | `auditor123` | `$2y$10$...` |

---

## 🔧 Mantenimiento de Contraseñas

### Regenerar Hashes de Usuarios Demo
```bash
cd /path/to/figger-energy/scripts
php regenerar_passwords.php
```

### Verificar Seguridad de Contraseñas
```sql
-- Ver estructura de contraseñas (SIN mostrar las reales)
SELECT email, LEFT(password, 10) as hash_preview, 
       LENGTH(password) as hash_length 
FROM usuarios;
```

### Crear Usuario con Contraseña Segura
```php
$email = 'nuevo@figgerenergy.gov.co';
$password_real = 'MiClaveSegura2024!';
$password_hash = password_hash($password_real, PASSWORD_DEFAULT);

// Guardar en DB
INSERT INTO usuarios (email, password) VALUES (?, ?);
```

---

## ❌ Errores Comunes

### ❌ **NO hagas esto:**
```sql
-- NUNCA almacenar contraseñas en texto plano
INSERT INTO usuarios (password) VALUES ('password123');

-- NUNCA mostrar contraseñas reales en logs
echo "Password: " . $password;
```

### ✅ **Haz esto:**
```php
// Siempre hashear antes de guardar
$hash = password_hash($password, PASSWORD_DEFAULT);

// Siempre verificar con password_verify()
password_verify($input, $stored_hash);
```

---

## 🔍 Verificación del Sistema

### Comprobar que funciona correctamente:

1. **Accede al sistema con las credenciales demo**
2. **Revisa phpMyAdmin** - deberías ver hashes, no contraseñas
3. **Si puedes hacer login** = el sistema funciona correctamente

### Si NO puedes hacer login:
1. Ejecuta el script `regenerar_passwords.php`
2. Verifica la configuración de la base de datos
3. Revisa los logs de errores de PHP

---

## 📋 Checklist de Seguridad

- [x] Contraseñas hasheadas en base de datos
- [x] Uso de `password_verify()` para autenticación  
- [x] Algoritmos seguros (bcrypt/Argon2ID)
- [x] No hay contraseñas en texto plano en el código
- [x] Logs de seguridad implementados
- [x] Validación de fortaleza de contraseñas

---

## 📞 Soporte

Si tienes dudas sobre la seguridad del sistema:
1. Revisa esta documentación
2. Ejecuta los scripts de verificación
3. Consulta los logs del sistema
4. Contacta al equipo de desarrollo

**Recuerda: Ver hashes en lugar de contraseñas es SEÑAL de un sistema SEGURO** ✅
