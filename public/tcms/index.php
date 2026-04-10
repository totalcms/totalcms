<?php

/**
 * Total CMS - Web Entry Point (Composer Install)
 *
 * This file bootstraps Total CMS for Composer-based installations.
 * TCMS_PROJECT_ROOT tells the CMS where writable directories live.
 */

define('TCMS_PROJECT_ROOT', dirname(__DIR__, 2));

require __DIR__ . '/../../vendor/autoload.php';

// Redirect root to admin
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$basePath    = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($requestPath !== false && rtrim((string) $requestPath, '/') === rtrim($basePath, '/')) {
	header('Location: ' . rtrim((string) $requestPath, '/') . '/admin', true, 301);
	exit;
}

(require \TotalCMS\Support\PathResolver::packageRoot() . '/config/bootstrap.php')->run();
