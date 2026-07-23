# Audit Logs

A simple yet flexible implementation of audit logging in Laravel.

![Static Analysis](https://github.com/bradietilley/laravel-audit-logs/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/laravel-audit-logs/actions/workflows/tests.yml/badge.svg)
![Laravel Version](https://img.shields.io/badge/Laravel%20Version-13.x-F9322C)
![PHP Version](https://img.shields.io/badge/PHP%20Version-8.3-4F5B93)

## Documentation

Full documentation is available at [bradietilley.dev/laravel-audit-logs](https://bradietilley.dev/laravel-audit-logs).

## Installation

```bash
composer require bradietilley/laravel-audit-logs
php artisan vendor:publish --tag="audit-logs-migrations"
php artisan migrate
```

```php
use BradieTilley\AuditLogs\Concerns\HasAuditLogs;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements WithAuditLogs
{
    use HasAuditLogs;
}

$product = Product::create(['name' => 'Widget']);
// → "Product created"

$product->update(['name' => 'Gadget']);
// → "Product updated" with human-readable change details
```

See the [documentation](https://bradietilley.dev/laravel-audit-logs) for activity logs, auth events, change filtering, and customization.

## Credits

- [Bradie Tilley](https://github.com/bradietilley)
