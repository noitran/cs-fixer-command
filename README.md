PhpCsCommand
====================

<p align="center">
<a href="https://scrutinizer-ci.com/g/noitran/cs-fixer-command/code-structure"><img src="https://img.shields.io/scrutinizer/coverage/g/noitran/cs-fixer-command.svg?style=flat-square" alt="Coverage Status"></img></a>
<a href="https://scrutinizer-ci.com/g/noitran/cs-fixer-command"><img src="https://img.shields.io/scrutinizer/g/noitran/cs-fixer-command.svg?style=flat-square" alt="Quality Score"></img></a>
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></img></a>
<a href="https://github.com/noitran/cs-fixer-command/releases"><img src="https://img.shields.io/github/release/noitran/cs-fixer-command.svg?style=flat-square" alt="Latest Version"></img></a>
<a href="https://packagist.org/packages/iocaste/cs-fixer-command"><img src="https://img.shields.io/packagist/dt/iocaste/cs-fixer-command.svg?style=flat-square" alt="Total Downloads"></img></a>
</p>

## About

Lumen/Laravel wrapper around PHPCS-Fixer package that allows, using artisan command, easly apply pre-defined coding standards to your laravel or lumen project.

Wrapper uses [PHP Coding Standards Fixer package](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

List of all fixer rules can be found [here](https://mlocati.github.io/php-cs-fixer-configurator)
 
## Features

* Adds console command `php artisan phpcs:fix` that fixes your project to follow standards defined in your `config/phpcs.php`
* Adds command to install pre-commit git hook `php artisan phpcs:install-hook`. After install phpcs:fix will be triggered before each commit and will auto fix your code

## Install

* Install as composer package

```bash
$ composer require noitran/cs-fixer-command
```

#### Laravel

* Laravel uses provider auto discovery. Config file can be published using command

```
$ artisan vendor:publish --provider="Noitran\CsFixer\CsFixerServiceProvider"
```

#### Lumen

* Open your bootstrap/app.php and register as service provider

```php
$app->register(Noitran\CsFixer\CsFixerServiceProvider::class);
```

* Config file should be loaded manually in bootstrap/app.php

```php
$app->configure('phpcs');
```

## Usage

* Publish `phpcs` and change if necessary, run command for inspection `php artisan phpcs:fix`

* Hook installation can be done using command `php artisan phpcs:install-hook`

* Hook install command can be added into `composer.json` as post-install command, so that, at example, other team members will get pre-commit hook when installing composer dependencies at first time.

#### Example: Adding as composer post-install command

```json
{
    "name": "laravel/lumen",
    "require": {
        ...
    },
    "autoload": {
        ...
    },
    "scripts": {
        "post-install-cmd": [
            "php artisan phpcs:install-hook"
        ],
        "post-update-cmd": [
            ...
        ]
    }
}
```
