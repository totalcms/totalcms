#!/usr/bin/env bash
#
# Total CMS deploy script.
#
# Run this on the server after a fresh checkout / pull. Edit the bits in
# the "Server-specific" block to match your environment, then leave the
# rest alone — the standard pipeline is:
#
#   1. Install production PHP dependencies
#   2. Build the frontend asset pipeline
#   3. Hand off to `tcms deploy` for runtime cleanup the library knows
#      how to do safely (compiled DI container wipe, app cache clear,
#      pending migrations, CLI OPcache reset)
#   4. Reload PHP-FPM so its OPcache picks up the new files (CLI can't
#      reach FPM's OPcache — separate process, separate cache)
#
# If your stack doesn't use PHP-FPM, or you set opcache.validate_timestamps=1, skip step 4.

set -euo pipefail

# ─────────────────────────────────────────────────────────────────────
# Standard pipeline — edit at your own risk
# ─────────────────────────────────────────────────────────────────────

PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PROJECT_ROOT"

echo "→ Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction --no-progress

if [ -d frontend ]; then
	echo "→ Building frontend assets"
	(
		cd frontend
		npm ci --no-audit --no-fund --silent
		npm run build
	)
fi

echo "→ Running Total CMS deploy cleanup"
vendor/bin/tcms deploy

# ─────────────────────────────────────────────────────────────────────
# Optional: reload PHP-FPM after the deploy.
#
# CLI OPcache (cleared by `tcms deploy`) and PHP-FPM OPcache are
# separate. If your stack uses PHP-FPM and OPcache without
# opcache.validate_timestamps, set the service name below and grant
# the deploy user NOPASSWD sudo for `systemctl reload <service>`.
#
# Common values: "php8.4-fpm" (Debian/Ubuntu), "php-fpm" (RHEL/Fedora).
# Leave blank to skip — that's the safe default for portable scripts.
# ─────────────────────────────────────────────────────────────────────
PHP_FPM_SERVICE=""

if [ -n "$PHP_FPM_SERVICE" ]; then
	echo "→ Reloading $PHP_FPM_SERVICE to flush PHP-FPM OPcache"
	sudo systemctl reload "$PHP_FPM_SERVICE"
fi

echo "✓ Deploy complete"
