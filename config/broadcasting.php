<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "reverb", "pusher", "ably", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_CONNECTION', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over WebSockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST'),
                'port' => env('REVERB_PORT', 443),
                'scheme' => env('REVERB_SCHEME', 'https'),
                'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'encrypted' => true,
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Notificaciones en vivo (hallazgos)
    |--------------------------------------------------------------------------
    |
    | En el cliente web, quien registra el hallazgo no recibe modal ni sonido
    | (filtro por usuario_registro_id vs current-user-id).
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Host WebSocket (cliente Echo / Capacitor)
    |--------------------------------------------------------------------------
    |
    | Si está vacío, el navegador usa window.location.hostname. En Android con
    | Capacitor a veces conviene fijar la IP del PC donde corre Reverb, p. ej.:
    | ECHO_WS_HOST=192.168.20.11
    | (sin puerto: el puerto del WebSocket lo define REVERB_SERVER_PORT y va en meta reverb-client).
    |
    */
    'echo_ws_host' => (static function (): ?string {
        $raw = env('ECHO_WS_HOST');
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $raw)) {
            $host = parse_url($raw, PHP_URL_HOST);

            return $host !== false && $host !== null && $host !== ''
                ? (string) $host
                : null;
        }

        /** @var string $soloHost IPv4/host con ":puertoAPP" equivocado quitado (:8006 no es WebSocket Reverb). */
        $soloHost = preg_replace('#:\d+$#', '', $raw);

        return $soloHost !== '' ? $soloHost : null;
    })(),

    /*
    | Log de conexión Echo en consola aunque APP_DEBUG esté desactivado (móvil).
    */
    'hallazgo_echo_debug' => env('HALLAZGO_ECHO_DEBUG', false),

];
