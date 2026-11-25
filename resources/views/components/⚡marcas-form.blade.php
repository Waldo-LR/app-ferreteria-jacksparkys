<?php

use App\Models\Marca;
use Livewire\Component;

new class extends Component
{
    public ?Marca $registro = null;

    public function mount(): void
    {
        if (request()->routeIs('marcas.edit')) {
            $this->registro = Marca::where('activo', true)->findOrFail(request()->route('marca'));
        }
    }
};
?>
<div class="bg-slate-800 p-5 rounded-xl border border-slate-700 h-fit">
            <h3 class="font-bold text-slate-200 mb-3 text-sm">{{ $registro ? 'Editar Marca' : 'Nueva Marca' }}</h3>
            <form action="{{ $registro ? route('marcas.update', $registro) : route('marcas.store') }}" method="POST" class="space-y-3">
                @csrf
                @if($registro)
                    @method('PUT')
                @endif
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $registro?->nombre) }}" maxlength="100" required placeholder="Ej: Truper" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                    @error('nombre') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="w-full bg-amber-500 text-slate-950 font-bold py-2 rounded-lg text-sm hover:bg-amber-400 transition">
                    {{ $registro ? 'Guardar cambios' : 'Guardar Marca' }}
                </button>
                @if($registro)
                    <a href="{{ route('marcas.index') }}" class="block text-center text-sm text-slate-300">Cancelar</a>
                @endif
            </form>
        </div>
