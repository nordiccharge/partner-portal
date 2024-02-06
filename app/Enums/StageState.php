<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StageState: string implements HasLabel {

    case None = 'none';
    case Created = 'order_created';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::None => 'None',
            self::Created => 'Order Created',
        };
    }
}
