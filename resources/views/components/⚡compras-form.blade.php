<?php

use App\Http\Controllers\CompraController as PageController;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        $view = app()->call([app(PageController::class), 'index']);

        return $view->getData();
    }
};
?>
<section class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="border-b border-slate-700 p-5">
            <h2 class="font-bold text-slate-100">Registrar compra y recibir mercadería</h2>
            <p class="mt-1 text-xs text-slate-400">El costo promedio ponderado y el historial de stock se actualizan en una sola operación.</p>
        </div>

        <form action="{{ route('compras.store') }}" method="POST" class="space-y-5 p-5">
            @csrf
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label class="space-y-1 text-xs text-slate-300">
                    <span>Proveedor</span>
                    <select name="id_proveedor" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                        <option value="">Selecciona un proveedor</option>
                        @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->id_proveedor }}" @selected(old('id_proveedor') == $proveedor->id_proveedor)>
                                {{ $proveedor->razon_social }} · {{ $proveedor->ruc }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1 text-xs text-slate-300">
                    <span>Tipo de comprobante</span>
                    <select name="tipo_comprobante" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                        @foreach(['Factura', 'Boleta', 'Ticket', 'Guía de remisión', 'Otro'] as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo_comprobante', 'Factura') === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1 text-xs text-slate-300">
                    <span>Número de comprobante</span>
                    <input name="numero_comprobante" value="{{ old('numero_comprobante') }}" required maxlength="50" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100" placeholder="F001-00001234">
                </label>
                <label class="space-y-1 text-xs text-slate-300">
                    <span>Fecha de compra</span>
                    <input type="date" name="fecha_compra" value="{{ old('fecha_compra', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                </label>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-100">Productos recibidos</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Indica cantidad y costo unitario de compra.</p>
                    </div>
                    <button id="add-purchase-line" type="button" class="inline-flex items-center gap-2 rounded-lg border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-xs font-bold text-amber-300 hover:bg-amber-500/20">
                        <i class="fa-solid fa-plus"></i>
                        Añadir producto
                    </button>
                </div>

                <div id="purchase-lines" class="space-y-3">
                    @foreach(array_values($lineasFormulario) as $index => $linea)
                        <div class="purchase-line grid grid-cols-1 gap-3 rounded-xl border border-slate-700 bg-slate-900/60 p-3 sm:grid-cols-[minmax(0,1fr)_120px_160px_auto]">
                            <label class="space-y-1 text-xs text-slate-300">
                                <span>Producto</span>
                                <select name="detalles[{{ $index }}][id_producto]" required class="purchase-product w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2.5 text-sm text-slate-100">
                                    <option value="">Selecciona producto</option>
                                    @foreach($productos as $producto)
                                        <option value="{{ $producto->id_producto }}" data-costo="{{ $producto->precio_compra }}" @selected(($linea['id_producto'] ?? '') == $producto->id_producto)>
                                            {{ $producto->codigo }} · {{ $producto->nombre }} (stock {{ $producto->stock }})
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="space-y-1 text-xs text-slate-300">
                                <span>Cantidad</span>
                                <input type="number" name="detalles[{{ $index }}][cantidad]" value="{{ $linea['cantidad'] ?? 1 }}" min="1" step="1" required class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2.5 text-sm text-slate-100">
                            </label>
                            <label class="space-y-1 text-xs text-slate-300">
                                <span>Costo unitario (S/)</span>
                                <input type="number" name="detalles[{{ $index }}][precio_unitario]" value="{{ $linea['precio_unitario'] ?? '' }}" min="0.01" step="0.01" required class="purchase-cost w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2.5 text-sm text-slate-100" placeholder="0.00">
                            </label>
                            <button type="button" class="remove-purchase-line self-end rounded-lg border border-rose-500/30 px-3 py-2.5 text-rose-300 hover:bg-rose-500/10" title="Quitar producto" aria-label="Quitar producto">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <label class="space-y-1 text-xs text-slate-300">
                    <span>Pago inicial (S/)</span>
                    <input type="number" name="monto_pagado" value="{{ old('monto_pagado', '0.00') }}" min="0" step="0.01" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                    <span class="block text-[11px] text-slate-500">El saldo restante queda como cuenta por pagar.</span>
                </label>
                <label class="space-y-1 text-xs text-slate-300">
                    <span>Medio de pago inicial</span>
                    <select name="medio_pago" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                        <option value="">Sin pago inicial</option>
                        @foreach(\App\Models\Caja::MEDIOS_PAGO as $medio)
                            <option value="{{ $medio }}" @selected(old('medio_pago') === $medio)>{{ ucfirst($medio) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1 text-xs text-slate-300">
                    <span>Observaciones</span>
                    <input name="observaciones" value="{{ old('observaciones') }}" maxlength="2000" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100" placeholder="Opcional">
                </label>
            </div>

            @unless($cajaVentasAbierta)
                <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-amber-200">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    No hay una Caja de Venta de Productos abierta. Puedes registrar la compra al crédito; para abonar, primero abre esa caja.
                </div>
            @endunless

            <div class="flex justify-end border-t border-slate-700 pt-4">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-extrabold text-slate-950 shadow-lg shadow-amber-500/10 transition hover:bg-amber-400">
                    <i class="fa-solid fa-check"></i>
                    Registrar compra y recibir stock
                </button>
            </div>
        </form>
    
<template id="purchase-line-template">
    <div class="purchase-line grid grid-cols-1 gap-3 rounded-xl border border-slate-700 bg-slate-900/60 p-3 sm:grid-cols-[minmax(0,1fr)_120px_160px_auto]">
        <label class="space-y-1 text-xs text-slate-300">
            <span>Producto</span>
            <select name="detalles[__INDEX__][id_producto]" required class="purchase-product w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2.5 text-sm text-slate-100">
                <option value="">Selecciona producto</option>
                @foreach($productos as $producto)
                    <option value="{{ $producto->id_producto }}" data-costo="{{ $producto->precio_compra }}">
                        {{ $producto->codigo }} · {{ $producto->nombre }} (stock {{ $producto->stock }})
                    </option>
                @endforeach
            </select>
        </label>
        <label class="space-y-1 text-xs text-slate-300">
            <span>Cantidad</span>
            <input type="number" name="detalles[__INDEX__][cantidad]" value="1" min="1" step="1" required class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2.5 text-sm text-slate-100">
        </label>
        <label class="space-y-1 text-xs text-slate-300">
            <span>Costo unitario (S/)</span>
            <input type="number" name="detalles[__INDEX__][precio_unitario]" min="0.01" step="0.01" required class="purchase-cost w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2.5 text-sm text-slate-100" placeholder="0.00">
        </label>
        <button type="button" class="remove-purchase-line self-end rounded-lg border border-rose-500/30 px-3 py-2.5 text-rose-300 hover:bg-rose-500/10" title="Quitar producto" aria-label="Quitar producto">
            <i class="fa-solid fa-trash"></i>
        </button>
    </div>
</template>
@push('page-scripts')
    @include('shared.compras-script')
@endpush
</section>
