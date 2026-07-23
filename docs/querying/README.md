# Querying Logs

## Relations on audited models

Models using `HasAuditLogs` gain:

```php
// Logs where this model is the subject
$product->auditLogs;

// Logs where this model/user performed the action
$user->actionLogs;
```

Both are `morphMany` relations ordered by descending `id`.

## The AuditLog model

```php
use BradieTilley\AuditLogs\Models\AuditLog;

AuditLog::query()
    ->where('type', AuditLog::TYPE_AUDIT)
    ->latest('id')
    ->get();
```

### Scope by IP

```php
AuditLog::query()->ip('203.0.113.10')->get();
```

Invalid IPs yield an empty result set. The `ip` column is nullable (binary
storage via `IpAddressCast`).

### Morphs

```php
$log->model; // subject
$log->user;  // actor
```

## Custom model class

Point `audit-logs.models.audit_log` at your subclass if you need extra scopes,
casts, or tables. Relations resolve through `AuditLogConfig::getAuditLogModel()`.

## Next steps

- [Customization](../customization/README.md)
