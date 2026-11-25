<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BajasProductosController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof Usuario &&
            ($user->esAdmin() || $user->esAlmacenero()),
            403
        );

        $validated = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        $query = Producto::with(['categoria', 'marca', 'usuarioBaja'])
            ->where('activo', false)
            ->whereNotNull('fecha_baja')
            ->latest('fecha_baja');

        if (! empty($validated['buscar'])) {
            $buscar = trim($validated['buscar']);

            $query->where(function (Builder $query) use ($buscar): void {
                $query->where('codigo', 'like', '%'.$buscar.'%')
                    ->orWhere('nombre', 'like', '%'.$buscar.'%')
                    ->orWhereHas('categoria', fn (Builder $categoria) => $categoria->where('nombre', 'like', '%'.$buscar.'%'))
                    ->orWhereHas('marca', fn (Builder $marca) => $marca->where('nombre', 'like', '%'.$buscar.'%'));
            });
        }

        if (! empty($validated['fecha_desde'])) {
            $query->whereDate('fecha_baja', '>=', $validated['fecha_desde']);
        }

        if (! empty($validated['fecha_hasta'])) {
            $query->whereDate('fecha_baja', '<=', $validated['fecha_hasta']);
        }

        $bajas = $query->paginate(20)->withQueryString();

        return view('shared.livewire-data', compact('bajas'));
    }

    public function reactivar(Request $request, Producto $producto): RedirectResponse
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof Usuario &&
            ($user->esAdmin() || $user->esAlmacenero()),
            403
        );

        abort_unless(! $producto->activo, 404);

        $producto->update([
            'activo' => true,
            'fecha_baja' => null,
            'id_usuario_baja' => null,
        ]);

        return back()->with('success', 'Producto habilitado correctamente. Vuelve a estar disponible para la venta.');
    }
}
