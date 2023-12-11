<?php

namespace App\Filament\Admin\Resources\TeamResource\Pages;

use App\Filament\Admin\Resources\TeamResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;

    public function mount(): void {
        $this->form->fill([
            'secret_key' => Str::random(50)
        ]);
    }
}
