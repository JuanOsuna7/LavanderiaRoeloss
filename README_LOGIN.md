# Sistema de Login - Lavandería Roeloss

## 🔐 Implementación Completa de Autenticación

Este sistema de login incluye todas las medidas de seguridad necesarias para una aplicación web profesional.

## 📋 Archivos Creados

### 1. **login.php** - Página de inicio de sesión
- Diseño consistente con el resto de la aplicación
- Validaciones frontend en tiempo real
- Responsive design
- Efectos visuales modernos

### 2. **procesar_login.php** - Procesamiento de autenticación
- Validaciones de seguridad completas
- Protección contra ataques de fuerza bruta
- Hashing de contraseñas con PHP password_hash()
- Registro de intentos de login
- Sesiones seguras

### 3. **auth.php** - Middleware de autenticación
- Verificación automática de sesiones
- Control de timeouts
- Protección CSRF
- Gestión de roles (preparado para expansión)

### 4. **logout.php** - Cierre de sesión
- Destrucción completa de sesiones
- Limpieza de cookies
- Registro de logout
- Soporte para peticiones AJAX

### 5. **database_security_updates.sql** - Actualizaciones de BD
- Tabla para registrar intentos de login
- Índices para mejorar rendimiento
- Campo ultimo_login en usuarios

## 🛠️ Configuración Requerida

### 1. Ejecutar el script SQL
```sql
-- Ejecutar en phpMyAdmin o su gestor de BD
source database_security_updates.sql
```

### 2. Verificar tabla usuarios
La tabla `usuarios` debe tener esta estructura mínima:
```sql
usuarios (
    pk_usuario INT PRIMARY KEY,
    correoUsu VARCHAR(100), -- Usado como nombre de usuario
    contraUsu VARCHAR(255), -- Para contraseñas hasheadas
    rolUsu VARCHAR(50),     -- Rol del usuario
    estatusUsu VARCHAR(20), -- 'Activo' o 'Inactivo'
    fk_persona INT,         -- Relación con datos personales
    ultimo_login DATETIME   -- Agregado por el script
)
```

### 3. Crear usuario de prueba
```sql
INSERT INTO usuarios (correoUsu, contraUsu, rolUsu, estatusUsu) 
VALUES ('admin', '$2y$10$ejemplo_hash_aqui', 'Administrador', 'Activo');
```

## 🔒 Características de Seguridad

### ✅ Protección contra ataques
- **Fuerza bruta**: Límite de 5 intentos fallidos por IP/usuario en 15 minutos
- **SQL Injection**: Uso de prepared statements
- **XSS**: Sanitización con htmlspecialchars()
- **CSRF**: Tokens de protección (preparado)
- **Session Fixation**: Regeneración de session_id
- **Timing Attacks**: Delays aleatorios en errores

### ✅ Gestión de sesiones
- **Timeout**: 2 horas de inactividad
- **Seguridad**: Cookies httponly y secure
- **Regeneración**: ID de sesión se regenera en login
- **Limpieza**: Destrucción completa en logout

### ✅ Validaciones
- **Frontend**: Validación en tiempo real con JavaScript
- **Backend**: Validaciones robustas en PHP
- **Longitud**: Mínimo 3 caracteres usuario, 6 contraseña
- **Sanitización**: Limpieza de todos los inputs

## 🎨 Características de UI/UX

### Diseño Consistente
- Mismos colores y tipografías del proyecto
- Variables CSS para fácil mantenimiento
- Efectos visuales modernos
- Responsive design

### Experiencia de Usuario
- Validaciones en tiempo real
- Mensajes de error claros
- Loading states en el botón
- Animaciones suaves
- Auto-focus en campos

### Accesibilidad
- Labels apropiados
- Contraste adecuado
- Navegación por teclado
- Mensajes descriptivos

## 🚀 Uso del Sistema

### Para usuarios
1. Acceder a `login.php`
2. Ingresar credenciales
3. El sistema redirige a `index.php` si es exitoso
4. Navegar normalmente por la aplicación
5. Usar "Cerrar sesión" para salir

### Para desarrolladores
```php
// Verificar si usuario está logueado
if (!verificarAutenticacion()) {
    redirigirALogin();
}

// Obtener datos del usuario actual
$usuario = obtenerUsuarioActual();
echo "Bienvenido " . $usuario['nombre_completo'];

// Verificar rol específico
if (!verificarRol(['Administrador', 'Gerente'])) {
    // Acceso denegado
}
```

## 📊 Monitoreo y Logs

### Intentos de Login
```sql
SELECT * FROM intentos_login 
WHERE exitoso = 0 
ORDER BY fecha_intento DESC;
```

### Sesiones Activas
Las sesiones se pueden monitorear através de los logs del servidor o implementando una tabla de sesiones activas.

### Logs de Error
Todos los errores se registran en el log de PHP para debugging.

## 🔧 Configuraciones Avanzadas

### En `auth.php`
```php
// Configurar verificación de IP
define('VERIFICAR_IP_CONSISTENTE', true);

// Modo debug (solo desarrollo)
define('DEBUG_MODE', false);
```

### Timeouts personalizados
```php
// Cambiar timeout de sesión (en segundos)
$timeout = 4 * 60 * 60; // 4 horas
```

## 🛡️ Recomendaciones de Seguridad

### Para Producción
1. **HTTPS**: Usar siempre SSL/TLS
2. **Contraseñas**: Política de contraseñas fuertes
3. **Backup**: Respaldos regulares de la BD
4. **Updates**: Mantener PHP y MySQL actualizados
5. **Logs**: Monitorear logs regularmente

### Configuración de Servidor
```apache
# .htaccess para mayor seguridad
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

## 🚨 Solución de Problemas

### Error: "Tabla intentos_login no existe"
```sql
-- Ejecutar el script SQL proporcionado
source database_security_updates.sql
```

### Error: "Sesión no se mantiene"
- Verificar configuración de sesiones en php.ini
- Comprobar que las cookies estén habilitadas
- Revisar la configuración de dominio

### Error: "No se puede conectar a la BD"
- Verificar credenciales en config.php
- Comprobar que el servidor MySQL esté ejecutándose
- Revisar permisos de usuario de BD

## 📈 Próximas Mejoras

- [ ] Autenticación de dos factores (2FA)
- [ ] Recuperación de contraseñas por email
- [ ] Sistema de roles más granular
- [ ] Dashboard de administración de usuarios
- [ ] API de autenticación para aplicaciones móviles

---

## 📞 Soporte

Para dudas o problemas con la implementación, revisar:
1. Logs de error de PHP
2. Logs de base de datos
3. Consola del navegador para errores JavaScript

¡El sistema está listo para usar! 🎉