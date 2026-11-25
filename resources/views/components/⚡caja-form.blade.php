<?php

use Livewire\Component;

new class extends Component
{
    public string $modo;
    public ?\App\Models\Caja $caja = null;
    public string $tipoCaja;
    public string $nombreCaja = '';
    public array $categoriasMovimiento = [];
};
?>
<div>
@if($modo === 'movimiento')
<form action="{{ route('caja.movimientos.store', $caja) }}" method="POST" class="bg-slate-900/60 p-4 md:p-5 rounded-xl border border-slate-700 space-y-4">
                            @csrf
                            <input type="hidden" name="tipo_caja" value="{{ $tipoCaja }}">
                            <div>
                                <h3 class="font-bold text-slate-100">Registrar movimiento</h3>
                                <p class="text-xs text-slate-400 mt-1">Los ingresos y egresos quedan vinculados a esta caja y al usuario.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="text-xs text-slate-300 space-y-1">
                                    <span>Tipo</span>
                                    <select name="tipo_movimiento" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">
                                        <option value="ingreso" {{ old('tipo_movimiento') === 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                                        <option value="egreso" {{ old('tipo_movimiento') === 'egreso' ? 'selected' : '' }}>Egreso</option>
                                    </select>
                                </label>
                                <label class="text-xs text-slate-300 space-y-1">
                                    <span>Categoría</span>
                                    <select name="categoria" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">
                                        <option value="">Selecciona una categoría</option>
                                        @foreach($categoriasMovimiento as $categoria)
                                            <option value="{{ $categoria }}" {{ old('categoria') === $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="text-xs text-slate-300 space-y-1">
                                    <span>Monto (S/)</span>
                                    <input type="number" name="monto" min="0.01" step="0.01" value="{{ old('monto') }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">
                                </label>
                                <label class="text-xs text-slate-300 space-y-1">
                                    <span>Medio de pago</span>
                                    <select name="medio_pago" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">
                                        <option value="efectivo" {{ old('medio_pago', 'efectivo') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="yape" {{ old('medio_pago') === 'yape' ? 'selected' : '' }}>Yape / pago digital</option>
                                        <option value="deposito" {{ old('medio_pago') === 'deposito' ? 'selected' : '' }}>Depósito / transferencia</option>
                                    </select>
                                </label>
                            </div>
                            <label class="text-xs text-slate-300 space-y-1 block">
                                <span>Detalle / sustento</span>
                                <textarea name="detalle" rows="2" minlength="3" maxlength="2000" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100" placeholder="Describe el motivo del movimiento">{{ old('detalle') }}</textarea>
                            </label>
                            @error('monto')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                            @error('caja')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-lg text-sm transition">
                                <i class="fa-solid fa-plus mr-1"></i> Guardar movimiento
                            </button>
                        </form>
@elseif($modo === 'cierre')
<form action="{{ route('caja.cerrar', $caja) }}" method="POST" class="bg-slate-900/60 p-4 md:p-5 rounded-xl border border-rose-500/20 space-y-4">
                            @csrf
                            <input type="hidden" name="tipo_caja" value="{{ $tipoCaja }}">
                            <div>
                                <h3 class="font-bold text-slate-100">Cerrar caja y realizar arqueo</h3>
                                <p class="text-xs text-slate-400 mt-1">Cuenta solo el efectivo físico. Los pagos digitales se concilian por separado.</p>
                            </div>
                            <div class="bg-slate-800 p-3 rounded-lg flex items-center justify-between gap-3">
                                <span class="text-sm text-slate-300">Efectivo esperado</span>
                                <span class="font-extrabold text-amber-300">S/ {{ number_format($caja->monto_final, 2) }}</span>
                            </div>
                            <label class="text-xs text-slate-300 space-y-1 block">
                                <span>Efectivo contado (S/)</span>
                                <input type="number" name="monto_contado" min="0" step="0.01" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100" placeholder="0.00">
                            </label>
                            <label class="text-xs text-slate-300 space-y-1 block">
                                <span>Observaciones / motivo de diferencia</span>
                                <textarea name="observaciones" rows="2" maxlength="1000" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100" placeholder="Obligatorio si hay sobrante o faltante"></textarea>
                            </label>
                            @error('monto_contado')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                            @error('observaciones')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-rose-500 hover:bg-rose-400 text-white font-bold rounded-lg text-sm transition" onclick="return confirm('¿Confirmas el cierre y el arqueo de esta caja?');">
                                <i class="fa-solid fa-lock mr-1"></i> Cerrar y guardar arqueo
                            </button>
                        </form>
@else
<div class="p-5 md:p-6">
<form action="{{ route('caja.aperturar') }}" method="POST" class="max-w-2xl bg-slate-900/60 p-4 md:p-5 rounded-xl border border-slate-700 space-y-4">
                        @csrf
                        <input type="hidden" name="tipo_caja" value="{{ $tipoCaja }}">
                        <div>
                            <h3 class="font-bold text-slate-100">Abrir caja de {{ $nombreCaja }}</h3>
                            <p class="text-xs text-slate-400 mt-1">Define el fondo inicial de efectivo para este turno.</p>
                        </div>
                        <label class="text-xs text-slate-300 space-y-1 block">
                            <span>Saldo inicial en efectivo (S/)</span>
                            <input type="number" name="monto_inicial" min="0" step="0.01" value="{{ old('monto_inicial', '0.00') }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">
                        </label>
                        <label class="text-xs text-slate-300 space-y-1 block">
                            <span>Observaciones de apertura</span>
                            <textarea name="observaciones" rows="2" maxlength="1000" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('observaciones') }}</textarea>
                        </label>
                        @error('tipo_caja')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                        @error('monto_inicial')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                        <button type="submit" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-sm transition">
                            <i class="fa-solid fa-lock-open mr-1"></i> Abrir caja
                        </button>
                    </form>
</div>
@endif
</div>
