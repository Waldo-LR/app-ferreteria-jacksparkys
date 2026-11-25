<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogModuleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_pages_require_authentication(): void
    {
        $this->withoutVite();

        $this->get(route('marcas.index'))->assertRedirect(route('login'));
        $this->get(route('categorias.index'))->assertRedirect(route('login'));
        $this->get(route('proveedores.index'))->assertRedirect(route('login'));
        $this->get(route('ventas.pos'))->assertRedirect(route('login'));
        $this->get(route('ventas.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_brand_and_category_catalogs(): void
    {
        $this->withoutVite();

        $administrator = new Usuario;
        $administrator->setAttribute('id_usuario', 1);
        $administrator->setAttribute('id_rol', 1);
        $administrator->setAttribute('nombre', 'Administradora de prueba');
        $administrator->setRelation('rol', new Rol(['nombre' => 'Administrador']));

        $this->actingAs($administrator);
        $categoria = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $marca = Marca::create(['nombre' => 'Industrial', 'activo' => true]);
        $producto = Producto::create([
            'codigo' => 'CAT-001', 'nombre' => 'Martillo', 'id_categoria' => $categoria->getKey(),
            'id_marca' => $marca->getKey(), 'stock' => 0, 'stock_minimo' => 0,
            'precio_compra' => 10, 'precio_venta' => 15, 'activo' => false,
        ]);
        foreach (['categorias' => $categoria, 'marcas' => $marca] as $module => $asociada) {
            $this->get(route($module.'.index'))->assertDontSee(route($module.'.eliminar', $asociada));
            $this->delete(route($module.'.eliminar', $asociada))->assertSessionHas('error');
            $this->deleteJson(route('api.'.$module.'.destroy', $asociada))->assertConflict();
            $this->delete(route($module.'.destroy', $asociada))->assertRedirect();
            $this->assertNotNull($asociada->refresh()->fecha_baja);
            $fields = $producto->only(['nombre', 'codigo', 'precio_compra', 'precio_venta', 'stock_minimo', 'id_categoria', 'id_marca']);
            $this->post(route('productos.store'), [...$fields, 'codigo' => 'OTRO', 'stock' => 0])
                ->assertSessionHasErrors($asociada->getKeyName());
            $producto->update(['activo' => true]);
            $this->put(route('productos.update', $producto), $fields)->assertSessionHasNoErrors();
            $producto->update(['activo' => false]);
            $this->assertSame($asociada->getKey(), $producto->refresh()->getAttribute($asociada->getKeyName()));
            $this->patch(route($module.'.reactivar', $asociada))->assertRedirect();
            $this->assertNull($asociada->refresh()->fecha_baja);
            $this->assertTrue((bool) $asociada->activo);
        }
        foreach (['marcas' => Marca::class, 'categorias' => Categoria::class] as $module => $model) {
            $record = $model::create(['nombre' => 'Original', 'activo' => true]);
            $this->get(route($module.'.index'))->assertOk()
                ->assertSee(route($module.'.edit', $record))
                ->assertSee('aria-label="Eliminar Original"', false);
            $this->get(route($module.'.edit', $record))->assertOk()
                ->assertSee('value="Original"', false)
                ->assertSee(route($module.'.update', $record));
            $this->put(route($module.'.update', $record), ['nombre' => 'Actualizado'])
                ->assertRedirect(route($module.'.index'));
            $this->assertSame('Actualizado', $record->refresh()->nombre);
            $this->delete(route($module.'.destroy', $record))->assertRedirect(route($module.'.index'));
            $this->assertFalse((bool) $record->refresh()->activo);
            $this->delete(route($module.'.eliminar', $record))->assertRedirect();
            $this->assertModelMissing($record);
        }
    }
}
