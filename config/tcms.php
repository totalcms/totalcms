<?php

/**
 * Total CMS Configuration
 *
 * Override default settings here. This file is loaded automatically for
 * Composer-based installations and deep-merged into the defaults — you
 * only need to specify the keys you want to change.
 *
 * See https://docs.totalcms.co for the full list of available settings.
 *
 * ----------------------------------------------------------------------
 * Path overrides
 * ----------------------------------------------------------------------
 * By default, writable directories are placed at the project root
 * (alongside `public/`):
 *
 *   <project>/cache/         cache files
 *   <project>/logs/          log files
 *   <project>/tmp/           temp files
 *   <project>/tcms-data/     content storage  (above docroot)
 *   <project>/public/tcms-data/             content storage  (in docroot)
 *
 * That layout assumes `public/index.php` is the front controller. If you
 * instead host T3 at a subpath — e.g. `public/tcms/index.php` — the
 * front controller's `define('TCMS_PROJECT_ROOT', dirname(__DIR__))` will
 * resolve to `public/` itself, and the writable directories above will be
 * created INSIDE the document root. That's almost certainly not what you
 * want. Two ways to fix:
 *
 *   1. Update the front controller to point one level higher:
 *        define('TCMS_PROJECT_ROOT', dirname(__DIR__, 2));
 *      (cleanest — TCMS_PROJECT_ROOT then lands at the actual project root)
 *
 *   2. Override each path explicitly here. Useful when the project
 *      root and your "writable storage" location aren't the same dir
 *      (e.g. docker-style deployments where logs go to a mounted volume).
 *
 * Example for a subpath install:
 *   return [
 *       'cachedir' => __DIR__ . '/../cache',
 *       'tmpdir'   => __DIR__ . '/../tmp',
 *       'logger'   => [
 *           'path' => __DIR__ . '/../logs',
 *       ],
 *       'datadir'  => __DIR__ . '/../tcms-data',
 *   ];
 *
 * ----------------------------------------------------------------------
 * Other commonly overridden settings
 * ----------------------------------------------------------------------
 *   'debug' => true,                         enable error display
 *   'env'   => 'dev' | 'prod' | 'preview',   environment flag
 *   'logger' => ['level' => Monolog\Level::Debug],
 *   'sentry' => false,                       disable Sentry error tracking
 */

return [];
