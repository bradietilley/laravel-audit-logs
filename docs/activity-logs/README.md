# Activity Logs

Attach automatic activity logging to any Eloquent model with the contract and
trait:

```php
use BradieTilley\AuditLogs\Concerns\HasAuditLogs;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements WithAuditLogs
{
    use HasAuditLogs;
}
```

The trait registers `HasAuditLogsObserver`
(configurable via `audit-logs.classes.observer`) after the model has booted.

## Events recorded

| Eloquent event | Example action |
| --- | --- |
| `created` | `Product created` |
| `updated` | `Product updated` (with change details — see [Change Logging](../change-logging/README.md)) |
| `deleted` | `Product deleted` (soft or hard delete) |
| `forceDeleted` | `Product force deleted` |
| `restored` | `Product restored` |

On SoftDeletes models, a force-delete records only the `forceDeleted` action
(the intermediate `deleted` observer call is skipped).

## Display names

Human-readable model and field labels can be overridden:

```php
use BradieTilley\AuditLogs\AuditLogUtil;

AuditLogUtil::usingModelNames([
    Product::class => 'Catalogue product',
]);

AuditLogUtil::usingFieldNames([
    'sku' => 'SKU',
]);
```

## Subclassing models

If a child model extends a parent that already uses `HasAuditLogs`, do **not**
re-apply the trait on the child — that would register the observer twice.

## Next steps

- [Change Logging](../change-logging/README.md)
- [Querying Logs](../querying/README.md)
