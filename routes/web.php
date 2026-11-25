<?php

use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BajasProductosController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use App\Http\Middleware\ValidateLivewirePageFilters;
use App\Http\Middleware\ValidateProductCatalogFilters;
use Illuminate\Support\Facades\Route;

// Autenticación
Route::livewire('/login', 'pages::auth.login')->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->middleware('guest')->name('quick.login');
Route::post('/cambiar-perfil/{role}', [AuthController::class, 'cambiarPerfil'])->middleware('auth')->whereIn('role', ['admin', 'vendedor', 'almacen', 'cobranzas', 'cajero'])->name('perfil.cambiar');
Route::post('/profile/update', [AuthController::class, 'updateProfile'])->middleware('auth')->name('profile.update');

// Panel y Módulos Web del Sistema
Route::livewire('/', 'pages::dashboard.listados')->middleware('auth')->name('home');
Route::livewire('/dashboard', 'pages::dashboard.listados')->middleware('auth')->name('dashboard');

// Módulo POS & Ventas
Route::middleware('auth')->group(function (): void {
    Route::livewire('/ventas/pos', 'pages::ventas.pos')->name('ventas.pos');
    Route::livewire('/ventas', 'pages::ventas.listados')->middleware(ValidateLivewirePageFilters::class)->name('ventas.index');
    Route::post('/ventas/store', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/imprimir/{id}', [VentaController::class, 'imprimir'])->name('ventas.imprimir');
    Route::get('/ventas/{id}/pdf', [VentaController::class, 'descargarPdf'])->name('ventas.pdf');
    Route::post('/ventas/anular/{id}', [VentaController::class, 'anular'])->name('ventas.anular');
    Route::post('/ventas/alertar-almacen', [VentaController::class, 'alertarAlmacen'])->name('ventas.alertarAlmacen');
    Route::post('/ventas/notificar-cliente', [VentaController::class, 'notificarCliente'])->name('ventas.notificarCliente');
});

// Módulo Alquileres de Equipos
Route::middleware(['auth', 'check.rol:admin,vendedor,cobrador'])->group(function (): void {
    Route::redirect('/alquileres', '/alquileres/historial')->name('alquileres.home');
    Route::livewire('/alquileres/registrar', 'pages::alquileres.registro')->name('alquileres.create');
    Route::livewire('/alquileres/historial', 'pages::alquileres.listados')->name('alquileres.index');
    Route::post('/alquileres/store', [AlquilerController::class, 'store'])->name('alquileres.store');
    Route::post('/alquileres/{alquiler}/devolver', [AlquilerController::class, 'devolver'])->name('alquileres.devolver');

    // Módulo Cobranzas y Moras
    Route::livewire('/cobranzas', 'pages::cobranzas.listados')->name('cobranzas.index');
    Route::post('/cobranzas/pagar/{cobranza}', [CobranzaController::class, 'pagar'])->name('cobranzas.pagar');
    Route::post('/cobranzas/recuperar/{alquiler}', [CobranzaController::class, 'recuperar'])->name('cobranzas.recuperar');
});

// Control de Caja
Route::middleware('auth')->group(function () {
    Route::livewire('/caja', 'pages::caja.listados')->name('caja.index');
    Route::post('/caja/aperturar', [CajaController::class, 'aperturar'])->name('caja.aperturar');
    Route::post('/caja/{caja}/movimientos', [CajaController::class, 'registrarMovimiento'])->name('caja.movimientos.store');
    Route::post('/caja/{caja}/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
    Route::livewire('/inventario', 'pages::inventario.listados')->name('inventario.index');
    Route::livewire('/historial-movimientos', 'pages::historial-movimientos.listados')->middleware(ValidateLivewirePageFilters::class)->name('historial-movimientos.index');
    Route::post('/inventario/{producto}/movimientos', [InventarioController::class, 'storeMovement'])->name('inventario.movimientos.store');
    Route::livewire('/reportes', 'pages::reportes.listados')->middleware(ValidateLivewirePageFilters::class)->name('reportes.index');
    Route::get('/reportes/pdf', [ReportesController::class, 'pdf'])->name('reportes.pdf');
});

Route::middleware(['auth', 'check.rol:admin,almacenero'])->group(function () {
    Route::livewire('/compras', 'pages::compras.listados')->name('compras.index');
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');
    Route::post('/compras/{compra}/pagos', [CompraController::class, 'storePayment'])->name('compras.pagos.store');
});

Route::middleware(['auth', 'check.rol:admin,almacenero'])->group(function () {
    Route::livewire('/historial-bajas-productos', 'pages::bajas.listados')->name('bajas.index');
});
Route::middleware(['auth', 'check.rol:admin,almacenero'])->post('/historial-bajas-productos/{producto}/reactivar', [BajasProductosController::class, 'reactivar'])->name('bajas.reactivar');

// Módulos CRUD de Administración
Route::middleware('auth')->get('/productos/{producto}/imagen', [ProductoController::class, 'image'])->name('productos.image');
Route::livewire('/productos', 'pages::productos.listado')
    ->middleware(['auth', ValidateProductCatalogFilters::class])
    ->name('productos.index');
Route::middleware('auth')->group(function () {
    Route::livewire('/productos/create', 'pages::productos.create')->name('productos.create');
    Route::livewire('/productos/{producto}/edit', 'pages::productos.edit')->name('productos.edit');
    Route::resource('productos', ProductoController::class)->only(['store', 'update', 'destroy']);
});
Route::middleware('auth')->group(function (): void {
    Route::livewire('/categorias', 'pages::categorias.listados')->name('categorias.index');
    Route::livewire('/categorias/{categoria}/edit', 'pages::categorias.listados')->name('categorias.edit');
    Route::patch('/categorias/{categoria}/reactivar', [CategoriaController::class, 'reactivar'])->name('categorias.reactivar');
    Route::delete('/categorias/{categoria}/definitivo', [CategoriaController::class, 'eliminar'])->name('categorias.eliminar');
    Route::resource('categorias', CategoriaController::class)->only(['store', 'update', 'destroy']);
    Route::livewire('/marcas', 'pages::marcas.listados')->name('marcas.index');
    Route::livewire('/marcas/{marca}/edit', 'pages::marcas.listados')->name('marcas.edit');
    Route::patch('/marcas/{marca}/reactivar', [MarcaController::class, 'reactivar'])->name('marcas.reactivar');
    Route::delete('/marcas/{marca}/definitivo', [MarcaController::class, 'eliminar'])->name('marcas.eliminar');
    Route::resource('marcas', MarcaController::class)->only(['store', 'update', 'destroy']);
});
Route::middleware('auth')->group(function () {
    Route::livewire('/clientes', 'pages::clientes.listados')->name('clientes.index');
    Route::livewire('/clientes/{cliente}/edit', 'pages::clientes.editar')->name('clientes.edit');
    Route::resource('clientes', ClienteController::class)->only(['store', 'update', 'destroy']);
});
Route::middleware('auth')->group(function (): void {
    Route::livewire('/proveedores', 'pages::proveedores.listados')->name('proveedores.index');
    Route::livewire('/proveedores/{proveedore}/edit', 'pages::proveedores.editar')->name('proveedores.edit');
    Route::resource('proveedores', ProveedorController::class)->only(['store', 'update', 'destroy']);
});
Route::middleware('auth')->group(function () {
    Route::livewire('/usuarios', 'pages::usuarios.listados')->name('usuarios.index');
    Route::livewire('/usuarios/{usuario}/edit', 'pages::usuarios.editar')->name('usuarios.edit');
    Route::resource('usuarios', UsuarioController::class)->only(['store', 'update', 'destroy']);
});
Route::middleware('auth')->group(function () {
    Route::livewire('/auditoria', 'pages::auditoria.listados')->middleware(ValidateLivewirePageFilters::class)->name('auditoria.index');
    Route::livewire('/configuracion', 'pages::configuracion.listados')->name('configuracion.index');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
});
