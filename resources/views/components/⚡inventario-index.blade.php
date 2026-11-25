<?php

use App\Http\Controllers\InventarioController as PageController;
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
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-black text-slate-100">
                <i class="fa-solid fa-warehouse text-amber-400"></i>
                Control de Inventario
            </h1>
            <p class="mt-1 text-sm text-slate-400">Consulta existencias y mantén trazabilidad de cada movimiento.</p>
        </div>
        @if($canManageInventory)
            <a href="{{ route('productos.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                <i class="fa-solid fa-plus"></i>
                Registrar producto
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('inventario.index') }}" class="rounded-2xl border border-slate-700 bg-slate-800 p-5 transition hover:border-slate-500">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-400">Productos activos</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-400"><i class="fa-solid fa-boxes-stacked"></i></span>
            </div>
            <p class="mt-3 text-3xl font-black text-slate-100">{{ number_format($summary['products']) }}</p>
            <p class="mt-1 text-xs text-slate-500">En el catálogo</p>
        </a>
        <a href="{{ route('inventario.index', ['stock' => 'bajo']) }}" class="rounded-2xl border border-slate-700 bg-slate-800 p-5 transition hover:border-rose-500/50">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-400">Por reponer</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-400"><i class="fa-solid fa-triangle-exclamation"></i></span>
            </div>
            <p class="mt-3 text-3xl font-black text-slate-100">{{ number_format($summary['low_stock']) }}</p>
            <p class="mt-1 text-xs text-slate-500">En o bajo el mínimo</p>
        </a>
        <div class="rounded-2xl border border-slate-700 bg-slate-800 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-400">Valor al costo</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400"><i class="fa-solid fa-sack-dollar"></i></span>
            </div>
            <p class="mt-3 text-3xl font-black text-slate-100">S/ {{ number_format((float) $summary['stock_value'], 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Existencias por costo registrado</p>
        </div>
        <a href="{{ route('historial-movimientos.index') }}" class="rounded-2xl border border-slate-700 bg-slate-800 p-5 transition hover:border-amber-500/50">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-400">Movimientos de hoy</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400"><i class="fa-solid fa-arrow-right-arrow-left"></i></span>
            </div>
            <p class="mt-3 text-3xl font-black text-slate-100">{{ number_format($summary['today_movements']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Ventas, alquileres y ajustes</p>
        </a>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="border-b border-slate-700 p-5">
            <div class="mb-4">
                <h2 class="font-bold text-slate-100">Existencias</h2>
                <p class="mt-1 text-xs text-slate-400">Busca por nombre o código y revisa productos bajo su nivel mínimo.</p>
            </div>
            <form action="{{ route('inventario.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_200px_auto]">
                <label class="relative">
                    <span class="sr-only">Buscar producto</span>
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por producto o código..." class="w-full rounded-xl border border-slate-600 bg-slate-900 py-2.5 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none">
                </label>
                <label>
                    <span class="sr-only">Filtrar por disponibilidad</span>
                    <select name="stock" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                        <option value="">Todos los niveles</option>
                        <option value="bajo" @selected(($filters['stock'] ?? '') === 'bajo')>Por reponer</option>
                        <option value="agotado" @selected(($filters['stock'] ?? '') === 'agotado')>Agotados</option>
                    </select>
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-bold text-slate-100 transition hover:bg-slate-600">
                        <i class="fa-solid fa-filter"></i>
                        Filtrar
                    </button>
                    <a href="{{ route('inventario.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-600 px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-700" aria-label="Limpiar filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead class="bg-slate-900/70 text-[10px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Producto</th>
                        <th class="px-5 py-3">Categoría / Marca</th>
                        <th class="px-5 py-3 text-center">Existencia</th>
                        <th class="px-5 py-3 text-center">Mínimo</th>
                        <th class="px-5 py-3 text-right">Costo</th>
                        @if($canManageInventory)
                            <th class="px-5 py-3 text-right">Acción</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/80">
                    @forelse($products as $product)
                        <tr class="align-top transition hover:bg-slate-700/20">
                            <td class="px-5 py-4">
                                <span class="font-bold text-amber-400">{{ $product->codigo }}</span>
                                <span class="mt-1 block font-semibold text-slate-100">{{ $product->nombre }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-400">
                                <span class="block">{{ $product->categoria?->nombre ?? 'Sin categoría' }}</span>
                                <span class="mt-1 block">{{ $product->marca?->nombre ?? 'Sin marca' }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex min-w-20 items-center justify-center rounded-full border px-3 py-1 text-xs font-bold {{ $product->stock <= $product->stock_minimo ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' }}">
                                    {{ number_format($product->stock) }} und.
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center text-slate-300">{{ number_format($product->stock_minimo) }} und.</td>
                            <td class="px-5 py-4 text-right font-semibold text-slate-200">S/ {{ number_format((float) $product->precio_compra, 2) }}</td>
                            @if($canManageInventory)
                                <td class="px-5 py-4 text-right">
                                    <details class="relative inline-block text-left" @if((string) old('_inventario_producto') === (string) $product->id_producto) open @endif>
                                        <summary title="Movimiento" aria-label="Movimiento" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-blue-500/40 text-blue-400 hover:bg-blue-500/10 cursor-pointer list-none">
                                            <i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i>
                                        </summary>
                                        <div class="absolute right-0 z-20 mt-2 w-72 rounded-xl border border-slate-600 bg-slate-900 p-4 text-left shadow-2xl">
                                            <p class="mb-3 text-xs font-bold text-slate-100">{{ $product->nombre }}</p>
                                            <form action="{{ route('inventario.movimientos.store', $product) }}" method="POST" class="space-y-3">
                                                @csrf
                                                <input type="hidden" name="_inventario_producto" value="{{ $product->id_producto }}">
                                                <label class="block space-y-1 text-xs font-semibold text-slate-300">
                                                    <span>Operación</span>
                                                    <select name="tipo" required class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100">
                                                        <option value="entrada" @selected(old('tipo') === 'entrada')>Entrada</option>
                                                        <option value="salida" @selected(old('tipo') === 'salida')>Salida</option>
                                                        <option value="ajuste" @selected(old('tipo') === 'ajuste')>Ajuste por conteo físico</option>
                                                    </select>
                                                </label>
                                                <label class="block space-y-1 text-xs font-semibold text-slate-300">
                                                    <span>Cantidad o stock contado</span>
                                                    <input type="number" name="cantidad" value="{{ old('cantidad', 1) }}" required min="0" step="1" class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100">
                                                    <span class="block font-normal text-slate-500">En un ajuste ingresa el total contado, no la diferencia.</span>
                                                </label>
                                                <label class="block space-y-1 text-xs font-semibold text-slate-300">
                                                    <span>Motivo *</span>
                                                    <input type="text" name="motivo" value="{{ old('motivo') }}" required maxlength="180" class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100">
                                                </label>
                                                <label class="block space-y-1 text-xs font-semibold text-slate-300">
                                                    <span>Documento / referencia</span>
                                                    <input type="text" name="referencia" value="{{ old('referencia') }}" maxlength="100" class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100">
                                                </label>
                                                <button type="submit" title="Registrar movimiento" aria-label="Registrar movimiento" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10">
                                                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManageInventory ? 6 : 5 }}" class="px-5 py-12 text-center">
                                <i class="fa-solid fa-box-open text-3xl text-slate-600"></i>
                                <p class="mt-3 font-semibold text-slate-300">No se encontraron productos.</p>
                                <p class="mt-1 text-xs text-slate-500">Prueba con otros filtros o registra un producto nuevo.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-700 p-4">{{ $products->links() }}</div>
    </section>
</div>

</div>
