# Change Logging

When a model with [`HasAuditLogs`](../activity-logs/README.md) is updated, the
package formats `getChanges()` into human-readable strings via
`BradieTilley\AuditLogs\Loggers\ChangeLogger`.

If every changed field is ignored (or there are no changes), no update log is
written.

## Ignoring fields

Configure irrelevant attributes under `audit-logs.changes.ignored_fields`:

```php
'ignored_fields' => [
    '*' => [
        'id',
        'updated_at',
        'deleted_at',
    ],
    App\Models\User::class => [
        'remember_token',
    ],
],
```

## Redacting sensitive fields

Sensitive attributes still produce a log line, without the value:

```php
'sensitive_fields' => [
    '*' => [
        'password',
        '*_token',
        'token',
        'secret',
        '*_secret',
    ],
],
```

Patterns use `Str::is()` wildcards. Redacted output looks like `Password updated`.

## Truncating long strings

```php
'truncate_string_lengths' => [
    '*' => [
        '*' => 100,
        'content' => 150,
    ],
    App\Models\Post::class => [
        '*' => 125,
        'body' => 200,
    ],
],
```

Resolution order: model+field → model+`*` → `*`+field → `*`+`*`.

## Date formats

```php
'date_format' => 'j F Y',
'date_time_format' => 'j F Y, H:i:s',
```

## Custom ChangeLogger

Extend `ChangeLogger` and bind it via config:

```php
// config/audit-logs.php
'classes' => [
    'change_logger' => App\Loggers\MyChangeLogger::class,
],
```

```php
namespace App\Loggers;

use BradieTilley\AuditLogs\Loggers\ChangeLogger;
use App\Models\Product;

class MyChangeLogger extends ChangeLogger
{
    protected function getChanges(): array
    {
        $changes = parent::getChanges();

        if ($this->model instanceof Product) {
            unset($changes['stock']);
        }

        return $changes;
    }
}
```

You can also bind in a service provider; `ChangeLogger::make()` resolves the
configured class from the container.

## Next steps

- [Recording Logs](../recording/README.md)
- [Customization](../customization/README.md)
