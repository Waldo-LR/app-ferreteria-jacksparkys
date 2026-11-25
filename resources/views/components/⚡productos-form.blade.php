<?php

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\MovimientoStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public bool $showModal = false;

    public string $codigo = '';

    public string $nombre = '';

    public string $id_categoria = '';

    public string $id_marca = '';

    public string $descripcion = '';

    public string $stock = '0';

    public string $stock_minimo = '0';

    public string $precio_compra = '';

    public string $precio_venta = '';

    public string $precio_venta_mayor = '';

    public string $cantidad_mayorista = '2';

    public bool $es_alquilable = false;

    public string $precio_alquiler_dia = '0';

    public string $successMessage = '';

    public function with(): array
    {
        $canManageProducts = $this->canManageProducts();

        return [
            'canManageProducts' => $canManageProducts,
            'categorias' => $canManageProducts && $this->showModal
                ? Categoria::query()->where('activo', true)->orderBy('nombre')->get()
                : collect(),
            'marcas' => $canManageProducts && $this->showModal
                ? Marca::query()->where('activo', true)->orderBy('nombre')->get()
                : collect(),
        ];
    }

    #[On('nuevo-producto')]
    public function nuevo(): void
    {
        $this->authorizeManage();
        $this->resetForm();
        $this->showModal = true;
    }

    public function cancelar(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function updated(string $property): void
    {
        $this->validateOnly($property, $this->rules());
    }

    public function guardar(MovimientoStockService $movimientoStockService): void
    {
        $this->authorizeManage();
        $datos = $this->validate($this->rules());
        $stockInicial = (int) $datos['stock'];
        $datos['precio_venta_mayor'] ??= $datos['precio_venta'];
        $datos['cantidad_mayorista'] ??= 2;
        $datos['es_alquilable'] = (bool) $datos['es_alquilable'];
        $datos['precio_alquiler_dia'] ??= 0;
        $datos['activo'] = true;
        $datos['stock'] = 0;

        $usuario = Auth::user();
        abort_unless($usuario instanceof Usuario, 401);

        DB::transaction(function () use ($datos, $stockInicial, $usuario, $movimientoStockService): void {
            $producto = Producto::create($datos);

            if ($stockInicial > 0) {
                $movimientoStockService->registrar(
                    $producto,
                    $usuario,
                    'entrada',
                    $stockInicial,
                    'Stock inicial por creación de producto'
                );
            }
        }, 3);

        $this->showModal = false;
        $this->resetForm();
        $this->successMessage = 'Producto registrado correctamente en el inventario.';
        $this->dispatch('producto-guardado');
    }

    protected function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:50', Rule::unique('tproductos', 'codigo')],
            'id_categoria' => ['required', 'integer', Rule::exists('tcategorias', 'id_categoria')->where('activo', true)],
            'id_marca' => ['required', 'integer', Rule::exists('tmarcas', 'id_marca')->where('activo', true)],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'stock_minimo' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'precio_compra' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'precio_venta' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'precio_venta_mayor' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'cantidad_mayorista' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'es_alquilable' => ['boolean'],
            'precio_alquiler_dia' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }

    private function canManageProducts(): bool
    {
        $usuario = Auth::user();

        return $usuario instanceof Usuario && ($usuario->esAdmin() || $usuario->esAlmacenero());
    }

    private function authorizeManage(): void
    {
        abort_unless($this->canManageProducts(), 403);
    }

    private function resetForm(): void
    {
        $this->reset([
            'codigo',
            'nombre',
            'id_categoria',
            'id_marca',
            'descripcion',
            'precio_compra',
            'precio_venta',
            'precio_venta_mayor',
        ]);
        $this->stock = '0';
        $this->stock_minimo = '0';
        $this->cantidad_mayorista = '2';
        $this->es_alquilable = false;
        $this->precio_alquiler_dia = '0';
    }
};
?>

