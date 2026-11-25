<?php

use App\Http\Controllers\ProveedorController as PageController;
use Livewire\Component;

new class extends Component
{
    public $registroId = null;

    public function with(): array
    {
        $controller = app(PageController::class);
        $view = $this->registroId === null
            ? app()->call([$controller, 'index'])
            : app()->call([$controller, 'edit'], ['id' => $this->registroId]);

        return $view->getData();
    }
};
?>
<div>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Gestión de Proveedores</h1>
            <p class="text-xs text-slate-400">Directorio de proveedores y datos de contacto</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2">
            <span class="block text-[10px] font-bold uppercase text-amber-300">Proveedores activos</span>
            <span class="text-lg font-extrabold text-amber-400">{{ $proveedores->total() }}</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300" role="alert">
            <p class="font-bold">Revisa los datos del formulario:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        @livewire('proveedores-form', ['proveedorEditando' => $proveedorEditando])

        <section class="space-y-4 xl:col-span-2">
            <form action="{{ route('proveedores.index') }}" method="GET" class="flex flex-col gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4 sm:flex-row">
                <label for="buscar" class="sr-only">Buscar proveedor</label>
                <input
                    id="buscar"
                    type="search"
                    name="buscar"
                    value="{{ request('buscar') }}"
                    placeholder="Buscar por razón social, RUC, correo o teléfono..."
                    class="min-w-0 flex-1 rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-xs text-slate-100 focus:border-amber-500 focus:outline-none"
                >
                <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-bold text-slate-950 transition hover:bg-amber-400">
                    Buscar
                </button>
                @if (request()->filled('buscar'))
                    <a href="{{ route('proveedores.index') }}" class="rounded-xl border border-slate-600 px-4 py-2.5 text-center text-xs font-semibold text-slate-300 hover:bg-slate-700">
                        Limpiar
                    </a>
                @endif
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-900/80 text-xs uppercase text-slate-400">
                            <tr>
                                <th class="p-4">Proveedor</th>
                                <th class="p-4">RUC</th>
                                <th class="p-4">Contacto</th>
                                <th class="p-4">Dirección</th>
                                <th class="p-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @forelse ($proveedores as $proveedor)
                                <tr class="align-top transition hover:bg-slate-700/40">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-100">{{ $proveedor->razon_social }}</div>
                                        <div class="mt-1 text-[11px] text-slate-500">#{{ $proveedor->id_proveedor }}</div>
                                    </td>
                                    <td class="p-4 font-semibold text-amber-400">{{ $proveedor->ruc }}</td>
                                    <td class="p-4 text-xs text-slate-300">
                                        <div>{{ $proveedor->telefono ?: 'Sin teléfono' }}</div>
                                        <div class="mt-1 break-all text-slate-400">{{ $proveedor->email ?: 'Sin correo' }}</div>
                                    </td>
                                    <td class="max-w-xs whitespace-normal p-4 text-xs text-slate-400">
                                        {{ $proveedor->direccion ?: 'Sin dirección registrada' }}
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-center gap-2">
                                            <a
                                                href="{{ route('proveedores.edit', $proveedor->id_proveedor) }}" title="Editar" aria-label="Editar {{ $proveedor->razon_social }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-amber-500/40 text-amber-400 hover:bg-amber-500/10">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>
                                            <form
                                                action="{{ route('proveedores.destroy', $proveedor->id_proveedor) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Dar de baja a este proveedor?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Dar de baja" aria-label="Dar de baja {{ $proveedor->razon_social }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-rose-500/40 text-rose-400 hover:bg-rose-500/10">
                                                    <i class="fa-solid fa-box-archive" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-sm text-slate-400">
                                        @if (request()->filled('buscar'))
                                            No se encontraron proveedores para “{{ request('buscar') }}”.
                                        @else
                                            Aún no hay proveedores registrados.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($proveedores->hasPages())
                <div class="rounded-xl border border-slate-700 bg-slate-800 p-3">
                    {{ $proveedores->links() }}
                </div>
            @endif
        </section>
    </div>
</div>

</div>
