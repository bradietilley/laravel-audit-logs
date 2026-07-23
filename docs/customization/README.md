# Customization

## Config keys

| Key | Purpose |
| --- | --- |
| `models.audit_log` | Eloquent model class for persisted logs |
| `classes.observer` | Observer for `HasAuditLogs` models |
| `classes.change_logger` | Formatter for model updates |
| `log_channel` | Laravel log channel name, or `null` for DB-only |
| `user_identifier` | Attribute included on auth event payloads |
| `auth_events` | Event → listener map (`false`/`null` disables) |
| `changes.*` | Ignore / redact / truncate / date formats |

## Swap the recorder

Bind your own implementation of `BradieTilley\AuditLogs\Contracts\AuditLogger`:

```php
use BradieTilley\AuditLogs\Contracts\AuditLogger;
use BradieTilley\AuditLogs\AuditLogger as DefaultAuditLogger;

$this->app->singleton(AuditLogger::class, MyAuditLogger::class);
$this->app->alias(AuditLogger::class, DefaultAuditLogger::class);
```

Or extend `BradieTilley\AuditLogs\AuditLogger` and re-bind the concrete class.

Static helpers (`AuditLogger::write`, `::make`, …) resolve the contract from the
container.

## AuditLogRecorded

After each successful persist, the package dispatches:

```php
use BradieTilley\AuditLogs\Events\AuditLogRecorded;
use Illuminate\Support\Facades\Event;

Event::listen(AuditLogRecorded::class, function (AuditLogRecorded $event) {
    // $event->auditLog
});
```

Use this for metrics, webhooks, or async side-effects without subclassing the
logger.

## Custom observer / change logger

```php
'classes' => [
    'observer' => App\Observers\MyAuditObserver::class,
    'change_logger' => App\Loggers\MyChangeLogger::class,
],
```

Observers should accept `BradieTilley\AuditLogs\Contracts\AuditLogger` via the
constructor. Change loggers should extend
`BradieTilley\AuditLogs\Loggers\ChangeLogger`.

## See also

- [Change Logging](../change-logging/README.md)
- [Auth Events](../auth-events/README.md)
- [Compatibility](../compatibility/README.md)
