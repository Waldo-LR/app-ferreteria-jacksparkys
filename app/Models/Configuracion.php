<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    public const EMAIL_SOPORTE = 'soporte@jacksparkys.com';
    public const CENTRAL_SOPORTE = '(01) 456-7890 anexo 102';

    protected $table = 'tconfiguracion';
    protected $primaryKey = 'id_configuracion';
    protected $fillable = ['razon_social', 'ruc', 'direccion', 'telefono', 'email', 'slogan', 'email_soporte', 'central_soporte', 'igv_porcentaje', 'mora_porcentaje', 'dias_recuperacion', 'dias_alerta_vencimiento', 'cierre_automatico'];

}
