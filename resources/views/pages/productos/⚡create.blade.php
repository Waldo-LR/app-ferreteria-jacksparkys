<?php

use Livewire\Component;

new class extends Component {};
?>

@extends('layouts.app')

@section('title', 'Registrar Productos - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div><h1 class="text-2xl font-black text-slate-100">Registrar Productos</h1><p class="mt-1 text-sm text-slate-400">El stock inicial también quedará registrado en el historial.</p></div>
    @if($errors->any()) <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-200"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    @livewire('registro-productos-form')
</div>
@endsection
