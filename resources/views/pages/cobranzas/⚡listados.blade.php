<?php

use Livewire\Component;

new class extends Component
{
};
?>

@extends('layouts.app')

@section('title', 'Historial de Cobranzas - Ferretería Jack Sparkys')

@section('content')
    <div class="space-y-4">
        @livewire('cobranzas-index')
    </div>
@endsection
