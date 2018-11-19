# voluntariado-eventual

## Requisitos previos del servidor
- sudo apt-get install python-software-properties
- sudo add-apt-repository ppa:ondrej/php
- sudo apt-get update
- sudo apt-get install php7.2
- sudo apt-get install php7.2-mbstring
- sudo apt-get install php7.2-zip
- sudo apt-get install php7.2-xmlreader
- sudo apt-get install php7.2-gd
- sudo apt-get install php7.2-mysql
- sudo apt-get install php7.2-imagick
- sudo apt-get install apache2
- sudo apt-get install mysql-server
- curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
- sudo apt-get install -y nodejs
- sudo apt-get install gzip
- sudo apt install composer

## Instalación:

- Clonar repositorio
- Copiar archivo .env.example como .env
- Modificar datos de conexión, mail y app en archivo .env
- Crear base de datos
- gzip -d test_db.sql.gz
- mysql -u [usuario] -p [nombre base] < test_db.sql
- $ composer install
- $ php artisan key:generate
- $ php artisan migrate --seed
- $ npm install
- $ npm run dev
- $ chmod -R 775 storage
- $ mkdir photos
- $ mkdir photos/shares
- $ mkdir photos/shares/thumbs 
- $ chmod -R 777 photos/  

## Stack
La plataforma está creada con Laravel 5.5 + VueJS 2,
PHP 7.2+,
MySQL 5.7+
