<?php

use App\Http\Controllers\UsuarioController as PageController;
use Livewire\Component;

new class extends Component
{
    public $registroId = null;

    public function with(): array
    {
        $controller = app(PageController::class);
        $view = $this->registroId === null
            ? app()->call([$controller, 'index'])
            : app()->call([$controller, 'edit'], ['usuario' => $this->registroId]);

        return $view->getData();
    }
};
?>
<div>
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Gestión de Usuarios</h1>
            <p class="text-xs text-slate-400">Administra las cuentas, roles y accesos del sistema.</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2">
            <span class="block text-[10px] font-bold uppercase text-amber-300">Usuarios encontrados</span>
            <span class="text-lg font-extrabold text-amber-400">{{ $usuarios->total() }}</span>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-300" role="status">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300" role="alert">
            {{ session('error') }}
        </div>
    @endif
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

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        @livewire('usuarios-form')

        <section class="space-y-4 xl:col-span-2">
            <form method="GET" action="{{ route('usuarios.index') }}" class="flex flex-col gap-2 rounded-2xl border border-slate-700 bg-slate-800 p-4 sm:flex-row">
                <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, usuario o correo"
                    class="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-500 focus:outline-none">
                <select name="estado" class="rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                    <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivos</option>
                </select>
                <button type="submit" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600">Buscar</button>
                @if (request()->hasAny(['buscar', 'estado']))
                    <a href="{{ route('usuarios.index') }}" class="rounded-lg px-3 py-2 text-center text-sm font-semibold text-slate-300 hover:bg-slate-700">Limpiar</a>
                @endif
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-900/70 text-xs uppercase text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Usuario</th>
                                <th class="px-4 py-3">Contacto</th>
                                <th class="px-4 py-3">Rol</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @forelse ($usuarios as $usuario)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-100">{{ $usuario->nombre }}</p>
                                        <p class="text-xs text-slate-400">{{ '@' . $usuario->usuario }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-slate-200">{{ $usuario->email }}</p>
                                        <p class="text-xs text-slate-400">{{ $usuario->telefono ?: 'Sin teléfono' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-300">{{ $usuario->rol?->nombre ?? 'Sin rol' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $usuario->activo ? 'bg-emerald-500/10 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">
                                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('usuarios.edit', ['usuario' => $usuario->id_usuario] + request()->query()) }}" title="Editar" aria-label="Editar {{ $usuario->nombre }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-amber-500/40 text-amber-400 hover:bg-amber-500/10">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>
                                            <form action="{{ route('usuarios.destroy', $usuario->id_usuario) }}" method="POST"
                                                onsubmit="return confirm('¿Confirmas {{ $usuario->activo ? 'dar de baja' : 'reactivar' }} este usuario?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="{{ $usuario->activo ? 'Dar de baja' : 'Reactivar' }}" aria-label="{{ $usuario->activo ? 'Dar de baja' : 'Reactivar' }} {{ $usuario->nombre }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition {{ $usuario->activo ? 'border-rose-500/40 text-rose-400 hover:bg-rose-500/10' : 'border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10' }}">
                                                    <i class="fa-solid {{ $usuario->activo ? 'fa-box-archive' : 'fa-rotate-left' }}" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">No se encontraron usuarios.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($usuarios->hasPages())
                    <div class="border-t border-slate-700 px-4 py-3">{{ $usuarios->links() }}</div>
                @endif
            </div>
        </section>
    </div>
</div>

</div>
