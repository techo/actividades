# voluntariado-eventual

La plataforma de TECHO para organizar actividades de voluntariado.

Desarrollada en alianza con Globallogic y publicada como software libre.

Podés verla en uso [acá](https://actividades.techo.org) y colaborar [reportando errores o proponiendo mejoras](https://github.com/techo/voluntariado-eventual/issues).

## Stack
La plataforma está creada con Laravel 5.5 + VueJS 2,
PHP 7.2+,
MySQL 5.7+

## Instalación para desarrollo (Docker)

### Instalar herramientas
- [git](https://git-scm.com/downloads)
- [docker](https://docs.docker.com/install/)
- [docker-compose](https://docs.docker.com/compose/install/)
- [composer](https://getcomposer.org/)
- [nodejs](https://nodejs.org/es/download/package-manager/)

### Instalar aplicación
    git clone https://github.com/techo/voluntariado-eventual.git
    cd voluntariado-eventual
    cp .env.docker .env
    mkdir photos photos/shares photos/shares/thumbs
    chmod u+w photos
    npm install
    npm run dev
    export UID && docker-compose up

#### En otra terminal
    docker-compose exec app php composer.phar install
    docker-compose exec app php artisan key:generate
    docker-compose exec app php artisan migrate --seed

#### Abrir en navegador
    http://localhost:8080/


## Instalación para producción (Debian/Ubuntu)

### Dependencias
    sudo apt-get install python-software-properties
    sudo add-apt-repository ppa:ondrej/php
    sudo apt-get update
    sudo apt-get install php7.2 php7.2-mbstring php7.2-zip php7.2-xmlreader php7.2-gd php7.2-mysql php7.2-imagick
    sudo apt-get install apache2
    sudo apt-get install mysql-server
    curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
    sudo apt-get install -y nodejs
    sudo apt-get install gzip
    sudo apt install composer

### Instalar la aplicación

    Clonar repositorio
    Copiar archivo .env.example como .env
    Modificar datos de conexión, mail y app en archivo .env
    Crear base de datos
    mysql -u [usuario] -p [nombre base] < db.sql
    rm db.sql
    $ composer install
    $ php artisan key:generate
    $ php artisan migrate --seed
    $ npm install
    $ npm run dev
    $ chmod -R 775 storage
    $ mkdir photos
    $ mkdir photos/shares
    $ mkdir photos/shares/thumbs
    $ chmod -R 777 photos/
