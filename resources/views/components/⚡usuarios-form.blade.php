<?php

use App\Http\Controllers\UsuarioController as PageController;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        $controller = app(PageController::class);
        $registroId = request()->route('usuario');
        $view = $registroId === null
            ? app()->call([$controller, 'index'])
            : app()->call([$controller, 'edit'], ['usuario' => $registroId]);

        return $view->getData();
    }
};
?>
<section class="h-fit rounded-2xl border border-slate-700 bg-slate-800 p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-100">
                        {{ $usuarioEditando ? 'Editar usuario' : 'Registrar usuario' }}
                    </h2>
                    <p class="mt-1 text-xs text-slate-400">La contraseña debe tener al menos 6 caracteres.</p>
                </div>
                @if ($usuarioEditando)
                    <a href="{{ route('usuarios.index', request()->query()) }}" class="text-xs font-semibold text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                @endif
            </div>

            <form
                action="{{ $usuarioEditando ? route('usuarios.update', $usuarioEditando->id_usuario) : route('usuarios.store') }}"
                method="POST"
                class="space-y-4"
                autocomplete="off"
            >
                @csrf
                @if ($usuarioEditando)
                    @method('PUT')
                @endif

                <div>
                    <label for="nombre" class="mb-1 block text-xs font-semibold text-slate-300">Nombre completo</label>
                    <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $usuarioEditando?->nombre) }}" maxlength="100" required
                        autocomplete="name" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('nombre') ? 'true' : 'false' }}">
                    @error('nombre')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="usuario" class="mb-1 block text-xs font-semibold text-slate-300">Usuario</label>
                    <input id="usuario" name="usuario" type="text" value="{{ old('usuario', $usuarioEditando?->usuario) }}" maxlength="50" required
                        autocomplete="username" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('usuario') ? 'true' : 'false' }}">
                    @error('usuario')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-xs font-semibold text-slate-300">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $usuarioEditando?->email) }}" maxlength="100" required
                        autocomplete="email" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    @error('email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="id_rol" class="mb-1 block text-xs font-semibold text-slate-300">Rol</label>
                    <select id="id_rol" name="id_rol" required
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('id_rol') ? 'true' : 'false' }}">
                        <option value="">Selecciona un rol</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" @selected((string) old('id_rol', $usuarioEditando?->id_rol) === (string) $rol->id_rol)>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_rol')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-xs font-semibold text-slate-300">
                        Contraseña {{ $usuarioEditando ? '(dejar en blanco para conservarla)' : '' }}
                    </label>
                    <input id="password" name="password" type="password" minlength="6" {{ $usuarioEditando ? '' : 'required' }}
                        autocomplete="new-password" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                    @error('password')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-xs font-semibold text-slate-300">Confirmar contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" minlength="6"
                        autocomplete="new-password" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                </div>

                <div>
                    <label for="sexo" class="mb-1 block text-xs font-semibold text-slate-300">Sexo</label>
                    <select id="sexo" name="sexo" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                        <option value="">No especificado</option>
                        <option value="M" @selected(old('sexo', $usuarioEditando?->sexo) === 'M')>Masculino</option>
                        <option value="F" @selected(old('sexo', $usuarioEditando?->sexo) === 'F')>Femenino</option>
                    </select>
                    @error('sexo')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="telefono" class="mb-1 block text-xs font-semibold text-slate-300">Teléfono</label>
                    <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $usuarioEditando?->telefono) }}" maxlength="20"
                        autocomplete="tel" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('telefono')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="direccion" class="mb-1 block text-xs font-semibold text-slate-300">Dirección</label>
                    <input id="direccion" name="direccion" type="text" value="{{ old('direccion', $usuarioEditando?->direccion) }}" maxlength="150"
                        autocomplete="street-address" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('direccion')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="sobre_mi" class="mb-1 block text-xs font-semibold text-slate-300">Sobre mí</label>
                    <textarea id="sobre_mi" name="sobre_mi" rows="3" maxlength="1000"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">{{ old('sobre_mi', $usuarioEditando?->sobre_mi) }}</textarea>
                    @error('sobre_mi')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-amber-500 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                    {{ $usuarioEditando ? 'Guardar cambios' : 'Registrar usuario' }}
                </button>
            </form>
        </section>
