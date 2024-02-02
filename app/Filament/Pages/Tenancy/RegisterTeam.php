<?php
namespace App\Filament\Pages\Tenancy;

use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterTeam extends RegisterTenant
{

    public static function getLabel(): string
    {
        return 'Contact your administrator to register a team';
    }





    public function getRegisterFormAction(): Action
    {
        return parent::getRegisterFormAction()
            ->disabled()
            ->hidden();
    }
}
