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

    // Un empleado es un Usuario (el empleado es un usuario con rol de empleado)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Un empleado puede tener varios horarios
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'empleado_id');
    }

    // Un empleado puede tener varias reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'empleado_id');
    }

    // Un empleado puede tener varias Notificaciones
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'empleado_id');
    }

    // Un empleado puede pertenecer a varios Servicios
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'empleado_servicio', 'empleado_id', 'servicio_id');
    }
}
