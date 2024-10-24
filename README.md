# Audit Logs

A simple yet flexible implementation of Audit Logging in Laravel.

![Static Analysis](https://github.com/bradietilley/laravel-audit-logs/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/laravel-audit-logs/actions/workflows/tests.yml/badge.svg)
![Min Laravel Version](https://img.shields.io/badge/Min%20Laravel%20Version-11-F9322C)

Keep an audit record of all modifications made to your eloquent models.

Whether you're tracking security events, other notable events, resource changes, or just everyday actions of users, all of these changes can be described as _Audit Logs_. Use cases of this package are:

- **Regulatory auditing purposes**
    - If you hook up the log stream to point to CloudWatch or similar, the logs can be considered immutable and trustworthy, helping serve the purpose of regulatory auditing.
- **Viewing a resource's history**
    - This package provides a morph ownership to a resource that was affected, such as a resource undergoing updates. This allows you to trace when a resource was modified in a particular way and see the user or resource that performed that action.
- **Viewing a user's actions**
    - This package provides a morph ownership to the user or resource that performed the action, by default this is the authorised user. This allows you to therefore trace a user's steps as they navigate and interact with your application.
- **Analytical reporting**
    - This package can be *extended* to produce reports on user or resource activity.

## Installation

```bash
composer require bradietilley/laravel-audit-logs
```

## Usage

### Activity Logs → Resource creation, updates, deletion, restoration

Add the following interface and trait to the model you wish to automatically attach audit logs to:

```php
<?php

namespace App\Models;

use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use BradieTilley\AuditLogs\Concerns\HasAuditLogs;

class User extends Model implements WithAuditLogs
{
    use HasAuditLogs;
}
```

The `HasAuditLogs` trait will register an observer (`BradieTilley\AuditLogs\Observers\HasAuditLogsObserver`, configurable via `audit-logs.classes.observer`) that listens to the following eloquent events: `created`, `updated`, `deleted`, `forceDeleted`, `restored`.

These events will record a pretty ordinary audit log for the respective event.

For the `updated` event, the attributes that are changed will be logged with some exceptions:

**Ignoring Irrelevant Fields**

The `audit_logs.changes.ignored_fields` configuration allows you to configure which fields should be considered irrelevant. If there are no relevant fields, no 'Updated' log will be written.

Default Global: `id`, `updated_at`, `deleted_at` \
Default User model specific: `remember_token`

**Redacting Sensitive Fields**

The `audit_logs.changes.sensitive_fields` configuration allows you to configure which fields should be redacted when logged. This allows you to still record when changes are made but not see the sensitive data.

Default Global: `password`, `token`/`*_token`, `secret`/`*_secret` \
Default User model specific: `drivers_licence` (a demonstrative example)

**Truncating Long Strings**

The `audit_logs.changes.truncate_string_lengths` configuration allows you to configure how long strings can be before they are truncated (`Str::limit()` with '...'), and can be configured to specific models and fields, or generic fields across any model, or simply a global default.

Default: 100 characters

**Customising the Change Logger**

Want to completely customise what gets logged? The `BradieTilley\AuditLogs\Loggers\ChangeLogger` class can be swapped out for any class that extends `ChangeLogger`:

```php
<?php

namespace App\Loggers;

use BradieTilley\AuditLogs\Loggers\ChangeLogger;

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

Now configure your app to utilise this `ModelLogger` in `AppServiceProvider` or a similar spot:

```php
$this->app->bind(\BradieTilley\AuditLogs\Logger\ChangeLogger::class, \App\Loggers\MyChangeLogger::class);
```

### Audit Logs → Security and authentication events

By default, various authentication events are already logged;

- `Illuminate\Auth\Events\Attempting` -> _"Login Attempt"_
- `Illuminate\Auth\Events\Authenticated` -> not logged (see `Login`)
- `Illuminate\Auth\Events\CurrentDeviceLogout` -> _"Logout (current device) successful"_
- `Illuminate\Auth\Events\Failed` -> _"Login failed"_
- `Illuminate\Auth\Events\Lockout` -> _"Request locked out"_
- `Illuminate\Auth\Events\Login` -> _"Login successful"_
- `Illuminate\Auth\Events\OtherDeviceLogout` -> _"Logout (other device) successful"_
- `Illuminate\Auth\Events\PasswordReset` -> _"Password reset successful"_
- `Illuminate\Auth\Events\PasswordResetLinkSent` -> _"Password reset link sent"_
- `Illuminate\Auth\Events\Registered` -> _"User registered"_
- `Illuminate\Auth\Events\Validated` -> _"Login validation successful"_
- `Illuminate\Auth\Events\Verified` -> _"Email verification successful"_

Each of these events will log the event details, including the auth guard name and user, where applicable.

### Ad-hoc Logs → Logging misc events wherever you want

**Dependency Injection**

The AuditLogger singleton can be injected where Dependency Injection is supported.

```php
use App\Models\Product;
use BradieTilley\AuditLogs\AuditLogger;

...

public Product $product

public function handle(AuditLogger $logger): void
{
    $logger->record('Something happened');
    $logger->record('Something happened against a resource', $this->product);
}
```

**Statically**

An alternative and more direct approach is to statically call the AuditLogger.

```php
use App\Models\Product;
use BradieTilley\AuditLogs\AuditLogger;

...

public Product $product

public function handle(): void
{
    AuditLogger::write('Something happened');
    AuditLogger::write('Something happened against a resource', $this->product);
}
```

### Unique Logs → Logging once per request

Sometimes you may wish to avoid multiple of the same log from being written in the same request lifecycle. To do this, simply use the `->recordOne()` or `::writeOnce()` methods on the audit logger. Note that the model *and* action serve as a unique key for the "once" tracking.

```php
    AuditLogger::writeOnce('Something happened'); // First log written
    AuditLogger::writeOnce('Something happened'); // No log was written

    AuditLogger::writeOnce('Something happened', $this->product); // Second log written
    AuditLogger::writeOnce('Something happened', $this->product); // No log was written
```

### Pausing Logs → Temporarily disable logs

Sometimes you might want to temporarily disable the logs. This can be achieved via the `withoutLogging` static helper method.

```php
function doSomething() {
    AuditLog::write('Something happened');
}

AuditLogger::withoutLogging(fn () => doSomething());
AuditLogger::withoutLogging(doSomething(...));

// `doSomething` was run twice, however no audit logs were written!
```

### Custom Logs → Customising the entire audit logger logic

The `BradieTilley\AuditLogs\AuditLogger` singleton can be swapped out to a custom class of your choosing, if you wish to customise the data that gets logged.

### Metadata → Details

In the `BradieTilley\AuditLogs\AuditLogger` singleton, metadata is appended to logs. Only some of this metadata makes its way into the database, but all of the metadata makes its way into the log stream.

Some metadata retrieval is cached in the singleton to drastically optimise when logging multiple times within a single request. The caching of this data is typically things that don't change throughout the request lifecycle, such as the full URL, middleware, IP address, and more.

Note: The authorised user is also cached for the duration of the request, so during a log out event expect to see the authenticated user in the logs post-logout. However subsequent requests will not have the user associated.


## Credits

- [Bradie Tilley](https://github.com/bradietilley)
