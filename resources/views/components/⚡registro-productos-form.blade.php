<?php

use App\Http\Controllers\ProductoController as PageController;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        $view = app(PageController::class)->create();

        return $view->getData();
    }
};
?>

<form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 rounded-2xl border border-slate-700 bg-slate-800 p-5 sm:grid-cols-2 xl:grid-cols-4">
    @csrf
    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
        <span>Código *</span>
        <input type="text" name="codigo" value="{{ old('codigo') }}" required maxlength="50" autocomplete="off" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
    </label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
        <span>Nombre *</span>
        <input type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="150" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
    </label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
        <span>Categoría *</span>
        <select name="id_categoria" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
            <option value="">Selecciona una categoría</option>
            @foreach($categorias as $category)
                <option value="{{ $category->id_categoria }}" @selected(old('id_categoria') == $category->id_categoria)>{{ $category->nombre }}</option>
            @endforeach
        </select>
    </label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
        <span>Marca *</span>
        <select name="id_marca" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
            <option value="">Selecciona una marca</option>
            @foreach($marcas as $brand)
                <option value="{{ $brand->id_marca }}" @selected(old('id_marca') == $brand->id_marca)>{{ $brand->nombre }}</option>
            @endforeach
        </select>
    </label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
        <span>Stock inicial</span>
        <input type="number" name="stock" value="{{ old('stock', 0) }}" required min="0" step="1" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
    </label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
        <span>Stock mínimo *</span>
        <input type="number" name="stock_minimo" value="{{ old('stock_minimo', 0) }}" required min="0" step="1" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
    </label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
        <span>Costo unitario (S/) *</span>
        <input type="number" name="precio_compra" value="{{ old('precio_compra') }}" required min="0" step="0.01" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
    </label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
        <span>Precio de venta (S/) *</span>
        <input type="number" name="precio_venta" value="{{ old('precio_venta') }}" required min="0" step="0.01" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
    </label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
        <span>Precio mayorista (S/)</span>
        <input type="number" name="precio_venta_mayor" value="{{ old('precio_venta_mayor') }}" min="0" step="0.01" placeholder="Igual al precio de venta" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
    </label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300">
        <span>Cantidad mínima mayorista</span>
        <input type="number" name="cantidad_mayorista" value="{{ old('cantidad_mayorista', 2) }}" min="1" step="1" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
    </label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300"><span>Imagen del producto</span><input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-1.5 file:font-bold file:text-slate-950"><span class="block font-normal text-slate-500">JPG, PNG o WebP. Máximo 2 MB.</span></label>
    <label class="space-y-1.5 text-xs font-semibold text-slate-300 sm:col-span-2 xl:col-span-4">
        <span>Descripción</span>
        <textarea name="descripcion" rows="2" maxlength="5000" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">{{ old('descripcion') }}</textarea>
    </label>
    <div class="flex flex-wrap items-center gap-3 sm:col-span-2 xl:col-span-4">
        <label class="inline-flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="es_alquilable" value="1" @checked(old('es_alquilable')) class="rounded border-slate-600 bg-slate-900 text-amber-500 focus:ring-amber-500">
            También se alquila
        </label>
        <label class="flex items-center gap-2 text-xs font-semibold text-slate-300">
            <span>Precio alquiler/día (S/)</span>
            <input type="number" name="precio_alquiler_dia" value="{{ old('precio_alquiler_dia', 0) }}" min="0" step="0.01" class="w-36 rounded-xl border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
        </label>
        <a href="{{ route('productos.index') }}" class="ml-auto rounded-xl border border-slate-600 px-4 py-2.5 text-sm text-slate-300">Cancelar</a>
        <button type="submit" class=" inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
            <i class="fa-solid fa-floppy-disk"></i>
            Guardar producto
        </button>
    </div>
</form>
