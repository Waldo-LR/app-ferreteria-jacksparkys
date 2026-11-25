<?php

use App\Http\Controllers\MarcaController as PageController;
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
                        <th class="p-4">Nombre de Marca</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($marcas as $m)
                        <tr class="hover:bg-slate-700/40">
                            <td class="p-4 font-bold text-amber-400">#{{ $m->id_marca }}</td>
                            <td class="p-4 font-semibold text-slate-200">{{ $m->nombre }}</td>
                            <td class="p-4 text-xs">{{ $m->activo ? 'Activa' : 'Inhabilitada' }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($m->activo)
                                    <a href="{{ route('marcas.edit', $m) }}" title="Editar Marca" aria-label="Editar {{ $m->nombre }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-500/40 text-amber-400 hover:bg-amber-500/10">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </a>
                                    @endif
                                    <form action="{{ $m->activo ? route('marcas.destroy', $m) : route('marcas.reactivar', $m) }}" method="POST">
                                        @csrf
                                        @method($m->activo ? 'DELETE' : 'PATCH')
                                        <button type="submit" title="{{ $m->activo ? 'Inhabilitar' : 'Reactivar' }}" aria-label="{{ $m->activo ? 'Inhabilitar' : 'Reactivar' }} {{ $m->nombre }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-600 text-slate-300">
                                            <i class="fa-solid {{ $m->activo ? 'fa-ban' : 'fa-rotate-left' }}" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    @if(! $m->productos_exists)
                                        <form action="{{ route('marcas.eliminar', $m) }}" method="POST" onsubmit="return confirm('¿Eliminar este registro definitivamente?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Eliminar definitivamente" aria-label="Eliminar {{ $m->nombre }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-500/40 text-rose-400">
                                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-slate-400">Sin marcas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
