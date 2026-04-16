# Total CMS

This is the project skeleton for [Total CMS](https://totalcms.co), a modern flat-file CMS for PHP.

## Quick Start

```bash
composer create-project totalcms/totalcms mysite
cd mysite
```

Point your web server's document root to `public/tcms/`, then visit `/admin` to complete setup.

## Project Structure

```
mysite/
├── config/
│   └── tcms.php        # Your site configuration
├── public/
│   └── tcms/
│       └── index.php   # Web entry point
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

## License

Total CMS is commercial software. See [LICENSE.md](https://github.com/totalcms/cms/blob/master/LICENSE.md) for terms.

Free 45-day trials are available at [totalcms.co](https://totalcms.co).
