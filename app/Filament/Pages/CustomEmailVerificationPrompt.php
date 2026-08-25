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

    public function updateEmailAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('updateEmail')
            ->label('Digitou o e-mail errado? Clique aqui para alterar')
            ->link()
            ->color('warning')
            ->form([
                \Filament\Forms\Components\TextInput::make('email')
                    ->label('Novo E-mail')
                    ->email()
                    ->required()
                    ->rule('email:rfc,dns')
                    ->unique('users', 'email', ignorable: auth()->user()),
            ])
            ->action(function (array $data) {
                $user = auth()->user();
                $user->update(['email' => $data['email']]);
                
                // Envia o e-mail pro novo endereço
                $user->sendEmailVerificationNotification();
                session()->put('verification_email_sent', true);

                \Filament\Notifications\Notification::make()
                    ->title('E-mail alterado e link reenviado com sucesso!')
                    ->success()
                    ->send();
            });
    }
}
