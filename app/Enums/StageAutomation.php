<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StageAutomation: string implements HasLabel {

    case None = 'none';
    case Created = 'order_created';
    case InstallerContacted = 'installer_contacted';
    case InstallationDateConfirmed = 'installation_date_confirmed';
    case Invoice = 'create_invoice';
    case Monta = 'create_monta';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::None => 'None',
            self::Created => 'Order Created',
            self::InstallerContacted => 'Installer Contacted',
            self::InstallationDateConfirmed => 'Installation Date Confirmed',
            self::Invoice => 'Create Invoice on first stage',
            self::Monta => 'Monta team etc. is created',
        };
    }
}
