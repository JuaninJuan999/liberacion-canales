<?php

namespace App\Models;

use Carbon\CarbonInterface;
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
        'observacion',
        'accion_correctiva',
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

    /**
     * Operario asignado al puesto Desinfección en "Gestión de operarios" para la fecha dada.
     */
    public static function operarioDesinfeccionParaFecha(CarbonInterface|string $fecha): ?string
    {
        $fechaYmd = $fecha instanceof CarbonInterface
            ? $fecha->format('Y-m-d')
            : $fecha;

        $puesto = PuestoTrabajo::query()
            ->withoutGlobalScope('ordered')
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower('Desinfección', 'UTF-8')])
                    ->orWhereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower('Desinfeccion', 'UTF-8')]);
            })
            ->first();

        if (! $puesto) {
            return null;
        }

        $fila = OperarioPorDia::query()
            ->where('fecha_operacion', $fechaYmd)
            ->where('puesto_trabajo_id', $puesto->id)
            ->with('operario')
            ->first();

        if (! $fila?->operario) {
            return null;
        }

        $nombre = trim((string) $fila->operario->nombre);

        return $nombre === '' ? null : $nombre;
    }

    /**
     * Texto a mostrar: el guardado al momento de registrar; si estaba vacío, el operario
     * de Desinfección asignado a la fecha de ese registro (puede rellenarse luego en gestión).
     */
    public function responsablePuestoResuelto(): string
    {
        $guardado = trim((string) ($this->responsable_puesto_trabajo ?? ''));
        if ($guardado !== '') {
            return $guardado;
        }

        $r = self::operarioDesinfeccionParaFecha($this->created_at);

        return $r ?? '—';
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
