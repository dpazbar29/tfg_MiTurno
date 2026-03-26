<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'reserva_id',
        'usuario_id',
        'empleado_id',
        'tipo',
        'enviado',
    ];

    protected $casts = [
        'enviado' => 'boolean',
    ];

    // Relaciones
    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
