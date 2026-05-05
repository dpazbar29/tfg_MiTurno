<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected $fillable = [
        'usuario_id',
        'empleado_id',
        'servicio_id',
        'fecha_hora_inicio',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_hora_inicio' => 'datetime',
    ];

    // Una reserva pertenece a un Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Una reserva pertenece a un Empleado
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    // Una reserva pertenece a un serivicio
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    // Una reserva puede tener varias notificaciones
    public function notificaciones()
    {
        return $this->hasMany(Notificaciones::class, 'reserva_id');
    }
}
