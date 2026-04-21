<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <p class="text-sm text-slate-600 leading-relaxed mb-6">
        Actualiza tu nombre y correo electrónico. Si cambias el correo, puede ser necesario verificarlo de nuevo.
    </p>

    <form wire:submit="updateProfileInformation" class="space-y-6">
        <div>
            <x-input-label for="name" value="Nombre" class="text-slate-700" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full border-slate-200 focus:border-emerald-600 focus:ring-emerald-500/30" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" class="text-slate-700" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full border-slate-200 focus:border-emerald-600 focus:ring-emerald-500/30" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-slate-700">
                        Tu correo aún no está verificado.

                        <button type="button" wire:click.prevent="sendVerification" class="underline text-sm font-medium text-emerald-800 hover:text-emerald-950 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#7ce8ad]">
                            Reenviar correo de verificación
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-emerald-700">
                            Se envió un nuevo enlace de verificación a tu correo.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-1">
            <x-primary-button class="!normal-case !text-sm !font-semibold !px-5 !py-2.5 !rounded-xl !bg-gradient-to-r !from-[#f9dff8] !to-[#7ce8ad] !text-slate-900 !border-0 !shadow-md hover:!opacity-95 focus:!ring-2 focus:!ring-emerald-500/50 focus:!ring-offset-2">
                Guardar cambios
            </x-primary-button>

            <x-action-message class="me-3 text-emerald-800 font-medium" on="profile-updated">
                Guardado.
            </x-action-message>
        </div>
    </form>
</section>
