<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 10)]
    public int $per_page = 10;

    #[Url(except: 'nombre')]
    public string $sort = 'nombre';

    #[Url(except: 'asc')]
    public string $direction = 'asc';

    public function boot(): void
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof Usuario && ($user->esAdmin() || $user->esAlmacenero() || $user->esVendedor()),
            403
        );
    }

    public function mount(Request $request): void
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50])],
            'sort' => ['nullable', Rule::in(['nombre', 'categoria', 'stock', 'precio_compra', 'precio_venta', 'precio_venta_mayor', 'estado_stock'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
    }

    public function updated(string $property): void
    {
        if ($property === 'q') {
            $this->validateOnly('q', ['q' => ['nullable', 'string', 'max:100']]);
        }

        if ($property === 'per_page' && ! in_array((int) $this->per_page, [10, 25, 50], true)) {
            $this->per_page = 10;
        }

        if (in_array($property, ['q', 'per_page', 'sort', 'direction'], true)) {
            $this->resetPage();
        }
    }

    public function ordenarPor(string $sort): void
    {
        $allowedSorts = ['nombre', 'categoria', 'stock', 'precio_compra', 'precio_venta', 'precio_venta_mayor', 'estado_stock'];
        abort_unless(in_array($sort, $allowedSorts, true), 422);

        $this->direction = $this->sort === $sort && $this->direction === 'asc' ? 'desc' : 'asc';
        $this->sort = $sort;
        $this->resetPage();
    }

    #[On('producto-guardado')]
    public function refrescar(): void
    {
    }

    public function with(): array
    {
        $perPage = in_array((int) $this->per_page, [10, 25, 50], true) ? (int) $this->per_page : 10;
        $sort = in_array($this->sort, ['nombre', 'categoria', 'stock', 'precio_compra', 'precio_venta', 'precio_venta_mayor', 'estado_stock'], true)
            ? $this->sort
            : 'nombre';
        $direction = in_array($this->direction, ['asc', 'desc'], true) ? $this->direction : 'asc';

        $productosQuery = Producto::query()
            ->with(['categoria', 'marca'])
            ->where('activo', true)
            ->when(trim($this->q) !== '', function (Builder $query): void {
                $search = trim($this->q);
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('codigo', 'like', '%'.$search.'%')
                        ->orWhere('nombre', 'like', '%'.$search.'%')
                        ->orWhereHas('categoria', fn (Builder $categoryQuery) => $categoryQuery->where('nombre', 'like', '%'.$search.'%'))
                        ->orWhereHas('marca', fn (Builder $brandQuery) => $brandQuery->where('nombre', 'like', '%'.$search.'%'));
                });
            });

        if ($sort === 'categoria') {
            $productosQuery->orderBy(
                Categoria::query()
                    ->select('nombre')
                    ->whereColumn('tcategorias.id_categoria', 'tproductos.id_categoria'),
                $direction
            );
        } elseif ($sort === 'estado_stock') {
            $productosQuery->orderByRaw('CASE WHEN stock = 0 THEN 1 WHEN stock <= stock_minimo THEN 2 ELSE 3 END '.$direction);
        } else {
            $productosQuery->orderBy($sort, $direction);
        }

        $user = Auth::user();

        return [
            'productos' => $productosQuery->orderBy('id_producto')->paginate($perPage)->withQueryString(),
            'filters' => ['q' => $this->q, 'per_page' => $perPage, 'sort' => $sort, 'direction' => $direction],
            'canManageProducts' => $user instanceof Usuario && ($user->esAdmin() || $user->esAlmacenero()),
        ];
    }
};
?>
<div>
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-100">Catálogo de Productos</h1>
            <p class="mt-1 text-sm text-slate-400">Consulta productos, precios y disponibilidad de stock.</p>
        </div>
        <div class="flex items-center gap-2 self-start rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-300 sm:self-auto">
            <i class="fa-regular fa-clock text-amber-400"></i>
            {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <form action="{{ route('productos.index') }}" method="GET" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4 md:grid-cols-[minmax(0,1fr)_auto_auto] md:items-end">
        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        <input type="hidden" name="direction" value="{{ $filters['direction'] }}">
        <label class="space-y-1.5 text-xs font-semibold text-slate-300">
            <span>Buscar por producto, código, categoría o marca</span>
            <span class="relative block">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input id="product-search-input" type="search" name="q" wire:model.live.debounce.400ms="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="Ej. taladro, código o marca" class="w-full rounded-xl border border-slate-600 bg-slate-900 py-2.5 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
            </span>
        </label>
        <span wire:loading wire:target="q" class="sr-only" role="status">Buscando productos…</span>
        <label class="flex items-center gap-2 text-xs font-semibold text-slate-300">
            <span>Mostrar</span>
            <select name="per_page" wire:model.live="per_page" class="rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                @foreach([10, 25, 50] as $pageSize)
                    <option value="{{ $pageSize }}" @selected($filters['per_page'] === $pageSize)>{{ $pageSize }}</option>
                @endforeach
            </select>
            <span>registros</span>
        </label>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-bold text-slate-100 transition hover:bg-slate-600">
                <i class="fa-solid fa-filter"></i>
                Buscar
            </button>
            <button id="clear-catalog-search" type="button" wire:click="$set('q', '')" class="{{ $filters['q'] === '' ? 'hidden' : '' }} inline-flex items-center justify-center rounded-xl border border-slate-600 px-3 py-2.5 text-sm text-slate-300 transition hover:border-slate-500 hover:text-white" aria-label="Limpiar búsqueda" title="Limpiar búsqueda">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </form>

    <div id="catalog-results" aria-live="polite" class="space-y-6">
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-400">
            @if($productos->total() > 0)
                Mostrando <span class="font-semibold text-slate-200">{{ $productos->firstItem() }}–{{ $productos->lastItem() }}</span>
                de <span class="font-semibold text-slate-200">{{ number_format($productos->total()) }}</span> productos
            @else
                No hay productos para mostrar
            @endif
        </p>
        <div class="flex flex-wrap items-center gap-2">
            @if($productos->hasPages())
                <nav aria-label="Paginación del catálogo" class="inline-flex items-center overflow-hidden rounded-xl border border-slate-600 text-sm">
                    @if($productos->onFirstPage())
                        <span class="px-3 py-2.5 text-slate-500" aria-disabled="true">Anterior</span>
                    @else
                        <button type="button" wire:click="previousPage" class="px-3 py-2.5 text-slate-300 transition hover:bg-slate-700">Anterior</button>
                    @endif
                    <span class="border-x border-slate-600 bg-blue-500/20 px-3 py-2.5 font-bold text-blue-200">{{ $productos->currentPage() }} / {{ $productos->lastPage() }}</span>
                    @if($productos->hasMorePages())
                        <button type="button" wire:click="nextPage" class="px-3 py-2.5 text-slate-300 transition hover:bg-slate-700">Siguiente</button>
                    @else
                        <span class="px-3 py-2.5 text-slate-500" aria-disabled="true">Siguiente</span>
                    @endif
                </nav>
            @endif
            @if($canManageProducts)
                @livewire('productos-form')
                <a href="{{ route('compras.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                    <i class="fa-solid fa-plus"></i>
                    Registrar Compra
                </a>
            @endif
            @if(auth()->user()->esAdmin() || auth()->user()->esVendedor() || auth()->user()->esCajero())
                <a href="{{ route('ventas.pos') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#146650] px-4 py-2.5 text-sm font-bold text-[#ffffff] transition hover:bg-[#104e3e]">
                    <i class="fa-solid fa-plus"></i>
                    Registrar Venta
                </a>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-700 bg-slate-800">
            <table class="w-full table-fixed text-left text-sm">
                <thead class="border-b border-slate-700 bg-slate-900/80 text-xs uppercase tracking-wide text-slate-400">
                    <tr>
                        <th scope="col" class="w-16 px-2 py-3 text-center sm:px-4">Imagen</th>
                        <th scope="col" class="w-[20%] px-2 py-3 sm:px-4">
                            <a href="#" wire:click.prevent="ordenarPor('nombre')" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Producto <i class="fa-solid {{ $filters['sort'] === 'nombre' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="w-[12%] hidden px-4 py-3 sm:table-cell">
                            <a href="#" wire:click.prevent="ordenarPor('categoria')" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Categoría / Marca <i class="fa-solid {{ $filters['sort'] === 'categoria' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="w-[10%] px-2 py-3 text-center sm:px-4 sm:w-auto">
                            <a href="#" wire:click.prevent="ordenarPor('stock')" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Stock <i class="fa-solid {{ $filters['sort'] === 'stock' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="w-[10%] hidden px-4 py-3 text-right sm:table-cell">
                            <a href="#" wire:click.prevent="ordenarPor('precio_compra')" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Precio de compra <i class="fa-solid {{ $filters['sort'] === 'precio_compra' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="w-[10%] hidden px-4 py-3 text-right sm:table-cell">
                            <a href="#" wire:click.prevent="ordenarPor('precio_venta')" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Precio unitario <i class="fa-solid {{ $filters['sort'] === 'precio_venta' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="w-[10%] hidden px-4 py-3 text-right lg:table-cell">
                            <a href="#" wire:click.prevent="ordenarPor('precio_venta_mayor')" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Mayorista <i class="fa-solid {{ $filters['sort'] === 'precio_venta_mayor' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="w-[8%] hidden px-4 py-3 text-right lg:table-cell">Alquiler / día</th>
                        <th scope="col" class="w-[10%] hidden px-4 py-3 text-center md:table-cell">
                            <a href="#" wire:click.prevent="ordenarPor('estado_stock')" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Estado de stock <i class="fa-solid {{ $filters['sort'] === 'estado_stock' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="w-[10%] px-2 py-3 text-center sm:px-4">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($productos as $producto)
                        <tr class="transition hover:bg-slate-700/30">
<td class="w-16 px-2 py-4 text-center sm:px-4">
                                @include('shared.producto-miniatura')
                            </td>
                            <td class="w-[20%] px-2 py-4 sm:px-4 min-w-0">
                                <div class="flex items-center">
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-100">{{ $producto->nombre }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $producto->codigo }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="w-[12%] hidden px-4 py-4 sm:table-cell min-w-0">
                                <p class="truncate text-slate-200">{{ $producto->categoria?->nombre ?? 'Sin categoría' }}</p>
                                <p class="mt-0.5 text-[10px] truncate text-slate-400">{{ $producto->marca?->nombre ?? 'Sin marca' }}</p>
                            </td>
                            <td class="w-[10%] px-2 py-4 text-center sm:px-4">
                                <span class="inline-flex rounded-full bg-blue-500/10 px-2.5 py-1 text-[10px] font-bold text-blue-300">
                                    {{ number_format($producto->stock) }} und.
                                </span>
                                <p class="mt-1 text-[10px] text-slate-500">Mín. {{ number_format($producto->stock_minimo) }}</p>
                            </td>
                            <td class="w-[10%] hidden px-4 py-4 text-right sm:table-cell font-medium text-slate-300">S/ {{ number_format((float) $producto->precio_compra, 2) }}</td>
                            <td class="w-[10%] hidden px-4 py-4 text-right sm:table-cell font-bold text-emerald-400">S/ {{ number_format((float) $producto->precio_venta, 2) }}</td>
                            <td class="w-[10%] hidden px-4 py-4 text-right lg:table-cell">
                                <p class="font-semibold text-slate-200">S/ {{ number_format((float) ($producto->precio_venta_mayor ?? $producto->precio_venta), 2) }}</p>
                                <p class="mt-0.5 text-[10px] text-slate-400">Desde {{ number_format($producto->cantidad_mayorista) }} und.</p>
                            </td>
                            <td class="w-[8%] hidden px-4 py-4 text-right lg:table-cell">
                                @if($producto->es_alquilable)
                                    <span class="font-semibold text-blue-300">S/ {{ number_format((float) $producto->precio_alquiler_dia, 2) }}</span>
                                @else
                                    <span class="text-[10px] text-slate-500">No alquilable</span>
                                @endif
                            </td>
                            <td class="w-[10%] hidden px-4 py-4 text-center md:table-cell">
                                @if($producto->stock === 0)
                                    <span class="inline-flex rounded-full border border-rose-500/30 bg-rose-500/10 px-2.5 py-1 text-[10px] font-bold text-rose-300">Agotado</span>
                                @elseif($producto->stock <= $producto->stock_minimo)
                                    <span class="inline-flex rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-[10px] font-bold text-amber-300">Por reponer</span>
                                @else
                                    <span class="inline-flex rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-bold text-emerald-300">Disponible</span>
                                @endif
                            </td>
                            <td class="w-[10%] px-2 py-4 sm:px-4">
                                <div class="flex items-center justify-center gap-4">
                                    @if($canManageProducts)
                                        <a href="{{ route('productos.edit', $producto) }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-amber-500/30 text-amber-300 transition hover:bg-amber-500 hover:text-slate-950" aria-label="Editar {{ $producto->nombre }}" title="Editar producto">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('productos.destroy', $producto->id_producto) }}" method="POST" onsubmit="return confirm('¿Confirma dar de baja este producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-rose-500/30 text-rose-300 transition hover:bg-rose-500 hover:text-white" aria-label="Inhabilitar o dar de baja {{ $producto->nombre }}" title="Inhabilitar o dar de baja">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
<td colspan="10" class="px-4 py-12 text-center">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-700 text-slate-400">
                                    <i class="fa-solid fa-box-open"></i>
                                </span>
                                <p class="mt-3 font-semibold text-slate-200">No se encontraron productos</p>
                                <p class="mt-1 text-sm text-slate-400">
                                    {{ $filters['q'] !== '' ? 'Prueba con otro nombre, código, categoría o marca.' : 'Los productos activos aparecerán aquí.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @if($productos->hasPages())
            <div class="border-t border-slate-700 p-4">{{ $productos->links() }}</div>
        @endif
    </div>
    </div>
</div>

    @include('shared.producto-vista-ampliada')
</div>
