<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ValidateProductCatalogFilters
{
    public function handle(Request $request, Closure $next): Response
    {
        Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50])],
            'sort' => ['nullable', Rule::in(['nombre', 'categoria', 'stock', 'precio_compra', 'precio_venta', 'precio_venta_mayor', 'estado_stock'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ])->validate();

        return $next($request);
    }
}
