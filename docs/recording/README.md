# Recording Logs

Besides automatic model and auth logging, you can record ad-hoc audit entries.

## Dependency injection

Inject the contract (or the concrete `AuditLogger`) where the container is
available:

```php
use BradieTilley\AuditLogs\Contracts\AuditLogger;
use App\Models\Product;

public function handle(AuditLogger $logger, Product $product): void
{
    $logger->record('Something happened');
    $logger->record('Something happened against a resource', $product);
}
```

## Static helpers

```php
use BradieTilley\AuditLogs\AuditLogger;

AuditLogger::write('Something happened');
AuditLogger::write('Something happened against a resource', $product);
```

## Once per request

Deduplicate by model + action within the request lifecycle:

```php
AuditLogger::writeOnce('Something happened'); // written
AuditLogger::writeOnce('Something happened'); // skipped

AuditLogger::writeOnce('Something happened', $product); // written
AuditLogger::writeOnce('Something happened', $product); // skipped
```

## Pausing logs

```php
use BradieTilley\AuditLogs\AuditLogger;

AuditLogger::withoutLogging(function () {
    AuditLogger::write('Something happened'); // not written
});
```

Nesting is supported; logging resumes when the outermost callback finishes.

## Types

Pass `AuditLog::TYPE_AUDIT` or `AuditLog::TYPE_ACTIVITY` (default) as the third
argument to `record` / `write` when you need to distinguish security events from
everyday activity.

## Next steps

- [Querying Logs](../querying/README.md)
- [Customization](../customization/README.md)
