<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_seller_can_search_sort_and_paginate_products(): void
    {
        $seller = $this->createUser('Vendedor', 2);
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca Activa', 'activo' => true]);

        for ($number = 1; $number <= 12; $number++) {
            $this->createProduct(
                'CAT-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'Producto '.$number,
                $number,
                $category,
                $brand
            );
        }

        $this->actingAs($seller)
            ->get(route('productos.index', [
                'q' => 'Marca Activa',
                'per_page' => 10,
                'sort' => 'stock',
                'direction' => 'desc',
            ]))
            ->assertOk()
            ->assertSee('Mostrando')
            ->assertSee('10')
            ->assertSee('de')
            ->assertSee('12')
            ->assertSee('Marca Activa')
            ->assertSee('CAT-012')
            ->assertSee(route('ventas.pos'))
            ->assertDontSee(route('productos.create'));
    }

    public function test_catalog_search_and_sort_update_reactively(): void
    {
        $seller = $this->createUser('Vendedor', 2);
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca Activa', 'activo' => true]);
        $this->createProduct('TAL-001', 'Taladro ligero', 1, $category, $brand);
        $this->createProduct('TAL-002', 'Taladro industrial', 9, $category, $brand);
        $this->createProduct('MAR-001', 'Martillo', 3, $category, $brand);

        Livewire::actingAs($seller)
            ->test('productos-index')
            ->set('q', 'Taladro')
            ->assertSeeInOrder(['Taladro industrial', 'Taladro ligero'])
            ->assertDontSee('Martillo')
            ->call('ordenarPor', 'stock')
            ->assertSeeInOrder(['Taladro ligero', 'Taladro industrial'])
            ->call('ordenarPor', 'stock')
            ->assertSeeInOrder(['Taladro industrial', 'Taladro ligero']);
    }

    public function test_catalog_escapes_product_content_and_shows_stock_status(): void
    {
        $administrator = $this->createUser('Administrador', 1);
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);
        $this->createProduct(
            'CAT-XSS',
            '<script>alert(1)</script>',
            0,
            $category,
            $brand
        );

        $this->actingAs($administrator)
            ->get(route('productos.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Agotado')
            ->assertSee(route('compras.index'), false)
            ->assertSeeInOrder(['Registrar Producto', 'Registrar Compra', 'Registrar Venta']);
    }

    public function test_catalog_shows_edit_and_deactivate_actions_for_every_product(): void
    {
        $administrator = $this->createUser('Administrador', 1);
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);
        $stockedProduct = $this->createProduct('CAT-STOCKED', 'Taladro con stock', 5, $category, $brand);
        $emptyProduct = $this->createProduct('CAT-EMPTY', 'Taladro sin stock', 0, $category, $brand);

        $this->actingAs($administrator)
            ->get(route('productos.index'))
            ->assertOk()
            ->assertSee('data-update-uri=', false)
            ->assertSee('id="product-search-input"', false)
            ->assertSee('class="w-full table-fixed text-left text-sm"', false)
            ->assertSee(route('productos.edit', $stockedProduct), false)
            ->assertSee(route('productos.edit', $emptyProduct), false)
            ->assertSee(route('productos.destroy', $stockedProduct->id_producto), false)
            ->assertSee(route('productos.destroy', $emptyProduct->id_producto), false)
            ->assertSee('aria-label="Inhabilitar o dar de baja Taladro con stock"', false)
            ->assertSee('aria-label="Inhabilitar o dar de baja Taladro sin stock"', false)
            ->assertDontSee('Ver inventario')
            ->assertDontSee('fa-eye', false);
    }

    public function test_admin_can_register_product_with_audited_initial_stock_from_livewire_form(): void
    {
        $administrator = $this->createUser('Administrador', 1);
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);

        Livewire::actingAs($administrator)
            ->test('productos-form')
            ->call('nuevo')
            ->assertSet('showModal', true)
            ->set('codigo', 'LW-NEW-001')
            ->set('nombre', 'Taladro Livewire')
            ->set('id_categoria', (string) $category->id_categoria)
            ->set('id_marca', (string) $brand->id_marca)
            ->set('stock', '8')
            ->set('stock_minimo', '2')
            ->set('precio_compra', '12.50')
            ->set('precio_venta', '19.90')
            ->call('guardar')
            ->assertSet('showModal', false)
            ->assertSet('successMessage', 'Producto registrado correctamente en el inventario.');

        $product = Producto::query()->where('codigo', 'LW-NEW-001')->firstOrFail();
        $this->assertSame(8, $product->stock);
        $this->assertDatabaseHas('tmovimientosstock', [
            'id_producto' => $product->id_producto,
            'id_usuario' => $administrator->id_usuario,
            'tipo' => 'entrada',
            'cantidad' => 8,
            'stock_resultante' => 8,
            'motivo' => 'Stock inicial por creación de producto',
        ]);
        $this->assertSame(1, MovimientoStock::query()->where('id_producto', $product->id_producto)->count());
    }

    public function test_seller_cannot_open_product_creation_form(): void
    {
        $seller = $this->createUser('Vendedor', 2);

        Livewire::actingAs($seller)
            ->test('productos-form')
            ->call('nuevo')
            ->assertForbidden();
    }

    public function test_catalog_can_sort_products_by_category(): void
    {
        $seller = $this->createUser('Vendedor', 2);
        $laterCategory = Categoria::create(['nombre' => 'Zeta', 'activo' => true]);
        $firstCategory = Categoria::create(['nombre' => 'Alfa', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);
        $this->createProduct('CAT-ZETA', 'Producto Zeta', 4, $laterCategory, $brand);
        $this->createProduct('CAT-ALFA', 'Producto Alfa', 4, $firstCategory, $brand);

        $this->actingAs($seller)
            ->get(route('productos.index', [
                'sort' => 'categoria',
                'direction' => 'asc',
            ]))
            ->assertOk()
            ->assertSeeInOrder(['Producto Alfa', 'Producto Zeta']);
    }

    public function test_guest_is_redirected_to_login_from_catalog(): void
    {
        $this->get(route('productos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_replacing_a_photo_twice_serves_the_latest_image_in_the_catalog(): void
    {
        $disk = Storage::fake('public');
        $this->actingAs($this->createUser('Administrador', 1));
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca', 'activo' => true]);
        $fields = [
            'codigo' => 'HER-001', 'nombre' => 'Martillo', 'id_categoria' => $category->getKey(),
            'id_marca' => $brand->getKey(), 'stock_minimo' => 2, 'precio_compra' => 10, 'precio_venta' => 15,
        ];
        $this->post(route('productos.store'), [
            ...$fields, 'stock' => 3, 'imagen' => UploadedFile::fake()->image('inicial.png'),
        ])->assertSessionHasNoErrors();
        $product = Producto::where('codigo', 'HER-001')->firstOrFail();
        $this->assertSame('productos/her-001.webp', $product->imagen);
        $disk->assertExists($product->imagen);

        foreach ([32, 64] as $width) {
            $this->post(route('productos.update', $product), [
                ...$fields,
                '_method' => 'PUT',
                'imagen' => UploadedFile::fake()->image('martillo.png', $width, 20),
            ])->assertSessionHasNoErrors()->assertRedirect(route('productos.index'));

            $product->refresh();
            $this->assertSame('productos/her-001.webp', $product->imagen);
            $response = $this->get($product->imagenUrl())->assertOk();
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertSame($width, getimagesize($response->baseResponse->getFile()->getPathname())[0]);
            $this->get(route('productos.index'))->assertOk()->assertSee($product->imagenUrl());
        }

        $latest = $product->imagen;
        $this->put(route('productos.update', $product), $fields)->assertSessionHasNoErrors();
        $this->assertSame($latest, $product->refresh()->imagen);
    }

    public function test_admin_can_update_product_details_without_changing_stock(): void
    {
        $administrator = $this->createUser('Administrador', 1);
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);
        $product = $this->createProduct('CAT-EDIT', 'Taladro original', 7, $category, $brand);

        $this->actingAs($administrator)
            ->get(route('productos.edit', $product))
            ->assertOk()
            ->assertSee('Editar producto')
            ->assertSee('El stock se modifica desde Control de Inventario.');

        $this->put(route('productos.update', $product), [
            'codigo' => $product->codigo,
            'nombre' => 'Taladro actualizado',
            'id_categoria' => $category->id_categoria,
            'id_marca' => $brand->id_marca,
            'stock_minimo' => 3,
            'precio_compra' => 12,
            'precio_venta' => 18,
            'precio_venta_mayor' => 16,
            'cantidad_mayorista' => 4,
            'es_alquilable' => '1',
            'precio_alquiler_dia' => 5,
            'descripcion' => 'Descripción actualizada',
        ])
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('success', 'Producto actualizado correctamente.');

        $product->refresh();
        $this->assertSame('Taladro actualizado', $product->nombre);
        $this->assertSame(7, $product->stock);
        $this->assertSame(3, $product->stock_minimo);
        $this->assertSame(18.0, (float) $product->precio_venta);
        $this->assertTrue((bool) $product->es_alquilable);
    }

    public function test_product_update_rejects_stock_changes(): void
    {
        $administrator = $this->createUser('Administrador', 1);
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);
        $product = $this->createProduct('CAT-STOCK', 'Producto existente', 7, $category, $brand);

        $this->actingAs($administrator)
            ->from(route('productos.edit', $product))
            ->put(route('productos.update', $product), [
                'codigo' => $product->codigo,
                'nombre' => 'Intento de cambio de stock',
                'id_categoria' => $category->id_categoria,
                'id_marca' => $brand->id_marca,
                'stock' => 99,
                'stock_minimo' => 2,
                'precio_compra' => 10,
                'precio_venta' => 15,
            ])
            ->assertRedirect(route('productos.edit', $product))
            ->assertSessionHasErrors('stock');

        $this->assertSame(7, $product->refresh()->stock);
        $this->assertSame('Producto existente', $product->nombre);
    }

    public function test_seller_cannot_open_product_editor_or_update_product(): void
    {
        $seller = $this->createUser('Vendedor', 2);
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);
        $product = $this->createProduct('CAT-ROLE', 'Producto protegido', 4, $category, $brand);

        $this->actingAs($seller)
            ->get(route('productos.edit', $product))
            ->assertForbidden();

        $this->put(route('productos.update', $product), [])
            ->assertForbidden();

        $this->assertSame('Producto protegido', $product->refresh()->nombre);
    }

    public function test_deactivated_products_show_their_photo_or_a_placeholder_in_history(): void
    {
        $administrator = $this->createUser('Administrador', 1);
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);
        $withPhoto = $this->createProduct('BAJ-001', 'Taladro', 0, $category, $brand);
        $withoutPhoto = $this->createProduct('BAJ-002', 'Llave', 0, $category, $brand);
        $withPhoto->update(['activo' => false, 'fecha_baja' => now(), 'imagen' => 'productos/taladro.webp']);
        $withoutPhoto->update(['activo' => false, 'fecha_baja' => now()]);

        $this->actingAs($administrator)->get(route('bajas.index'))
            ->assertOk()
            ->assertSee($withPhoto->imagenUrl())
            ->assertSee('Ampliar imagen de Taladro')
            ->assertSee('Cerrar previsualización')
            ->assertSee('Sin imagen disponible')
            ->assertDontSee('Ampliar imagen de Llave');
    }

    public static function invalidCatalogFilters(): array
    {
        return [
            'unapproved sort column' => [['sort' => 'nombre; drop table tproductos']],
            'unapproved sort direction' => [['direction' => 'desc; drop table tproductos']],
            'unsupported page size' => [['per_page' => '1000']],
        ];
    }

    #[DataProvider('invalidCatalogFilters')]
    public function test_catalog_rejects_unapproved_sort_and_page_size_values(array $filters): void
    {
        $administrator = $this->createUser('Administrador', 1);

        $this->actingAs($administrator)
            ->from(route('productos.index'))
            ->get(route('productos.index', $filters))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHasErrors(array_key_first($filters));
    }

    private function createUser(string $roleName, int $roleId): Usuario
    {
        $role = new Rol([
            'nombre' => $roleName,
            'activo' => true,
        ]);
        $role->id_rol = $roleId;
        $role->save();

        return Usuario::create([
            'id_rol' => $role->id_rol,
            'nombre' => $roleName.' de prueba',
            'usuario' => strtolower($roleName).'-catalogo',
            'email' => strtolower($roleName).'-catalogo@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
    }

    private function createProduct(
        string $code,
        string $name,
        int $stock,
        Categoria $category,
        Marca $brand
    ): Producto {
        return Producto::create([
            'codigo' => $code,
            'id_categoria' => $category->id_categoria,
            'id_marca' => $brand->id_marca,
            'nombre' => $name,
            'stock' => $stock,
            'stock_minimo' => 2,
            'precio_compra' => 10,
            'precio_venta' => 15,
            'precio_venta_mayor' => 14,
            'cantidad_mayorista' => 2,
            'es_alquilable' => false,
            'precio_alquiler_dia' => 0,
            'activo' => true,
        ]);
    }
}
