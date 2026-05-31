<?php

declare(strict_types=1);

/**
 * Post-install setup for `composer create-project totalcms/totalcms`.
 *
 * Runs automatically once after create-project (wired via composer.json's
 * post-create-project-cmd hook), then self-destructs IF it actually did
 * work (subpath migration, a starter, or the frontend scaffold). Every
 * prompted decision has a direct CLI equivalent (`tcms builder:init`,
 * `tcms builder:frontend`), so the script has no second-run use case worth
 * the footgun of an accidental layout flip.
 *
 * A pure no-op run (root + none + no-frontend) leaves the script on disk so
 * the operator can re-invoke it later. This matters for scripted installs
 * that pass `--no-install` (vendor/bin/tcms isn't there yet) or
 * `--no-interaction` (defaults get picked silently) — without the guard,
 * the script would delete itself before the operator could use it.
 *
 * If the script fails partway, it leaves itself on disk so the operator can
 * fix the underlying issue and re-run.
 *
 * Prompts the operator for a few setup decisions:
 *
 *   1. Layout — "root" (default, T3 owns the docroot) or "subpath"
 *      (T3 lives at /tcms/, leaving public/ free for a static frontend
 *      build). Subpath reorganizes the front controller and .htaccess
 *      automatically.
 *
 *   2. Starter pack — only offered for root layout. Discovered from
 *      vendor/totalcms/cms/resources/builder/starters/. Delegates to
 *      `tcms builder:init <starter>` once chosen — the CLI imports the
 *      starter's `jumpstart.json` (pages + any demo content) automatically.
 *
 *   3. Frontend asset pipeline — only offered for root layout. Bundled
 *      into the starter command via `--frontend`, or run on its own via
 *      `tcms builder:frontend` when no starter was picked.
 *
 *   4. Git-managed templates — only offered for root layout. Creates a
 *      `builder/` folder at the project root, which Total CMS detects as
 *      "templates are git-managed": they become the committed source of
 *      truth and the dashboard template editor goes read-only. Created
 *      BEFORE the starter runs so `builder:init` scaffolds into it; seeded
 *      with the built-in default layout when no starter is chosen.
 *
 * Non-interactive runs (CI, --no-interaction, no TTY) fall back to env
 * vars or sensible defaults so this script never blocks unattended
 * installs:
 *
 *   TCMS_LAYOUT        = root | subpath               (default: root)
 *   TCMS_STARTER       = none | <starter id>          (default: none)
 *   TCMS_FRONTEND      = 0 | 1                         (default: 0)
 *   TCMS_GIT_TEMPLATES = 0 | 1                         (default: 0)
 */

$projectRoot   = dirname(__DIR__);
$cmsPackageDir = $projectRoot . '/vendor/totalcms/cms';
$tcmsBin       = $projectRoot . '/vendor/bin/tcms';

$nonInteractive = (bool)getenv('COMPOSER_NO_INTERACTION')
	|| !function_exists('posix_isatty')
	|| !defined('STDIN')
	|| !@posix_isatty(STDIN);

echo "\n";
echo "============================================================\n";
echo "  Total CMS — post-install setup\n";
echo "============================================================\n";
echo "\n";

// --- Layout ----------------------------------------------------------

$layout = resolveChoice(
	envVar: 'TCMS_LAYOUT',
	allowed: ['root', 'subpath'],
	default: 'root',
	nonInteractive: $nonInteractive,
	prompt: <<<'TXT'
		Where should Total CMS live in your URL space?

		  [1] root      T3 owns the whole domain. Front controller at
		                public/index.php, requests go to T3 by default.
		                Pick this if T3 is the whole site.

		  [2] subpath   T3 lives at /tcms/. public/ is free for your own
		                frontend build (Next.js, Astro, static, etc).
		                Pick this if you're using T3 as a headless CMS
		                alongside another frontend.

		Choice [1]:
		TXT,
	choiceMap: ['1' => 'root', '2' => 'subpath', '' => 'root'],
);

