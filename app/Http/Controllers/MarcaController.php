<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        return view('shared.livewire-data', [
            'marcas' => Marca::query()->withExists('productos')->orderByDesc('activo')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(["nombre" => "required|string|max:100"]);
        Marca::create(["nombre" => $request->nombre, "activo" => 1]);
        return redirect()->route("marcas.index")->with("success", "Marca agregada con éxito.");
    }

    public function update(Request $request, Marca $marca)
    {
        abort_unless($marca->activo, 404);
        $marca->update($request->validate(['nombre' => 'required|string|max:100']));

        return redirect()->route('marcas.index')->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy(Marca $marca)
    {
        $marca->update(['activo' => false]);
        return redirect()->route('marcas.index')->with('success', 'Marca inhabilitada.');
    }

    public function reactivar(Marca $marca)
    {
        $marca->update(['activo' => true]);
        return redirect()->route('marcas.index')->with('success', 'Marca reactivada.');
    }

    public function eliminar(Marca $marca)
    {
        if (! $marca->eliminarSiEstaLibre()) {
            return back()->with('error', 'Hay productos asociados. Puedes inhabilitar este registro.');
        }

        return redirect()->route('marcas.index')->with('success', 'Marca eliminada definitivamente.');
    }
}
