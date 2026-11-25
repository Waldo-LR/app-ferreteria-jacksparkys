@if($producto->imagen)
    <button type="button" class="cursor-zoom-in" aria-haspopup="dialog" aria-label="Ampliar imagen de {{ $producto->nombre }}" data-nombre="{{ $producto->nombre }}"
        onclick="const vista = document.getElementById('vistaFotoCatalogo'); vista.querySelector('img').src = this.querySelector('img').src; vista.querySelector('h3').textContent = this.dataset.nombre; vista.showModal()">
        <img src="{{ $producto->imagenUrl() }}" alt="Imagen de {{ $producto->nombre }}" class="mx-auto h-10 w-10 rounded-xl border border-slate-600 bg-slate-900 object-cover">
    </button>
@else
    <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl border border-slate-600 bg-slate-900 text-lg text-amber-400" aria-label="Sin imagen disponible"><i class="fa-solid fa-box-open" aria-hidden="true"></i></span>
@endif