// Track whether the script actually did anything beyond default choices. A
// pure no-op run (root layout, no starter, no frontend) leaves the script on
// disk so the operator can re-invoke it later — common in scripted installs
// that pass --no-install, where vendor/bin/tcms isn't there yet and the
// non-interactive defaults would otherwise self-destruct us into oblivion.
$didWork = false;

// Whether the operator opted into git-managed templates (root layout only).
// Hoisted so the closing "Next steps" message can reference it.
$gitTemplates = '0';

if ($layout === 'subpath') {
	reorganizeForSubpath($projectRoot);
	$didWork = true;
}

// --- Starter pack & frontend bundle (root layout only) ---------------
//
// Both are delegated to the CLI:
//   tcms builder:init <starter> [--frontend]   (with starter)
//   tcms builder:frontend                       (frontend only)
//
// The CLI bootstraps T3, resolves Config, and runs the same services
// the admin would — single source of truth, no logic duplicated here.

if ($layout === 'root') {
	$starters = discoverStarters($cmsPackageDir);
	$starter  = promptStarter($starters, $nonInteractive);

	$frontend = resolveChoice(
		envVar: 'TCMS_FRONTEND',
		allowed: ['0', '1'],
		default: '0',
		nonInteractive: $nonInteractive,
		prompt: <<<'TXT'

			Install the frontend asset pipeline?

			A Vite-based bundle for compiling CSS/JS that builder layouts
			can reference via `{{ cms.builder.css(...) }}`. Drops a
			`frontend/` directory at the project root. Run `npm install`
			inside it to build assets.

			Choice [n]: (y/n)
			TXT,
		choiceMap: ['y' => '1', 'yes' => '1', 'n' => '0', 'no' => '0', '' => '0'],
	);

	$gitTemplates = resolveChoice(
		envVar: 'TCMS_GIT_TEMPLATES',
		allowed: ['0', '1'],
		default: '0',
		nonInteractive: $nonInteractive,
		prompt: <<<'TXT'

			Manage Site Builder templates with git?

			Keeps templates in a `builder/` folder at the project root so you
			edit them in your IDE and deploy with git — the dashboard template
			editor becomes read-only. (Default: edit templates in the dashboard,
			stored under tcms-data/.) See docs: operations/git-first-templates.

			Choice [n]: (y/n)
			TXT,
		choiceMap: ['y' => '1', 'yes' => '1', 'n' => '0', 'no' => '0', '' => '0'],
	);

	// Create ./builder BEFORE scaffolding, so `tcms builder:init` writes the
	// starter's templates straight into the committed, git-managed location
	// (Total CMS detects git-managed mode by the folder's presence).
	$builderDir = $projectRoot . '/builder';
	if ($gitTemplates === '1' && !is_dir($builderDir)) {
		mkdir($builderDir, 0755, true);
		echo "==> Created ./builder — Site Builder templates are git-managed (edit in your IDE, deploy with git).\n";
		$didWork = true;
	}

	if ($starter !== 'none' || $frontend === '1') {
		dispatchBuilderCommands($tcmsBin, $starter, $frontend === '1');
		$didWork = true;
	}

	// Git-managed with no starter would leave ./builder empty — and an empty
	// git-managed builder is locked with nothing to edit. Seed the built-in
	// default layout so there's a baseline to extend.
	if ($gitTemplates === '1' && $starter === 'none') {
		seedBuilderDefaults($cmsPackageDir, $builderDir);
	}
}

// --- Done ------------------------------------------------------------

echo "\n";
echo "Done.\n";
echo "\n";
echo "Next steps:\n";
echo "  1. Point your web server at <project>/public/.\n";
echo $layout === 'subpath'
	? "  2. Visit /tcms/ to start the setup wizard.\n"
	: "  2. Visit / to start the setup wizard.\n";
if ($gitTemplates === '1') {
	echo "  3. Commit the ./builder/ folder — your templates are git-managed.\n";
	echo "     Edit them in your IDE; the dashboard editor is read-only.\n";
}
echo "\n";

if ($didWork) {
	selfDestruct(__FILE__);
}

exit(0);


// ===================== prompt + IO helpers ==========================

