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

    <form wire:submit="login">
        <!-- Username -->
        <div class="form-group">
            <label for="username" class="form-label">Usuario</label>
            <div class="form-input-wrapper">
                <input wire:model="form.username" 
                       id="username" 
                       class="form-input" 
                       type="text" 
                       name="username" 
                       placeholder="Ingresa tu usuario"
                       required 
                       autofocus 
                       autocomplete="username" />
                <span class="form-input-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
            </div>
            @error('form.username')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">Contraseña</label>
            <div class="password-input-wrapper">
                <div class="form-input-wrapper">
                    <input wire:model="form.password" 
                           id="password" 
                           class="form-input" 
                           type="password"
                           name="password"
                           placeholder="Ingresa tu contraseña"
                           required 
                           autocomplete="current-password"
                           style="padding-left: 44px; padding-right: 44px;" />
                    <span class="form-input-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                </div>
                <button type="button" 
                        onclick="togglePasswordVisibility()" 
                        class="eye-toggle-btn"
                        title="Mostrar/Ocultar contraseña">
                    <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path id="eyeOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path id="eyePath" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
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
                    eyeIcon.style.color = 'var(--color-primary-light, #34d399)';
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.style.color = '';
                }
            }
        </script>

        <!-- Remember Me -->
        <div class="remember-me-container">
            <div class="remember-me-left">
                <input wire:model="form.remember" 
                       id="remember" 
                       type="checkbox" 
                       name="remember">
                <label for="remember">Recuérdame</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate style="color: var(--color-gray-light, #94a3b8); font-size: 13px; text-decoration: none; font-weight: 500; transition: color 0.2s;">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <button type="submit" class="login-button">
            <span wire:loading.remove wire:target="login">Iniciar Sesión</span>
            <span wire:loading wire:target="login">
                <svg class="inline w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Verificando...
            </span>
        </button>


    </form>
</div>
