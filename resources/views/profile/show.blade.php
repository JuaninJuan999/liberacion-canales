<x-app-layout>
    {{-- Mismo estilo que welcome-dashboard: fondo oscuro + #f9dff8 / #7ce8ad --}}
    <div
        class="relative w-full min-h-screen overflow-hidden"
        style="min-height:100vh;width:100%;background-color:#0c1222;background-image:radial-gradient(ellipse 80% 50% at 100% 0%, rgba(249,223,248,0.18), transparent 55%), radial-gradient(ellipse 70% 45% at 0% 100%, rgba(124,232,173,0.16), transparent 50%);"
    >
        <div class="pointer-events-none absolute -right-24 -top-32 h-[22rem] w-[22rem] rounded-full bg-[#f9dff8]/25 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-20 h-[20rem] w-[20rem] rounded-full bg-[#7ce8ad]/20 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-1/2 top-1/3 h-64 w-64 -translate-x-1/2 rounded-full bg-[#f9dff8]/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative z-10">
            {{-- Cabecera tipo Welcome --}}
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                <div class="overflow-hidden rounded-xl border border-white/10 bg-white/95 shadow-xl shadow-black/30 backdrop-blur-sm ring-1 ring-[#f9dff8]/30">
                    <div class="h-1.5 bg-gradient-to-r from-[#f9dff8] via-white to-[#7ce8ad]"></div>
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <img src="{{ asset('logo.png') }}" alt="Logo" class="h-6 sm:h-10 max-w-[40px] sm:max-w-[80px] object-contain flex-shrink-0">
                            <div class="min-w-0">
                                <h1 class="text-xl sm:text-3xl font-bold text-slate-900 truncate">Mi perfil</h1>
                                <p class="text-sm sm:text-base text-slate-600 mt-1 truncate">{{ auth()->user()->name }} — datos de cuenta y seguridad</p>
                                @php $rolUsuario = auth()->user()->rolNormalizado(); @endphp
                                @if(!empty($rolUsuario))
                                    <p class="text-xs font-semibold text-emerald-800 mt-2 inline-flex items-center gap-1.5 rounded-md bg-[#7ce8ad]/35 px-2 py-1 border border-emerald-800/10">
                                        <span class="opacity-80">Rol:</span> {{ $rolUsuario }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-12">

                @if (session('success'))
                    <div class="rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-slate-100 shadow-lg backdrop-blur-sm mb-10">
                        <span class="font-semibold text-[#7ce8ad]">✓</span> {{ session('success') }}
                    </div>
                @endif

                {{-- Datos del perfil --}}
                <div class="space-y-8">
                    <h2
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-xl sm:text-2xl font-bold shadow-lg border border-slate-900/10"
                        style="background: linear-gradient(90deg, #f9dff8 0%, #7ce8ad 100%); color: #0f172a;"
                    >
                        <span class="h-8 w-1.5 shrink-0 rounded-full bg-slate-900/80" aria-hidden="true"></span>
                        Datos del perfil
                    </h2>
                    <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-lg shadow-slate-900/25 border-l-4 border-l-[#f9dff8] hover:ring-2 hover:ring-[#7ce8ad]/35 transition-all duration-300 max-w-2xl">
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#f9dff8] to-[#7ce8ad] opacity-90"></div>
                        <div class="pt-1 max-w-xl">
                            <livewire:profile.update-profile-information-form />
                        </div>
                    </div>
                </div>

                {{-- Contraseña: margen superior para separar la franja de la tarjeta de arriba --}}
                <div class="mt-12 sm:mt-16 space-y-8">
                    <h2
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-xl sm:text-2xl font-bold shadow-lg border border-slate-900/10"
                        style="background: linear-gradient(90deg, #7ce8ad 0%, #f9dff8 100%); color: #0f172a;"
                    >
                        <span class="h-8 w-1.5 shrink-0 rounded-full bg-slate-900/80" aria-hidden="true"></span>
                        Cambiar contraseña
                    </h2>
                    <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-lg shadow-slate-900/25 border-l-4 border-l-[#7ce8ad] hover:ring-2 hover:ring-[#f9dff8]/50 transition-all duration-300 max-w-2xl">
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#7ce8ad] to-[#f9dff8] opacity-90"></div>
                        <div class="pt-1 max-w-xl">
                            <livewire:profile.update-password-form />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
