<?php

use App\Http\Controllers\CajaController as PageController;
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
<div>
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-cash-register text-amber-400"></i>
            Control de Caja
        </h1>
        <p class="text-sm text-slate-400 mt-1">Apertura, ingresos, egresos y arqueo independiente para cada operación.</p>
    </div>

    <div class="rounded-2xl border border-slate-700 bg-slate-800 p-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" role="tablist" aria-label="Tipo de caja">
            @foreach($tiposCaja as $tipoCaja => $nombreCaja)
                @php
                    $pestanaActiva = old('tipo_caja', $loop->first ? $tipoCaja : null) === $tipoCaja;
                @endphp
                <button
                    id="tab-{{ $tipoCaja }}"
                    type="button"
                    role="tab"
                    aria-selected="{{ $pestanaActiva ? 'true' : 'false' }}"
                    aria-controls="panel-{{ $tipoCaja }}"
                    tabindex="{{ $pestanaActiva ? '0' : '-1' }}"
                    data-caja-tab="{{ $tipoCaja }}"
                    class="rounded-xl px-4 py-3 text-left text-sm font-bold transition {{ $pestanaActiva ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-500/10' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}"
                >
                    <span class="flex items-center gap-2">
                        <i class="fa-solid {{ $tipoCaja === 'ventas' ? 'fa-bag-shopping' : 'fa-screwdriver-wrench' }}"></i>
                        {{ $nombreCaja }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    @foreach($tiposCaja as $tipoCaja => $nombreCaja)
        @php
            $caja = $cajasAbiertas->get($tipoCaja);
            $pestanaActiva = old('tipo_caja', $loop->first ? $tipoCaja : null) === $tipoCaja;
        @endphp
        <section
            id="panel-{{ $tipoCaja }}"
            role="tabpanel"
            aria-labelledby="tab-{{ $tipoCaja }}"
            data-caja-panel="{{ $tipoCaja }}"
            @unless($pestanaActiva) hidden @endunless
            class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden"
        >
            <div class="p-5 md:p-6 border-b border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-100">{{ $nombreCaja }}</h2>
                </div>
                @if($caja)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold w-fit">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        ABIERTA
                    </div>
                @else
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-700 text-slate-300 text-xs font-bold w-fit">
                        <i class="fa-solid fa-lock"></i> CERRADA
                    </span>
                @endif
            </div>

            @if($caja)
                @php
                    $ingresosDigitales = (float) $caja->total_ingresos - (float) $caja->ingresos_efectivo;
                    $egresosDigitales = (float) $caja->total_egresos - (float) $caja->egresos_efectivo;
                @endphp

                <div class="p-5 md:p-6 space-y-6">
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="bg-slate-900/70 p-4 rounded-xl border border-slate-700">
                            <span class="text-xs text-slate-400 block">Saldo inicial</span>
                            <span class="text-lg font-bold text-slate-100">S/ {{ number_format($caja->monto_inicial, 2) }}</span>
                        </div>
                        <div class="bg-slate-900/70 p-4 rounded-xl border border-slate-700">
                            <span class="text-xs text-slate-400 block">Ingresos totales</span>
                            <span class="text-lg font-bold text-emerald-400">S/ {{ number_format($caja->total_ingresos, 2) }}</span>
                        </div>
                        <div class="bg-slate-900/70 p-4 rounded-xl border border-slate-700">
                            <span class="text-xs text-slate-400 block">Egresos totales</span>
                            <span class="text-lg font-bold text-rose-400">S/ {{ number_format($caja->total_egresos, 2) }}</span>
                        </div>
                        <div class="bg-slate-900/70 p-4 rounded-xl border border-slate-700">
                            <span class="text-xs text-slate-400 block">Ingresos en efectivo</span>
                            <span class="text-lg font-bold text-emerald-300">S/ {{ number_format($caja->ingresos_efectivo, 2) }}</span>
                        </div>
                        <div class="bg-slate-900/70 p-4 rounded-xl border border-slate-700">
                            <span class="text-xs text-slate-400 block">Neto no efectivo</span>
                            <span class="text-lg font-bold text-blue-300">S/ {{ number_format($ingresosDigitales - $egresosDigitales, 2) }}</span>
                        </div>
                        <div class="bg-amber-500/10 p-4 rounded-xl border border-amber-500/30">
                            <span class="text-xs text-amber-200 block">Efectivo esperado</span>
                            <span class="text-lg font-extrabold text-amber-300">S/ {{ number_format($caja->monto_final, 2) }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                        @livewire('caja-form', ['modo' => 'movimiento', 'caja' => $caja, 'tipoCaja' => $tipoCaja, 'categoriasMovimiento' => $categoriasMovimiento])

                        @livewire('caja-form', ['modo' => 'cierre', 'caja' => $caja, 'tipoCaja' => $tipoCaja])
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h3 class="font-bold text-slate-100">Actividad reciente</h3>
                            <span class="text-xs text-slate-400">Hasta 20 movimientos</span>
                        </div>
                        <div class="overflow-x-auto rounded-xl border border-slate-700">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-900 text-xs uppercase text-slate-400">
                                    <tr>
                                        <th class="p-3">Fecha</th>
                                        <th class="p-3">Concepto</th>
                                        <th class="p-3">Medio</th>
                                        <th class="p-3">Usuario</th>
                                        <th class="p-3 text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700">
                                    @forelse($actividadPorCaja[$tipoCaja] ?? [] as $movimiento)
                                        <tr>
                                            <td class="p-3 whitespace-nowrap text-slate-400">{{ $movimiento['fecha']?->format('d/m/Y H:i') }}</td>
                                            <td class="p-3 text-slate-200">
                                                <span class="font-semibold {{ $movimiento['tipo'] === 'ingreso' ? 'text-emerald-400' : 'text-rose-400' }}">
                                                    {{ ucfirst($movimiento['tipo']) }}
                                                </span>
                                                <span class="block text-xs text-slate-400">{{ $movimiento['concepto'] }}</span>
                                            </td>
                                            <td class="p-3 text-slate-300">{{ ucfirst($movimiento['medio_pago']) }}</td>
                                            <td class="p-3 text-slate-400">{{ $movimiento['usuario'] }}</td>
                                            <td class="p-3 text-right font-bold {{ $movimiento['tipo'] === 'ingreso' ? 'text-emerald-400' : 'text-rose-400' }}">
                                                {{ $movimiento['tipo'] === 'ingreso' ? '+' : '-' }} S/ {{ number_format($movimiento['monto'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="p-5 text-center text-slate-400">Aún no hay movimientos en este turno.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-5 md:p-6">
                    @livewire('caja-form', ['modo' => 'apertura', 'tipoCaja' => $tipoCaja, 'nombreCaja' => $nombreCaja])
                </div>
            @endif
        </section>
    @endforeach

    <script>
        (() => {
            const tabs = Array.from(document.querySelectorAll('[data-caja-tab]'));
            const panels = Array.from(document.querySelectorAll('[data-caja-panel]'));

            const activateTab = (type) => {
                tabs.forEach((tab) => {
                    const isActive = tab.dataset.cajaTab === type;
                    tab.setAttribute('aria-selected', String(isActive));
                    tab.setAttribute('tabindex', isActive ? '0' : '-1');
                    tab.classList.toggle('bg-amber-500', isActive);
                    tab.classList.toggle('text-slate-950', isActive);
                    tab.classList.toggle('shadow-lg', isActive);
                    tab.classList.toggle('shadow-amber-500/10', isActive);
                    tab.classList.toggle('text-slate-300', !isActive);
                    tab.classList.toggle('hover:bg-slate-700', !isActive);
                    tab.classList.toggle('hover:text-white', !isActive);
                });

                panels.forEach((panel) => {
                    panel.hidden = panel.dataset.cajaPanel !== type;
                });
            };

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => activateTab(tab.dataset.cajaTab));
                tab.addEventListener('keydown', (event) => {
                    if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
                        return;
                    }

                    event.preventDefault();
                    const nextIndex = event.key === 'Home'
                        ? 0
                        : event.key === 'End'
                            ? tabs.length - 1
                            : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
                    tabs[nextIndex].focus();
                    activateTab(tabs[nextIndex].dataset.cajaTab);
                });
            });
        })();
    </script>

    <section class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">
        <div class="p-5 border-b border-slate-700">
            <h2 class="font-bold text-slate-100">Cierres recientes</h2>
            <p class="text-xs text-slate-400 mt-1">Historial de arqueos por tipo de caja.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-900 text-xs uppercase text-slate-400">
                    <tr>
                        <th class="p-3">Caja</th>
                        <th class="p-3">Apertura</th>
                        <th class="p-3">Cierre</th>
                        <th class="p-3">Responsables</th>
                        <th class="p-3 text-right">Esperado</th>
                        <th class="p-3 text-right">Contado</th>
                        <th class="p-3 text-right">Diferencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($cierres as $cierre)
                        <tr>
                            <td class="p-3 text-slate-200">{{ $tiposCaja[$cierre->tipo_caja] ?? $cierre->tipo_caja }}</td>
                            <td class="p-3 text-slate-400 whitespace-nowrap">{{ $cierre->fecha_apertura?->format('d/m/Y H:i') }}</td>
                            <td class="p-3 text-slate-400 whitespace-nowrap">{{ $cierre->fecha_cierre?->format('d/m/Y H:i') }}</td>
                            <td class="p-3 text-slate-400">{{ $cierre->usuario?->nombre }} / {{ $cierre->usuarioCierre?->nombre }}</td>
                            <td class="p-3 text-right text-slate-300">S/ {{ number_format($cierre->monto_final, 2) }}</td>
                            <td class="p-3 text-right text-slate-300">S/ {{ number_format($cierre->monto_contado, 2) }}</td>
                            <td class="p-3 text-right font-bold {{ (float) $cierre->diferencia_cierre < 0 ? 'text-rose-400' : ((float) $cierre->diferencia_cierre > 0 ? 'text-emerald-400' : 'text-slate-300') }}">
                                S/ {{ number_format($cierre->diferencia_cierre, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-5 text-center text-slate-400">Todavía no hay cierres registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

</div>
