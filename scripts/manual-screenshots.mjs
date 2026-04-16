/**
 * Genera capturas PNG para el manual de usuario.
 * Requisitos: aplicación Laravel accesible (php artisan serve o Laragon).
 *
 * Opcional: archivo .env.manual en la raíz del proyecto con:
 *   MANUAL_BASE_URL=http://127.0.0.1:8000
 *   MANUAL_USERNAME=su_usuario
 *   MANUAL_PASSWORD=su_clave
 *   MANUAL_CHROME_PATH=C:\Program Files\Google\Chrome\Application\chrome.exe
 *
 * Uso: npm run manual:capturas
 */
import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const outDir = path.join(root, 'public', 'manual', 'capturas');

function loadEnvManual() {
    const p = path.join(root, '.env.manual');
    if (!fs.existsSync(p)) return;
    const text = fs.readFileSync(p, 'utf8');
    for (const line of text.split(/\n/)) {
        const t = line.trim();
        if (!t || t.startsWith('#')) continue;
        const eq = t.indexOf('=');
        if (eq === -1) continue;
        const key = t.slice(0, eq).trim();
        let val = t.slice(eq + 1).trim();
        if ((val.startsWith('"') && val.endsWith('"')) || (val.startsWith("'") && val.endsWith("'"))) {
            val = val.slice(1, -1);
        }
        if (!process.env[key]) process.env[key] = val;
    }
}

function sleep(ms) {
    return new Promise((r) => setTimeout(r, ms));
}

loadEnvManual();

const BASE = (process.env.MANUAL_BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const USER = process.env.MANUAL_USERNAME || '';
const PASS = process.env.MANUAL_PASSWORD || '';
const CHROME =
    process.env.MANUAL_CHROME_PATH ||
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

if (!fs.existsSync(CHROME)) {
    console.error('No se encontró Chrome en:', CHROME);
    console.error('Defina MANUAL_CHROME_PATH en .env.manual');
    process.exit(1);
}

fs.mkdirSync(outDir, { recursive: true });

async function screenshot(page, name, fullPage = false) {
    const dest = path.join(outDir, name);
    await page.screenshot({ path: dest, fullPage, type: 'png' });
    console.log('OK', dest);
}

async function main() {
    const browser = await puppeteer.launch({
        executablePath: CHROME,
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1400,900'],
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 900, deviceScaleFactor: 1 });

    // 1 — Login (siempre)
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForSelector('#username', { timeout: 20000 }).catch(() => null);
    await sleep(1500);
    await screenshot(page, '01-login.png', true);

    if (!USER || !PASS) {
        console.warn('\nSin MANUAL_USERNAME / MANUAL_PASSWORD: solo se generó 01-login.png');
        console.warn('Copie .env.manual.example a .env.manual y complete credenciales de un usuario ADMINISTRADOR.\n');
        await browser.close();
        return;
    }

    await page.click('#username', { clickCount: 3 }).catch(() => {});
    await page.keyboard.type(USER, { delay: 12 });
    await page.click('#password', { clickCount: 3 }).catch(() => {});
    await page.keyboard.type(PASS, { delay: 12 });
    await page.click('button.login-button, button[type="submit"]');
    await page
        .waitForFunction(() => !window.location.pathname.includes('login'), { timeout: 40000 })
        .catch(() => {});
    await sleep(2800);

    const shots = [
        ['02-bienvenida.png', `${BASE}/`, false],
        ['03-dashboard.png', `${BASE}/dashboard`, false],
        ['04-registro-hallazgos.png', `${BASE}/hallazgos/registrar`, false],
        ['05-historial.png', `${BASE}/hallazgos/historial`, false],
        ['06-operarios.png', `${BASE}/operarios`, false],
        ['07-asignacion-dia.png', `${BASE}/operarios/dia`, false],
        ['08-tolerancia-cero.png', `${BASE}/tolerancia-cero/registrar`, false],
        ['09-indicadores-dia.png', `${BASE}/indicadores/detalle-dia`, false],
        ['10-gestion-usuarios.png', `${BASE}/usuarios/gestion`, false],
    ];

    for (const [file, url, full] of shots) {
        try {
            const res = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
            await sleep(2200);
            if (res && res.status() === 403) {
                console.warn('403 omitido:', url);
                continue;
            }
            await screenshot(page, file, full);
        } catch (e) {
            console.error('Error', url, e.message);
        }
    }

    await browser.close();
    console.log('\nCapturas listas en public/manual/capturas/');
}

main().catch((e) => {
    console.error(e);
    process.exit(1);
});
