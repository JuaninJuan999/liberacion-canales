<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6 text-sm text-gray-700 p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-300">
        <strong>Área Segura</strong><br>
        Esta es una zona segura de la aplicación. Por favor, confirma tu contraseña para continuar.
    </div>

    <form wire:submit="confirmPassword" class="space-y-5">
        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">
                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                </svg>
                Contraseña
            </label>
            <input wire:model="password"
                   id="password"
                   class="form-input"
                   type="password"
                   name="password"
                   placeholder="••••••••"
                   required 
                   autocomplete="current-password" />
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="login-button">
            Confirmar
        </button>
    </form>
</div>
