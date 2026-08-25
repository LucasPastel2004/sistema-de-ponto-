<?php

namespace App\Filament\Pages;

use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;
use Filament\Forms;
use Filament\Forms\Form;

class CustomPersonalInfo extends PersonalInfo
{
    public function mount(): void
    {
        parent::mount();
        $avatarField = filament('filament-breezy')->getAvatarUploadComponent()->getStatePath(false);
        $this->only = array_values(array_diff($this->only, [$avatarField]));
    }

    protected function getProfileFormSchema(): array
    {
        return [
            Forms\Components\Group::make($this->getProfileFormComponents())->columnSpan(3)
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getProfileFormSchema())
            ->columns(3)
            ->statePath('data');
    }

    protected function getEmailComponent(): Forms\Components\TextInput
    {
        return parent::getEmailComponent()->rule('email:rfc,dns');
    }
}
