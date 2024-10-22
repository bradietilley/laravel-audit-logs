# Activity Logs 

Keep an audit record of all modifications made to your eloquent models.

## Installation

```bash
composer require bradietilley/laravel-audit-logs
```

## Usage

Add the following interface and trait to the model you wish to attach audit logs to:

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

By default this will use a rudimentary `BradieTilley\AuditLogs\Loggers\ModelLogger` logger instance to track eloquent events for this model, including creation, deletion, updates and restorations.

Optionally, create a customer `ModelLogger` class for your model to customise the logs that get written, such as if you wish to customise how specific fields are written.

```php
<?php

namespace App\AuditLoggers;

use BradieTilley\AuditLogs\Loggers\ModelLogger;

class UserAuditLogger extends ModelLogger
{
    protected function updated(): void
    {
        if ($this->wasChanged('email')) {
            $this->record("Email was changed to {$this->email}");
        }
    }
}
```

Now configure your model to utilise this `ModelLogger`:

```php
namespace App\Models;

use App\AuditLoggers\UserAuditLogger;
use BradieTilley\AuditLogs\Contracts\WithAuditLogs;
use BradieTilley\AuditLogs\Concerns\HasAuditLogs;

class User extends Model implements WithAuditLogs
{
    use HasAuditLogs;

    public function getAuditLogger(): UserAuditLogger
    {
        return UserAuditLogger::make($this);
    }
}
```

## Credits

- [Bradie Tilley](https://github.com/bradietilley)
