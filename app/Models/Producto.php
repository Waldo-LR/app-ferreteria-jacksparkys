<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'tproductos';

    protected $primaryKey = 'id_producto';

    protected $fillable = ['codigo', 'id_categoria', 'id_marca', 'nombre', 'descripcion', 'stock', 'stock_minimo', 'precio_compra', 'precio_venta', 'precio_venta_mayor', 'cantidad_mayorista', 'es_alquilable', 'precio_alquiler_dia', 'fecha_vencimiento', 'imagen', 'activo', 'fecha_baja'];

    protected $casts = [
        'fecha_baja' => 'datetime',
        'fecha_vencimiento' => 'datetime',
    ];

    public function imagenUrl(): string
    {
        return route('productos.image', [$this, 'foto' => basename($this->imagen)]);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function usuarioBaja(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_baja', 'id_usuario');
    }
}
