<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register" class="space-y-5">
        <!-- Name -->
        <div class="form-group">
            <label for="name" class="form-label">
                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
                Nombre Completo
            </label>
            <input wire:model="name" 
                   id="name" 
                   class="form-input" 
                   type="text" 
                   name="name" 
                   placeholder="Tu nombre completo"
                   required 
                   autofocus 
                   autocomplete="name" />
            @error('name')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

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
                   autocomplete="username" />
            @error('email')
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
            <input wire:model="password" 
                   id="password" 
                   class="form-input" 
                   type="password"
                   name="password"
                   placeholder="••••••••"
                   required 
                   autocomplete="new-password" />
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation" class="form-label">
                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                </svg>
                Confirmar Contraseña
            </label>
            <input wire:model="password_confirmation" 
                   id="password_confirmation" 
                   class="form-input"
                   type="password"
                   name="password_confirmation" 
                   placeholder="••••••••"
                   required 
                   autocomplete="new-password" />
            @error('password_confirmation')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Register Button -->
        <button type="submit" class="login-button">
            Registrarse
        </button>

        <!-- Link to Login -->
        <div class="text-center pt-4 border-t border-gray-200">
            <span class="text-gray-500 text-sm">
                ¿Ya tienes cuenta?
                <a class="text-pink-500 hover:text-pink-600 font-semibold" href="{{ route('login') }}" wire:navigate>
                    Inicia sesión aquí
                </a>
            </span>
        </div>
    </form>
</div>
