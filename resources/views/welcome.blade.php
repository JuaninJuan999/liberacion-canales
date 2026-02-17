@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="text-center py-12">
    <div class="card max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">
            🎉 Proyecto Liberación de Canales
        </h1>
        <p class="text-gray-600 mb-6">
            Sistema de control de calidad para el proceso de beneficio de reses
        </p>
        
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
            <strong class="font-bold">✅ Fase 1 Completada!</strong>
            <span class="block sm:inline">El setup inicial está listo.</span>
        </div>
        
        <div class="mt-8 text-left">
            <h2 class="text-xl font-semibold mb-4">Stack Tecnológico Instalado:</h2>
            <ul class="space-y-2">
                <li>✅ Laravel 12</li>
                <li>✅ Livewire 4</li>
                <li>✅ Tailwind CSS 4</li>
                <li>✅ MySQL</li>
                <li>✅ DomPDF</li>
            </ul>
            
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded p-4">
                <p class="text-sm text-blue-800">
                    <strong>📌 Próximo paso:</strong> Fase 2 - Crear migraciones y modelos
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
