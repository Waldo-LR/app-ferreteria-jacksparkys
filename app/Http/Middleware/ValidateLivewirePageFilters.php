<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ValidateLivewirePageFilters
{
    public function handle(Request $request, Closure $next): Response
    {
        $rules = match ($request->route()?->getName()) {
            'historial-movimientos.index' => [
                'producto_historial' => ['nullable', 'string', 'max:100'],
                'tipo_movimiento' => ['nullable', Rule::in(['entrada', 'salida', 'ajuste'])],
                'fecha_desde' => ['nullable', 'date_format:Y-m-d', 'required_with:fecha_hasta'],
                'fecha_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_desde', 'required_with:fecha_desde'],
            ],
            'auditoria.index' => [
                'buscar' => ['nullable', 'string', 'max:100'],
                'fecha_desde' => ['nullable', 'date'],
                'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            ],
            'ventas.index' => [
                'buscar' => ['nullable', 'string', 'max:100'],
                'tipo_comprobante' => ['nullable', Rule::in(['nota_venta', 'boleta', 'factura'])],
                'estado' => ['nullable', Rule::in(['emitida', 'anulada'])],
                'fecha_desde' => ['nullable', 'date'],
                'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            ],
            'reportes.index' => [
                'tipo' => ['nullable', Rule::in(['ventas', 'alquileres', 'cobranzas', 'caja', 'inventario', 'compras', 'movimientos_inventario'])],
                'fecha_desde' => ['nullable', 'date_format:Y-m-d', 'required_with:fecha_hasta'],
                'fecha_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_desde', 'required_with:fecha_desde'],
            ],
            default => [],
        };

        $validator = Validator::make($request->query(), $rules);

        if ($request->route()?->getName() === 'reportes.index') {
            $validator->after(function ($validator) use ($request): void {
                $desde = $request->query('fecha_desde');
                $hasta = $request->query('fecha_hasta');

                if ($desde && $hasta && ! $validator->errors()->hasAny(['fecha_desde', 'fecha_hasta'])) {
                    if (Carbon::parse($desde)->diffInDays(Carbon::parse($hasta)) > 365) {
                        $validator->errors()->add('fecha_desde', 'El periodo máximo de consulta es de 366 días.');
                    }
                }
            });
        }

        $validator->validate();

        return $next($request);
    }
}
