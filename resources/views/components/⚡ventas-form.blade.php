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
<form id="formPOS" action="{{ route('ventas.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- PANEL 1: DETALLE DE PRODUCTOS (2 Columnas) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">1</span>
                            <h2 class="font-bold text-slate-200 text-sm">Detalle de Productos a Vender</h2>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5 text-xs text-slate-300">
                                <i class="fa-solid fa-calendar text-amber-400"></i>
                                <span>Emisión:</span>
                                <input type="date" name="fecha_emision" value="{{ date('Y-m-d') }}" class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-1 focus:outline-none focus:border-amber-500">
                            </div>
                            <button type="button" onclick="abrirModalBuscarProductos()" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs transition flex items-center gap-1.5 shadow">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <span>Buscar Productos</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs" id="tablaPOS">
                            <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px]">
                                <tr>
                                    <th class="p-3">N°</th>
                                    <th class="p-3">Producto</th>
                                    <th class="p-3">Detalle / Marca</th>
                                    <th class="p-3 text-center w-24">Cantidad</th>
                                    <th class="p-3 text-right">Precio Unit.</th>
                                    <th class="p-3 text-right">Sub Total</th>
                                    <th class="p-3 text-center w-16">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/60" id="bodyPOS">
                                <tr id="rowVacia">
                                    <td colspan="7" class="p-8 text-center text-slate-500 italic">
                                        <i class="fa-solid fa-basket-shopping text-3xl mb-2 block text-slate-600"></i>
                                        Aún no has agregado productos a la venta. Haz clic en <strong>"Buscar Productos"</strong> para seleccionar artículos del catálogo.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-900/90 text-slate-200 border-t-2 border-slate-700 font-bold">
                                <tr>
                                    <td colspan="5" class="p-3 text-right text-xs uppercase tracking-wider">Importe Total General:</td>
                                    <td class="p-3 text-right text-base text-emerald-400 font-extrabold" id="totalGeneralDisplay">S/ 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>            <!-- PANEL 2 & 3: CLIENTE & PAGO Y COMPROBANTE -->
            <div class="space-y-6">

                <!-- PANEL 2: DATOS DEL CLIENTE -->
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5 shadow-xl space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">2</span>
                            <h2 class="font-bold text-slate-200 text-sm">Datos del Cliente</h2>
                        </div>
                        <button type="button" onclick="abrirModalBuscarCliente()" class="px-2.5 py-1 bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold rounded-lg text-[11px] transition flex items-center gap-1">
                            <i class="fa-solid fa-user-check"></i>
                            <span>Buscar / Seleccionar</span>
                        </button>
                    </div>

                    <input type="hidden" name="id_cliente" id="input_id_cliente" value="{{ $clientes->first()->id_cliente ?? 1 }}">

                    <div id="cardCliente" class="p-3 rounded-xl bg-slate-900/80 border border-slate-700 space-y-1 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-amber-400" id="cli_nombre">{{ $clientes->first()->nombres ?? 'Cliente General' }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700" id="cli_doc">DNI: {{ $clientes->first()->dni_ruc ?? '00000000' }}</span>
                        </div>
                        <div class="text-slate-400 text-[11px]" id="cli_dir"><i class="fa-solid fa-location-dot mr-1"></i> {{ $clientes->first()->direccion ?? 'Atención en mostrador' }}</div>
                        <div class="text-slate-400 text-[11px]" id="cli_tel"><i class="fa-solid fa-phone mr-1"></i> {{ $clientes->first()->telefono ?? '-' }}</div>
                    </div>
                </div>                <!-- PANEL 3: REGISTRAR PAGO Y GENERAR COMPROBANTE -->
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5 shadow-xl space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-700">
                        <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">3</span>
                        <h2 class="font-bold text-slate-200 text-sm">Pago & Comprobante SUNAT</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Comprobante</label>
                            <select name="tipo_comprobante" id="tipo_comprobante" onchange="cambiarTipoComprobante()" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 font-semibold focus:outline-none focus:border-amber-500">
                                <option value="boleta">Boleta de Venta</option>
                                <option value="factura">Factura (IGV 18%)</option>
                                <option value="nota_venta">Nota de Venta / Proforma</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Serie Asignada</label>
                            <input type="text" readonly id="display_serie" value="{{ $serieBoleta->serie ?? 'B001' }}" class="w-full bg-slate-900/60 border border-slate-700 rounded-xl p-2.5 text-amber-400 font-bold text-center">
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Medio de Pago</label>
                            <select name="medio_pago" id="medio_pago" onchange="calcularVuelto()" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 focus:outline-none focus:border-amber-500">
                                <option value="efectivo">Efectivo en Caja</option>
                                <option value="yape">Yape / Plin (Billetera Digital)</option>
                                <option value="deposito">Depósito / Transferencia Bancaria</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Paga con (S/)</label>
                                <input type="number" step="0.50" min="0" name="paga_con" id="paga_con" oninput="calcularVuelto()" placeholder="0.00" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-100 font-bold text-sm focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Cambio / Vuelto</label>
                                <input type="text" readonly id="display_vuelto" value="S/ 0.00" class="w-full bg-slate-900/80 border border-slate-700 rounded-xl p-2.5 text-emerald-400 font-extrabold text-sm text-center">
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 p-3 rounded-xl border border-slate-700/80 space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal / Op. Gravada:</span>
                            <span id="displaySubtotal">S/ 0.00</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>IGV (18%):</span>
                            <span id="displayIGV">S/ 0.00</span>
                        </div>
                        <div class="flex justify-between text-slate-100 font-extrabold text-sm pt-1 border-t border-slate-800">
                            <span>Total Pagar:</span>
                            <span id="displayTotalPagar" class="text-emerald-400">S/ 0.00</span>
                        </div>
                    </div>

                    <div class="pt-2 space-y-2">
                        <button type="submit" {{ $cajaActual ? '' : 'disabled' }} class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 disabled:cursor-not-allowed text-slate-950 font-black rounded-xl text-sm transition shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Guardar Venta & Generar Comprobante</span>
                        </button>
                    </div>

                </div>

            </div>

        </div>
    </form>
