php artisan down
git pull
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
