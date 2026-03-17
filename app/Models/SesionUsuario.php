<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesionUsuario extends Model
{
    protected $table = 'sesiones_usuario';

    protected $fillable = [
        'user_id',
        'login_at',
        'ultima_actividad',
        'logout_at',
        'duracion_minutos',
        'ip_address',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'ultima_actividad' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