/**
 * Resolve a choice from (in order): env var, prompt, default.
 *
 * @param array<string> $allowed Canonical values the choice can resolve to.
 * @param array<string,string> $choiceMap User-typed string => canonical value.
 */
function resolveChoice(
	string $envVar,
	array $allowed,
	string $default,
	bool $nonInteractive,
	string $prompt,
	array $choiceMap,
): string {
	$envValue = getenv($envVar);
	if ($envValue !== false && $envValue !== '') {
		$mapped = $choiceMap[strtolower($envValue)] ?? $envValue;
		if (in_array($mapped, $allowed, true)) {
			echo sprintf("[%s] resolved to '%s' from env\n\n", $envVar, $mapped);

			return $mapped;
		}
	}

	if ($nonInteractive) {
		return $default;
	}

	while (true) {
		echo $prompt . ' ';
		$line   = fgets(STDIN);
		$input  = $line === false ? '' : strtolower(trim($line));
		$mapped = $choiceMap[$input] ?? $input;

		if (in_array($mapped, $allowed, true)) {
			return $mapped;
		}

		echo "Sorry — try again.\n\n";
	}
}

// ===================== starter discovery & prompt ===================

/**
 * @return array<string,array{id:string,name:string,description:string}>
 */
function discoverStarters(string $cmsPackageDir): array
{
	$startersDir = $cmsPackageDir . '/resources/builder/starters';
	if (!is_dir($startersDir)) {
		return [];
	}

	$found = [];
	foreach ((array)scandir($startersDir) as $entry) {
		if (!is_string($entry) || $entry[0] === '.') {
			continue;
		}

		$manifestPath = $startersDir . '/' . $entry . '/manifest.json';
		if (!is_file($manifestPath)) {
			continue;
		}

		$decoded = json_decode((string)file_get_contents($manifestPath), true);
		if (!is_array($decoded)) {
			continue;
		}

		$found[$entry] = [
			'id'          => $entry,
			'name'        => (string)($decoded['name'] ?? $entry),
			'description' => (string)($decoded['description'] ?? ''),
		];
	}

	return $found;
}

/**
 * @param array<string,array{id:string,name:string,description:string}> $starters
 */
function promptStarter(array $starters, bool $nonInteractive): string
{
	$ids       = ['none', ...array_keys($starters)];
	$envVal    = getenv('TCMS_STARTER');
	$envMapped = is_string($envVal) ? strtolower($envVal) : '';

	if ($envMapped !== '' && in_array($envMapped, $ids, true)) {
		echo sprintf("[TCMS_STARTER] resolved to '%s' from env\n\n", $envMapped);

		return $envMapped;
	}

	if ($nonInteractive || $starters === []) {
		return 'none';
	}

	echo "\n";
	echo "Pick a starter pack to seed the install with sample pages and content:\n";
	echo "\n";
	echo "  [0] none — empty install, set everything up yourself\n";

	$indexed = array_values($starters);
	foreach ($indexed as $i => $starter) {
		$num = $i + 1;
		echo sprintf("  [%d] %-9s %s\n", $num, $starter['id'], $starter['description']);
	}

	echo "\n";
	echo 'Choice [0]: ';

	while (true) {
		$line  = fgets(STDIN);
		$input = $line === false ? '' : strtolower(trim($line));

		if ($input === '' || $input === '0' || $input === 'none') {
			return 'none';
		}

		if (ctype_digit($input)) {
			$idx = (int)$input - 1;
			if (isset($indexed[$idx])) {
				return $indexed[$idx]['id'];
			}
		}

		if (isset($starters[$input])) {
			return $input;
		}

		echo "Sorry — try again. Choice: ";
	}
}

// ===================== CLI delegation ===============================

/**
 * Dispatch the chosen starter / frontend combo to the tcms CLI:
 *
 *   starter + frontend  -> tcms builder:init <starter> --frontend
 *   starter only        -> tcms builder:init <starter>
 *   frontend only       -> tcms builder:frontend
 *   neither             -> nothing (silent)
 */
