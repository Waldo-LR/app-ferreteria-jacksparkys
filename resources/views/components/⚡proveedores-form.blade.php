<?php

use App\Models\Proveedor;
use Livewire\Component;

new class extends Component
{
    public ?Proveedor $proveedorEditando = null;
};
?>
<section class="h-fit rounded-2xl border border-slate-700 bg-slate-800 p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-100">
                        {{ $proveedorEditando ? 'Editar proveedor' : 'Registrar proveedor' }}
                    </h2>
                    <p class="mt-1 text-xs text-slate-400">El RUC debe tener 11 dígitos.</p>
                </div>
                @if ($proveedorEditando)
                    <a href="{{ route('proveedores.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                @endif
            </div>

            <form
                action="{{ $proveedorEditando ? route('proveedores.update', $proveedorEditando->id_proveedor) : route('proveedores.store') }}"
                method="POST"
                class="space-y-4"
            >
                @csrf
                @if ($proveedorEditando)
                    @method('PUT')
                @endif

                <div>
                    <label for="razon_social" class="mb-1 block text-xs font-semibold text-slate-300">Razón social</label>
                    <input
                        id="razon_social"
                        type="text"
                        name="razon_social"
                        value="{{ old('razon_social', $proveedorEditando?->razon_social) }}"
                        maxlength="150"
                        required
                        autocomplete="organization"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('razon_social') ? 'true' : 'false' }}"
                    >
                    @error('razon_social')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ruc" class="mb-1 block text-xs font-semibold text-slate-300">RUC</label>
                    <input
                        id="ruc"
                        type="text"
                        name="ruc"
                        value="{{ old('ruc', $proveedorEditando?->ruc) }}"
                        inputmode="numeric"
                        pattern="[0-9]{11}"
                        maxlength="11"
                        minlength="11"
                        required
                        autocomplete="off"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('ruc') ? 'true' : 'false' }}"
                    >
                    @error('ruc')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telefono" class="mb-1 block text-xs font-semibold text-slate-300">Teléfono</label>
                    <input
                        id="telefono"
                        type="tel"
                        name="telefono"
                        value="{{ old('telefono', $proveedorEditando?->telefono) }}"
                        maxlength="20"
                        autocomplete="tel"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('telefono') ? 'true' : 'false' }}"
                    >
                    @error('telefono')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-xs font-semibold text-slate-300">Correo electrónico</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $proveedorEditando?->email) }}"
                        maxlength="100"
                        autocomplete="email"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="direccion" class="mb-1 block text-xs font-semibold text-slate-300">Dirección</label>
                    <textarea
                        id="direccion"
                        name="direccion"
                        rows="3"
                        maxlength="200"
                        autocomplete="street-address"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('direccion') ? 'true' : 'false' }}"
                    >{{ old('direccion', $proveedorEditando?->direccion) }}</textarea>
                    @error('direccion')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-amber-500 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                    {{ $proveedorEditando ? 'Guardar cambios' : 'Registrar proveedor' }}
                </button>
            </form>
        </section>
