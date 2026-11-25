<?php

use App\Http\Controllers\CategoriaController as PageController;
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
<div class="md:col-span-2 bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs">
                    <tr>
                        <th class="p-4">ID</th>
                        <th class="p-4">Nombre de Categoría</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($categorias as $c)
                        <tr class="hover:bg-slate-700/40">
                            <td class="p-4 font-bold text-amber-400">#{{ $c->id_categoria }}</td>
                            <td class="p-4 font-semibold text-slate-200">{{ $c->nombre }}</td>
                            <td class="p-4 text-xs">{{ $c->activo ? 'Activa' : 'Inhabilitada' }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($c->activo)
                                    <a href="{{ route('categorias.edit', $c) }}" title="Editar Categoría" aria-label="Editar {{ $c->nombre }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-500/40 text-amber-400 hover:bg-amber-500/10">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </a>
                                    @endif
                                    <form action="{{ $c->activo ? route('categorias.destroy', $c) : route('categorias.reactivar', $c) }}" method="POST">
                                        @csrf
                                        @method($c->activo ? 'DELETE' : 'PATCH')
                                        <button type="submit" title="{{ $c->activo ? 'Inhabilitar' : 'Reactivar' }}" aria-label="{{ $c->activo ? 'Inhabilitar' : 'Reactivar' }} {{ $c->nombre }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-600 text-slate-300">
                                            <i class="fa-solid {{ $c->activo ? 'fa-ban' : 'fa-rotate-left' }}" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    @if(! $c->productos_exists)
                                        <form action="{{ route('categorias.eliminar', $c) }}" method="POST" onsubmit="return confirm('¿Eliminar este registro definitivamente?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Eliminar definitivamente" aria-label="Eliminar {{ $c->nombre }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-500/40 text-rose-400">
                                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-slate-400">Sin categorías.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
