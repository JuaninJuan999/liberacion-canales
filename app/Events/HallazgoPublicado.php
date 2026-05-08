<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Canal público; solo mensajes genéricos (sin datos sensibles).
 * Quien registra no recibe notificación desde el mismo evento aquí —
 * pueden abrir pestañas múltiples y ver el aviso.
 */
class HallazgoPublicado implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array{origen: string, usuario_registro_id?: int|null, tipo_nombre?: string|null, usuario_nombre?: string|null, codigo?: string|null, producto_nombre?: string|null, lado_nombre?: string|null, media_etiqueta?: string|null, registro_id: int|string, registrado_en: string}  $payload
     */
    public function __construct(
        public array $payload,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('hallazgos')];
    }

    public function broadcastAs(): string
    {
        return 'registrado';
    }

    /**
     * @return array{origen: string, usuario_registro_id?: int|null, tipo_nombre?: string|null, usuario_nombre?: string|null, codigo?: string|null, producto_nombre?: string|null, lado_nombre?: string|null, media_etiqueta?: string|null, registro_id: int|string, registrado_en: string}
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
