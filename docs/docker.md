# Docker — Guía de desarrollo local

## Stack

| Contenedor | Imagen | Puerto |
|---|---|---|
| `laravel_app` | PHP 7.2-fpm (custom) | interno 9000 |
| `laravel_nginx` | nginx:alpine | **http://localhost:8000** |
| `laravel_mysql` | mysql:5.7 | localhost:**3307** |
| `laravel_node` | node:10-buster | — (npm watch) |

El runtime de Docker es **Colima** (no Docker Desktop).

---

## Arrancar el entorno

```bash
# 1. Iniciar Colima (motor de Docker)
colima start

# 2. Levantar los contenedores
cd /Users/agus/Projects/actividades
docker compose up -d

# 3. Verificar que todo esté corriendo
docker compose ps
```

> Si Colima ya estaba corriendo, saltá directo al paso 2.

---

## Apagar el entorno

```bash
# Solo los contenedores (Colima sigue corriendo)
docker compose down

# Contenedores + Colima
docker compose down && colima stop
```

---

## Comandos del día a día

```bash
# Entrar al contenedor PHP (para artisan, composer, etc.)
docker exec -it laravel_app bash

# Artisan sin entrar al contenedor
docker exec -it laravel_app php artisan <comando>

# Tinker
docker exec -it laravel_app php artisan tinker

# Logs de Laravel en tiempo real
docker exec -it laravel_app tail -f storage/logs/laravel.log

# Logs de nginx
docker logs laravel_nginx -f

# Reiniciar un contenedor
docker compose restart app
```

---

## Setup inicial (primera vez o base de datos vacía)

```bash
docker exec -it laravel_app bash

composer install
cp .env.example .env    # si no existe .env
php artisan key:generate
php artisan migrate
php artisan db:seed     # opcional
```

---

## Acceso a la base de datos

- **Host:** localhost  
- **Puerto:** 3307  
- **Base de datos:** laravel  
- **Usuario:** laravel  
- **Contraseña:** password  

Conectar desde cualquier cliente SQL (TablePlus, DBeaver, etc.) con esos datos.

---

## Variables de entorno relevantes para desarrollo

```bash
# .env
APP_ENV=local
QUEUE_CONNECTION=sync       # jobs corren inline, sin worker separado
TELESCOPE_ENABLED=true      # http://localhost:8000/telescope
ONESIGNAL_APP_ID_DEV=...    # app separada de producción en OneSignal
```

---

## Diagnóstico rápido

```bash
# ¿Colima está corriendo?
colima status

# ¿Los contenedores están corriendo?
docker compose ps

# ¿Hay errores en PHP?
docker exec -it laravel_app tail -50 storage/logs/laravel.log

# ¿MySQL está accesible?
docker exec -it laravel_mysql mysql -ularavel -ppassword laravel -e "SELECT 1;"
```
