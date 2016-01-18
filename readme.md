UNBRANDED
=======
Copyright (c) 2016 Piethein Strengholt, piethein@strengholt-online.nl

Unbranded is an easy to use management solution to easily maintain and document regulatory, management and disclosure reports.
Unbranded features a lightweight fluid responsive design. It is written in PHP/Laravel + jQuery / HTML / CSS (Bootstrap).

REQUIREMENTS
------------
* PHP >= 5.5.9
* OpenSSL PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension
* Composer
* Git

DEPLOYMENT
------------
* install composer: `curl -sS https://getcomposer.org/installer | php — –filename=composer`
* ssh to the machine, go the www directory
* clone the repository: `git clone https://github.com/pietheinstrengholt/laravel.git .`
* run `composer install --no-dev --optimize-autoloader`
* copy the `.env.example` to `.env` and configure with the correct database settings
* deploy the database, use the following command: `php artisan migrate:refresh --seed`
* run `php artisan optimize`
* run `php artisan route:optimize`
* run `php artisan cache:clear`
* run `chmod -R 777 storage/`
* run `composer dump-autoload`


TODO
------------
* manual page per template (pdf?)
* reference tables?
* cleanup EventHandler / of via Middleware
* builder rechten via Middleware?
* system config?
* definitions / hovers?
* hyperlinks after submission of a change request
* superadmin can approve own changes
* create new template -> automatisch juiste section selecteren?
* in de log section_id, template_id, user_id toevoegen ipv sectionName
* use YourModel::create(Input::all());
* submit template, section, user object to event
* t_terms with lookup function
* cleanup database create script
* opschonen archive fuctie, letterlijk kopie maken van de content naar archive
* testen deployment script
* rechten admin, section mogen dan aangepast worden. templates, namen mogen dan ook aangepast worden
* t_settings with viewComposer
* dialoog met laatste aanpassingen op homescreen
* backdoor voor superadmin
* alt/pop-up (title="" on div) message wie content voor het laatst heeft aangepast.