<?php

use Livewire\Component;

new class extends Component
{
};
?>

@extends('layouts.app')

@section('title', 'Registrar Ventas - Ferretería Jack Sparkys')

@section('content')
    <div class="space-y-4">
        @livewire('ventas-pos')
    </div>
@endsection
