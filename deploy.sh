#!/bin/bash
cd html
php artisan down
git status
git clean -n -d
git clean -f -d
git reset --hard origin/master
git pull
git log --pretty="%h" -n1 HEAD > version
composer install
php artisan migrate
php artisan config:clear
php artisan cache:clear
#php artisan route:cache
chown www-data:www-data * -R
chown www-data:www-data .env -R
chown www-data:www-data public/.htaccess -R
chmod -R 777 storage/
php artisan up
