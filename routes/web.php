<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Filament handles the root route ('/') directly now.

use App\Models\User;
use Illuminate\Http\Request;

Route::get('/verificar-email/{id}/{hash}', function ($id, $hash) {
    $user = User::findOrFail($id);
    if (! hash_equals((string) $hash, hash('sha256', $user->getEmailForVerification()))) {
        abort(403, 'Link de verificação inválido.');
    }
    
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));
    }
    
    return view('custom-verify-success');
})->middleware(['signed'])->name('custom.verification.verify');


Route::match(['get', 'post'], '/toggle-mode', function () {
    $current = session('view_mode', 'admin');
    session(['view_mode' => $current === 'admin' ? 'colaborador' : 'admin']);
    return redirect('/');
})->name('toggle.mode')->middleware(['web', 'auth']);

