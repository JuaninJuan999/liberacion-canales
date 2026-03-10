<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    @if (session('status'))
        <div class="status-message">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-5">
        <!-- Username -->
        <div class="form-group">
            <label for="username" class="form-label">
                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                </svg>
                Nombre de Usuario
            </label>
            <input wire:model="form.username" 
                   id="username" 
                   class="form-input" 
                   type="text" 
                   name="username" 
                     placeholder="Nombre de usuario"
                   required 
                   autofocus 
                   autocomplete="username" />
            @error('form.username')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">
                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                </svg>
                Contraseña
            </label>

            <div class="password-input-wrapper">
                <input wire:model="form.password" 
                       id="password" 
                       class="form-input w-full" 
                       type="password"
                       name="password"
                       placeholder="••••••••"
                       required 
                       autocomplete="current-password" />
                <button type="button" 
                        onclick="togglePasswordVisibility()" 
                        class="eye-toggle-btn"
                        title="Mostrar/Ocultar contraseña">
                    <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>

            @error('form.password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <script>
            function togglePasswordVisibility() {
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.setAttribute('fill', 'currentColor');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.setAttribute('fill', 'none');
                }
            }
        </script>

        <!-- Remember Me -->
        <div class="remember-me-container">
            <input wire:model="form.remember" 
                   id="remember" 
                   type="checkbox" 
                   name="remember">
            <label for="remember">Recuérdame en este dispositivo</label>
        </div>

        <!-- Login Button -->
        <button type="submit" class="login-button">
            Iniciar Sesión
        </button>

        <!-- Links -->
        <div class="text-center pt-4 border-t border-gray-200">
            @if (Route::has('password.request'))
                <div class="forgot-password-link">
                    <a href="{{ route('password.request') }}" wire:navigate>
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            @endif

            @if (Route::has('register'))
                <div class="forgot-password-link mt-3">
                    <span class="text-gray-500 text-sm">
                        ¿No tienes cuenta?
                        <a href="{{ route('register') }}" wire:navigate class="font-semibold">
                            Regístrate aquí
                        </a>
                    </span>
                </div>
            @endif
        </div>
    </form>
</div>
