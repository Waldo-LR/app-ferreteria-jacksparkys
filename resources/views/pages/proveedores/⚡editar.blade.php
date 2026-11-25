<?php

use Livewire\Component;

new class extends Component
{
};
?>

@extends('layouts.app')

@section('title', 'Editar proveedor - Ferretería Jack Sparkys S.A.C.')

@section('content')
    <div class="space-y-4">
        @livewire('proveedores-index', ['registroId' => request()->route('proveedore')])
    </div>
@endsection
