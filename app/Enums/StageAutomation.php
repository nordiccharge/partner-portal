<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StageAutomation: string implements HasLabel {

    case None = 'none';
    case Created = 'order_created';
    case Completed = 'order_completed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::None => 'None',
            self::Created => 'Order Created',
            self::Completed => 'Order Completed',
        };
    }
}
