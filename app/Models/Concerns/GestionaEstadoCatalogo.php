<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

trait GestionaEstadoCatalogo
{
    protected static function bootGestionaEstadoCatalogo(): void
    {
        static::saving(function ($registro): void {
            if ($registro->isDirty('activo')) {
                $registro->fecha_baja = $registro->activo ? null : now();
            }
        });
    }

    public function eliminarSiEstaLibre(): bool
    {
        return DB::transaction(function (): bool {
            $registro = static::query()->lockForUpdate()->findOrFail($this->getKey());
            if ($registro->productos()->exists()) {
                return false;
            }

            return $registro->delete();
        }, 3);
    }
}
