<?php

use Livewire\Component;

new class extends Component
{
};
?>

@extends('layouts.app')

@section('title', 'Categorías de Productos - Ferretería Jack Sparkys S.A.C.')

@section('content')
    <div class="space-y-6 max-w-4xl">
        <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Categorías de Ferretería</h1>
            <p class="text-xs text-slate-400">Clasificación de artículos y herramientas</p>
        </div>
    </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @livewire('categorias-form')
            @livewire('categorias-index')
        </div>
    </div>
@endsection
