<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvoiceStatus: string implements HasLabel {

    case Pending = 'pending';
    case Sent = 'Sent';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Sent => 'Sent for payment',
        };
    }
}
