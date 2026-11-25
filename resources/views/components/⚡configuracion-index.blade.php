<?php

use App\Http\Controllers\ConfiguracionController as PageController;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        $view = app()->call([app(PageController::class), 'index']);

        return $view->getData();
    }
};
?>
<div>
<div class="mx-auto w-full max-w-5xl space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-100">Configuración de Empresa</h1>
        <p class="text-xs text-slate-400">Actualiza los datos comerciales y parámetros utilizados por el sistema.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300" role="alert">
            <p class="font-bold">No se guardaron los cambios. Revisa los siguientes campos:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @livewire('configuracion-form')
</div>

</div>
