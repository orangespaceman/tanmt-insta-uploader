# tanmt insta uploader

Connect to API endpoint, check for new images, post to insta if found.

This repo contains both a JS and a PHP implementation


## Setup

- Deploy somewhere (e.g. a Pi)

### PHP version
- `composer install` to install dependencies
- duplicate `config.example.php` and call it `config.php`, populate fields
- call `cron.php` (with a cron)

### JS version

- `npm install` to install dependencies
- duplicate `config.example.json` and call it `config.json`, populate fields
- call `uploader-js/upload.js` (with a cron)