<?php

use App\Services\LoginWeb;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    public string $usuario = '';
    public string $password = '';

    #[Locked]
    public int $segundosRestantes = 0;

    public function mount(LoginWeb $login): void
    {
        $bloqueado = (string) session('login.usuario_bloqueado', '');
        $this->usuario = (string) old('usuario', '');
        if ($bloqueado !== '' && $login->segundosDeEspera(request(), $bloqueado) > 0) {
            $this->usuario = $bloqueado;
            $this->actualizarBloqueo($login);
        }
    }

    public function actualizarBloqueo(LoginWeb $login): void
    {
        $this->segundosRestantes = $login->segundosDeEspera(request(), $this->usuario);
        if ($this->segundosRestantes === 0) {
            $this->resetValidation('usuario');
        }
    }

    public function ingresar(LoginWeb $login)
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        try {
            $login->autenticar(request(), ['usuario' => $this->usuario, 'password' => $this->password]);
        } finally {
            $this->reset('password');
            $this->segundosRestantes = $login->segundosDeEspera(request(), $this->usuario);
        }

        return redirect()->intended(route('dashboard'));
    }
};
?>
<div>
                @if(session('success'))
                    <div class="mb-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($segundosRestantes > 0)
                    <div wire:key="bloqueo-login" wire:poll.1s="actualizarBloqueo" class="mb-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
                        <i class="fa-solid fa-clock mt-0.5" aria-hidden="true"></i>
                        <span>Demasiados intentos. Podrás ingresar de nuevo en <strong role="timer" class="tabular-nums">{{ gmdate('i:s', $segundosRestantes) }}</strong>.</span>
                    </div>
                @elseif($errors->any())
                    <div class="mb-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" wire:submit="ingresar" class="space-y-3">
                    @csrf
                    <fieldset @disabled($segundosRestantes > 0) class="space-y-3">
                    <div>
                        <label for="usuario" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Usuario o Correo Electrónico
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <input type="text" id="usuario" name="usuario" wire:model="usuario" value="{{ $usuario }}" autocomplete="username" required autofocus placeholder="Ej: admin o usuario@jacksparkys.com" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-3 pl-4 pr-11 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Contraseña
                            </label>
                            <button type="button" aria-haspopup="dialog" aria-controls="modalOlvide" onclick="document.getElementById('modalOlvide').showModal()" class="text-xs font-medium text-amber-600 hover:text-amber-700 hover:underline">
                                ¿Olvidé mi contraseña?
                            </button>
                        </div>
                        <div class="relative rounded-xl shadow-sm">
                            <input type="password" id="password" name="password" wire:model="password" autocomplete="current-password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-3 pl-4 pr-11 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            <button type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition focus:outline-none" title="Mostrar / Ocultar">
                                <i id="togglePasswordIcon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="submit" @disabled($segundosRestantes > 0) class="login-submit-button w-full py-3 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl shadow-lg shadow-amber-500/25 focus:outline-none focus:ring-2 focus:ring-amber-500 transition duration-150 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid {{ $segundosRestantes > 0 ? 'fa-lock' : 'fa-arrow-right-to-bracket' }}" aria-hidden="true"></i>
                            <span>{{ $segundosRestantes > 0 ? 'Ingreso bloqueado' : 'Ingresar al Sistema' }}</span>
                        </button>
                    </div>
                    </fieldset>
                </form>
</div>
