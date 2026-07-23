# Auth Events

Authentication events are logged as `audit` type records by default. Listeners
are registered from `audit-logs.auth_events` in config.

## Default map

| Event | Action |
| --- | --- |
| `Illuminate\Auth\Events\Attempting` | Login Attempt |
| `Illuminate\Auth\Events\Authenticated` | *(no log — preloads the user on the recorder)* |
| `Illuminate\Auth\Events\CurrentDeviceLogout` | Logout (current device) successful |
| `Illuminate\Auth\Events\Failed` | Login failed |
| `Illuminate\Auth\Events\Lockout` | Request locked out |
| `Illuminate\Auth\Events\Login` | Login successful |
| `Illuminate\Auth\Events\OtherDeviceLogout` | Logout (other device) successful |
| `Illuminate\Auth\Events\PasswordReset` | Password reset successful |
| `Illuminate\Auth\Events\PasswordResetLinkSent` | Password reset link sent |
| `Illuminate\Auth\Events\Registered` | User registered |
| `Illuminate\Auth\Events\Validated` | Login validation successful |
| `Illuminate\Auth\Events\Verified` | Email verification successful |

Where applicable, payloads include the auth guard and the configured
`user_identifier` attribute (default `email`).

## Disabling or remapping

Set a listener to `null` or `false` to skip registration:

```php
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;

'auth_events' => [
    Attempting::class => false, // noisy; often disabled
    Login::class => App\Listeners\CustomLoginAudit::class,
    // …keep or omit other defaults as needed
],
```

Publish the config and copy the defaults you want to keep from
`vendor/bradietilley/laravel-audit-logs/config/audit-logs.php`.

## User caching note

The authorised user is cached on the recorder for the request. After logout you
may still see the previous user on logs written in the same request — that is
intentional so logout events remain attributable. Subsequent requests will not
carry that user.

## Next steps

- [Recording Logs](../recording/README.md)
- [Customization](../customization/README.md)
