# API RESSOURCE OPERATIONNELLE
## Prerequisites
- [Php 8.1 or higher](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)
- [MySQL](https://www.mysql.com/fr/)
## Installation
- Clone the project
- Run `composer install`
## Configuration
- Create a `.env.local` file and copy the content of `.env` with your own configuration
- Create a database by running `php bin/console doctrine:database:create`
- Run `php bin/console doctrine:migrations:migrate`
## Fixtures
- Run `php bin/console doctrine:fixtures:load`
## Run the project
- Run `symfony serve`