/**
 * Usa public/vaca.png (mismo favicon que el layout web) como fuente única del ícono Android.
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const vaca = path.join(root, 'public', 'vaca.png');
const iconOnly = path.join(root, 'assets', 'icon-only.png');
const resMain = path.join(root, 'android', 'app', 'src', 'main', 'res');

if (!fs.existsSync(vaca)) {
    console.error('No existe public/vaca.png');
    process.exit(1);
}

fs.mkdirSync(path.dirname(iconOnly), { recursive: true });
fs.copyFileSync(vaca, iconOnly);

execSync('npx capacitor-assets generate --android', { stdio: 'inherit', cwd: root });

for (const d of ['mipmap-mdpi', 'mipmap-hdpi', 'mipmap-xhdpi', 'mipmap-xxhdpi', 'mipmap-xxxhdpi']) {
    const src = path.join(resMain, d, 'ic_launcher.png');
    const dest = path.join(resMain, d, 'ic_launcher_foreground.png');
    if (fs.existsSync(src)) {
        fs.copyFileSync(src, dest);
    }
}

console.log('Íconos Android actualizados desde public/vaca.png (launcher + foreground adaptativo).');
