UNBRANDED
=======
Copyright (c) 2016 Piethein Strengholt, piethein@strengholt-online.nl

Unbranded is an easy to use management solution to easily maintain and document regulatory, management and disclosure reports.
Unbranded features a lightweight fluid responsive design. It is written in JavaScript and PHP and uses the Laravel 5.1 and Twitter boostrap framework.

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
* `composer install --no-dev --optimize-autoloader`
* `php artisan migrate`
* `php artisan optimize`
* `php artisan route:optimize`
* `php artisan cache:clear`


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