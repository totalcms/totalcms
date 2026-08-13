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
 * IMPORTANT: this file is not the last word
 * ----------------------------------------------------------------------
 * Settings merge in this order, each layer beating the one above it:
 *
 *   1. config/defaults.php                 shipped defaults
 *   2. config/tcms.php                     THIS FILE
 *   3. <docroot>/tcms.php                  installation overrides
 *   4. tcms-data/.system/settings.json     the admin Settings pages
 *
 * The admin UI is merged LAST, so a setting saved in the browser beats the
 * same key set here. That is deliberate — a value an operator changed in
 * the admin should not be silently reverted by a file they may not know
 * exists.
 *
 * So: if a setting has a control in the admin, change it THERE. Setting it
 * here will look like it did nothing. There is no error and no log line —
 * this file still loads and its other keys still apply — so the only
 * symptom is a value that stubbornly refuses to change.
 *
 * This applies per key, not per block: settings.json holds only the fields
 * the admin pages actually expose and you have saved, so a nested key the
 * UI does not manage still takes the value you set here even when a
 * sibling key does not. To check whether the admin already owns a setting:
 *
 *   grep -n <settingName> tcms-data/.system/settings.json
 *
 * A hit means go change it in the admin instead.
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
 *   'env'   => 'dev' | 'prod' | 'preview',   environment flag  (also in admin)
 *   'logger' => ['level' => Monolog\Level::Debug],
 *   'sentry' => false,                       error tracking     (also in admin)
 *
 * The two marked "also in admin" have controls on the Settings pages, so
 * per the precedence note above, a saved value there wins over this file.
 * Set them in the admin unless you specifically need them fixed in code —
 * pinning `env` here, for example, so a staging deploy cannot be flipped to
 * production from the browser.
 */

return [];
