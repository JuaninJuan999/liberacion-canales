<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asignación de Operarios por Día
        </h2>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            @livewire('gestion-operarios-dia')
        </div>
    </div>
</x-app-layout>
