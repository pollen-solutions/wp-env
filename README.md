# Wordpress Env Component

[![Latest Stable Version](https://img.shields.io/packagist/v/pollen-solutions/wp-env.svg?style=for-the-badge)](https://packagist.org/packages/pollen-solutions/wp-env)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)](LICENSE.md)
[![PHP Supported Versions](https://img.shields.io/badge/PHP->=7.4-8892BF?style=for-the-badge&logo=php)](https://www.php.net/supported-versions.php)

Pollen Solutions **Wordpress Env** Component provides a simply way to configure your Wordpress application.

## Installation

```bash
composer require pollen-solutions/wp-env
```

Replace the contents of wp-config.php file with the code below :

```php
use Pollen\WpEnv\WpEnv;

// Optionnal but recommended start time global indicator
defined('START_TIME') ?: define('START_TIME', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

new WpEnv(__DIR__);

require_once(ABSPATH . 'wp-settings.php');
```

## Configuration

### .env config file

#### Fondamentals

Create a .env file at the root of your project :

```dotenv
# ENVIRONMENT
## Environnement of your application. 
## dev|prod. 
APP_ENV=dev

## Enabling debug
APP_DEBUG=true

## Url of your application
APP_URL=https://127.0.0.1:8000

## Application timezone
APP_TIMEZONE=Europe/Paris

# PATH (Optionnal)
## Relative public path of your application 
APP_PUBLIC_DIR=/
## Relative path to the wordpress root folder, containing wp-admin and wp-includes folder 
APP_WP_DIR=/
## Relative path to the wp-content root folder, containing languages, themes, uploads ...
## MUST BE WRITABLE BY HTTP SERVER 
APP_WP_PUBLIC_DIR=wp-content

# DATABASE
## DATABASE DRIVER
DB_CONNECTION=mysql
## DATABASE HOST
DB_HOST=127.0.0.1
## DATABASE PORT
DB_PORT=3306
## DATABASE NAME
DB_DATABASE=wordpress
## DATABASE USERNAME
DB_USERNAME=root
## DATABASE PASSWORD
DB_PASSWORD=root
## DATABASE TABLES PREFIX
DB_PREFIX=wp_

# WORDPRESS CONFIG
# @see https://wordpress.org/support/article/editing-wp-config-php/
WP_DEBUG_LOG=true
DISALLOW_FILE_MODS=true
AUTOMATIC_UPDATER_DISABLED=false
DISABLE_WP_CRON=false

## WORDPRESS SALT
## @see https://developer.wordpress.org/reference/functions/wp_salt/
## @see https://api.wordpress.org/secret-key/1.1/salt/
## Generate salt from cli :
## php vendor/bin/wp-salt dotenv --clean >> .env
```

#### Nesting Variables

It's possible to nest an environment variable within another, useful to cut down on repetition.

This is done by wrapping an existing environment variable in ${…} e.g.

```dotenv
BASE_DIR=/var/webroot/project-root
CACHE_DIR=${BASE_DIR}/cache
TMP_DIR=${BASE_DIR}/tmp
```

#### Overwriting

It's possible to overwrite environment variable within another throught .env.local file.

```dotenv
# > .env file
BASE_DIR=/var/webroot/common-project-root
```

```dotenv
# > .env.local file
BASE_DIR=/var/webroot/local-project-root
```

### .wp-config.local config file

Same as the original wp-config.php file, the wp-config.local.php file at the root of your project allow to define 
constants of your Wordpress application.

```php
use Pollen\Support\Env;
use Pollen\Support\Filesystem as fs;

// LOGS
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', getcwd() . fs::DS . 'var' . fs::DS . 'log' . fs::DS . 'error.log');
}

// AUTHENTICATION
if (!defined('COOKIE_DOMAIN')) {
    define('COOKIE_DOMAIN', Env::get('DOMAIN_CURRENT_SITE'));
}
if (!defined('COOKIEPATH')) {
    define('COOKIEPATH', '/');
}
if (!defined('SITECOOKIEPATH')) {
    define('SITECOOKIEPATH', '/');
}
```