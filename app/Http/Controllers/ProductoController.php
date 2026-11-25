<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\MovimientoStockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->authorizeView();
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50])],
            'sort' => ['nullable', Rule::in(['nombre', 'categoria', 'stock', 'precio_compra', 'precio_venta', 'precio_venta_mayor', 'estado_stock'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $sort = $filters['sort'] ?? 'nombre';
        $direction = $filters['direction'] ?? 'asc';
        $perPage = (int) ($filters['per_page'] ?? 10);

        $productosQuery = Producto::query()
            ->with(['categoria', 'marca'])
            ->where('activo', true)
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('codigo', 'like', '%'.$search.'%')
                        ->orWhere('nombre', 'like', '%'.$search.'%')
                        ->orWhereHas('categoria', fn (Builder $categoryQuery) => $categoryQuery->where('nombre', 'like', '%'.$search.'%'))
                        ->orWhereHas('marca', fn (Builder $brandQuery) => $brandQuery->where('nombre', 'like', '%'.$search.'%'));
                });
            });

        if ($sort === 'categoria') {
            $productosQuery->orderBy(
                Categoria::query()
                    ->select('nombre')
                    ->whereColumn('tcategorias.id_categoria', 'tproductos.id_categoria'),
                $direction
            );
        } elseif ($sort === 'estado_stock') {
            $productosQuery->orderByRaw(
                'CASE 
                    WHEN stock = 0 THEN 1 
                    WHEN stock <= stock_minimo THEN 2 
                    ELSE 3 
                END '.$direction
            );
        } else {
            $productosQuery->orderBy($sort, $direction);
        }

        $productos = $productosQuery
            ->orderBy('id_producto')
            ->paginate($perPage)
            ->withQueryString();

        return view('shared.livewire-data', [
            'productos' => $productos,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'canManageProducts' => $user->esAdmin() || $user->esAlmacenero(),
            'categorias' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
            'marcas' => Marca::query()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function edit(Producto $producto): View
    {
        $this->authorizeManage();
        abort_unless($producto->activo, 404);

        return view('shared.livewire-data', [
            'producto' => $producto,
            'categorias' => Categoria::query()->where('activo', true)->orWhere('id_categoria', $producto->id_categoria)->orderBy('nombre')->get(),
            'marcas' => Marca::query()->where('activo', true)->orWhere('id_marca', $producto->id_marca)->orderBy('nombre')->get(),
        ]);
    }

    public function image(Producto $producto): BinaryFileResponse
    {
        abort_unless($producto->imagen, 404);

        $disk = Storage::disk('public');
        if ($disk->exists($producto->imagen)) {
            return response()->file($disk->path($producto->imagen), ['Cache-Control' => 'private, no-store']);
        }

        // Algunas fotos antiguas solo existen en la copia pública de XAMPP.
        abort_unless(preg_match('~^productos/[\w.-]+\.(webp|png|jpe?g)$~i', $producto->imagen), 404);
        $path = public_path('storage/'.$producto->imagen);
        abort_unless(is_file($path), 404);

        return response()->file($path, ['Cache-Control' => 'private, no-store']);
    }

    public function create(): View
    {
        $this->authorizeManage();

        return view('shared.livewire-data', [
            'categorias' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
            'marcas' => Marca::query()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, MovimientoStockService $movimientoStockService): RedirectResponse
    {
        $this->authorizeManage();

        $validated = $request->validate([
            ...$this->validationRules(),
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        $archivo = $request->file('imagen');
        unset($validated['imagen']);
        $initialStock = (int) $validated['stock'];
        $validated['precio_venta_mayor'] ??= $validated['precio_venta'];
        $validated['cantidad_mayorista'] ??= 2;
        $validated['es_alquilable'] = $request->boolean('es_alquilable');
        $validated['precio_alquiler_dia'] ??= 0;
        $validated['activo'] = true;
        $validated['stock'] = 0;

        DB::transaction(function () use ($validated, $initialStock, $movimientoStockService, $archivo): void {
            $product = Producto::create($validated);

            if ($initialStock > 0) {
                $user = Auth::user();
                abort_unless($user instanceof Usuario, 401);

                $movimientoStockService->registrar(
                    $product,
                    $user,
                    'entrada',
                    $initialStock,
                    'Stock inicial por creación de producto'
                );
            }

            if ($archivo) {
                $this->guardarFoto($product, $archivo);
            }
        }, 3);

        return back()->with('success', 'Producto registrado correctamente en el inventario.');
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $this->authorizeManage();
        abort_unless($producto->activo, 404);

        $validated = $request->validate($this->validationRules($producto, true));

        $validated['precio_venta_mayor'] ??= $validated['precio_venta'];
        $validated['cantidad_mayorista'] ??= 2;
        $validated['es_alquilable'] = $request->boolean('es_alquilable');
        $validated['precio_alquiler_dia'] ??= 0;

        unset($validated['imagen']);
        DB::transaction(function () use ($producto, $validated, $request): void {
            $actual = Producto::query()->lockForUpdate()->findOrFail($producto->getKey());
            abort_unless($actual->activo, 404);
            $actual->update($validated);
            if ($request->hasFile('imagen')) {
                $this->guardarFoto($actual, $request->file('imagen'));
            }
        });

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->authorizeManage();

        if ($producto->stock > 0) {
            throw ValidationException::withMessages([
                'producto' => 'No se puede dar de baja un producto con existencias. Registra primero su salida o ajuste.',
            ]);
        }

        $producto->update([
            'activo' => false,
            'fecha_baja' => now(),
            'id_usuario_baja' => auth()->id(),
        ]);

        return back()->with('success', 'Producto dado de baja correctamente.');
    }

    private function authorizeView(): Usuario
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof Usuario && ($user->esAdmin() || $user->esAlmacenero() || $user->esVendedor()),
            403
        );

        return $user;
    }

    private function authorizeManage(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof Usuario && ($user->esAdmin() || $user->esAlmacenero()), 403);
    }

    /**
     * @return array<string, array<int, string|Exists|Unique>>
     */
    private function validationRules(?Producto $producto = null, bool $updating = false): array
    {
        $rules = [
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tproductos', 'codigo')->ignore($producto?->id_producto, 'id_producto'),
            ],
            'id_categoria' => ['required', 'integer', Rule::exists('tcategorias', 'id_categoria')->where(fn ($query) => $query->where(fn ($validas) => $validas
                    ->where('activo', true)->orWhere('id_categoria', $producto?->id_categoria)))],
            'id_marca' => ['required', 'integer', Rule::exists('tmarcas', 'id_marca')->where(fn ($query) => $query->where(fn ($validas) => $validas
                    ->where('activo', true)->orWhere('id_marca', $producto?->id_marca)))],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'stock_minimo' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'precio_compra' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'precio_venta' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'precio_venta_mayor' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'cantidad_mayorista' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'es_alquilable' => ['nullable', 'boolean'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'precio_alquiler_dia' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ];

        if ($updating) {
            $rules['stock'] = ['prohibited'];
        }

        return $rules;
    }

    /**
     * Convierte una imagen subida a formato WebP y la almacena en el disco público.
     */
    private function guardarFoto(Producto $producto, UploadedFile $archivo): void
    {
        if (! function_exists('imagecreatefromjpeg') || ! function_exists('imagewebp')) {
            throw new \Exception('La extensión GD de PHP no está habilitada. No se puede procesar imágenes.');
        }

        $imagen = imagecreatefromstring($archivo->get());
        if (! $imagen) {
            throw ValidationException::withMessages(['imagen' => 'No se pudo leer la imagen seleccionada.']);
        }

        imagepalettetotruecolor($imagen);
        $nombre = rawurlencode(Str::lower($producto->codigo));
        if (preg_match('/^(con|prn|aux|nul|com[1-9]|lpt[1-9])(\.|$)/i', $nombre)) {
            $nombre = '%'.bin2hex($nombre[0]).substr($nombre, 1);
        }
        $ruta = 'productos/'.$nombre.'.webp';
        $anterior = $producto->imagen;
        $disk = Storage::disk('public');
        if (Producto::where('imagen', $ruta)->whereKeyNot($producto->getKey())->exists()) {
            throw ValidationException::withMessages(['codigo' => 'La foto de otro producto ya utiliza este código.']);
        }
        $temporal = tmpfile();
        try {
            if (! imagewebp($imagen, $temporal, 85) || ftell($temporal) === 0) {
                throw new \RuntimeException('No se pudo convertir la imagen a WebP.');
            }
            rewind($temporal);
            $producto->update(['imagen' => $ruta]);
            File::ensureDirectoryExists($disk->path('productos'));
            File::replace($disk->path($ruta), stream_get_contents($temporal));
        } finally {
            fclose($temporal);
        }

        if ($anterior && $anterior !== $ruta) {
            DB::afterCommit(function () use ($disk, $anterior): void {
                if (! Producto::where('imagen', $anterior)->exists()) {
                    $disk->delete($anterior);
                }
            });
        }
    }
}
