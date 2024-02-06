<?php
namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\Log;

class RegisterTeam extends RegisterTenant
{

    public static function getLabel(): string
    {
        return 'New Team';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([Placeholder::make('No Label')
                ->hiddenLabel()
                ->content("Contact your manager for more details.")
            ]);
    }

    public function getFormActions(): array
    {
        return [
            Action::make('Apply for Team membership')
                ->form([
                    TextInput::make('team_id')
                        ->label('Team ID')
                        ->placeholder('Enter the ID of the team')
                        ->required(),
                    TextInput::make('code')
                        ->label('Team Access Code')
                        ->placeholder('Enter the code')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Apply')
                ->modalWidth('sm')
                ->action(
                    function (array $data) {
                        $team = Team::find($data['team_id']);
                        if ($team->secret_key === $data['code']) {
                            $team->users()->attach(auth()->user());
                            Notification::make('Team membership request submitted')
                                ->title('Team membership granted')
                                ->success()
                                ->send();
                            redirect()->to('partner/' . $team->id);
                        } else {
                            Notification::make('Invalid team access code')
                                ->title('Wrong Team ID og Code')
                                ->danger()
                                ->send();
                        }
                        return;
                    }
                )
        ];
    }

    public function getRegisterFormAction(): Action
    {
        return parent::getRegisterFormAction()
            ->disabled()
            ->hidden();
    }

}
