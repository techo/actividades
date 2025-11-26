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


# Instalación Legacy para desarrollo VIA CONSOLA

###Usa
- [Laravel 5.5](https://laravel.com/docs/5.5)
- [VueJS 2](https://vuejs.org/v2/guide/)

###Pre-Requisitos:
- PHP 7.2
- Composer
- Mysql 5.7
- Nodejs 10 y npm

###Instalación requisitos previos
```bash
sudo apt-get install python-software-properties
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php7.2
sudo apt-get install php7.2-mbstring
sudo apt-get install php7.2-zip
sudo apt-get install php7.2-xmlreader
sudo apt-get install php7.2-gd
sudo apt-get install php7.2-mysql
sudo apt-get install php7.2-imagick
sudo apt-get install php7.2-bcmath
sudo apt-get install php7.2-curl
sudo apt-get install apache2
sudo apt-get install mysql-server
curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
sudo apt-get install -y nodejs
sudo apt-get install gzip
sudo apt install composer
```

### Si exiten errores verificar version 7.2 de PHP como default
```bash
sudo update-alternatives --config php
```

###Paso 1 - Clonar repo y archivo de config

```bash
git clone https://github.com/techo/voluntariado-eventual.git
cd voluntariado-eventual
cp .env.example .env
```

###Paso 2 - Datos de conexión
Modificar datos de conexión, mail y app en archivo 

Editar: .env

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=TU_DB
DB_USERNAME=TU_USUARIO
DB_PASSWORD=TU_CONTRASEÑA
```

###Paso 3 - Instalación de dependencias y datos mínimos para funcionar
```bash
composer install
php artisan key:generate
npm install
npm run dev
```

###Paso 4 - Levantar servidor de desarrollo

```bash
php artisan serve
```

Ingresar a en el navegador a [localhost:8000](http://localhost:8000)

###Paso 5 (extra) - Verificar que pasan los tests

```bash
vendor/bin/phpunit
```


## Instalación para producción en Ubuntu 18.04

###Paso 1: Instalar requisitos previos 
```bash
sudo apt-get install python-software-properties
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php7.2
sudo apt-get install php7.2-mbstring
sudo apt-get install php7.2-zip
sudo apt-get install php7.2-xmlreader
sudo apt-get install php7.2-gd
sudo apt-get install php7.2-mysql
sudo apt-get install php7.2-imagick
sudo apt-get install php7.2-bcmath
sudo apt-get install php7.2-curl
sudo apt-get install apache2
sudo apt-get install mysql-server
curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
sudo apt-get install -y nodejs
sudo apt-get install gzip
sudo apt install composer
```

###Verificar version 7.2 de PHP
```bash
sudo update-alternatives --config php
```

###Paso 2: Clonar repo y copiar config
```bash
git clone https://github.com/techo/voluntariado-eventual.git
cd voluntariado-eventual
cp .env.example .env
```

###Paso 3: Configurar Mysql y Apache según necesidad

###Paso 4: Crear base de datos y configurar db
```bash
sudo mysql -u TU_USUARIO -p
CREATE DABATASE TU_DB;
```
###Paso 5: Completar archivo .env según configuración de mysql y apache

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=TU_DB
DB_USERNAME=TU_USUARIO
DB_PASSWORD=TU_CONTRASEÑA
```

###Paso 6: Instalar app

```bash
composer install
php artisan key:generate
php artisan migrate
npm install
php artisan vue-i18n:generate
npm run prod
chmod -R 775 storage
mkdir photos
mkdir photos/shares
mkdir photos/shares/thumbs 
chmod -R 777 photos/  
php artisan storage:link
```