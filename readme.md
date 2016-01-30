RED PEPPER REPORTING FRAMEWORK
=======
Copyright (c) 2016 Piethein Strengholt, piethein@strengholt-online.nl

Red Pepper Reporting Framework is an easy to use management solution to easily maintain and document regulatory, management and disclosure reports.
Red Pepper Reporting Framework features a lightweight fluid responsive design. It is written in PHP/Laravel + jQuery / HTML / CSS (Bootstrap).

REQUIREMENTS
------------
* PHP >= 5.5.9
* OpenSSL PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension
* Composer
* Git

INITIAL DEPLOYMENT
------------
* install composer: `curl -sS https://getcomposer.org/installer | php — –filename=composer`
* ssh to the machine, go the www directory
* clone the repository: `git clone https://github.com/pietheinstrengholt/laravel.git .`
* run `composer install --no-dev --optimize-autoloader` , use your github key when asked.
* copy the `.env.example` to `.env` and configure with the correct database settings. If localhost doesn't work, try 127.0.0.1 instead.
* run `php artisan key:generate` to generate an unique key. Add this key to the .env configuration file
* deploy the database, use the following command: `php artisan migrate:refresh --seed`
* run `php artisan optimize`
* run `php artisan route:optimize`
* run `php artisan cache:clear`
* run `chmod -R 777 storage/`
* run `composer dump-autoload`
* in case the apache user needs rights, use `chown apache:apache * -R`

UPDATING
------------
* run `git pull`
* to save your credentials permanently using the following: `git config credential.helper store`
* run `composer install`
* run `php artisan migrate`
* run `composer dump-autoload`
* run `php artisan cache:clear`
* run `chmod -R 777 storage/`

TODO
------------
* manual page per template (pdf?)
* reference tables?
* definitions / hovers?
* hyperlinks after submission of a change request
* create new template -> automatisch juiste section selecteren?
* opschonen archive fuctie, letterlijk kopie maken van de content naar archive
* testen deployment script
* backdoor for superadmin?
* style tooltip in cell.blade.php
* highlight search results, e.g. when searched for 'test', highlight this word
* when changerequest is approved, compare with history instead of current content
* reopen changerequest
* select all for rights
* select an user to give a notification when submitting content
* make email configurable
