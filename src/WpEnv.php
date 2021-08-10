<?php

declare(strict_types=1);

namespace Pollen\WpEnv;

use Pollen\Support\Env;
use Pollen\Support\Filesystem as fs;

class WpEnv
{
    /**
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        $basePath = fs::normalizePath($basePath);
        $isStandard = $this->isStandard($basePath);

        Env::load($basePath);

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
        $publicPath = fs::normalizePath($basePath . fs::DS . $publicDir);

        $debug = Env::get('WP_DEBUG', Env::get('APP_DEBUG'));
        define('WP_DEBUG', filter_var($debug ?? false, FILTER_VALIDATE_BOOLEAN));
        define('WP_DEBUG_LOG', filter_var(Env::get('WP_DEBUG_LOG', false), FILTER_VALIDATE_BOOLEAN));
        define('WP_DEBUG_DISPLAY', filter_var(Env::get('WP_DEBUG_DISPLAY', $debug), FILTER_VALIDATE_BOOLEAN));
        define('SCRIPT_DEBUG', filter_var(Env::get('SCRIPT_DEBUG', $debug), FILTER_VALIDATE_BOOLEAN));

        define('DB_NAME', Env::get('DB_DATABASE'));
        define('DB_USER', Env::get('DB_USERNAME'));
        define('DB_PASSWORD', Env::get('DB_PASSWORD'));
        $port = Env::get('DB_PORT');
        $host = Env::get('DB_HOST');
        define('DB_HOST', $host ? $host . ($port ? ':' . $port : '') : '127.0.0.1:3306');
        define('DB_CHARSET', Env::get('DB_CHARSET', 'utf8mb4'));
        define('DB_COLLATE', Env::get('DB_COLLATE', 'utf8mb4_unicode_ci'));
        global $table_prefix;
        $table_prefix = Env::get('DB_PREFIX') ?? 'wp_';

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $_SERVER['HTTPS'] = 'on';
        }

        define('AUTH_KEY', Env::get('AUTH_KEY', ''));
        define('SECURE_AUTH_KEY', Env::get('SECURE_AUTH_KEY', ''));
        define('LOGGED_IN_KEY', Env::get('LOGGED_IN_KEY', ''));
        define('NONCE_KEY', Env::get('NONCE_KEY', ''));
        define('AUTH_SALT', Env::get('AUTH_SALT', ''));
        define('SECURE_AUTH_SALT', Env::get('SECURE_AUTH_SALT', ''));
        define('LOGGED_IN_SALT', Env::get('LOGGED_IN_SALT', ''));
        define('NONCE_SALT', Env::get('NONCE_SALT', ''));

        defined('APP_WP_DIR') ?: define('APP_WP_DIR', Env::get('APP_WP_DIR', $isStandard ? '/' : 'wordpress'));
        define('WP_HOME', Env::get('APP_URL') ?? 'http://127.0.0.1:8000');
        define('WP_SITEURL', WP_HOME . '/' . APP_WP_DIR);

        $wpPublicDir = ltrim(rtrim(Env::get('APP_WP_PUBLIC_DIR', $isStandard ? 'wp-content' : '/'), '/'));
        define('WP_CONTENT_DIR', fs::normalizePath($publicPath . fs::DS . $wpPublicDir));
        define('WP_CONTENT_URL', WP_HOME . '/' . $wpPublicDir);

        define('EMPTY_TRASH_DAYS', Env::get('EMPTY_TRASH_DAYS', 7));

        define('WP_AUTO_UPDATE_CORE', Env::get('WP_AUTO_UPDATE_CORE', 'minor'));

        define('WP_POST_REVISIONS', Env::get('WP_POST_REVISIONS', 2));

        define('IMAGE_EDIT_OVERWRITE', filter_var(Env::get('IMAGE_EDIT_OVERWRITE', true), FILTER_VALIDATE_BOOLEAN));

        define('DISALLOW_FILE_EDIT', filter_var(Env::get('DISALLOW_FILE_EDIT', true), FILTER_VALIDATE_BOOLEAN));

        if (defined('WP_INSTALLING') && WP_INSTALLING === false) {
            define(
                'DISALLOW_FILE_MODS',
                filter_var(Env::get('DISALLOW_FILE_MODS', false), FILTER_VALIDATE_BOOLEAN)
            );
        }

        define('DISABLE_WP_CRON', filter_var(Env::get('DISABLE_WP_CRON', false), FILTER_VALIDATE_BOOLEAN));

        // https://make.wordpress.org/core/2019/04/16/fatal-error-recovery-mode-in-5-2/
        define('WP_DISABLE_FATAL_ERROR_HANDLER', Env::get('WP_DISABLE_FATAL_ERROR_HANDLER', false));

        define('WP_CACHE', Env::get('WP_CACHE', true));

        // Multisite
        define('WP_ALLOW_MULTISITE', filter_var(Env::get('WP_ALLOW_MULTISITE', false), FILTER_VALIDATE_BOOLEAN));
        define('MULTISITE', filter_var(Env::get('MULTISITE', false), FILTER_VALIDATE_BOOLEAN));
        if (defined('MULTISITE') && MULTISITE === true) {
            define('DOMAIN_CURRENT_SITE', Env::get('DOMAIN_CURRENT_SITE', ''));
            define('NOBLOGREDIRECT', Env::get('NOBLOGREDIRECT', '%siteurl%'));
            define('SUBDOMAIN_INSTALL', filter_var(Env::get('SUBDOMAIN_INSTALL', false), FILTER_VALIDATE_BOOLEAN));
            define('PATH_CURRENT_SITE', Env::get('PATH_CURRENT_SITE', ''));
            define('SITE_ID_CURRENT_SITE', filter_var(Env::get('SITE_ID_CURRENT_SITE', 1), FILTER_VALIDATE_INT));
            define('BLOG_ID_CURRENT_SITE', filter_var(Env::get('BLOG_ID_CURRENT_SITE', 1), FILTER_VALIDATE_INT));
            define('WP_DEFAULT_THEME', Env::get('WP_DEFAULT_THEME', 'twentytwentyone'));
        }

        if (!defined('ABSPATH')) {
            define('ABSPATH', fs::normalizePath($basePath . fs::DS . $publicDir . fs::DS . APP_WP_DIR) . fs::DS);
        }
    }

    /**
     * Check if Wordpress is installed in standard mode.
     *
     * @param string $basePath
     *
     * @return bool
     */
    protected function isStandard(string $basePath): bool
    {
        return file_exists($basePath . fs::DS . 'wp-admin') &&
            file_exists($basePath . fs::DS . 'wp-content') &&
            file_exists($basePath . fs::DS . 'wp-includes');
    }
}