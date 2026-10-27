// Menyiapkan isi aplikasi sebelum electron-builder:
//   build/laravel  salinan Laravel (tanpa dev deps, aset Vite sudah di-build)
//   build/php      PHP untuk Windows (hanya dengan --php-windows)
//
// Pemakaian:  node scripts/prepare.mjs [--php-windows] [--skip-laravel]
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const desktopDir = path.resolve(here, '..');
const webDir = path.resolve(desktopDir, '..', 'web');
const buildDir = path.join(desktopDir, 'build');
const laravelOut = path.join(buildDir, 'laravel');
const phpOut = path.join(buildDir, 'php');
const PHP_VERSION = process.env.SAVVE_PHP_VERSION || '8.3';

const args = new Set(process.argv.slice(2));
const run = (cmd, cwd) => {
  console.log(`> ${cmd}   (${path.relative(desktopDir, cwd) || '.'})`);
  execSync(cmd, { cwd, stdio: 'inherit', shell: true, env: { ...process.env, CYPRESS_INSTALL_BINARY: '0' } });
};

const EXCLUDE = new Set([
  '.git', 'node_modules', 'vendor', 'tests', 'cypress', '.env', '.env.backup',
  'cypress.config.js', 'phpunit.xml', 'db-backup.ps1', '.phpunit.result.cache',
]);

function copyLaravel() {
  console.log('\n== Menyalin Laravel ==');
  fs.rmSync(laravelOut, { recursive: true, force: true });
  fs.cpSync(webDir, laravelOut, {
    recursive: true,
    filter: (src) => {
      const rel = path.relative(webDir, src);
      if (!rel) return true;
      const top = rel.split(path.sep)[0];
      if (EXCLUDE.has(top)) return false;
      if (rel === path.join('public', 'storage') || rel === path.join('public', 'hot')) return false;
      if (rel.startsWith(path.join('database', 'backups'))) return false;
      if (rel.endsWith('.sqlite') || rel.endsWith('.sqlite-wal') || rel.endsWith('.sqlite-shm')) return false;
      // Isi storage/ dibuat ulang di folder data pengguna
      if (top === 'storage' && rel !== 'storage') return false;
      if (rel.startsWith(path.join('bootstrap', 'cache') + path.sep)) return false;
      return true;
    },
  });
  fs.mkdirSync(path.join(laravelOut, 'bootstrap', 'cache'), { recursive: true });
  for (const dir of ['app/public', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs']) {
    fs.mkdirSync(path.join(laravelOut, 'storage', dir), { recursive: true });
  }

  console.log('\n== Composer (tanpa dev) ==');
  run('composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-reqs', laravelOut);
  run('composer dump-autoload --optimize --no-dev --no-scripts', laravelOut);

  console.log('\n== Build aset (Vite) ==');
  if (!fs.existsSync(path.join(webDir, 'node_modules'))) run('npm install --no-audit --no-fund', webDir);
  run('npm run build', webDir);
  fs.rmSync(path.join(laravelOut, 'public', 'build'), { recursive: true, force: true });
  fs.cpSync(path.join(webDir, 'public', 'build'), path.join(laravelOut, 'public', 'build'), { recursive: true });
}

async function download(url, file) {
  console.log(`Mengunduh ${url}`);
  const res = await fetch(url, { headers: { 'User-Agent': 'savve-build' } });
  if (!res.ok) throw new Error(`Gagal mengunduh ${url}: ${res.status}`);
  fs.writeFileSync(file, Buffer.from(await res.arrayBuffer()));
}

async function fetchWindowsPhp() {
  console.log('\n== PHP untuk Windows ==');
  const releases = await (await fetch('https://windows.php.net/downloads/releases/releases.json')).json();
  const rel = releases[PHP_VERSION];
  if (!rel) throw new Error(`PHP ${PHP_VERSION} tidak ditemukan di windows.php.net`);
  const key = Object.keys(rel).find((k) => /^nts-.*-x64$/.test(k));
  const zipName = rel[key].zip.path;
  const zipFile = path.join(buildDir, zipName);
  fs.mkdirSync(buildDir, { recursive: true });
  if (!fs.existsSync(zipFile)) await download(`https://windows.php.net/downloads/releases/${zipName}`, zipFile);
  fs.rmSync(phpOut, { recursive: true, force: true });
  fs.mkdirSync(phpOut, { recursive: true });
  if (process.platform === 'win32') {
    run(`powershell -NoProfile -Command "Expand-Archive -Force '${zipFile}' '${phpOut}'"`, buildDir);
  } else {
    run(`unzip -q -o "${zipFile}" -d "${phpOut}"`, buildDir);
  }
  console.log(`PHP ${rel.version} siap di build/php`);
}

if (!args.has('--skip-laravel')) copyLaravel();
if (args.has('--php-windows')) await fetchWindowsPhp();
console.log('\nSelesai.');
