<x-filament-panels::page.simple wire:poll.3s="checkIfVerified">
    <p class="text-center text-sm text-gray-500 dark:text-gray-400">
        Enviamos um e-mail para <span class="font-bold">{{ filament()->auth()->user()->email }}</span> contendo instruções sobre como verificar seu e-mail.
    </p>

    <p class="text-center text-sm text-gray-500 dark:text-gray-400">
        Não recebeu o e-mail que enviamos?
        {{ $this->resendNotificationAction }}
    </p>

    <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
        {{ $this->updateEmailAction }}
    </div>
</x-filament-panels::page.simple>
