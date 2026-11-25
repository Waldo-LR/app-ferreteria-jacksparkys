<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        return view('shared.livewire-data', [
            'categorias' => Categoria::query()->withExists('productos')->orderByDesc('activo')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(["nombre" => "required|string|max:100"]);
        Categoria::create(["nombre" => $request->nombre, "activo" => 1]);
        return redirect()->route("categorias.index")->with("success", "Categoría agregada con éxito.");
    }

    public function update(Request $request, Categoria $categoria)
    {
        abort_unless($categoria->activo, 404);
        $categoria->update($request->validate(['nombre' => 'required|string|max:100']));

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->update(['activo' => false]);
        return redirect()->route('categorias.index')->with('success', 'Categoría inhabilitada.');
    }

    public function reactivar(Categoria $categoria)
    {
        $categoria->update(['activo' => true]);
        return redirect()->route('categorias.index')->with('success', 'Categoría reactivada.');
    }

    public function eliminar(Categoria $categoria)
    {
        if (! $categoria->eliminarSiEstaLibre()) {
            return back()->with('error', 'Hay productos asociados. Puedes inhabilitar este registro.');
        }

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada definitivamente.');
    }
}
