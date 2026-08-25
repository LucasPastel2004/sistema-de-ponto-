<?php

namespace App\Filament\Pages;

use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt as BasePrompt;

class CustomEmailVerificationPrompt extends BasePrompt
{
    protected static string $view = 'filament.pages.custom-email-verification-prompt';

    public function checkIfVerified()
    {
        if (auth()->user()?->hasVerifiedEmail()) {
            return redirect()->intended(filament()->getUrl());
        }
    }
}
