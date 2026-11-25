<?php

use App\Http\Controllers\BajasProductosController as PageController;
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
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Historial de Bajas de Productos</h1>
            <p class="text-xs text-slate-400">Consulta de productos dados de baja en el inventario.</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2">
            <span class="block text-[10px] font-bold uppercase text-amber-300">Bajas registradas</span>
            <span class="text-lg font-extrabold text-amber-400">{{ $bajas->total() }}</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300" role="alert">
            <p class="font-bold">Revisa los filtros:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('bajas.index') }}" method="GET" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4 lg:grid-cols-[minmax(0,1fr)_180px_180px_auto_auto] lg:items-end">
        <div>
            <label for="buscar" class="mb-1 block text-xs font-semibold text-slate-300">Búsqueda</label>
            <input id="buscar" type="search" name="buscar" value="{{ request('buscar') }}"
                placeholder="Producto, código, categoría o marca"
                maxlength="100"
                class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-500 focus:outline-none">
        </div>
        <div>
            <label for="fecha_desde" class="mb-1 block text-xs font-semibold text-slate-300">Desde</label>
            <input id="fecha_desde" type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
        </div>
        <div>
            <label for="fecha_hasta" class="mb-1 block text-xs font-semibold text-slate-300">Hasta</label>
            <input id="fecha_hasta" type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
        </div>
        <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
            Buscar
        </button>
        @if (request()->hasAny(['buscar', 'fecha_desde', 'fecha_hasta']))
            <a href="{{ route('bajas.index') }}" class="rounded-lg border border-slate-600 px-4 py-2.5 text-center text-sm font-semibold text-slate-300 transition hover:bg-slate-700">
                Limpiar
            </a>
        @endif
    </form>

    <section class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-900/80 text-xs uppercase text-slate-400">
                    <tr>
                        <th class="p-4">Fecha de baja</th>
                        <th class="p-4">Producto</th>
                        <th class="p-4">Código</th>
                        <th class="p-4">Categoría / Marca</th>
                        <th class="p-4 text-right">Precio venta</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4">Usuario baja</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse ($bajas as $baja)
                        <tr class="align-top transition hover:bg-slate-700/40">
                            <td class="whitespace-nowrap p-4 text-xs text-slate-300">
                                {{ ($baja->fecha_baja instanceof \Carbon\Carbon ? $baja->fecha_baja->format('d/m/Y H:i:s') : (is_string($baja->fecha_baja) ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y H:i:s') : 'Sin fecha')) }}
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="shrink-0">
                                        @include('shared.producto-miniatura', ['producto' => $baja])
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-100">{{ $baja->nombre }}</div>
                                        <div class="mt-0.5 text-xs text-slate-400">{{ $baja->codigo }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-xs text-slate-300">{{ $baja->codigo }}</td>
                            <td class="p-4 text-xs text-slate-300">
                                {{ $baja->categoria?->nombre ?? 'Sin categoría' }} / {{ $baja->marca?->nombre ?? 'Sin marca' }}
                            </td>
                            <td class="p-4 text-right text-xs font-medium text-slate-200">
                                S/ {{ number_format((float) $baja->precio_venta, 2) }}
                            </td>
                            <td class="p-4 text-xs">
                                <span class="inline-flex rounded-full border border-rose-500/30 bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-300">
                                    Dado de baja
                                </span>
                            </td>
                            <td class="p-4 text-xs text-slate-300">
                                {{ $baja->usuarioBaja?->nombre ?? 'No registrado' }}
                            </td>
                            <td class="p-4 text-center">
                                <form action="{{ route('bajas.reactivar', $baja) }}" method="POST" onsubmit="return confirm('¿Confirma habilitar este producto nuevamente?');">
                                    @csrf
                                    <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-emerald-500/30 text-emerald-300 transition hover:bg-emerald-500 hover:text-white" aria-label="Habilitar {{ $baja->nombre }}" title="Habilitar producto">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-sm text-slate-400">
                                No se encontraron productos dados de baja con esos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($bajas->hasPages())
            <div class="border-t border-slate-700 p-3">{{ $bajas->links() }}</div>
        @endif
    </section>
    @include('shared.producto-vista-ampliada')
</div>

</div>
