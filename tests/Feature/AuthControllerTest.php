<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads_without_external_assets(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('cdnjs.cloudflare.com', false)
            ->assertDontSee('images.unsplash.com', false);
    }

    public function test_login_with_email_rotates_session_and_logout_protects_the_panel_again(): void
    {
        $user = $this->cuenta();
        $this->get('/productos')->assertRedirect('/login');
        $id = session()->getId();
        $this->post('/login', ['usuario' => $user->email, 'password' => 'clave-prueba'])->assertRedirect('/productos');
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($id, session()->getId());
        $this->withoutVite();
        $panel = $this->get('/productos')->assertOk()->assertSee($user->nombre);
        $this->assertTrue($panel->headers->hasCacheControlDirective('no-store'));
        $this->get('/login')->assertRedirect('/dashboard');
        $this->post('/login')->assertRedirect('/dashboard');
        $token = session()->token();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->assertNotSame($token, session()->token());
        $this->get('/productos')->assertRedirect('/login');
        $this->assertDatabaseHas('tauditoria', ['accion' => 'LOGIN_EXITOSO', 'id_usuario' => $user->getKey()]);
        $this->assertDatabaseHas('tauditoria', ['accion' => 'LOGOUT', 'id_usuario' => $user->getKey()]);
    }

    public function test_livewire_and_post_share_the_five_failure_limit(): void
    {
        $user = $this->cuenta();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['usuario' => $user->usuario, 'password' => 'incorrecta'])->assertSessionHasErrors('usuario');
        }
        Livewire::test('login-form')->set('usuario', $user->usuario)->set('password', 'clave-prueba')
            ->call('ingresar')->assertHasErrors('usuario');
        $this->assertGuest();
        $this->travel(61)->seconds();
        Livewire::test('login-form')->set('usuario', $user->usuario)->set('password', 'clave-prueba')
            ->call('ingresar')->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_demo_requires_credentials_and_rejects_unknown_or_inactive_accounts(): void
    {
        $user = $this->cuenta();
        $this->get('/quick-login/admin')->assertRedirect('/login')->assertSessionHasInput('usuario', $user->usuario);
        $this->get('/quick-login/desconocido')->assertNotFound();
        $this->assertGuest();
        $user->update(['activo' => false]);
        $this->post('/login', ['usuario' => $user->email, 'password' => 'clave-prueba'])->assertSessionHasErrors('usuario');
        $this->post('/login', ['usuario' => 'correo@', 'password' => 'clave-prueba'])->assertSessionHasErrors('usuario');
        $this->assertGuest();
    }

    public function test_countdown_survives_reload_and_unlocks_at_the_server_deadline(): void
    {
        $this->freezeTime();
        $user = $this->cuenta();
        $form = Livewire::test('login-form')->set('usuario', $user->usuario);
        for ($i = 0; $i < 5; $i++) {
            $form->set('password', 'incorrecta')->call('ingresar');
        }
        $form->assertSet('segundosRestantes', 60)->assertSee('01:00')->assertSee('Ingreso bloqueado');

        $this->travel(37)->seconds();
        $reloaded = Livewire::test('login-form')->assertSet('segundosRestantes', 23)->assertSee('00:23');
        $this->travel(22)->seconds();
        $reloaded->call('actualizarBloqueo')->assertSee('00:01');
        $this->travel(1)->second();
        $reloaded->call('actualizarBloqueo')->assertSet('segundosRestantes', 0)
            ->assertDontSee('Ingreso bloqueado')->assertSee('Ingresar al Sistema')
            ->set('password', 'clave-prueba')->call('ingresar')->assertRedirect(route('dashboard'));
    }

    public function test_all_business_web_routes_require_authentication(): void
    {
        foreach (Route::getRoutes() as $route) {
            if (in_array('web', $route->gatherMiddleware(), true)
                && $route->getName() && ! str_starts_with($route->getName(), 'livewire.')
                && ! in_array($route->getName(), ['login', 'login.post', 'quick.login', 'sanctum.csrf-cookie', 'default-livewire.update', 'storage.local', 'storage.local.upload'], true)) {
                $this->assertContains('auth', $route->gatherMiddleware(), $route->uri());
            }
        }
    }

    private function cuenta(): Usuario
    {
        $rol = Rol::create(['nombre' => 'Administrador', 'activo' => true]);
        $user = Usuario::create([
            'nombre' => 'Personal de prueba', 'usuario' => 'personal', 'email' => 'personal@example.test',
            'password' => 'clave-prueba', 'id_rol' => $rol->getKey(), 'activo' => true,
        ]);
        $this->assertTrue(Hash::check('clave-prueba', $user->password));

        return $user;
    }
}
