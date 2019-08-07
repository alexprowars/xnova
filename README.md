## Description

SPA ogame clone writen on php and vue.js

## Demo

https://xnova.su/  
https://x.xnova.su/

## Frameworks

Phalcon (**https://phalconphp.com/en/**)  
Vue.js (**https://vuejs.org/**)  
Nuxt.js (**https://nuxtjs.org/**)  

## System requirements:
- PHP 7.0 and higher
- MySQL 5.7 and higher
- Phalcon 3.3 and higher
- NodeJs 10 and higher

## Installation
- Install **Phalcon** framework on server (**https://phalconphp.com/ru/download**)
- Configure Nginx as proxy (sample config install/nginx.conf)
- Upload database **install/db.sql**
- Rename config file **app/config/_.core.ini** to **core.ini**
- Install NodeJs dependencies **npm install**
- Install Composer dependencies **composer install**
- Install crontab **install/cron.conf**

## Usage

#### Development:
**npm run dev**  
  
#### Production:
**npm run build**  
**npm run start** or use PM2 (**http://pm2.keymetrics.io/**)

#### Login Information
login **admin@xnova.su**  
password **123456**

## Chat launch
- Get ssl certificate (example **letsencrypt**)
- Fill in the settings in the section **[chat]** in **app/config/core.ini**
- Start daemon **node chat/chat.js** or use PM2 (**http://pm2.keymetrics.io/**)