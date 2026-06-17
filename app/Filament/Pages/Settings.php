<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.settings';

    protected static ?int $navigationSort = 99;

    public ?float $admin_fee = null;

    public static function getNavigationGroup(): string
    {
        return 'Configuration';
    }

    public static function getNavigationLabel(): string
    {
        return 'Settings';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public function mount(): void
    {
        $this->form->fill([
            'admin_fee' => Setting::getAdminFee(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Financial Settings')
                    ->description('Configure platform-wide financial settings')
                    ->schema([
                        TextInput::make('admin_fee')
                            ->label('Admin Fee (Fixed per Transaction)')
                            ->helperText('This fee will be added to every order. Example: 5000 = Rp 5,000')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(5000),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::setAdminFee($data['admin_fee']);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    public static function getNavigationBadge(): ?string
    {
        return null;
    }
}
