<?php

use App\Http\Controllers\ClienteController as PageController;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        $controller = app(PageController::class);
        $registroId = request()->route('cliente');
        $view = $registroId === null
            ? app()->call([$controller, 'index'])
            : app()->call([$controller, 'edit'], ['cliente' => $registroId]);

        return $view->getData();
    }
};
?>
<section class="h-fit rounded-2xl border border-slate-700 bg-slate-800 p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <h2 class="text-sm font-bold text-slate-100">{{ $clienteEditando ? 'Editar cliente' : 'Registrar cliente' }}</h2>
                @if ($clienteEditando)
                    <a href="{{ route('clientes.index', request()->query()) }}" class="text-xs font-semibold text-slate-400 hover:text-white">Cancelar</a>
                @endif
            </div>

            <form action="{{ $clienteEditando ? route('clientes.update', $clienteEditando->id_cliente) : route('clientes.store') }}" method="POST" class="space-y-4" autocomplete="on">
                @csrf
                @if ($clienteEditando) @method('PUT') @endif

                <div>
                    <label for="tipo_documento" class="mb-1 block text-xs font-semibold text-slate-300">Tipo de documento</label>
                    <select id="tipo_documento" name="tipo_documento" required
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('tipo_documento') ? 'true' : 'false' }}">
                        <option value="">Selecciona un documento</option>
                        @foreach (['DNI', 'RUC', 'CE'] as $tipoDocumento)
                            <option value="{{ $tipoDocumento }}" @selected(old('tipo_documento', $clienteEditando?->tipo_documento) === $tipoDocumento)>{{ $tipoDocumento }}</option>
                        @endforeach
                    </select>
                    @error('tipo_documento')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="dni_ruc" class="mb-1 block text-xs font-semibold text-slate-300">Número de documento</label>
                    <input id="dni_ruc" name="dni_ruc" type="text" value="{{ old('dni_ruc', $clienteEditando?->dni_ruc) }}" maxlength="20" required
                        autocomplete="off" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('dni_ruc') ? 'true' : 'false' }}">
                    @error('dni_ruc')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="nombres" class="mb-1 block text-xs font-semibold text-slate-300">Nombres / Razón social</label>
                    <input id="nombres" name="nombres" type="text" value="{{ old('nombres', $clienteEditando?->nombres) }}" maxlength="150" required
                        autocomplete="name" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('nombres') ? 'true' : 'false' }}">
                    @error('nombres')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-xs font-semibold text-slate-300">Correo electrónico <span class="font-normal text-slate-500">(opcional)</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email', $clienteEditando?->email) }}" maxlength="100"
                        autocomplete="email" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    @error('email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="telefono" class="mb-1 block text-xs font-semibold text-slate-300">Teléfono <span class="font-normal text-slate-500">(opcional)</span></label>
                    <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $clienteEditando?->telefono) }}" maxlength="20"
                        autocomplete="tel" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('telefono') ? 'true' : 'false' }}">
                    @error('telefono')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="direccion" class="mb-1 block text-xs font-semibold text-slate-300">Dirección <span class="font-normal text-slate-500">(opcional)</span></label>
                    <input id="direccion" name="direccion" type="text" value="{{ old('direccion', $clienteEditando?->direccion) }}" maxlength="200"
                        autocomplete="street-address" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('direccion') ? 'true' : 'false' }}">
                    @error('direccion')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-amber-500 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                    {{ $clienteEditando ? 'Guardar cambios' : 'Registrar cliente' }}
                </button>
            </form>
        </section>
