<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PipelineAutomation: string implements hasLabel {
    case None = 'none';
    case Shipping = 'muramura_shipping';
    case MontaShipping = 'monta_shipping';
    case Return = 'muramura_return';

    case Abort = 'abort';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::None => 'None',
            self::Shipping => 'Ship with MuraMura',
            self::MontaShipping => 'Ship with MuraMura + Create on Monta',
            self::Return => 'Return with MuraMura',
            self::Abort => 'Abort',
        };
    }
}
