<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificacionPccRegistro extends Model
{
    protected $table = 'verificacion_pcc_registros';

    protected $fillable = [
        'user_id',
        'external_ins_id',
        'id_producto',
        'snapshot_externo',
        'cumple_media_canal_1',
        'cumple_media_canal_2',
        'responsable_puesto_trabajo',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_externo' => 'array',
            'cumple_media_canal_1' => 'boolean',
            'cumple_media_canal_2' => 'boolean',
            'id_producto' => 'string',
        ];
    }

    /** Código tal cual viene de la BD externa (ej. 2604-00666). */
    public function codigoProductoCompleto(): string
    {
        $desdeSnapshot = data_get($this->snapshot_externo, 'id_producto');

        $raw = $desdeSnapshot !== null && $desdeSnapshot !== ''
            ? $desdeSnapshot
            : $this->id_producto;

        return trim((string) $raw);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
