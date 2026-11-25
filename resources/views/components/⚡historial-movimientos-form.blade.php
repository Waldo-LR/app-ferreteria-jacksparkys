<?php

use Livewire\Component;

new class extends Component
{
    public array $filters = [];
};
?>
<form action="{{ route('historial-movimientos.index') }}" method="GET" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_180px_170px_170px_auto]">
    <label>
        <span class="sr-only">Buscar producto en movimientos</span>
        <input type="search" name="producto_historial" value="{{ $filters['producto_historial'] ?? '' }}" placeholder="Producto o código..." class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none">
    </label>
    <label>
        <span class="sr-only">Tipo de movimiento</span>
        <select name="tipo_movimiento" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
            <option value="">Todos los movimientos</option>
            <option value="entrada" @selected(($filters['tipo_movimiento'] ?? '') === 'entrada')>Entradas</option>
            <option value="salida" @selected(($filters['tipo_movimiento'] ?? '') === 'salida')>Salidas</option>
            <option value="ajuste" @selected(($filters['tipo_movimiento'] ?? '') === 'ajuste')>Ajustes</option>
        </select>
    </label>
    <label>
        <span class="sr-only">Desde</span>
        <input type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] ?? '' }}" aria-label="Desde" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
    </label>
    <label>
        <span class="sr-only">Hasta</span>
        <input type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] ?? '' }}" aria-label="Hasta" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
    </label>
    <button type="submit" class="rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-bold text-slate-100 transition hover:bg-slate-600">
        <i class="fa-solid fa-filter mr-1"></i>
        Filtrar
    </button>
</form>
