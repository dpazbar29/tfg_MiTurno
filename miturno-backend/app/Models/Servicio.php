<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion_minutos',
        'precio',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'decimal:2',
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'servicio_id');
    }

    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'empleado_servicio', 'servicio_id', 'empleado_id');
    }
}