<div>
    @if($canManageProducts)
        <div class="flex flex-wrap items-center justify-end gap-2">
            @if($successMessage !== '')
                <p class="mr-auto rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-sm text-emerald-300" role="status">
                    {{ $successMessage }}
                </p>
            @endif
            <a href="{{ route('productos.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#045c94] px-4 py-2.5 text-sm font-bold text-[#ffffff] transition hover:bg-[#02314d]">
                <i class="fa-solid fa-plus"></i>
                Registrar Producto
            </a>
        </div>

        @if($showModal)
            <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" wire:click.self="cancelar">
                <section class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-slate-600 bg-slate-800 p-6 shadow-2xl" aria-labelledby="producto-form-title">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h2 id="producto-form-title" class="text-lg font-bold text-slate-100">Registrar producto</h2>
                            <p class="mt-1 text-xs text-slate-400">Los cambios de existencias quedan registrados en el historial de movimientos.</p>
                        </div>
                        <button type="button" wire:click="cancelar" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-700 hover:text-white" aria-label="Cerrar formulario">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form wire:submit="guardar" class="space-y-4">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                <span>Código *</span>
                                <input type="text" wire:model.blur="codigo" maxlength="50" autocomplete="off" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                @error('codigo') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                <span>Nombre *</span>
                                <input type="text" wire:model.blur="nombre" maxlength="150" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                @error('nombre') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                <span>Categoría *</span>
                                <select wire:model="id_categoria" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                    <option value="">Selecciona una categoría</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id_categoria }}">{{ $categoria->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_categoria') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                <span>Marca *</span>
                                <select wire:model="id_marca" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                    <option value="">Selecciona una marca</option>
                                    @foreach($marcas as $marca)
                                        <option value="{{ $marca->id_marca }}">{{ $marca->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_marca') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                <span>Stock inicial *</span>
                                <input type="number" wire:model.blur="stock" min="0" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                @error('stock') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                <span>Stock mínimo *</span>
                                <input type="number" wire:model.blur="stock_minimo" min="0" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                @error('stock_minimo') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                <span>Costo unitario (S/) *</span>
                                <input type="number" wire:model.blur="precio_compra" min="0" step="0.01" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                @error('precio_compra') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                <span>Precio de venta (S/) *</span>
                                <input type="number" wire:model.blur="precio_venta" min="0" step="0.01" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                @error('precio_venta') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                <span>Precio mayorista (S/)</span>
                                <input type="number" wire:model.blur="precio_venta_mayor" min="0" step="0.01" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                @error('precio_venta_mayor') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                <span>Cantidad para precio mayorista</span>
                                <input type="number" wire:model.blur="cantidad_mayorista" min="1" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                @error('cantidad_mayorista') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <label class="space-y-1.5 text-xs font-semibold text-slate-300 sm:col-span-2">
                                <span>Descripción</span>
                                <textarea wire:model.blur="descripcion" rows="3" maxlength="5000" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"></textarea>
                                @error('descripcion') <span class="text-rose-300">{{ $message }}</span> @enderror
                            </label>
                            <div class="space-y-2 sm:col-span-2">
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-300">
                                    <input type="checkbox" wire:model.live="es_alquilable" class="rounded border-slate-600 bg-slate-900 text-amber-500 focus:ring-amber-500">
                                    También se alquila
                                </label>
                                @if($es_alquilable)
                                    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                                        <span>Precio alquiler/día (S/)</span>
                                        <input type="number" wire:model.blur="precio_alquiler_dia" min="0" step="0.01" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                        @error('precio_alquiler_dia') <span class="text-rose-300">{{ $message }}</span> @enderror
                                    </label>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col-reverse justify-between gap-3 border-t border-slate-700 pt-4 sm:flex-row sm:items-center">
                            <a href="{{ route('productos.create') }}" class="text-xs text-slate-400 underline decoration-slate-600 underline-offset-4 hover:text-amber-300">
                                Formulario completo con imagen
                            </a>
                            <div class="flex gap-2 sm:justify-end">
                                <button type="button" wire:click="cancelar" class="rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-bold text-slate-200 transition hover:bg-slate-600">Cancelar</button>
                                <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400 disabled:opacity-60">
                                    <span wire:loading.remove wire:target="guardar">Guardar producto</span>
                                    <span wire:loading wire:target="guardar">Guardando...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        @endif
    @endif
</div>
