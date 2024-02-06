<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PipelineAutomation: string implements hasLabel {
    case None = 'none';
    case Shipping = 'muramura_shipping';
    case Return = 'muramura_return';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::None => 'None',
            self::Shipping => 'Ship with MuraMura',
            self::Return => 'Return with MuraMura',
        };
    }
}
