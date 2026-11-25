<?php

use App\Http\Controllers\AlquilerController as PageController;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        $view = app()->call([app(PageController::class), 'create']);

        return $view->getData();
    }
};
?>
<section id="registro-alquiler" class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="border-b border-slate-700 p-5">
            <h2 class="font-bold text-slate-100">Registrar alquiler</h2>
            <p class="mt-1 text-xs text-slate-400">Un equipo por operación. Se requiere garantía; el alquiler puede pagarse en abonos.</p>
        </div>
        <form action="{{ route('alquileres.store') }}" method="POST" class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
            @csrf
            <label class="space-y-1 text-xs text-slate-300">
                <span>Cliente</span>
                <select name="id_cliente" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                    <option value="">Selecciona un cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id_cliente }}" @selected(old('id_cliente') == $cliente->id_cliente) @disabled(in_array($cliente->id_cliente, $clientesBloqueados, true))>
                            {{ $cliente->nombres }} · {{ $cliente->dni_ruc }}{{ in_array($cliente->id_cliente, $clientesBloqueados, true) ? ' · Tiene pendiente' : '' }}
                        </option>
                    @endforeach
                </select>
                <span class="block text-[11px] text-slate-500">Clientes con equipo sin devolver o deuda no pueden iniciar otro alquiler.</span>
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Equipo / herramienta</span>
                <select name="id_producto" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                    <option value="">Selecciona un equipo</option>
                    @foreach($herramientas as $herramienta)
                        <option value="{{ $herramienta->id_producto }}" @selected(old('id_producto') == $herramienta->id_producto)>
                            {{ $herramienta->codigo }} · {{ $herramienta->nombre }} · {{ $herramienta->stock }} disp. · S/ {{ number_format($herramienta->precio_alquiler_dia, 2) }}/día
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Fecha de entrega</span>
                <input type="date" name="fecha_entrega" value="{{ old('fecha_entrega', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Días pactados</span>
                <input type="number" name="dias_pactados" value="{{ old('dias_pactados', 1) }}" min="1" max="3650" step="1" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Pago inicial del alquiler (S/)</span>
                <input type="number" name="monto_pagado" value="{{ old('monto_pagado', '0.00') }}" min="0" step="0.01" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Medio del pago inicial</span>
                <select name="medio_pago_alquiler" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                    <option value="">Sin pago inicial</option>
                    @foreach(\App\Models\Caja::MEDIOS_PAGO as $medio)
                        <option value="{{ $medio }}" @selected(old('medio_pago_alquiler') === $medio)>{{ ucfirst($medio) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Garantía del equipo (S/)</span>
                <input type="number" name="monto_garantia" value="{{ old('monto_garantia') }}" min="0.01" step="0.01" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Medio de la garantía</span>
                <select name="medio_pago_garantia" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                    @foreach(\App\Models\Caja::MEDIOS_PAGO as $medio)
                        <option value="{{ $medio }}" @selected(old('medio_pago_garantia', 'efectivo') === $medio)>{{ ucfirst($medio) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-xs text-slate-300 md:col-span-2">
                <span>Ubicación del equipo / obra</span>
                <input name="ubicacion_herramienta" value="{{ old('ubicacion_herramienta') }}" maxlength="200" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100" placeholder="Dirección o referencia">
            </label>
            <label class="space-y-1 text-xs text-slate-300 md:col-span-2">
                <span>Observaciones</span>
                <input name="observaciones" value="{{ old('observaciones') }}" maxlength="2000" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100" placeholder="Condición del equipo al entregar, accesorios, etc.">
            </label>
            <div class="flex flex-col justify-end gap-2 md:col-span-2 xl:col-span-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-400">El saldo del alquiler se registra en Cobranzas. La garantía se contabiliza aparte y no se considera ingreso por alquiler.</p>
                <button type="submit" @disabled(!$cajaActual) class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fa-solid fa-file-circle-plus"></i> Registrar y entregar equipo
                </button>
            </div>
        </form>
    </section>
