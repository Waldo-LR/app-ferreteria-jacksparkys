<?php

use Livewire\Component;

new class extends Component
{
};
?>

@extends('layouts.app')

@section('title', 'Editar usuario - Ferretería Jack Sparkys S.A.C.')

@section('content')
    <div class="space-y-4">
        @livewire('usuarios-index', ['registroId' => request()->route('usuario')])
    </div>
@endsection
