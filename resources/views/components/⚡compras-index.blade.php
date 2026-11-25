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
<div>
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-bold text-slate-100">
                <i class="fa-solid fa-cart-flatbed text-amber-400"></i>
                Compras
            </h1>
            <p class="mt-1 text-sm text-slate-400">Recepción de mercadería, actualización de costos y seguimiento de pagos.</p>
        </div>
        <a href="{{ route('inventario.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-amber-300 hover:text-amber-200">
            <i class="fa-solid fa-warehouse"></i>
            Ver inventario
        </a>
    </div>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-300">
            <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-200">
            <p class="mb-2 font-bold">No se pudo completar la operación:</p>
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-700 bg-slate-800 p-4">
            <p class="text-xs font-medium text-slate-400">Compras recibidas</p>
            <p class="mt-1 text-xl font-bold text-slate-100">S/ {{ number_format($resumen['total'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-700 bg-slate-800 p-4">
            <p class="text-xs font-medium text-slate-400">Pagos a proveedores</p>
            <p class="mt-1 text-xl font-bold text-emerald-400">S/ {{ number_format($resumen['pagado'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4">
            <p class="text-xs font-medium text-rose-200">Saldo pendiente</p>
            <p class="mt-1 text-xl font-bold text-rose-300">S/ {{ number_format($resumen['pendiente'], 2) }}</p>
        </div>
    </div>

    @livewire('compras-form')

    <section class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="border-b border-slate-700 p-5">
            <h2 class="font-bold text-slate-100">Historial de compras y pagos</h2>
            <form method="GET" action="{{ route('compras.index') }}" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Comprobante o proveedor" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <select name="id_proveedor" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    <option value="">Todos los proveedores</option>
                    @foreach($proveedores as $proveedor)
                        <option value="{{ $proveedor->id_proveedor }}" @selected(($filters['id_proveedor'] ?? '') == $proveedor->id_proveedor)>{{ $proveedor->razon_social }}</option>
                    @endforeach
                </select>
                <input type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] ?? '' }}" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100" aria-label="Fecha desde">
                <div class="flex gap-2">
                    <input type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] ?? '' }}" class="min-w-0 flex-1 rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100" aria-label="Fecha hasta">
                    <button class="rounded-lg bg-slate-700 px-3 text-sm font-bold text-slate-100 hover:bg-slate-600" aria-label="Filtrar compras">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>

        @if($compras->isEmpty())
            <div class="p-10 text-center text-sm text-slate-400">
                <i class="fa-solid fa-box-open mb-3 block text-2xl text-slate-500"></i>
                No hay compras que coincidan con los filtros.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left text-sm">
                    <thead class="bg-slate-900/70 text-xs uppercase text-slate-400">
                        <tr>
                            <th class="px-5 py-3">Fecha / proveedor</th>
                            <th class="px-5 py-3">Comprobante</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Pagado</th>
                            <th class="px-5 py-3 text-right">Saldo</th>
                            <th class="px-5 py-3">Estado de pago</th>
                            <th class="px-5 py-3 text-right">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @foreach($compras as $compra)
                            <tr class="align-top text-slate-300">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-100">{{ $compra->proveedor->razon_social }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ $compra->fecha_compra->format('d/m/Y') }} · {{ $compra->usuario->nombre }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-100">{{ $compra->tipo_comprobante }}</div>
                                    <div class="text-xs text-slate-400">{{ $compra->numero_comprobante }}</div>
                                </td>
                                <td class="px-5 py-4 text-right font-semibold text-slate-100">S/ {{ number_format($compra->total, 2) }}</td>
                                <td class="px-5 py-4 text-right text-emerald-300">S/ {{ number_format($compra->monto_pagado, 2) }}</td>
                                <td class="px-5 py-4 text-right font-bold {{ $compra->saldo_pendiente > 0 ? 'text-rose-300' : 'text-slate-300' }}">S/ {{ number_format($compra->saldo_pendiente, 2) }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $compra->estado_pago === 'pagada' ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : ($compra->estado_pago === 'parcial' ? 'border-amber-500/20 bg-amber-500/10 text-amber-300' : ($compra->estado_pago === 'sin_conciliar' ? 'border-slate-600 bg-slate-700 text-slate-300' : 'border-rose-500/20 bg-rose-500/10 text-rose-300')) }}">
                                        {{ $compra->estado_pago === 'pagada' ? 'Pagada' : ($compra->estado_pago === 'parcial' ? 'Pago parcial' : ($compra->estado_pago === 'sin_conciliar' ? 'Registro anterior' : 'Pendiente')) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <details class="group relative text-left">
                                        <summary title="Ver detalle" aria-label="Ver detalle" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-blue-500/40 text-blue-400 hover:bg-blue-500/10 cursor-pointer list-none">
                                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                        </summary>
                                        <div class="absolute right-4 z-20 mt-2 w-[min(90vw,42rem)] rounded-xl border border-slate-600 bg-slate-900 p-4 text-left shadow-2xl">
                                            <h3 class="font-bold text-slate-100">Productos recibidos</h3>
                                            <div class="mt-2 space-y-2">
                                                @foreach($compra->detalles as $detalle)
                                                    <div class="flex justify-between gap-3 border-b border-slate-800 pb-2 text-xs">
                                                        <span>{{ $detalle->producto->codigo }} · {{ $detalle->producto->nombre }} × {{ $detalle->cantidad }}</span>
                                                        <span class="shrink-0">S/ {{ number_format($detalle->subtotal, 2) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <h3 class="mt-4 font-bold text-slate-100">Pagos registrados</h3>
                                            @forelse($compra->pagos as $pago)
                                                <div class="mt-2 flex justify-between gap-3 text-xs text-slate-300">
                                                    <span>{{ $pago->created_at->format('d/m/Y H:i') }} · {{ ucfirst($pago->medio_pago) }} · {{ $pago->usuario->nombre }}</span>
                                                    <span class="shrink-0 text-emerald-300">S/ {{ number_format($pago->monto, 2) }}</span>
                                                </div>
                                            @empty
                                                <p class="mt-2 text-xs text-slate-400">Aún no se han registrado pagos.</p>
                                            @endforelse

                                            @if($compra->estado === 'recibida' && $compra->saldo_pendiente > 0)
                                                <form action="{{ route('compras.pagos.store', $compra) }}" method="POST" class="mt-4 grid grid-cols-1 gap-2 border-t border-slate-700 pt-4 sm:grid-cols-[1fr_1fr_auto]">
                                                    @csrf
                                                    <label class="space-y-1 text-xs text-slate-300">
                                                        <span>Monto a pagar (máx. S/ {{ number_format($compra->saldo_pendiente, 2) }})</span>
                                                        <input type="number" name="monto" min="0.01" max="{{ number_format($compra->saldo_pendiente, 2, '.', '') }}" step="0.01" required class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100">
                                                    </label>
                                                    <label class="space-y-1 text-xs text-slate-300">
                                                        <span>Medio de pago</span>
                                                        <select name="medio_pago" required class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100">
                                                            @foreach(\App\Models\Caja::MEDIOS_PAGO as $medio)
                                                                <option value="{{ $medio }}">{{ ucfirst($medio) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <button type="submit" @disabled(!$cajaVentasAbierta) title="Registrar pago" aria-label="Registrar pago" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10 disabled:cursor-not-allowed disabled:opacity-50">
                                                        <i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i>
                                                    </button>
                                                    @unless($cajaVentasAbierta)
                                                        <p class="text-xs text-amber-300 sm:col-span-3">Abre la caja de ventas para registrar un pago.</p>
                                                    @endunless
                                                </form>
                                            @endif
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-700 p-4">{{ $compras->links() }}</div>
        @endif
    </section>
</div>

</div>
