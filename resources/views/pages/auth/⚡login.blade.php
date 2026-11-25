<?php

use Livewire\Component;
use App\Models\Configuracion;

new class extends Component
{
    public function render()
    {
        $contacto = Configuracion::find(1, ['email_soporte', 'central_soporte']);

        return $this->view()->with([
            'emailSoporte' => $contacto?->email_soporte ?? Configuracion::EMAIL_SOPORTE,
            'centralSoporte' => $contacto?->central_soporte ?? Configuracion::CENTRAL_SOPORTE,
        ])->layout('layouts.auth');
    }
};
?>
<div>
<div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-100">
            <div class="px-5 py-4 text-center border-b border-slate-100 bg-gradient-to-b from-amber-50 to-white">
                <div class="login-brand-icon inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-500 text-slate-950 text-2xl font-extrabold shadow-lg shadow-amber-500/30 mb-2">
                    <i class="fa-solid fa-hammer"></i>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">
                    Ferretería <span class="text-amber-600">Jack Sparky's</span>
                </h1>
                <p class="text-xs font-semibold text-amber-700 mt-1 italic tracking-wide">
                    "Tu mejor aliado, en tus proyectos"
                </p>
                <div class="mt-2 inline-block px-3 py-0.5 bg-slate-100 rounded-full text-[11px] font-medium text-slate-500">
                    :: Quillabamba - Perú ::
                </div>
            </div>

            <div class="px-5 sm:px-7 py-4">
                @livewire('login-form')
                <!-- Selector de Perfiles Demo -->
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-500 text-center uppercase tracking-wider mb-2.5">
                        <i class="fa-solid fa-key text-amber-500 mr-1"></i> Acceso Rápido por Perfil de Usuario
                    </p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <a href="{{ route('quick.login', 'admin') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-semibold text-center transition border border-slate-200">
                            👑 Administrador
                        </a>
                        <a href="{{ route('quick.login', 'vendedor') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-semibold text-center transition border border-slate-200">
                            🛒 Vendedor
                        </a>
                        <a href="{{ route('quick.login', 'almacen') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-semibold text-center transition border border-slate-200">
                            📦 Almacenero
                        </a>
                        <a href="{{ route('quick.login', 'cobranzas') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-semibold text-center transition border border-slate-200">
                            📋 Cobranzas
                        </a>
                        <a href="{{ route('quick.login', 'cajero') }}" class="col-span-2 justify-self-center w-[calc(50%_-_0.25rem)] p-2 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-semibold text-center transition border border-slate-200">
                            🧾 Cajero
                        </a>
                    </div>
                </div>
            </div>

            <div class="py-2 px-5 bg-slate-50 border-t border-slate-100 text-center text-[11px] text-slate-400">
                Ferretería Jack Sparkys S.A.C. &copy; {{ date('Y') }} — RUC 20459862922
            </div>
        </div>
    </div>

    <!-- Modal ¿Olvidé mi contraseña? -->
    <dialog id="modalOlvide" aria-labelledby="tituloRecuperacion" class="fixed inset-0 m-auto max-w-sm w-[calc(100%-2rem)] rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-2xl backdrop:bg-slate-950/60 backdrop:backdrop-blur-sm">
            <form method="dialog" class="absolute right-3 top-3">
                <button type="submit" autofocus title="Cerrar" aria-label="Cerrar recuperación de contraseña" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#b91c1c] text-[#fff] hover:bg-[#991b1b]">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </form>
            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xl mx-auto mb-3">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 id="tituloRecuperacion" class="text-lg font-bold text-slate-900">Recuperación de Contraseña</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Por políticas de seguridad, para restablecer tu clave de acceso solicita asistencia al Administrador del Sistema:
            </p>
            <div class="my-4 p-3 bg-slate-50 rounded-xl text-xs text-slate-700 text-left space-y-1 border border-slate-200">
                <div><i class="fa-solid fa-envelope text-amber-600 mr-1.5"></i> <strong>Soporte:</strong> {{ $emailSoporte }}</div>
                <div><i class="fa-solid fa-phone text-amber-600 mr-1.5"></i> <strong>Central:</strong> {{ $centralSoporte }}</div>
            </div>
            <button type="button" onclick="this.closest('dialog').close()" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition">
                Entendido, regresar
            </button>
    </dialog>

    <script>
        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</div>
