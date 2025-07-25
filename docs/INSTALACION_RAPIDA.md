# 📋 INSTRUCCIONES DE INSTALACIÓN RÁPIDA
## Figger Energy SAS - Base de Datos Unificada

---

## 🎯 **INSTALACIÓN EN 3 PASOS**

### **PASO 1: Abrir phpMyAdmin**
1. Inicia **XAMPP Control Panel**
2. Asegúrate de que **Apache** y **MySQL** estén ejecutándose (verde)
3. Haz clic en **"Admin"** junto a MySQL
4. Se abrirá phpMyAdmin en tu navegador

### **PASO 2: Importar Base de Datos**
1. En phpMyAdmin, haz clic en la pestaña **"SQL"** (parte superior)
2. Haz clic en **"Elegir archivo"**
3. Selecciona: `database/figger_energy_complete.sql`
4. Haz clic en **"Continuar"**
5. Espera a que termine (aparecerá mensaje de éxito)

### **PASO 3: Verificar Instalación**
1. En la barra lateral izquierda, verás `figger_energy_db`
2. Haz clic en la base de datos
3. Deberías ver **10 tablas** creadas
4. Haz clic en tabla `usuarios` → `Examinar`
5. Deberías ver **3 usuarios** con contraseñas hasheadas

---

## ✅ **VERIFICACIÓN EXITOSA**

### **Si ves esto, ¡todo está bien!:**
- Base de datos `figger_energy_db` visible
- 10 tablas listadas
- En tabla `usuarios`: contraseñas como `$2y$10$...` (hashes)
- 3 usuarios: admin, empleado, auditor

### **Ahora puedes:**
1. **Ir a tu sitio web:** `http://localhost/tu-proyecto/login.php`
2. **Usar estas credenciales:**
   - `admin@figgerenergy.gov.co` / `admin123`
   - `empleado@figgerenergy.gov.co` / `empleado123`
   - `auditor@figgerenergy.gov.co` / `auditor123`

---

## 🚨 **¿Problemas?**

### **Error: "Base de datos no se puede crear"**
→ Verifica que MySQL esté corriendo en XAMPP

### **Error: "Archivo muy grande"**
→ El archivo es de ~30KB, debería importar sin problemas

### **Error: "Syntax error"**
→ Asegúrate de seleccionar el archivo correcto: `figger_energy_complete.sql`

### **Login falla en la web**
→ Verifica que:
- La base de datos se importó correctamente
- Usas las credenciales exactas (copia y pega)
- El archivo `includes/db.php` tiene la configuración correcta

---

## 📞 **¿Todo funciona?**

Si puedes hacer login y ves la información de contacto actualizada:
- **📍 Dirección:** Calle 1, Carrera 1, Edificio 1, Macondo, Colombia  
- **📞 Teléfono:** +57 300 0000 000
- **🚨 Emergencias:** +57 018000001

**¡Felicitaciones! La instalación fue exitosa.** 🎉

---

**Total tiempo estimado: 2-3 minutos** ⏱️
