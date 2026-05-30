# Total CMS

This is the project skeleton for [Total CMS](https://totalcms.co), a modern flat-file CMS for PHP.

## Quick Start

```bash
composer create-project totalcms/totalcms mysite
cd mysite
```

The installer will prompt you once Composer finishes:

1. **Layout** — `root` (T3 owns the whole domain) or `subpath` (T3 lives at `/tcms/`, leaving `public/` free for your own frontend build). Subpath reorganizes the front controller and rewrite rules automatically.
2. **Starter pack** (root layout only) — pick `none`, `minimal`, `blog`, `business`, or `portfolio`. Imports a Builder pack with sample pages, layouts, and seed content.
3. **Frontend asset pipeline** (root layout only) — copies a Vite-based bundle into `frontend/` so you can compile CSS/JS that builder layouts reference. Skip if you're bringing your own asset tooling.

Point your web server's document root to `public/`, then visit `/` (root) or `/tcms/` (subpath) — the setup wizard takes over from there.

### Non-interactive installs

For CI or scripted setups, pre-answer with environment variables:

```bash
TCMS_LAYOUT=root \
TCMS_STARTER=blog \
TCMS_FRONTEND=1 \
  composer create-project totalcms/totalcms mysite
```

Defaults: `TCMS_LAYOUT=root`, `TCMS_STARTER=none`, `TCMS_FRONTEND=0`.

The installer self-destructs once it finishes, so to add a starter or the frontend pipeline after the fact, use the CLI directly:

```bash
vendor/bin/tcms builder:init blog    # install a starter
vendor/bin/tcms builder:frontend     # install the Vite frontend scaffold
```

## Project Structure

```
mysite/
├── config/
│   └── tcms.php        # Your site configuration
├── public/
│   ├── .htaccess       # Front-controller rewrite rules (Apache)
│   └── index.php       # Web entry point
├── vendor/
│   └── totalcms/cms/   # Total CMS core (installed by Composer)
├── tcms-data/          # Content storage (created at runtime)
└── composer.json
```

## Configuration

Edit `config/tcms.php` to override default settings:

```php
return [
    'debug' => true,
    'datadir' => '/path/to/custom/tcms-data',
];
```

See the [Configuration Guide](https://docs.totalcms.co/getting-started/configuration) for all available options.

## Documentation

- [Installation Guide](https://docs.totalcms.co/getting-started/installation)
- [Full Documentation](https://docs.totalcms.co)

## Working with an AI agent

This skeleton ships an [`AGENTS.md`](AGENTS.md) that orients AI coding assistants —
the stack, project layout, the `tcms` CLI, conventions, and how to connect the
Total CMS [MCP documentation server](https://mcp.totalcms.co/) so your assistant
can look up exact Twig functions, field options, and API endpoints on demand.

`AGENTS.md` is read by many tools out of the box, but if yours expects a
different filename just rename or copy it — for example `CLAUDE.md` (Claude
Code), `GEMINI.md` (Gemini CLI), or a `.cursor/rules` file (Cursor). The content
applies regardless of the filename.

## License

Total CMS is commercial software. See [LICENSE.md](https://github.com/totalcms/cms/blob/master/LICENSE.md) for terms.

Free 45-day trials are available at [totalcms.co](https://totalcms.co).