function dispatchBuilderCommands(string $tcmsBin, string $starter, bool $frontend): void
{
	if ($starter === 'none' && !$frontend) {
		return;
	}

	if (!is_executable($tcmsBin)) {
		echo "==> Skipping CLI delegation — vendor/bin/tcms not found or not executable\n";
		echo "    Re-run after `composer install` finishes:\n";
		if ($starter !== 'none') {
			echo "      vendor/bin/tcms builder:init {$starter}" . ($frontend ? ' --frontend' : '') . "\n";
		} elseif ($frontend) {
			echo "      vendor/bin/tcms builder:frontend\n";
		}

		return;
	}

	if ($starter !== 'none') {
		$args = ['builder:init', $starter];
		if ($frontend) {
			$args[] = '--frontend';
		}
	} else {
		$args = ['builder:frontend'];
	}

	echo "\n==> tcms " . implode(' ', $args) . "\n";

	$cmd = escapeshellarg($tcmsBin);
	foreach ($args as $arg) {
		$cmd .= ' ' . escapeshellarg($arg);
	}

	passthru($cmd, $exitCode);

	if ($exitCode !== 0) {
		echo "==> CLI command exited non-zero ({$exitCode}). You can retry manually with the command above.\n";
	}
}

// ===================== file ops =====================================

/**
 * Move public/index.php and public/.htaccess into public/tcms/ and adjust
 * the front controller's TCMS_PROJECT_ROOT so it still resolves to the
 * project root from one level deeper.
 */
function reorganizeForSubpath(string $projectRoot): void
{
	echo "==> Reorganizing for subpath layout\n";

	$src = $projectRoot . '/public';
	$dst = $src . '/tcms';

	if (!is_dir($dst)) {
		mkdir($dst, 0755, true);
	}

	if (is_file($src . '/index.php')) {
		$contents = (string)file_get_contents($src . '/index.php');
		// Bump dirname depth so TCMS_PROJECT_ROOT still lands at project root.
		$contents = str_replace(
			"define('TCMS_PROJECT_ROOT', dirname(__DIR__));",
			"define('TCMS_PROJECT_ROOT', dirname(__DIR__, 2));",
			$contents,
		);
		file_put_contents($dst . '/index.php', $contents);
		unlink($src . '/index.php');
		echo "    moved public/index.php -> public/tcms/index.php (dirname depth bumped)\n";
	}

	if (is_file($src . '/.htaccess')) {
		rename($src . '/.htaccess', $dst . '/.htaccess');
		echo "    moved public/.htaccess -> public/tcms/.htaccess\n";
	}
}

/**
 * Seed a freshly-created git-managed ./builder with the built-in default
 * templates (from the cms package's resources/builder/defaults). Only used
 * when the operator chose git-managed templates WITHOUT a starter — an empty
 * ./builder would be git-managed-and-locked with nothing to edit. Existing
 * files are never overwritten.
 */
function seedBuilderDefaults(string $cmsPackageDir, string $builderDir): void
{
	$defaults = $cmsPackageDir . '/resources/builder/defaults';
	if (!is_dir($defaults)) {
		return;
	}

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($defaults, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST,
	);

	foreach ($iterator as $item) {
		if (!$item instanceof SplFileInfo) {
			continue;
		}

		$relative = ltrim(substr($item->getPathname(), strlen($defaults)), DIRECTORY_SEPARATOR);
		$dest     = $builderDir . DIRECTORY_SEPARATOR . $relative;

		if ($item->isDir()) {
			if (!is_dir($dest)) {
				mkdir($dest, 0755, true);
			}
			continue;
		}

		if (!file_exists($dest)) {
			copy($item->getPathname(), $dest);
		}
	}

	echo "    seeded ./builder with the built-in default layout\n";
}

/**
 * Delete this script so the project tree is clean after a successful first
 * run. Every prompted decision has a direct CLI equivalent under
 * `vendor/bin/tcms`, so there's no legitimate second-run case worth keeping
 * the file around for.
 *
 * The bin/ directory stays — operators commonly drop their own project
 * scripts in there, so removing it would be presumptuous. Best-effort: a
 * failed unlink shouldn't fail the install.
 */
function selfDestruct(string $self): void
{
	@unlink($self);
}
