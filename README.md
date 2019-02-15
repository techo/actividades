# Plataforma de Actividades TECHO

## Stack
La plataforma está creada con 
- Laravel 5.5 
- VueJS 2
- PHP 7.2
- MySQL 5.7

# Instalación para Ubuntu 18.04

## Requisitos previos del servidor

```
sudo apt install php7.2 php7.2-mbstring php7.2-zip php7.2-xmlreader php7.2-gd php7.2-mysql php7.2-imagick
sudo apt install mysql-server composer libpng-dev curl

# https://github.com/nodesource/distributions/blob/master/README.md#debinstall
curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
sudo apt-get install -y nodejs
```

## Instalación

### Clonar repositorio
`git clone https://github.com/techo/voluntariado-eventual/`

### Crear base de datos
```
CREATE DATABASE actividades;
GRANT ALL PRIVILEGES ON actividades.* TO actividades@localhost IDENTIFIED BY 'actividades';
```

### Ingresar al directorio
`cd voluntariado-eventual`

### Copiar archivo de configuración
`cp .env.example .env`

### Modificar datos de conexión, mail y app en archivo .env
`nano .env`

### Instalar dependencias
```
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
```

### Iniciar en modo desarrollo
`php artisan serve`


