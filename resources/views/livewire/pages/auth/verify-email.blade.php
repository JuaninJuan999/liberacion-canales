<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="mb-6 text-sm text-gray-700 p-4 bg-green-50 rounded-lg border-l-4 border-green-300">
        <strong>Verificación de Correo Electrónico</strong><br>
        Gracias por registrarte. Hemos enviado un enlace de verificación a tu correo electrónico. 
        Por favor, verifica tu dirección antes de continuar.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="status-message mb-4">
            Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
        </div>
    @endif

    <div class="space-y-3">
        <button wire:click="sendVerification" class="login-button block w-full">
            <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
            </svg>
            Reenviar Enlace de Verificación
        </button>

        <button wire:click="logout" type="button" class="w-full px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-all">
            <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V4a1 1 0 00-1-1H3zm11 4.414l-4.707 4.707a1 1 0 11-1.414-1.414L12.586 6H10a1 1 0 110-2h4a1 1 0 011 1v4a1 1 0 11-2 0V7.414z" clip-rule="evenodd"></path>
            </svg>
            Cerrar Sesión
        </button>
    </div>
</div>
