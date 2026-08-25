<?php

namespace App\Filament\Pages;

use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class UpdateAvatar extends MyProfileComponent
{
    protected string $view = 'livewire.update-avatar';

    public ?array $data = [];
    public $user;
    public static $sort = 15;

    public function mount(): void
    {
        $this->user = Filament::getCurrentPanel()->auth()->user();
        
        $avatarField = filament('filament-breezy')->getAvatarUploadComponent()->getStatePath(false);
        $this->form->fill($this->user->only([$avatarField]));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                filament('filament-breezy')->getAvatarUploadComponent()
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $avatarField = filament('filament-breezy')->getAvatarUploadComponent()->getStatePath(false);
        $data = collect($this->form->getState())->only([$avatarField])->all();
        
        $this->user->update($data);
        $this->form->fill($this->user->only([$avatarField]));
        
        Notification::make()
            ->success()
            ->title('Foto de perfil atualizada!')
            ->send();
    }
}
