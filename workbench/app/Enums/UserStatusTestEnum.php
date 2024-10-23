<?php

namespace Workbench\App\Enums;

enum UserStatusTestEnum: string
{
    case Pending = 'pending';
    case Active = 'active';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
