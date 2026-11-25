<?php

use Livewire\Component;

new class extends Component
{
};
?>

@extends('layouts.app')

@section('title', 'Historial de Alquileres - Ferretería Jack Sparkys S.A.C.')

@section('content')
    <div class="space-y-4">
        @livewire('alquileres-index')
    </div>
@endsection
