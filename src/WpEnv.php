<?php

declare(strict_types=1);

namespace Pollen\WpEnv;

use Dotenv\Dotenv;
use Pollen\Support\Env;
use Pollen\Support\Filesystem as fs;

class WpEnv
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        $this->basePath = fs::normalizePath($basePath);
    }

    /**
     * Load Wordpress config.
     * {@internal Used Dotenv and wp-config.local files.}
     *
     * @return Dotenv
     */
    public function load(): Dotenv
    {
        $isStandard = $this->isStandard();

        $loader = Env::load($this->basePath);

        if (!isset($GLOBALS['table_prefix'])) {
            $GLOBALS['table_prefix'] = Env::get('DB_PREFIX') ?? 'wp_';
        }

        $localConfig = fs::normalizePath("$this->basePath/wp-config.local.php");
        if (file_exists($localConfig)) {
            require_once $localConfig;
        }

        switch ($wpEnv = $_ENV['APP_ENV'] ?? 'production') {
            default :
                break;
            case 'dev':
                $wpEnv = 'development';
                break;
            case 'prod':
                $wpEnv = 'production';
                break;
        }
        defined('WP_ENVIRONMENT_TYPE') ?: define('WP_ENVIRONMENT_TYPE', $wpEnv);

        $publicDir = Env::get('APP_PUBLIC_DIR', $isStandard ? '/' : 'public');
        $publicPath = fs::normalizePath($this->basePath . fs::DS . $publicDir);

        // Debug
        $debug = Env::get('WP_DEBUG', Env::get('APP_DEBUG'));
        defined('WP_DEBUG') ?: define('WP_DEBUG', filter_var($debug ?? false, FILTER_VALIDATE_BOOLEAN));
        defined('WP_DEBUG_LOG') ?: define('WP_DEBUG_LOG', filter_var(Env::get('WP_DEBUG_LOG', false), FILTER_VALIDATE_BOOLEAN));
        defined('WP_DEBUG_DISPLAY') ?: define('WP_DEBUG_DISPLAY', filter_var(Env::get('WP_DEBUG_DISPLAY', $debug), FILTER_VALIDATE_BOOLEAN));
        defined('SCRIPT_DEBUG') ?: define('SCRIPT_DEBUG', filter_var(Env::get('SCRIPT_DEBUG', $debug), FILTER_VALIDATE_BOOLEAN));

        // Database
        defined('DB_NAME') ?: define('DB_NAME', Env::get('DB_DATABASE'));
        defined('DB_USER') ?: define('DB_USER', Env::get('DB_USERNAME'));
        defined('DB_PASSWORD') ?: define('DB_PASSWORD', Env::get('DB_PASSWORD'));
        $port = Env::get('DB_PORT');
        $host = Env::get('DB_HOST');
        defined('DB_HOST') ?: define('DB_HOST', $host ? $host . ($port ? ':' . $port : '') : '127.0.0.1:3306');
        defined('DB_CHARSET') ?: define('DB_CHARSET', Env::get('DB_CHARSET', 'utf8mb4'));
        defined('DB_COLLATE') ?: define('DB_COLLATE', Env::get('DB_COLLATE', 'utf8mb4_unicode_ci'));
        if (!isset($GLOBALS['table_prefix'])) {
            $table_prefix = Env::get('DB_PREFIX') ?? 'wp_';
        }

        // Https
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $_SERVER['HTTPS'] = 'on';
        }

        // Salt
        defined('AUTH_KEY') ?: define('AUTH_KEY', Env::get('AUTH_KEY', ''));
        defined('SECURE_AUTH_KEY') ?: define('SECURE_AUTH_KEY', Env::get('SECURE_AUTH_KEY', ''));
        defined('LOGGED_IN_KEY') ?: define('LOGGED_IN_KEY', Env::get('LOGGED_IN_KEY', ''));
        defined('NONCE_KEY') ?: define('NONCE_KEY', Env::get('NONCE_KEY', ''));
        defined('AUTH_SALT') ?: define('AUTH_SALT', Env::get('AUTH_SALT', ''));
        defined('SECURE_AUTH_SALT') ?: define('SECURE_AUTH_SALT', Env::get('SECURE_AUTH_SALT', ''));
        defined('LOGGED_IN_SALT') ?: define('LOGGED_IN_SALT', Env::get('LOGGED_IN_SALT', ''));
        defined('NONCE_SALT') ?: define('NONCE_SALT', Env::get('NONCE_SALT', ''));

        // Miscellaneous
        defined('EMPTY_TRASH_DAYS') ?: define('EMPTY_TRASH_DAYS', Env::get('EMPTY_TRASH_DAYS', 7));
        defined('WP_AUTO_UPDATE_CORE') ?: define('WP_AUTO_UPDATE_CORE', Env::get('WP_AUTO_UPDATE_CORE', 'minor'));
        defined('WP_POST_REVISIONS') ?: define('WP_POST_REVISIONS', Env::get('WP_POST_REVISIONS', 2));
        defined('IMAGE_EDIT_OVERWRITE') ?: define('IMAGE_EDIT_OVERWRITE', filter_var(Env::get('IMAGE_EDIT_OVERWRITE', true), FILTER_VALIDATE_BOOLEAN));
        defined('DISALLOW_FILE_EDIT') ?: define('DISALLOW_FILE_EDIT', filter_var(Env::get('DISALLOW_FILE_EDIT', true), FILTER_VALIDATE_BOOLEAN));
        if (defined('WP_INSTALLING') && WP_INSTALLING === false) {
            defined('DISALLOW_FILE_MODS') ?: define(
                'DISALLOW_FILE_MODS',
                filter_var(Env::get('DISALLOW_FILE_MODS', false), FILTER_VALIDATE_BOOLEAN)
            );
        }
        defined('DISABLE_WP_CRON') ?: define('DISABLE_WP_CRON', filter_var(Env::get('DISABLE_WP_CRON', false), FILTER_VALIDATE_BOOLEAN));
        // @see https://make.wordpress.org/core/2019/04/16/fatal-error-recovery-mode-in-5-2/
        defined('WP_DISABLE_FATAL_ERROR_HANDLER') ?: define('WP_DISABLE_FATAL_ERROR_HANDLER', Env::get('WP_DISABLE_FATAL_ERROR_HANDLER', false));
        defined('WP_CACHE') ?: define('WP_CACHE', Env::get('WP_CACHE', true));

        // Path
        defined('APP_WP_DIR') ?: define('APP_WP_DIR', Env::get('APP_WP_DIR', $isStandard ? '/' : 'wordpress'));
        defined('WP_HOME') ?: define('WP_HOME', Env::get('APP_URL') ?? 'http://127.0.0.1:8000');
        defined('WP_SITEURL') ?: define('WP_SITEURL', WP_HOME . '/' . APP_WP_DIR);
        $wpPublicDir = ltrim(rtrim(Env::get('APP_WP_PUBLIC_DIR', $isStandard ? 'wp-content' : '/'), '/'));
        defined('WP_CONTENT_DIR') ?: define('WP_CONTENT_DIR', fs::normalizePath($publicPath . fs::DS . $wpPublicDir));
        defined('WP_CONTENT_URL') ?: define('WP_CONTENT_URL', WP_HOME . '/' . $wpPublicDir);
        defined('ABSPATH') ?: define('ABSPATH', fs::normalizePath($this->basePath . fs::DS . $publicDir . fs::DS . APP_WP_DIR) . fs::DS);

        // Multisite
        defined('WP_ALLOW_MULTISITE') ?: define('WP_ALLOW_MULTISITE', filter_var(Env::get('WP_ALLOW_MULTISITE', false), FILTER_VALIDATE_BOOLEAN));
        defined('MULTISITE') ?: define('MULTISITE', filter_var(Env::get('MULTISITE', false), FILTER_VALIDATE_BOOLEAN));
        if (defined('MULTISITE') && MULTISITE === true) {
            defined('DOMAIN_CURRENT_SITE') ?: define('DOMAIN_CURRENT_SITE', Env::get('DOMAIN_CURRENT_SITE', ''));
            defined('NOBLOGREDIRECT') ?: define('NOBLOGREDIRECT', Env::get('NOBLOGREDIRECT', '%siteurl%'));
            defined('SUBDOMAIN_INSTALL') ?: define('SUBDOMAIN_INSTALL', filter_var(Env::get('SUBDOMAIN_INSTALL', false), FILTER_VALIDATE_BOOLEAN));
            defined('PATH_CURRENT_SITE') ?: define('PATH_CURRENT_SITE', Env::get('PATH_CURRENT_SITE', ''));
            defined('SITE_ID_CURRENT_SITE') ?: define('SITE_ID_CURRENT_SITE', filter_var(Env::get('SITE_ID_CURRENT_SITE', 1), FILTER_VALIDATE_INT));
            defined('BLOG_ID_CURRENT_SITE') ?: define('BLOG_ID_CURRENT_SITE', filter_var(Env::get('BLOG_ID_CURRENT_SITE', 1), FILTER_VALIDATE_INT));
            defined('WP_DEFAULT_THEME') ?: define('WP_DEFAULT_THEME', Env::get('WP_DEFAULT_THEME', 'twentytwentyone'));
        }

        return $loader;
    }

    /**
     * Check if Wordpress is installed in standard mode.
     *
     * @return bool
     */
    protected function isStandard(): bool
    {
        return file_exists($this->basePath . fs::DS . 'wp-admin') &&
            file_exists($this->basePath . fs::DS . 'wp-content') &&
            file_exists($this->basePath . fs::DS . 'wp-includes');
    }
}