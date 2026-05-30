# AGENTS.md

Guidance for AI coding agents working on a **Total CMS** site. If your assistant
expects a different filename, rename this file (see "Renaming" at the bottom).

## What this project is

This is a website built on [Total CMS](https://totalcms.co) (T3) — a modern,
flat-file PHP content management system. There is **no database**: all content
is stored as JSON files on disk. The CMS core is installed via Composer into
`vendor/totalcms/cms/`; this repository is your *site*, not the CMS itself.

- **Backend**: PHP 8.2+, Slim 4, Twig 3, PHP-DI
- **Storage**: flat-file JSON (no SQL, no migrations)
- **Frontend**: server-rendered Twig; optional Vite pipeline under `frontend/`
- **Admin**: built-in admin UI with a form builder, 20+ field types, and a
  Site Builder for dynamic pages

## Use the documentation — don't guess

Total CMS has thorough docs. Prefer looking things up over relying on training
data, which may be stale or wrong about exact Twig signatures and field options.

1. **MCP docs server (best for agents).** Total CMS publishes a public MCP
   server at `https://mcp.totalcms.co/` that exposes the full documentation as
   live lookup tools (`docs_search`, `docs_twig_function`, `docs_twig_filter`,
   `docs_field_type`, `docs_schema_config`, `docs_api_endpoint`,
   `docs_cli_command`, `docs_builder`, `docs_extension`). No API key required.
   If your tool supports MCP, connect it. Example for Claude Code (`~/.claude/mcp.json`):

   ```json
   {
     "mcpServers": {
       "totalcms-docs": {
         "url": "https://mcp.totalcms.co/"
       }
     }
   }
   ```

2. **Local docs (offline, in this repo).** The full documentation ships with
   the Composer package as Markdown under `vendor/totalcms/cms/resources/docs/`,
   organized by section (`get-started/`, `collections/`, `schemas/`, `fields/`,
   `site-builder/`, `twig/`, `forms/`, `auth/`, `apis/`, `extensions/`,
   `operations/`, …). `menu.php` is the table of contents and
   `search-index.json` is a prebuilt full-text index. Grep or read these
   directly when you don't have network/MCP access — but don't edit them; they
   belong to the CMS core and are overwritten on update.
3. **Public docs site**: <https://docs.totalcms.co>
4. **In-admin docs**: the running site serves the same docs under the admin
   panel (look for "Docs" in the admin navigation).

## Project layout

```
.
├── config/
│   └── tcms.php          # Site config — deep-merged over defaults; edit this, not vendor/
├── public/
│   ├── index.php         # Front controller (web entry point)
│   └── .htaccess         # Apache rewrite rules
├── frontend/             # (optional) Vite asset pipeline — present only if installed
├── tcms-data/            # Content storage (JSON). Created at runtime. NOT committed.
├── vendor/totalcms/cms/  # The CMS core — do NOT edit; it's replaced on update
└── composer.json
```

Writable runtime dirs (`cache/`, `logs/`, `tmp/`, `tcms-data/`) live at the
project root and are git-ignored.

## The CLI: `vendor/bin/tcms`

Total CMS ships a Symfony Console CLI. Common commands:

```bash
vendor/bin/tcms info                 # environment + config summary
vendor/bin/tcms collection:list      # list collections
vendor/bin/tcms object:list <coll>   # list objects in a collection
vendor/bin/tcms builder:init <name>  # scaffold a Site Builder starter (e.g. blog, business)
vendor/bin/tcms builder:frontend     # add the Vite frontend pipeline
vendor/bin/tcms cache:clear          # clear caches
```

Most commands accept `--json` for machine-readable output. Run
`vendor/bin/tcms list` to see everything.

## Conventions & guardrails

- **Never edit `vendor/`.** The CMS core is managed by Composer and overwritten
  on update. Configure via `config/tcms.php` (deep-merged — only specify keys
  you want to change) and extend via the extension system, not by patching core.
- **Content lives in `tcms-data/`** as JSON. Prefer the admin UI or the `tcms`
  CLI over hand-editing these files.
- **Templates**: Twig. The global is `cms` — e.g. `cms.config('key')`,
  `cms.env`, `cms.collection.objects(...)`, `cms.image(...)`. Look up exact
  signatures via the MCP server or docs rather than guessing.
- **Site Builder pages** are dynamic: a page record in the `builder-pages`
  collection is served at request time — there is no build/generate step.
  Builder templates live under `tcms-data/builder/{layouts,pages,partials,macros}/`.
- **Frontend assets**: if `frontend/` exists, it's a Vite project that compiles
  into `public/assets/` (git-ignored build output). Run `cd frontend && npm install
  && npm run build`. Reference compiled files in layouts with
  `{{ cms.builder.css('css/style.css') }}` and friends.
- **`composer.lock` is intentionally git-ignored** — the resolved lock differs
  by platform for this project, so it isn't committed.

## Running the site locally

Point a web server's document root at `public/` and visit `/`. On first run the
setup wizard takes over (welcome → environment → data path → admin account →
license → server config). PHP's built-in server works for quick checks:

```bash
php -S localhost:8080 -t public
```

## Renaming

`AGENTS.md` is read by many assistants out of the box. If yours expects a
different filename, copy or rename this file — e.g. `CLAUDE.md` (Claude Code),
`GEMINI.md` (Gemini CLI), or `.cursor/rules` (Cursor). The content applies
regardless of the filename.
