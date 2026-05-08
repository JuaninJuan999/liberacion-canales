import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { Capacitor } from '@capacitor/core';

let echoInicializado = false;

/** En web, un solo Audio reutilizable mejora la reproducción tras el primer gesto del usuario. */
let audioHallazgoWeb = null;
let audioWebDesbloqueado = false;

const ID_BANNER_SONIDO = 'hallazgo-banner-activar-audio';
const ID_MODAL_HALLAZGO = 'hallazgo-modal-alert';
const ESTILOS_MODAL_ID = 'hallazgo-modal-estilos';
/** Evita mostrar el banner en visitas posteriores una vez que el audio ya se pudo desbloquear (gesto o autoplay permitido). */
const LS_AUDIO_DESBLOQUEADO = 'hallazgo-audio-desbloqueado';

function marcarAudioDesbloqueadoPersistente() {
    try {
        localStorage.setItem(LS_AUDIO_DESBLOQUEADO, '1');
    } catch {
        /* modo privado / storage denegado */
    }
}

function yaDesbloqueoAudioAntes() {
    try {
        return localStorage.getItem(LS_AUDIO_DESBLOQUEADO) === '1';
    } catch {
        return false;
    }
}

function esAppNativa() {
    return Capacitor.isNativePlatform();
}

function debeLogEcho() {
    return document.querySelector('meta[name="echo-debug"]')?.content === '1' || esAppNativa();
}

/** Quitar :puerto por error (HTTP Laravel) o extraer host desde URL completa. Echo usa host + wsPort aparte. */
function normalizarHostEchoWebSocket(raw) {
    const s = typeof raw === 'string' ? raw.trim() : '';
    if (!s) {
        return '';
    }
    try {
        if (/^https?:\/\//i.test(s)) {
            return new URL(s).hostname || '';
        }
    } catch {
        /* continuar */
    }
    return s.replace(/:\d+$/, '');
}

function mediaEtiquetaDesdeLegado(payload) {
    const explicit = payload.media_etiqueta != null ? String(payload.media_etiqueta).trim() : '';
    if (explicit && explicit !== '—') {
        return explicit;
    }
    const p = payload.producto_nombre;
    if (typeof p !== 'string' || !p.trim()) {
        return '—';
    }
    const t = p.trim();
    if (/Media Canal\s*1/i.test(t)) {
        return 'Media Canal 1';
    }
    if (/Media Canal\s*2/i.test(t)) {
        return 'Media Canal 2';
    }
    return t;
}

/**
 * Valores del hallazgo para modal y notificación (compatible con payloads sin media_etiqueta/lado_nombre).
 * @returns {{ codigo: string, tipo: string, lado: string, ubicacion: string, usuario: string }}
 */
function extraerDetalleHallazgo(payload) {
    const cod =
        payload.codigo != null && String(payload.codigo).trim() !== ''
            ? String(payload.codigo).trim()
            : '—';
    const tipo =
        payload.tipo_nombre != null && String(payload.tipo_nombre).trim() !== ''
            ? String(payload.tipo_nombre).trim()
            : '—';
    const lado =
        payload.lado_nombre != null && String(payload.lado_nombre).trim() !== ''
            ? String(payload.lado_nombre).trim()
            : '—';
    const ubicacion = mediaEtiquetaDesdeLegado(payload);
    const usuario =
        payload.usuario_nombre != null && String(payload.usuario_nombre).trim() !== ''
            ? String(payload.usuario_nombre).trim()
            : '—';
    return { codigo: cod, tipo, lado, ubicacion, usuario };
}

/** Texto multilínea para Notification() y lectores de pantalla. */
function cuerpoPlanoHallazgo(d) {
    return [
        `Código: ${d.codigo}`,
        `Tipo de Hallazgo: ${d.tipo}`,
        `Lado: ${d.lado}`,
        `Ubicación: ${d.ubicacion}`,
        `Usuario que Registra: ${d.usuario}`,
    ].join('\n');
}

