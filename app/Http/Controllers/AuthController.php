<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use Carbon\Carbon;
use App\Services\LoginWeb;

class AuthController extends Controller
{
    public function login(Request $request, LoginWeb $login)
    {
        $login->autenticar($request, $request->only('usuario', 'password'));

        return redirect()->intended(route('dashboard'));
    }

    public function quickLogin(string $role)
    {
        $roles = ['admin' => 1, 'vendedor' => 2, 'almacen' => 3, 'cobranzas' => 4, 'cajero' => 5];
        abort_unless(isset($roles[$role]), 404);
        $usuario = Usuario::where('id_rol', $roles[$role])->where('activo', true)->orderBy('id_usuario')->value('usuario');
        if ($usuario === null) {
            return redirect()->route('login')->withErrors(['usuario' => 'No hay una cuenta activa para ese perfil.']);
        }

        return redirect()->route('login')->withInput(['usuario' => $usuario]);
    }

    public function cambiarPerfil(Request $request, string $role)
    {
        $this->logout($request);

        return $this->quickLogin($role);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            DB::table('tauditoria')->insert([
                'id_usuario' => Auth::id(),
                'accion' => 'LOGOUT',
                'tabla_afectada' => 'tusuarios',
                'id_registro' => Auth::id(),
                'detalle' => 'Cierre de sesión del sistema',
                'ip' => $request->ip(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Has cerrado sesión correctamente.');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:150',
            'sobre_mi' => 'nullable|string',
            'password' => 'nullable|string|min:6|confirmed'
        ]);

        $data = [
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'sobre_mi' => $request->sobre_mi,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        // Registro de auditoría
        DB::table('tauditoria')->insert([
            'id_usuario' => $user->id_usuario,
            'accion' => 'PERFIL_ACTUALIZADO',
            'tabla_afectada' => 'tusuarios',
            'id_registro' => $user->id_usuario,
            'detalle' => 'Actualización de datos personales / contraseña',
            'ip' => $request->ip(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return back()->with('success', 'Tu perfil ha sido actualizado con éxito.');
    }
}
