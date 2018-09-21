# Plataforma de Actividades TECHO

La plataforma de TECHO para organizar actividades de voluntariado.

Desarrollada en alianza con [Globallogic](https://www.globallogic.com/latam/) y publicada como software libre.

Podés verla en uso [acá](https://actividades.techo.org) y [colaborar reportando errores, proponiendo mejoras y desarrollando](https://github.com/techo/voluntariado-eventual/blob/master/CONTRIBUTING.md).

## Stack
La plataforma está creada con

- Laravel 5.5+
- VueJS 2
- PHP 7.2+,
- MySQL 5.7+

## Instalación para desarrollo (Docker)

### Instalar requisitos (depende de tu plataforma)
- [git](https://git-scm.com/downloads)
- [docker](https://docs.docker.com/install/)
- [docker-compose](https://docs.docker.com/compose/install/)
- [nodejs/npm](https://nodejs.org/es/download/package-manager/)

Instrucciones detalladas para [Ubuntu Bionic 18.04 (LTS) x86_64 / amd64](#instalación-requisitos-ubuntu-bionic)

### levantar el entorno
    git clone https://github.com/techo/voluntariado-eventual.git
    cd voluntariado-eventual
    npm install
    npm run prod
    cp .env.docker .env
    mkdir photos photos/shares photos/shares/thumbs
    export UID && docker-compose up

### instalar la aplicación (con el entorno funcionando)

    cd voluntariado-eventual
    curl -sS https://getcomposer.org/installer | php
    docker-compose exec app php composer.phar install
    docker-compose exec app php artisan key:generate
    docker-compose exec app php artisan migrate --seed

### como acceder

    Abrir en un navegador http://localhost:8080/

### Instalación Requisitos Ubuntu Bionic

#### git
    sudo apt install git

#### docker-ce
    sudo apt-get remove docker docker-engine docker.io
    sudo apt-get update
    sudo apt-get install apt-transport-https ca-certificates curl software-properties-common
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
    sudo apt-key fingerprint 0EBFCD88
    sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
    sudo apt-get update
    sudo apt-get install docker-ce

#### docker-compose
    sudo curl -L "https://github.com/docker/compose/releases/download/1.22.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose

#### npm 10+
    sudo apt-get remove nodejs node npm
    curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
    sudo apt-get install -y nodejs
