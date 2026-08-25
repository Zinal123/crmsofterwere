<?php

namespace App\Support;

/**
 * Shared literal defaults referenced from procedural files (routes/web.php,
 * config/*.php) that get `require`d fresh on every application boot within
 * the same PHP process (e.g. once per test) - a top-level `const` in those
 * files itself would fatal with "already defined" on the second boot, but a
 * class constant is safe since PHP's autoloader only loads a class once per
 * process no matter how many times the file that references it is required.
 */
class ConfigDefaults
{
    public const LOCAL_HOST = '127.0.0.1';
    public const LOG_PATH = 'logs/laravel.log';
    public const VENDOR_ID_ROUTE = 'admin/vendors/{id}';
}
