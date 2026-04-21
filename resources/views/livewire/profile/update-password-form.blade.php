<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
    <p class="text-sm text-slate-600 leading-relaxed mb-6">
        Escribe tu contraseña actual. La nueva debe cumplir las reglas de seguridad del sistema.
    </p>

    <form wire:submit="updatePassword" class="space-y-6">
        <div>
            <x-input-label for="update_password_current_password" value="Contraseña actual" class="text-slate-700" />
            <div class="relative mt-1">
                <x-text-input
                    wire:model="current_password"
                    id="update_password_current_password"
                    name="current_password"
                    x-bind:type="showCurrent ? 'text' : 'password'"
                    class="block w-full pr-11 border-slate-200 focus:border-emerald-600 focus:ring-emerald-500/30"
                    autocomplete="current-password"
                />
                <button
                    type="button"
                    @click="showCurrent = !showCurrent"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-emerald-700/70 hover:text-slate-900 focus:outline-none rounded-r-md"
                    :title="showCurrent ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    :aria-label="showCurrent ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                >
                    <svg x-show="!showCurrent" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showCurrent" x-cloak class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.182 4.182L12 12" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nueva contraseña" class="text-slate-700" />
            <div class="relative mt-1">
                <x-text-input
                    wire:model="password"
                    id="update_password_password"
                    name="password"
                    x-bind:type="showNew ? 'text' : 'password'"
                    class="block w-full pr-11 border-slate-200 focus:border-emerald-600 focus:ring-emerald-500/30"
                    autocomplete="new-password"
                />
                <button
                    type="button"
                    @click="showNew = !showNew"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-emerald-700/70 hover:text-slate-900 focus:outline-none rounded-r-md"
                    :title="showNew ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    :aria-label="showNew ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                >
                    <svg x-show="!showNew" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showNew" x-cloak class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.182 4.182L12 12" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar nueva contraseña" class="text-slate-700" />
            <div class="relative mt-1">
                <x-text-input
                    wire:model="password_confirmation"
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    x-bind:type="showConfirm ? 'text' : 'password'"
                    class="block w-full pr-11 border-slate-200 focus:border-emerald-600 focus:ring-emerald-500/30"
                    autocomplete="new-password"
                />
                <button
                    type="button"
                    @click="showConfirm = !showConfirm"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-emerald-700/70 hover:text-slate-900 focus:outline-none rounded-r-md"
                    :title="showConfirm ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    :aria-label="showConfirm ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                >
                    <svg x-show="!showConfirm" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showConfirm" x-cloak class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.182 4.182L12 12" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-1">
            <x-primary-button class="!normal-case !text-sm !font-semibold !px-5 !py-2.5 !rounded-xl !bg-gradient-to-r !from-[#7ce8ad] !to-[#f9dff8] !text-slate-900 !border-0 !shadow-md hover:!opacity-95 focus:!ring-2 focus:!ring-emerald-500/50 focus:!ring-offset-2">
                Actualizar contraseña
            </x-primary-button>

            <x-action-message class="me-3 text-emerald-800 font-medium" on="password-updated">
                Contraseña actualizada.
            </x-action-message>
        </div>
    </form>
</section>
