<?php

namespace App\Filament\Pages;

use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;
use Filament\Forms;

class CustomPersonalInfo extends PersonalInfo
{
    protected function getEmailComponent(): Forms\Components\TextInput
    {
        return parent::getEmailComponent()->rule('email:rfc,dns');
    }
}
