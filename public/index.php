<?php

/**
 * Total CMS - Web Entry Point (Composer Install)
 *
 * Front controller for Composer-based installations. All non-file
 * requests are routed here by .htaccess (or the equivalent for nginx /
 * Caddy) and dispatched by Slim. The setup wizard, admin UI, builder
 * pages, and API all live behind this single entry point.
 *
 * TCMS_PROJECT_ROOT is the *default* origin for writable directories
 * (tcms-data, cache, logs, tmp) and is also where the loader looks for
 * config/tcms.php. Anything specified inside config/tcms.php overrides
 * those defaults individually (deep-merged), so you can keep this
 * constant as the project root and still relocate single dirs as
 * needed.
 *
 * For a subpath layout (e.g. public/tcms/index.php) bump the dirname
 * depth so the constant still lands at the project root —
 * `dirname(__DIR__, 2)` for one level deeper.
 */

define('TCMS_PROJECT_ROOT', dirname(__DIR__));

require TCMS_PROJECT_ROOT . '/vendor/autoload.php';

(require \TotalCMS\Support\PathResolver::packageRoot() . '/config/bootstrap.php')->run();
