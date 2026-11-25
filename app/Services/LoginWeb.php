<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginWeb
{
    public function segundosDeEspera(Request $request, string $usuario): int
    {
        $clave = $this->clave($request, $usuario);

        return RateLimiter::tooManyAttempts($clave, 5) ? RateLimiter::availableIn($clave) : 0;
    }

    private function clave(Request $request, string $usuario): string
    {
        return 'login-web:'.hash('sha256', Str::lower(trim($usuario)).'|'.$request->ip());
    }

    private function verificarBloqueo(Request $request, string $usuario): void
    {
        if ($this->segundosDeEspera($request, $usuario) > 0) {
            session()->put('login.usuario_bloqueado', $usuario);
            throw ValidationException::withMessages(['usuario' => 'Demasiados intentos de ingreso.']);
        }
    }

    public function autenticar(Request $request, array $entrada): void
    {
        $datos = Validator::make($entrada, [
            'usuario' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ])->validate();
        $identificador = trim($datos['usuario']);
        if (str_contains($identificador, '@')) {
            Validator::make(['usuario' => $identificador], ['usuario' => 'email'], [
                'usuario.email' => 'Ingresa un correo electrónico válido.',
            ])->validate();
        }
        $clave = $this->clave($request, $identificador);
        $this->verificarBloqueo($request, $identificador);

        $credenciales = [
            'activo' => true,
            'password' => $datos['password'],
            fn ($query) => $query->where(fn ($q) => $q->where('usuario', $identificador)->orWhere('email', $identificador)),
        ];
        if (! Auth::guard('web')->attempt($credenciales)) {
            RateLimiter::hit($clave, 60);
            $this->verificarBloqueo($request, $identificador);
            throw ValidationException::withMessages([
                'usuario' => 'Usuario, correo o contraseña incorrectos, o cuenta inactiva.',
            ]);
        }

        RateLimiter::clear($clave);
        session()->forget('login.usuario_bloqueado');
        session()->regenerate();
        $user = Auth::guard('web')->user();
        $user->update(['ultimo_login' => now()]);
        DB::table('tauditoria')->insert([
            'id_usuario' => $user->getKey(), 'accion' => 'LOGIN_EXITOSO', 'tabla_afectada' => 'tusuarios',
            'id_registro' => $user->getKey(), 'detalle' => 'Inicio de sesión con rol '.($user->rol->nombre ?? 'Usuario'),
            'ip' => $request->ip(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        session()->flash('success', '¡Bienvenido(a), '.$user->nombre.'! Has ingresado como '.($user->rol->nombre ?? 'Personal'));
    }
}
