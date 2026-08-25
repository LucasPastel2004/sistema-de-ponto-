<?php

namespace App\Filament\Pages;

use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt as BasePrompt;

class CustomEmailVerificationPrompt extends BasePrompt
{
    protected static string $view = 'filament.pages.custom-email-verification-prompt';

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        if ($user && !$user->hasVerifiedEmail() && !session()->has('verification_email_sent')) {
            $user->sendEmailVerificationNotification();
            session()->put('verification_email_sent', true);
        }
    }

    public function checkIfVerified()
    {
        if (auth()->user()?->hasVerifiedEmail()) {
            return redirect()->intended(filament()->getUrl());
        }
    }
}
