# Audit Logs

A simple yet flexible Laravel package for recording model activity and authentication events as audit logs.

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

## Documentation

- [Introduction](introduction/README.md)
- [Installation](installation/README.md)
- [Activity Logs](activity-logs/README.md)
- [Change Logging](change-logging/README.md)
- [Auth Events](auth-events/README.md)
- [Recording Logs](recording/README.md)
- [Querying Logs](querying/README.md)
- [Customization](customization/README.md)
- [Compatibility](compatibility/README.md)

## Requirements

- PHP 8.3+
- Laravel 13+

## Quick start

```bash
composer require bradietilley/laravel-audit-logs
php artisan vendor:publish --tag="audit-logs-migrations"
php artisan migrate
```

```php
use BradieTilley\AuditLogs\Concerns\HasAuditLogs;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;

class User extends Model implements WithAuditLogs
{
    use HasAuditLogs;
}
```

See [Installation](installation/README.md) and [Activity Logs](activity-logs/README.md) for the full walkthrough.
