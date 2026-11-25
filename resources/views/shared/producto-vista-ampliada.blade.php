<dialog id="vistaFotoCatalogo" aria-labelledby="nombreFotoCatalogo" onclick="if (event.target === this) this.close()" class="m-auto max-w-[92vw] rounded-2xl border border-slate-700 bg-slate-800 p-4 shadow-2xl backdrop:bg-black/70">
    <form method="dialog" class="float-right ml-3">
        <button type="submit" autofocus aria-label="Cerrar previsualización" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#b91c1c] text-[#fff] hover:bg-[#991b1b]">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </form>
    <h3 id="nombreFotoCatalogo" class="mb-3 font-bold text-slate-100"></h3>
    <img alt="Vista ampliada del producto" class="clear-both mx-auto block max-h-[70vh] max-w-full rounded-xl bg-white object-contain">
</dialog>
