<?php

use App\Http\Controllers\VentaController as PageController;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        $view = app()->call([app(PageController::class), 'pos']);

        return $view->getData();
    }
};
?>
<div>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping text-amber-500"></i> Registra Venta:
            </h1>
            <p class="text-xs text-slate-400">Emisión de Boletas, Facturas y Notas de Venta / Proformas en tiempo real (IGV 18%)</p>
        </div>
        <div class="bg-slate-800 border border-slate-700 px-4 py-2 rounded-xl text-right">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">N° Operación Interna</span>
            <span class="text-sm font-extrabold text-amber-400">{{ $nroVenta }}</span>
        </div>
    </div>

    @unless($cajaActual)
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-200 text-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span>La caja de Ventas está cerrada. Ábrela antes de registrar una venta.</span>
            <a href="{{ route('caja.index') }}" class="font-bold underline underline-offset-2">Ir a Control de Caja</a>
        </div>
    @endunless

    <!-- Formulario POS de 3 Paneles -->
    @livewire('ventas-form')
    @push('page-scripts')
        @include('shared.ventas-pos-script')
    @endpush
</div><!-- MODAL: BUSCAR PRODUCTOS -->
<div id="modalBuscarProd" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-800 rounded-2xl max-w-3xl w-full border border-slate-700 shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between bg-slate-850">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-amber-500 text-lg"></i>
                <h3 class="font-bold text-slate-100 text-sm">Catálogo de Productos de Ferretería</h3>
            </div>
            <button type="button" onclick="cerrarModalBuscarProductos()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="p-4 border-b border-slate-700 bg-slate-900/50">
            <div class="relative">
                <input type="text" id="inputFiltroProd" onkeyup="filtrarProductosModal()" placeholder="Buscar por código, nombre, categoría o marca..." class="w-full bg-slate-900 border border-slate-700 rounded-xl py-2.5 pl-9 pr-4 text-xs text-slate-200 focus:outline-none focus:border-amber-500">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
            </div>
        </div>

        <div class="p-4 overflow-y-auto flex-1 space-y-2" id="listaProdsModal">
            @foreach($productos as $p)
                <div class="item-prod-modal p-3 rounded-xl bg-slate-900/80 border border-slate-700/80 flex flex-wrap items-center gap-3 text-xs hover:border-amber-500/50 transition">
                    <div class="relative flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-600 bg-white">
                        <i class="fa-solid fa-box-open text-lg text-slate-500" aria-hidden="true"></i>
                        @if($p->imagen)
                            <button type="button" class="absolute inset-0 cursor-zoom-in rounded-xl focus-visible:outline-offset-[-3px]" aria-label="Ampliar imagen de {{ $p->nombre }}" aria-haspopup="dialog" data-nombre="{{ $p->nombre }}" onclick="ampliarImagenPOS(this)">
                                <img src="{{ $p->imagenUrl() }}" alt="Imagen de {{ $p->nombre }}" width="56" height="56" loading="lazy" class="h-full w-full bg-white object-contain" onerror="this.parentElement.hidden = true">
                            </button>
                        @endif
                    </div>
                    <div class="min-w-32 flex-1 space-y-0.5">
                        <div class="font-bold text-slate-200 flex flex-wrap items-center gap-2">
                            <span>{{ $p->nombre }}</span>
                            <span class="text-[10px] px-2 py-0.2 rounded bg-slate-800 text-amber-400 font-bold border border-slate-700">{{ $p->codigo }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            Cat: <strong class="text-slate-300">{{ $p->categoria->nombre ?? '-' }}</strong> | Marca: <strong class="text-slate-300">{{ $p->marca->nombre ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="ml-auto flex flex-wrap items-center justify-end gap-4">
                        <div class="text-right">
                            <span class="block font-extrabold text-emerald-400 text-sm">S/ {{ number_format($p->precio_venta, 2) }}</span>
                            <span class="text-[10px] text-slate-400 block">Mayor: S/ {{ number_format($p->precio_venta_mayor ?? $p->precio_venta, 2) }}</span>
                        </div>

                        @if($p->stock > 0)
                            <div class="text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 block mb-1">
                                    Stock: {{ $p->stock }}
                                </span>
                                <button type="button" onclick="agregarProductoPOS({{ json_encode($p) }})" title="Agregar" aria-label="Agregar" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10">
                                    <i class="fa-solid fa-cart-plus" aria-hidden="true"></i>
                                </button>
                            </div>
                        @else
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                    AGOTADO (0)
                                </span>
                                <div class="flex gap-1">
                                    <button type="button" onclick="alertarAlmacen({{ $p->id_producto }})" title="Alertar Almacén" aria-label="Alertar Almacén" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-rose-500/40 text-rose-400 hover:bg-rose-500/10">
                                        <i class="fa-solid fa-bell" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" onclick="notificarCliente({{ $p->id_producto }})" title="Notificar Cliente" aria-label="Notificar Cliente" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition border-blue-500/40 text-blue-400 hover:bg-blue-500/10">
                                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<dialog id="vistaImagenPOS" aria-labelledby="tituloImagenPOS" class="m-auto max-w-[92vw] rounded-2xl border border-slate-700 bg-slate-800 p-4 shadow-2xl backdrop:bg-black/70">
    <div class="mb-3 flex items-center justify-between gap-4">
        <h3 id="tituloImagenPOS" class="font-bold text-slate-100"></h3>
        <form method="dialog">
            <button type="submit" autofocus aria-label="Cerrar previsualización" title="Cerrar previsualización" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#b91c1c] text-[#fff] hover:bg-[#991b1b] focus-visible:outline-2 focus-visible:outline-offset-2">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </form>
    </div>
    <img class="mx-auto block max-h-[70vh] max-w-full rounded-xl bg-white object-contain" alt="">
</dialog>
<!-- MODAL: BUSCAR CLIENTES -->
<div id="modalBuscarCli" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-800 rounded-2xl max-w-lg w-full border border-slate-700 shadow-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between bg-slate-850">
            <h3 class="font-bold text-slate-100 text-sm">Seleccionar Cliente para la Venta</h3>
            <button type="button" onclick="cerrarModalBuscarCliente()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-4 max-h-[60vh] overflow-y-auto space-y-2">
            @foreach($clientes as $cli)
                <div onclick="seleccionarClientePOS({{ json_encode($cli) }})" class="p-3 rounded-xl bg-slate-900 border border-slate-700/80 hover:border-amber-500 cursor-pointer transition flex justify-between items-center text-xs">
                    <div>
                        <div class="font-bold text-slate-200">{{ $cli->nombres }}</div>
                        <div class="text-[11px] text-slate-400">{{ $cli->tipo_documento }}: {{ $cli->dni_ruc }}</div>
                    </div>
                    <span class="text-amber-400 font-bold text-xs"><i class="fa-solid fa-check"></i> Elegir</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

</div>
