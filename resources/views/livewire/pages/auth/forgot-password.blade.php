<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="mb-6 text-sm text-gray-600 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-300">
        <strong>Recuperar Contraseña</strong><br>
        Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="status-message">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">
                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                </svg>
                Correo Electrónico
            </label>
            <input wire:model="email" 
                   id="email" 
                   class="form-input" 
                   type="email" 
                   name="email" 
                   placeholder="tu@ejemplo.com"
                   required 
                   autofocus />
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="login-button">
            Enviar Enlace de Recuperación
        </button>

        <!-- Link to Login -->
        <div class="text-center pt-4 border-t border-gray-200">
            <a href="{{ route('login') }}" wire:navigate class="text-gray-500 text-sm hover:text-gray-700">
                Volver al inicio de sesión
            </a>
        </div>
    </form>
</div>
