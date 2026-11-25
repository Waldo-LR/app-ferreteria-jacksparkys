<?php

namespace App\Models;

use App\Models\Concerns\GestionaEstadoCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use GestionaEstadoCatalogo, HasFactory;

    protected $table = 'tcategorias';
    protected $primaryKey = 'id_categoria';
    protected $fillable = ['nombre', 'activo', 'fecha_baja'];
    public function productos() { return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria'); }
}
