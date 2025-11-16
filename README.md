## Description

Ogame clone writen on php/laravel and vue.js/NuxtJs

## Frameworks

Laravel (**https://laravel.com**)\
Nuxt.js (**https://nuxt.com**)

## System requirements:
- PHP 8.4 and higher
- MySQL 8.0 and higher
- NodeJS 24 and higher
- Composer

## Run in docker
`composer install`\
`docker-compose build --no-cache`\
`docker-compose up -d`\
`docker exec xnova php artisan migrate --seed`\
`docker exec xnova php artisan storage:link`\
`docker exec xnova php artisan basset:fresh`\
`docker exec xnova php artisan key:generate`

#### Login Information
login **admin@admin.com**\
password **password**