<?php

use Livewire\Component;

new class extends Component
{
};
?>

@extends('layouts.app')

@section('title', 'Editar cliente - Ferretería Jack Sparkys S.A.C.')

@section('content')
    <div class="space-y-4">
        @livewire('clientes-index', ['registroId' => request()->route('cliente')])
    </div>
@endsection
