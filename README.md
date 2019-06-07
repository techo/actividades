# Plataforma de voluntariado eventual 
[![Build Status](https://travis-ci.org/techo/voluntariado-eventual.svg?branch=master)](https://travis-ci.org/techo/voluntariado-eventual)

Online en: [actividades.techo.org](https://actividades.techo.org)

## Instalación para desarrollo

###Pre-Requisitos:
- PHP 7.2
- Composer
- Mysql 5.7
- Nodejs 10 y npm

###Usa
- [Laravel 5.5](https://laravel.com/docs/5.5)
- [VueJS 2](https://vuejs.org/v2/guide/)

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
php artisan migrate --seed
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

###Usuarios de prueba que vienen para desarrollo
- administrador@administrador.com (administrador)
- coordinador@coordinador.com (coordinador)

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
sudo apt-get install apache2
sudo apt-get install mysql-server
curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
sudo apt-get install -y nodejs
sudo apt-get install gzip
sudo apt install composer
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
npm run prod
chmod -R 775 storage
mkdir photos
mkdir photos/shares
mkdir photos/shares/thumbs 
chmod -R 777 photos/  
```