/** HTML seguro (valores escapados) para el modal. */
function htmlModalDetalleHallazgo(payload) {
    const d = extraerDetalleHallazgo(payload);
    const row = (etiqueta, valor) =>
        `<div class="hallazgo-modal-row" role="group" aria-label="${escapeHtml(etiqueta)}">
            <div class="hallazgo-modal-lbl">${escapeHtml(etiqueta)}</div>
            <div class="hallazgo-modal-val">${escapeHtml(valor)}</div>
        </div>`;
    return (
        '<div class="hallazgo-modal-detalle">' +
            row('Código', d.codigo) +
            row('Tipo de Hallazgo', d.tipo) +
            row('Lado', d.lado) +
            row('Ubicación', d.ubicacion) +
            row('Usuario que Registra', d.usuario) +
            '</div>'
    );
}

function textoHallazgoNotificacion(payload) {
    const titulo =
        payload.origen === 'tolerancia_cero'
            ? 'Nuevo hallazgo — tolerancia cero'
            : 'Nuevo hallazgo registrado';
    const d = extraerDetalleHallazgo(payload);
    return {
        titulo,
        cuerpo: cuerpoPlanoHallazgo(d),
    };
}

/** Vibración táctil: útil en Android cuando el tono llega vía WebSocket sin “gesto” (iOS Safari web casi no soporta vibrate). */
function vibracionAlertaHallazgo() {
    try {
        if (typeof navigator.vibrate === 'function') {
            navigator.vibrate([100, 45, 100]);
        }
    } catch {
        /* sin API o denegado */
    }
}

/** Modal emergente centrado — visible en HTTP sin permisos del sistema. */
function mostrarModalNuevoHallazgo(payload) {
    const modalAnterior = document.getElementById(ID_MODAL_HALLAZGO);
    modalAnterior?.remove();

    if (!document.getElementById(ESTILOS_MODAL_ID)) {
        const est = document.createElement('style');
        est.id = ESTILOS_MODAL_ID;
        est.textContent = `
          @keyframes hallazgo-modal-in { from { opacity:0; transform: scale(0.96);} to { opacity:1; transform: scale(1);} }
          #${ID_MODAL_HALLAZGO} * { box-sizing: border-box; }
          #${ID_MODAL_HALLAZGO} .hallazgo-modal-detalle {
            margin: 0 0 20px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            text-align: left;
          }
          #${ID_MODAL_HALLAZGO} .hallazgo-modal-row {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
          }
          #${ID_MODAL_HALLAZGO} .hallazgo-modal-row:last-child {
            border-bottom: none;
            padding-bottom: 2px;
          }
          #${ID_MODAL_HALLAZGO} .hallazgo-modal-lbl {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
            line-height: 1.35;
          }
          #${ID_MODAL_HALLAZGO} .hallazgo-modal-val {
            font-size: 1.05rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.45;
            letter-spacing: -0.015em;
            word-break: break-word;
          }
        `;
        document.head.appendChild(est);
    }

    const detalleHtml = htmlModalDetalleHallazgo(payload);
    const overlay = document.createElement('div');
    overlay.id = ID_MODAL_HALLAZGO;
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-labelledby', 'hallazgo-modal-titulo');
    Object.assign(overlay.style, {
        position: 'fixed',
        inset: '0',
        zIndex: '100000',
        background: 'rgba(15, 23, 42, 0.65)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '16px',
        fontFamily: 'ui-sans-serif, system-ui, sans-serif',
    });

    const card = document.createElement('div');
    Object.assign(card.style, {
        background: '#fff',
        borderRadius: '14px',
        maxWidth: '420px',
        width: '100%',
        padding: '24px',
        boxShadow: '0 25px 50px -12px rgba(0,0,0,0.35)',
        border: '1px solid #e2e8f0',
        animation: 'hallazgo-modal-in 0.25s ease-out',
    });

    card.innerHTML = `
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <span style="display:flex;width:44px;height:44px;border-radius:12px;background:#fef3c7;align-items:center;justify-content:center;font-size:22px;line-height:1;">🔔</span>
            <h2 id="hallazgo-modal-titulo" style="margin:0;font-size:1.125rem;line-height:1.35;color:#0f172a;font-weight:700;">
                Se registró un nuevo hallazgo
            </h2>
        </div>
        ${detalleHtml}
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" id="hallazgo-modal-btn" style="
                padding:12px 20px;border-radius:10px;border:0;cursor:pointer;
                font-weight:700;font-size:0.9375rem;
                background:#d97706;color:#fff;min-width:120px;">
                Entendido
            </button>
        </div>
    `;

    overlay.appendChild(card);
    document.body.appendChild(overlay);
    vibracionAlertaHallazgo();

    /** Intento principal: en móvil (sobre todo iOS Safari) suele fallar si no hay gesto reciente. */
    const sonidoAlMostrarPromesa = playAlertSound();
    let tonoHallazgoConseguido = false;
    void sonidoAlMostrarPromesa.then((ok) => {
        if (ok) {
            tonoHallazgoConseguido = true;
        }
    });

    /**
     * Primer toque en el modal cuenta como gesto del usuario → desbloquea audio en navegadores móviles
     * antes de pulsar «Entendido».
     */
    const primerToqueParaSonido = () => {
        overlay.removeEventListener('pointerdown', primerToqueParaSonido, true);
        void (async () => {
            await intentarDesbloquearAudioWebDesdeUsuario();
            if (!tonoHallazgoConseguido && (await playAlertSound())) {
                tonoHallazgoConseguido = true;
            }
        })();
    };
    overlay.addEventListener('pointerdown', primerToqueParaSonido, true);

    document.body.style.overflow = 'hidden';

    let cerrando = false;
    const cerrarYReproducir = async () => {
        if (cerrando) {
            return;
        }
        cerrando = true;
        await intentarDesbloquearAudioWebDesdeUsuario();
        await sonidoAlMostrarPromesa;
        if (!tonoHallazgoConseguido) {
            tonoHallazgoConseguido = await playAlertSound();
        }
        document.removeEventListener('keydown', onEscape);
        overlay.remove();
        document.body.style.overflow = '';
    };

    function onEscape(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            void cerrarYReproducir();
        }
    }

    document.addEventListener('keydown', onEscape);

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            void cerrarYReproducir();
        }
    });

    card.querySelector('#hallazgo-modal-btn')?.addEventListener('click', () => {
        void cerrarYReproducir();
    });

    queueMicrotask(() => {
        const b = document.getElementById('hallazgo-modal-btn');
        if (b instanceof HTMLButtonElement) {
            b.focus();
        }
    });
}

