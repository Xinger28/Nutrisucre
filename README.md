# 🥗 NutriSucre — Plataforma de Nutrición Online

Plataforma web que conecta pacientes con nutricionistas certificados en Sucre, Bolivia.

## 🚀 Despliegue en Producción

- **Aplicación:** [Render](https://render.com) (Docker)
- **Base de datos:** [Clever Cloud](https://clever-cloud.com) (MySQL gratuito)

---

## 📋 Funcionalidades por Sprint

### Sprint 1 — Autenticación y Perfiles
- Registro e inicio de sesión (Paciente, Nutricionista, Administrador)
- Gestión de perfiles de nutricionistas
- Panel de administración de postulaciones

### Sprint 2 — Gestión de Servicios
- Registro de servicios/productos por nutricionistas
- Validación y aprobación por administrador
- Visualización de servicios disponibles

### Sprint 3 — Búsqueda, Solicitudes y Confirmaciones
- **Búsqueda de servicios** con filtros: más recientes, mejor calificados, más utilizados, modalidad, precio y categoría
- **Solicitud de servicio** por el paciente con datos clínicos (motivo, peso, altura, condiciones médicas)
- **Confirmación/rechazo** de solicitudes por el nutricionista con respuesta al paciente
- Vista de solicitudes recibidas para el nutricionista
- Historial de solicitudes para el paciente

---

## ⚙️ Configuración en Clever Cloud (Base de Datos)

### 1. Crear la base de datos MySQL gratuita
1. Ir a [console.clever-cloud.com](https://console.clever-cloud.com)
2. Crear organización o usar la personal
3. **Add a service → MySQL** (plan `DEV` es gratuito)
4. Anotar las credenciales que aparecen en la pestaña **Environment variables**:
   - `MYSQL_ADDON_HOST`
   - `MYSQL_ADDON_DB`
   - `MYSQL_ADDON_USER`
   - `MYSQL_ADDON_PASSWORD`
   - `MYSQL_ADDON_PORT`

### 2. Importar el esquema de la base de datos
Usar el cliente MySQL o phpMyAdmin (Clever Cloud lo ofrece en la pestaña **phpMyAdmin**):

```bash
mysql -h <MYSQL_ADDON_HOST> -P <MYSQL_ADDON_PORT> -u <MYSQL_ADDON_USER> -p<MYSQL_ADDON_PASSWORD> <MYSQL_ADDON_DB> < db_clevercloud.sql
```

> ⚠️ Usar `db_clevercloud.sql` (no `db.sql`) — ya no contiene `CREATE DATABASE` ni `USE`, que Clever Cloud no permite.

---

## ⚙️ Configuración en Render (Aplicación)

### 1. Crear el servicio web
1. Ir a [dashboard.render.com](https://dashboard.render.com)
2. **New → Web Service**
3. Conectar el repositorio de GitHub con este proyecto
4. Configuración:
   - **Runtime:** Docker
   - **Dockerfile path:** `./Dockerfile`

### 2. Variables de entorno en Render
En **Environment → Add Environment Variable**, agregar:

| Variable   | Valor (de Clever Cloud)      |
|------------|------------------------------|
| `DB_HOST`  | `MYSQL_ADDON_HOST`           |
| `DB_NAME`  | `MYSQL_ADDON_DB`             |
| `DB_USER`  | `MYSQL_ADDON_USER`           |
| `DB_PASS`  | `MYSQL_ADDON_PASSWORD`       |
| `DB_PORT`  | `MYSQL_ADDON_PORT`           |

### 3. Deploy
Render detectará el `render.yaml` y desplegará automáticamente en cada push a `main`.

---

## 💻 Desarrollo Local (XAMPP)

### Requisitos
- XAMPP con PHP 8.2+ y MySQL
- Git

### Configuración
```bash
# 1. Clonar el repositorio
git clone https://github.com/TU_USUARIO/nutrisucre.git

# 2. Copiar carpeta al directorio de XAMPP
# Windows: C:\xampp\htdocs\nutrisucre
# Linux/Mac: /opt/lampp/htdocs/nutrisucre

# 3. Importar la base de datos
# En phpMyAdmin: crear BD "nutrisucre" e importar db.sql

# 4. Acceder
# http://localhost/nutrisucre/login.php
```

### Usuarios de prueba
| Email                        | Contraseña | Rol             |
|------------------------------|------------|-----------------|
| admin@nutrisucre.com         | admin123   | Administrador   |
| pac@nutrisucre.com           | pac123     | Paciente        |
| nutricionista@nutrisucre.com | nutri123   | Nutricionista   |

---

## 🗂️ Estructura del Proyecto

```
nutrisucre/
├── api/
│   ├── auth.php          # Autenticación (login, logout, registro)
│   ├── nutricionistas.php # Perfiles de nutricionistas
│   ├── servicios.php     # CRUD de servicios + validación admin
│   ├── solicitudes.php   # Sprint 3: crear/responder solicitudes
│   ├── postulaciones.php # Postulaciones de nutricionistas
│   ├── citas.php         # Gestión de citas
│   ├── planes.php        # Planes nutricionales
│   ├── resenas.php       # Reseñas y calificaciones
│   └── seguimiento.php   # Seguimiento de progreso
├── login.php             # Inicio de sesión
├── registro_nutricionista.php # Registro y postulación
├── dashboard.php         # Panel principal
├── buscar.php            # Sprint 3: búsqueda con filtros
├── servicios.php         # Sprint 2+3: servicios y solicitudes
├── planes.php            # Planes nutricionales
├── progreso.php          # Seguimiento de progreso
├── admin.php             # Panel administrador
├── config.php            # Conexión a BD (usa variables de entorno)
├── _sidebar.php          # Navegación lateral
├── db.sql                # SQL completo (para XAMPP local)
├── db_clevercloud.sql    # SQL para Clever Cloud (sin CREATE DB)
├── Dockerfile            # Imagen Docker para Render
├── render.yaml           # Configuración de Render
└── .htaccess             # Seguridad y configuración Apache
```

---

## 🛠️ Stack Tecnológico

- **Backend:** PHP 8.2 con PDO
- **Frontend:** HTML5, Tailwind CSS, JavaScript vanilla
- **Base de datos:** MySQL 8
- **Servidor:** Apache (Docker)
- **Despliegue:** Render (Docker) + Clever Cloud (MySQL)

---

## 📌 Sprint 3 — Endpoints de la API

### Búsqueda de Servicios
```
GET /api/servicios.php?publico=1
GET /api/servicios.php?publico=1&orden=recientes
GET /api/servicios.php?publico=1&orden=mejor_calificados
GET /api/servicios.php?publico=1&orden=mas_utilizados
GET /api/servicios.php?publico=1&categoria=Pérdida+de+peso
GET /api/servicios.php?publico=1&precio_max=300
```

### Solicitudes
```
GET    /api/solicitudes.php                     # Listar (según rol)
POST   /api/solicitudes.php                     # Crear solicitud (Paciente)
PUT    /api/solicitudes.php?accion=responder    # Aceptar/rechazar (Nutricionista)
```
