# Installation

## Requirements

- PHP **8.3+**
- Laravel **13+**

## Install

```bash
composer require bradietilley/laravel-audit-logs
```

The service provider is auto-discovered.

## Publish assets

```bash
php artisan vendor:publish --tag="audit-logs-migrations"
php artisan vendor:publish --tag="audit-logs-config"
php artisan migrate
```

## Optional log channel

By default `audit-logs.log_channel` is `null` — logs are persisted to the
database only. To also write to a Laravel log stream, define a channel and point
the config at it:

```php
// config/logging.php
'channels' => [
    'audit_logs' => [
        'driver' => 'single',
        'path' => storage_path('logs/audit-logs.log'),
        'level' => 'info',
    ],
],
```

```php
// config/audit-logs.php
'log_channel' => 'audit_logs',
```

## Next steps

- [Activity Logs](../activity-logs/README.md) — attach logging to models
- [Auth Events](../auth-events/README.md) — authentication coverage
- [Customization](../customization/README.md) — swap models, observers, and loggers
