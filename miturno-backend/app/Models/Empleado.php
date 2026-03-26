<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'usuario_id',
        'especialidades',
        'fecha_contratacion',
        'activo',
    ];

    protected $casts = [
        'fecha_contratacion' => 'date',
        'activo' => 'boolean',
    ];

    // Relación 1-1 con Usuario (el empleado es un usuario con rol de empleado)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación 1-N con Horarios
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'empleado_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'empleado_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'empleado_id');
    }
}
