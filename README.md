# Plataforma de voluntariado eventual 

Online en: [actividades.techo.org](https://actividades.techo.org)
Prueba en: [sandbox.actividades.techo.org](https://sandbox.actividades.techo.org)

📖 Documentación de la API: [docs/api.md](docs/api.md)

# 🚀 Instalación y entorno de desarrollo con Docker

Este proyecto utiliza **Docker** para crear un entorno completo de desarrollo con PHP, Nginx, MySQL y Node.

---

## 📦 Requisitos

- Docker + Docker Compose (Mac: Docker Desktop o Colima)
- Git

---

## 📂 1. Clonar el repositorio

```bash
    git clone https://github.com/usuario/proyecto.git
    cd proyecto
```

## 🐳 2. Levantar los contenedores
```bash
    docker compose up -d --build
```
Servicios que se inician:

- laravel_app → PHP 7.2 + Composer
- laravel_nginx → Servidor web
- laravel_mysql → MySQL 5.7
- laravel_node → Node 10 (compilación de assets)

## 🔧 3. Instalar dependencias de PHP
```bash
    docker compose exec app composer install
```
## 🔑 4. Generar la APP_KEY
```bash
    docker compose exec app php artisan key:generate
```
## 🗄️ 5. Ejecutar migrations

```bash
    docker compose exec app php artisan migrate
```
## 🌱 6. Ejecutar seeders
```bash
    docker compose exec app php artisan db:seed --class=UsuarioAdminSeeder
    docker compose exec app php artisan db:seed --class=InitialDataSeeder

```
## 📦 7. Instalar dependencias de Node
 
Instalar:

```bash
    docker compose run --rm node npm install
```

Compilar assets:

```bash
    docker compose run --rm node npm run dev
```
## 📁 8. Crear directorios necesarios
```bash
    docker compose exec app mkdir -p photos/shares/thumbs
    docker compose exec app chmod -R 777 photos
    docker compose exec app chmod -R 775 storage
```
## 🔗 9. Crear symlink de storage y keys de passportdocker compose exec app php artisan passport:install

```bash
    docker compose exec app php artisan storage:link
    docker compose exec app php artisan passport:install

```
## 🧼 10. Limpiar cachés (recomendado)
```bash
    docker compose exec app php artisan cache:clear
    docker compose exec app php artisan config:clear
    docker compose exec app php artisan permission:cache-reset
```
## ✔️ 11. Acceder al sistema

Abrir en el navegador:

http://localhost:8000

## 🧪 12. Comandos útiles

Entrar al contenedor PHP:

```bash
    docker compose exec app bash
```

Ver logs de Laravel:

```bash
    docker compose exec app tail -f storage/logs/laravel.log
```

Reiniciar todo:

```bash
    docker compose down
    docker compose up -d
```
