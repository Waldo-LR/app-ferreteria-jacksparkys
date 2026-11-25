<?php

use Livewire\Component;

new class extends Component
{
    public $cob;
    public float $saldoPendiente = 0;
    public bool $cajaAlquileresAbierta = false;
};
?>
<div>
    <form action="{{ route('cobranzas.pagar', $cob) }}" method="POST" class="min-w-40 space-y-2">
        @csrf
        <input type="number" name="monto" min="0.01" max="{{ number_format($saldoPendiente, 2, '.', '') }}" step="0.01" value="{{ number_format($saldoPendiente, 2, '.', '') }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-2 py-2 text-slate-100">
        <select name="medio_pago" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-2 py-2 text-slate-100">
            @foreach(\App\Models\Caja::MEDIOS_PAGO as $medio)
                <option value="{{ $medio }}">{{ ucfirst($medio) }}</option>
            @endforeach
        </select>
        <input type="text" name="observaciones" maxlength="1000" placeholder="Observación (opcional)" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-2 py-2 text-slate-100">
        <button type="submit" @disabled(!$cajaAlquileresAbierta) title="Registrar abono" aria-label="Registrar abono" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10 disabled:cursor-not-allowed disabled:opacity-50">
            <i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i>
        </button>
    </form>
</div>
