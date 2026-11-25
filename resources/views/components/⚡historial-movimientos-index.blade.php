<?php

use App\Http\Controllers\InventarioController as PageController;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        $view = app()->call([app(PageController::class), 'historial']);

        return $view->getData();
    }
};
?>
<section class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
    <div class="border-b border-slate-700 p-5">
        <h1 class="text-2xl font-black text-slate-100">Historial de Movimientos</h1>
        <p class="mt-1 text-xs text-slate-400">Incluye ventas, alquileres, devoluciones y ajustes manuales.</p>
        @livewire('historial-movimientos-form', ['filters' => $filters])
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[850px] text-left text-sm">
            <thead class="bg-slate-900/70 text-[10px] uppercase tracking-wider text-slate-400">
                <tr>
                    <th class="px-5 py-3">Fecha</th>
                    <th class="px-5 py-3">Producto</th>
                    <th class="px-5 py-3">Tipo</th>
                    <th class="px-5 py-3 text-right">Cantidad</th>
                    <th class="px-5 py-3 text-right">Stock resultante</th>
                    <th class="px-5 py-3">Motivo / referencia</th>
                    <th class="px-5 py-3">Responsable</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/80">
                @forelse($movements as $movement)
                    <tr class="hover:bg-slate-700/20">
                        <td class="whitespace-nowrap px-5 py-3.5 text-xs text-slate-400">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3.5">
                            <span class="block font-semibold text-slate-100">{{ $movement->producto?->nombre ?? 'Producto eliminado' }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ $movement->producto?->codigo }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                                $movementLabel = match ($movement->tipo) {
                                    'entrada' => 'Entrada',
                                    'salida' => 'Salida',
                                    'ajuste' => 'Ajuste',
                                    default => ucfirst($movement->tipo),
                                };
                            @endphp
                            <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $movement->tipo === 'entrada' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' : ($movement->tipo === 'salida' ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : 'border-amber-500/30 bg-amber-500/10 text-amber-300') }}">
                                {{ $movementLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold text-slate-200">{{ number_format($movement->cantidad) }}</td>
                        <td class="px-5 py-3.5 text-right font-bold text-slate-100">{{ number_format($movement->stock_resultante) }}</td>
                        <td class="max-w-xs px-5 py-3.5 text-xs text-slate-300">
                            {{ $movement->motivo }}
                            @if($movement->referencia)
                                <span class="mt-1 block text-slate-500">Ref: {{ $movement->referencia }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-300">{{ $movement->usuario?->nombre ?? 'Usuario no disponible' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">No hay movimientos que coincidan con los filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-700 p-4">{{ $movements->links() }}</div>
</section>
