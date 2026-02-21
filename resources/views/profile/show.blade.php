<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <p class="font-semibold">Nombre:</p>
                        <p>{{ $user->name }}</p>
                    </div>
                    <div class="mb-4">
                        <p class="font-semibold">Email:</p>
                        <p>{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="font-semibold">Miembro desde:</p>
                        <p>{{ $user->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