function escapeHtml(s) {
    return String(s)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

/** Solo contextos seguros (HTTPS o localhost). Con http://192.168… el navegador no expone bien Notification. */
function mostrarNotificacionSistemaSiPuede(payload) {
    try {
        if (!window.isSecureContext || typeof Notification === 'undefined') {
            return;
        }
        if (Notification.permission !== 'granted') {
            return;
        }
        const { titulo, cuerpo } = textoHallazgoNotificacion(payload);
        const iconMeta = document.querySelector('link[rel="icon"]');
        const iconHref = iconMeta?.getAttribute('href') ?? '/vaca.png';
        try {
            const iconUrl = new URL(iconHref, window.location.href).href;
            new Notification(titulo, {
                body: cuerpo,
                icon: iconUrl,
                tag: `hallazgo-${payload.registro_id}-${payload.origen}`,
            });
        } catch {
            /* ignorar */
        }
    } catch {
        /* ignorar */
    }
}

function urlSonidoHallazgo() {
    const m = document.querySelector('meta[name="hallazgo-sound-url"]');
    const u = m?.getAttribute('content')?.trim();
    if (u) {
        return u;
    }
    return `${window.location.origin}/sounds/not.mp3`;
}

let sonidoUrlAplicada = '';

function obtenerAudioHallazgo() {
    const url = urlSonidoHallazgo();
    if (!audioHallazgoWeb) {
        audioHallazgoWeb = new Audio(url);
        audioHallazgoWeb.preload = 'auto';
        sonidoUrlAplicada = url;
        return audioHallazgoWeb;
    }
    if (url !== sonidoUrlAplicada) {
        audioHallazgoWeb.src = url;
        sonidoUrlAplicada = url;
    }
    return audioHallazgoWeb;
}

async function intentarDesbloquearAudioWebDesdeUsuario() {
    try {
        const a = obtenerAudioHallazgo();
        const vol = a.volume;
        a.volume = 0.01;
        await a.play();
        a.pause();
        a.currentTime = 0;
        a.volume = vol;
        audioWebDesbloqueado = true;
        marcarAudioDesbloqueadoPersistente();
        return true;
    } catch {
        return audioWebDesbloqueado;
    }
}

async function playAlertSound() {
    try {
        const audio = obtenerAudioHallazgo();
        audio.volume = 0.75;
        audio.currentTime = 0;
        await audio.play();
        return true;
    } catch (e) {
        if (debeLogEcho()) {
            console.warn(
                '[Hallazgo sonido] No se pudo reproducir not.mp3 (archivo o política del navegador). No se usa tono alternativo.',
                e?.message ?? e,
            );
        }
        return false;
    }
}

/** Primera interacción: desbloquea audio para eventos posteriores por WebSocket. */
function instalarDesbloqueoAudioWeb() {
    if (esAppNativa()) {
        return;
    }

    const unlock = async () => {
        await intentarDesbloquearAudioWebDesdeUsuario();
        detach();
        quitarBannerActivarSonido();
    };

    let attached = false;
    const detach = () => {
        if (!attached) {
            return;
        }
        attached = false;
        document.removeEventListener('pointerdown', onFirstGesture, true);
        document.removeEventListener('keydown', onFirstGesture, true);
        document.removeEventListener('click', onFirstGesture, true);
    };

    const onFirstGesture = () => {
        void unlock();
    };

    attached = true;
    document.addEventListener('pointerdown', onFirstGesture, true);
    document.addEventListener('keydown', onFirstGesture, true);
    document.addEventListener('click', onFirstGesture, true);
}

function quitarBannerActivarSonido() {
    document.getElementById(ID_BANNER_SONIDO)?.remove();
}

/** Con HTTP/LAN es frecuente que el navegador bloquee el audio hasta un clic explícito. */
function instalarBannerActivarSonido() {
    if (esAppNativa()) {
        return;
    }
    if (yaDesbloqueoAudioAntes()) {
        return;
    }
    window.setTimeout(() => {
        if (
            audioWebDesbloqueado ||
            yaDesbloqueoAudioAntes() ||
            document.getElementById(ID_BANNER_SONIDO)
        ) {
            return;
        }
        const bar = document.createElement('div');
        bar.id = ID_BANNER_SONIDO;
        bar.setAttribute('role', 'dialog');
        bar.setAttribute('aria-label', 'Activar alertas sonoras');
        Object.assign(bar.style, {
            position: 'fixed',
            bottom: 'max(12px, env(safe-area-inset-bottom))',
            left: '50%',
            transform: 'translateX(-50%)',
            zIndex: '99998',
            maxWidth: 'min(560px, calc(100vw - 24px))',
            padding: '14px 16px',
            borderRadius: '12px',
            background: '#1e293b',
            color: '#f8fafc',
            boxShadow: '0 10px 30px rgba(0,0,0,0.35)',
            fontFamily: 'ui-sans-serif, system-ui, sans-serif',
            fontSize: '14px',
            lineHeight: '1.45',
            display: 'flex',
            gap: '12px',
            flexWrap: 'wrap',
            alignItems: 'center',
            justifyContent: 'center',
        });
        bar.innerHTML = `
            <span style="flex:1; min-width:200px;">
                Activa el tono de <strong>nuevo hallazgo</strong> (el navegador lo pide con un clic).
            </span>
            <button type="button" data-accion="activar" style="padding:10px 16px; border-radius:8px; border:0;
                cursor:pointer; background:#f59e0b; color:#0f172a; font-weight:700; white-space:nowrap;">
                Activar sonido
            </button>
        `;
        bar.querySelector('[data-accion="activar"]')?.addEventListener('click', async () => {
            await intentarDesbloquearAudioWebDesdeUsuario();
            quitarBannerActivarSonido();
        });
        document.body.appendChild(bar);
    }, 1500);
}

function enlazarLogsEcho(conn, wsUrl) {
    if (!conn || !debeLogEcho()) {
        return;
    }
    conn.bind('connected', () => console.info('[Echo/Reverb] Conectado a', wsUrl));
    conn.bind('disconnected', () => console.warn('[Echo/Reverb] Desconectado'));
    conn.bind('error', (err) => console.warn('[Echo/Reverb] Error', err));
    conn.bind('unavailable', () =>
        console.warn(
            `[Echo/Reverb] WebSocket no disponible. Objetivo ${wsUrl}. ¿Corre \`php artisan reverb:start\` en el servidor y el firewall permite el puerto WS?`,
        ),
    );
}

export async function iniciarHallazgoNotificaciones() {
    if (echoInicializado || typeof document === 'undefined') {
        return;
    }

    const meta = document.querySelector('meta[name="reverb-client"]');
    if (!meta) {
        console.warn(
            '[Echo/Reverb] Falta meta reverb-client en esta página (broadcasting/Reverb no activo en el servidor o no estás usando el layout logueado). No habrá alertas por tiempo real.',
        );
        return;
    }

    let cfg;
    try {
        cfg = JSON.parse(meta.getAttribute('content') || '{}');
    } catch {
        console.warn('[Echo/Reverb] JSON inválido en meta reverb-client.');
        return;
    }

    if (!cfg.enabled || !cfg.key) {
        console.warn('[Echo/Reverb] Broadcasting desactivado o sin REVERB_APP_KEY; revisá .env (BROADCAST_CONNECTION=reverb).', cfg);
        return;
    }

    const rawWs = cfg.wsHost != null ? String(cfg.wsHost).trim() : '';
    const hostEcho = normalizarHostEchoWebSocket(rawWs);
    const host = hostEcho || window.location.hostname;
    if (!host) {
        console.warn('[Echo/Reverb] Sin hostname para WebSocket. Defina ECHO_WS_HOST en .env (solo IP/host, sin :puerto HTTP).');
        return;
    }

    echoInicializado = true;
    window.Pusher = Pusher;

    if (typeof window.Pusher !== 'undefined' && debeLogEcho()) {
        window.Pusher.logToConsole = true;
    }

    const port = Number(cfg.port) || 6001;
    const forceTLS = window.location.protocol === 'https:';
    const wsUrl = `${forceTLS ? 'wss' : 'ws'}://${host}:${port}`;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: cfg.key,
        wsHost: host,
        wsPort: port,
        wssPort: port,
        forceTLS,
        encrypted: forceTLS,
        enabledTransports: ['ws', 'wss'],
        enableStats: false,
    });

    const conn = window.Echo?.connector?.pusher?.connection;
    enlazarLogsEcho(conn, wsUrl);
    if (!conn && debeLogEcho()) {
        console.warn('[Echo/Reverb] No se pudo acceder al conector Pusher/Reverb.');
    }

    const canalHallazgos = window.Echo.channel('hallazgos');
    if (debeLogEcho()) {
        canalHallazgos.listenToAll((eventName, data) => {
            if (String(eventName).startsWith('pusher:')) {
                return;
            }
            console.info('[Echo] canal hallazgos — evento:', eventName, data);
        });
    }

    canalHallazgos.listen('.registrado', async (payload) => {
        /** Quien registra el hallazgo nunca ve ni oye esta alerta. */
        const metaUid = document.querySelector('meta[name="current-user-id"]')?.getAttribute('content');
        if (
            metaUid != null &&
            metaUid !== '' &&
            payload.usuario_registro_id != null &&
            String(payload.usuario_registro_id) === String(metaUid)
        ) {
            if (debeLogEcho()) {
                console.info('[Hallazgo] Omitido para el usuario que registra.');
            }
            return;
        }

        if (esAppNativa()) {
            return;
        }

        try {
            window.dispatchEvent(new CustomEvent('liberacion:hallazgo-registrado', { detail: payload }));
        } catch {
            /* ignorar */
        }

        mostrarModalNuevoHallazgo(payload);
        mostrarNotificacionSistemaSiPuede(payload);

        if (debeLogEcho()) {
            console.info('[Hallazgo] Evento web — modal emergente.', audioWebDesbloqueado);
        }
    });
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        instalarDesbloqueoAudioWeb();
        instalarBannerActivarSonido();
        /** Si el sitio tiene autoplay de audio permitido, desbloquea sin banner. */
        void intentarDesbloquearAudioWebDesdeUsuario().then(() => {
            quitarBannerActivarSonido();
        });
        void iniciarHallazgoNotificaciones();
    });
}
