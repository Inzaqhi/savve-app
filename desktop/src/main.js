'use strict';

const { app, BrowserWindow, Menu, dialog, ipcMain, shell, session } = require('electron');
const fs = require('fs');
const path = require('path');
const QRCode = require('qrcode');
const { SavveServer } = require('./server');

const isWindows = process.platform === 'win32';
const resourcesDir = app.isPackaged ? process.resourcesPath : path.join(__dirname, '..', 'build');
const configFile = () => path.join(app.getPath('userData'), 'config.json');

let config = {};
let server = null;
let mainWindow = null;
let infoWindow = null;
let quitting = false;

// ------------------------------------------------------------------ konfigurasi

function loadConfig() {
  try {
    config = JSON.parse(fs.readFileSync(configFile(), 'utf8'));
  } catch {
    config = {};
  }
}

function saveConfig() {
  fs.mkdirSync(path.dirname(configFile()), { recursive: true });
  fs.writeFileSync(configFile(), JSON.stringify(config, null, 2));
}

function phpBinary() {
  const bundled = path.join(resourcesDir, 'php', isWindows ? 'php.exe' : 'php');
  if (fs.existsSync(bundled)) return { path: bundled, bundled: true };
  return { path: process.env.SAVVE_PHP || 'php', bundled: false }; // mode pengembangan
}

function laravelDir() {
  const bundled = path.join(resourcesDir, 'laravel');
  if (fs.existsSync(path.join(bundled, 'artisan'))) return bundled;
  return path.join(__dirname, '..', '..', 'web'); // mode pengembangan
}

function isSavveUrl(url) {
  if (config.mode === 'server') return url.startsWith('https://127.0.0.1:');
  return !!config.serverUrl && url.startsWith(config.serverUrl);
}

function appUrl() {
  if (config.mode === 'server') return `https://127.0.0.1:${server.port}/login`;
  return config.serverUrl.replace(/\/+$/, '') + '/login';
}

// ------------------------------------------------------------------ sertifikat & izin

function hostOf(url) {
  try {
    return new URL(url).host;
  } catch {
    return '';
  }
}

app.on('certificate-error', (event, _wc, url, _error, certificate, callback) => {
  const host = hostOf(url);
  let trusted = false;
  if (config.mode === 'server' && server && server.fingerprint) {
    trusted = certificate.fingerprint === server.fingerprint;
  } else if (config.mode === 'client' && host === hostOf(config.serverUrl)) {
    // Sertifikat server pertama kali dipercaya lalu diingat (seperti SSH)
    if (!config.serverFingerprint) {
      config.serverFingerprint = certificate.fingerprint;
      saveConfig();
    }
    trusted = certificate.fingerprint === config.serverFingerprint;
  }
  if (trusted) event.preventDefault();
  callback(trusted);
});

function setupPermissions() {
  const allowed = new Set(['media', 'clipboard-sanitized-write', 'fullscreen']);
  session.defaultSession.setPermissionRequestHandler((_wc, permission, callback) => {
    callback(allowed.has(permission));
  });
  session.defaultSession.setPermissionCheckHandler((_wc, permission) => allowed.has(permission));
}

// ------------------------------------------------------------------ jendela

function createMainWindow() {
  mainWindow = new BrowserWindow({
    width: 1366,
    height: 860,
    minWidth: 900,
    minHeight: 600,
    title: 'Savve',
    icon: path.join(__dirname, 'ui', 'icon.png'),
    show: false,
    webPreferences: {
      contextIsolation: true,
      sandbox: true,
      preload: path.join(__dirname, 'preload.js'),
    },
  });
  mainWindow.once('ready-to-show', () => mainWindow.show());
  mainWindow.maximize();

  // Tautan ke luar aplikasi (mis. wa.me) dibuka di browser bawaan
  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    if (isSavveUrl(url)) {
      // Jendela nota juga butuh preload agar tombol cetak bisa mengatur ukuran kertas
      return {
        action: 'allow',
        overrideBrowserWindowOptions: {
          autoHideMenuBar: true,
          webPreferences: { contextIsolation: true, sandbox: true, preload: path.join(__dirname, 'preload.js') },
        },
      };
    }
    shell.openExternal(url);
    return { action: 'deny' };
  });

  mainWindow.webContents.on('did-fail-load', (_e, code, desc, url, isMainFrame) => {
    if (!isMainFrame || code === -3) return; // -3 = dibatalkan (navigasi biasa)
    mainWindow.loadFile(path.join(__dirname, 'ui', 'offline.html'), {
      query: { url, error: desc, mode: config.mode || '' },
    });
  });

  mainWindow.on('close', (e) => {
    if (quitting || config.mode !== 'server') return;
    const choice = dialog.showMessageBoxSync(mainWindow, {
      type: 'question',
      buttons: ['Tutup Savve', 'Batal'],
      defaultId: 1,
      cancelId: 1,
      title: 'Tutup Savve',
      message: 'Menutup Savve akan menghentikan server.',
      detail: 'Kasir di HP/perangkat lain tidak bisa memakai Savve sampai aplikasi ini dibuka lagi.',
    });
    if (choice !== 0) e.preventDefault();
  });

  buildMenu();
}

function showStatus(message) {
  mainWindow.loadFile(path.join(__dirname, 'ui', 'status.html'), { query: { message } });
}

function openInfoWindow() {
  if (!server) return;
  if (infoWindow && !infoWindow.isDestroyed()) {
    infoWindow.focus();
    return;
  }
  infoWindow = new BrowserWindow({
    width: 560,
    height: 720,
    title: 'Sambungkan Perangkat',
    parent: mainWindow,
    autoHideMenuBar: true,
    webPreferences: { contextIsolation: true, sandbox: true, preload: path.join(__dirname, 'preload.js') },
  });
  infoWindow.loadFile(path.join(__dirname, 'ui', 'info.html'));
}

let printerWindow = null;

function openPrinterWindow() {
  if (!server) return;
  if (printerWindow && !printerWindow.isDestroyed()) {
    printerWindow.focus();
    return;
  }
  printerWindow = new BrowserWindow({
    width: 760,
    height: 680,
    title: 'Printer per Kasir',
    parent: mainWindow,
    autoHideMenuBar: true,
    webPreferences: { contextIsolation: true, sandbox: true, preload: path.join(__dirname, 'preload.js') },
  });
  printerWindow.loadFile(path.join(__dirname, 'ui', 'printers.html'));
}

function buildMenu() {
  const serverMenu = config.mode === 'server'
    ? [{
      label: 'Server',
      submenu: [
        { label: 'Sambungkan HP / Perangkat Lain…', accelerator: 'F2', click: openInfoWindow },
        { label: 'Printer per Kasir…', click: openPrinterWindow },
        { type: 'separator' },
        { label: 'Backup Data…', click: backupData },
        { label: 'Pulihkan Data dari Backup…', click: restoreData },
        { type: 'separator' },
        { label: 'Buka Folder Data', click: () => shell.openPath(server.dataDir) },
      ],
    }]
    : [];

  Menu.setApplicationMenu(Menu.buildFromTemplate([
    {
      label: 'Savve',
      submenu: [
        { label: 'Halaman Awal', accelerator: 'Alt+Home', click: () => mainWindow.loadURL(appUrl()) },
        { label: 'Ganti Mode (Server / Klien)…', click: () => resetMode() },
        { label: 'Ganti Printer Nota…', click: () => choosePrinter(mainWindow, true) },
        { type: 'separator' },
        { role: 'quit', label: 'Keluar' },
      ],
    },
    ...serverMenu,
    {
      label: 'Tampilan',
      submenu: [
        { role: 'reload', label: 'Muat Ulang' },
        { role: 'resetZoom', label: 'Ukuran Normal' },
        { role: 'zoomIn', label: 'Perbesar' },
        { role: 'zoomOut', label: 'Perkecil' },
        { type: 'separator' },
        { role: 'togglefullscreen', label: 'Layar Penuh' },
        { role: 'toggleDevTools', label: 'Developer Tools' },
      ],
    },
  ]));
}

// ------------------------------------------------------------------ mode server / klien

async function startServerMode() {
  showStatus('Menyiapkan server Savve…');
  const php = phpBinary();
  server = new SavveServer({
    phpBinary: php.path,
    bundledPhp: php.bundled,
    laravelDir: laravelDir(),
    dataDir: path.join(app.getPath('userData'), 'data'),
    port: config.port || 8443,
    version: app.getVersion(),
    log: (...a) => console.log('[savve]', ...a),
    onPrintJob: printJobFromDevice,
  });
  try {
    await server.start();
  } catch (err) {
    console.error(err);
    await server.stop().catch(() => {});
    dialog.showErrorBox(
      'Server Savve gagal dijalankan',
      `${err.code === 'EADDRINUSE' ? `Port ${server.port} sudah dipakai program lain.\n\n` : ''}${err.message}`,
    );
    showStatus('Server gagal dijalankan. Tutup aplikasi lalu buka lagi.');
    return;
  }
  buildMenu();
  mainWindow.loadURL(appUrl());
  if (!config.infoShown) {
    config.infoShown = true;
    saveConfig();
    openInfoWindow();
  }
}

function startClientMode() {
  buildMenu();
  mainWindow.loadURL(appUrl());
}

function showSetup() {
  buildMenu();
  mainWindow.loadFile(path.join(__dirname, 'ui', 'setup.html'));
}

async function resetMode() {
  const choice = dialog.showMessageBoxSync(mainWindow, {
    type: 'question',
    buttons: ['Ganti Mode', 'Batal'],
    defaultId: 1,
    cancelId: 1,
    message: 'Ganti mode Server / Klien?',
    detail: 'Data di komputer ini tidak dihapus.',
  });
  if (choice !== 0) return;
  if (server) {
    await server.stop();
    server = null;
  }
  delete config.mode;
  saveConfig();
  showSetup();
}

ipcMain.handle('savve:setup', async (_e, { mode, serverUrl }) => {
  if (mode === 'server') {
    config.mode = 'server';
    saveConfig();
    startServerMode();
    return { ok: true };
  }
  let url = String(serverUrl || '').trim();
  if (!/^https?:\/\//i.test(url)) url = 'https://' + url;
  let parsed;
  try {
    parsed = new URL(url);
  } catch {
    return { ok: false, error: 'Alamat server tidak valid.' };
  }
  if (!parsed.port) parsed.port = '8443';
  parsed.protocol = 'https:';
  config.mode = 'client';
  config.serverUrl = parsed.origin;
  delete config.serverFingerprint;
  saveConfig();
  startClientMode();
  return { ok: true };
});

ipcMain.handle('savve:info', async () => {
  if (!server) return null;
  const info = server.info();
  for (const a of info.addresses) {
    a.qr = await QRCode.toDataURL(a.url, { margin: 1, width: 220 });
  }
  return info;
});

ipcMain.handle('savve:retry', () => {
  if (!config.mode) showSetup();
  else if (config.mode === 'server' && !server) startServerMode();
  else mainWindow.loadURL(appUrl());
});

ipcMain.handle('savve:change-mode', () => resetMode());

ipcMain.handle('savve:printer-settings', async () => {
  if (!server) return null;
  const printers = await mainWindow.webContents.getPrintersAsync();
  return {
    printers: printers.map((p) => ({ name: p.name, label: p.displayName || p.name, isDefault: !!p.isDefault })),
    kasir: await server.listKasir(),
    utama: config.notaPrinter || '',
    perKasir: config.printerKasir || {},
  };
});

ipcMain.handle('savve:printer-settings-save', (_e, data) => {
  const clean = {};
  for (const [id, name] of Object.entries((data && data.perKasir) || {})) {
    if (/^\d+$/.test(id) && typeof name === 'string' && name) clean[id] = name;
  }
  config.printerKasir = clean;
  if (data && typeof data.utama === 'string') {
    if (data.utama) config.notaPrinter = data.utama;
    else delete config.notaPrinter;
  }
  saveConfig();
  return { ok: true };
});

// Cetak lembar tes kecil agar mudah mengenali printer fisik mana milik kasir siapa
ipcMain.handle('savve:printer-test', async (_e, { printer, label }) => {
  const win = new BrowserWindow({ show: false, webPreferences: { sandbox: true } });
  try {
    const esc = (t) => String(t || '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    const html = `<!doctype html><meta charset="utf-8"><style>@page{margin:0;size:58mm 50mm}
      body{margin:0;font-family:Verdana,sans-serif;text-align:center}
      div{width:46mm;margin:4mm auto;border:2px solid #000;padding:3mm 0}
      h1{font-size:16px;margin:0 0 2mm}p{font-size:11px;margin:1mm 0}</style>
      <div><h1>TES PRINTER</h1><p><b>${esc(label)}</b></p><p>${esc(printer)}</p><p>Savve</p></div>`;
    await win.loadURL('data:text/html;charset=utf-8,' + encodeURIComponent(html));
    const res = await printOnce(win.webContents, {
      silent: true,
      deviceName: printer,
      printBackground: true,
      scaleFactor: 100,
      margins: { marginType: 'none' },
      pageSize: { width: 58000, height: 50000 },
    });
    return { ok: res.success, message: res.reason };
  } finally {
    win.destroy();
  }
});

// ------------------------------------------------------------------ cetak nota

// Printer nota dipilih sekali lalu diingat, supaya tiap lembar bisa dicetak langsung (silent)
// dengan panjang kertasnya sendiri dan skala 100%.
async function choosePrinter(parent, force) {
  const wc = (parent && !parent.isDestroyed() ? parent : mainWindow).webContents;
  const printers = await wc.getPrintersAsync();
  if (!force && config.notaPrinter && printers.some((p) => p.name === config.notaPrinter)) {
    return config.notaPrinter;
  }
  if (printers.length === 0) {
    dialog.showErrorBox('Printer tidak ditemukan', 'Belum ada printer yang terpasang di Windows.');
    return null;
  }
  const shown = printers.slice(0, 8);
  const def = Math.max(0, shown.findIndex((p) => p.isDefault));
  const choice = await dialog.showMessageBox(parent || mainWindow, {
    type: 'question',
    title: 'Printer Nota',
    message: 'Pilih printer untuk mencetak nota',
    detail: 'Pilihan ini diingat. Ganti kapan saja lewat menu Savve → Ganti Printer Nota.',
    buttons: [...shown.map((p) => p.displayName || p.name), 'Batal'],
    defaultId: def,
    cancelId: shown.length,
    noLink: true,
  });
  if (choice.response >= shown.length) return null;
  config.notaPrinter = shown[choice.response].name;
  saveConfig();
  return config.notaPrinter;
}

function printOnce(wc, options) {
  return new Promise((resolve) => wc.print(options, (success, reason) => resolve({ success, reason: reason || '' })));
}

// Tiap lembar (nota, label, ...) dicetak sebagai halaman/print job tersendiri dengan panjang kertas
// pasti (mikron). Tanpa ukuran pasti, driver printer thermal memakai kertas default (mis. 58 x 3276 mm);
// satu halaman yang terlalu panjang malah diperkecil oleh driver sehingga tulisan jadi kecil.
ipcMain.handle('savve:print-nota', async (event, size) => {
  const wc = event.sender;
  if (!isSavveUrl(wc.getURL())) return { success: false, reason: 'not allowed' };
  const num = (v, min, max, def) => (Number.isFinite(Number(v)) ? Math.min(max, Math.max(min, Number(v))) : def);
  const widthMm = num(size && size.widthMm, 30, 120, 58);
  const sheets = Array.isArray(size && size.sheets) ? size.sheets.slice(0, 50).map((h) => num(h, 20, 1000, 150)) : [];
  if (sheets.length === 0) return { success: false, reason: 'tidak ada lembar' };

  const printer = await choosePrinter(BrowserWindow.fromWebContents(wc));
  if (!printer) return { success: false, reason: 'cancelled' };

  return printSheets(wc, printer, widthMm, sheets);
});

async function printSheets(wc, printer, widthMm, sheets) {
  try {
    for (let i = 0; i < sheets.length; i++) {
      await wc.executeJavaScript(`window.savvePilihLembar(${i}, ${sheets[i]})`);
      const res = await printOnce(wc, {
        silent: true,
        deviceName: printer,
        printBackground: true,
        scaleFactor: 100,
        margins: { marginType: 'none' },
        pageSize: { width: Math.round(widthMm * 1000), height: Math.round(sheets[i] * 1000) },
      });
      if (!res.success) return res;
    }
    return { success: true, reason: '' };
  } finally {
    await wc.executeJavaScript('window.savvePilihLembar(-1)').catch(() => {});
  }
}

// "Cetak di Komputer Server" dari HP/perangkat lain: buka nota (tautan bertanda tangan dari Laravel)
// di jendela tersembunyi lalu cetak ke printer nota komputer ini. Dijalankan bergiliran.
// Printer untuk kasir tertentu (menu Server → Printer per Kasir), atau printer nota utama.
function printerForKasir(kasirId) {
  const map = config.printerKasir || {};
  return (kasirId != null && map[String(kasirId)]) || config.notaPrinter || null;
}

// Antrean terpisah per printer: printer yang lambat tidak menahan printer kasir lain.
const printQueues = new Map();

function printJobFromDevice(job) {
  const key = printerForKasir(job.kasir_id) || '';
  const prev = printQueues.get(key) || Promise.resolve();
  const run = prev.then(() => printNotaHidden(job));
  printQueues.set(key, run.catch(() => {}));
  return run;
}

async function printNotaHidden(job) {
  if (!server) return { ok: false, message: 'Server belum siap.' };
  const printers = await mainWindow.webContents.getPrintersAsync();
  const printer = printerForKasir(job.kasir_id);
  if (!printer) {
    return {
      ok: false,
      message: 'Printer nota belum dipilih di komputer server. Di komputer server buka menu Server → Printer per Kasir.',
    };
  }
  if (!printers.some((p) => p.name === printer)) {
    return { ok: false, message: `Printer "${printer}" tidak ditemukan di komputer server. Periksa kabel/daya printer.` };
  }
  const win = new BrowserWindow({
    show: false,
    width: 800,
    height: 1000,
    webPreferences: { contextIsolation: true, sandbox: true, preload: path.join(__dirname, 'preload.js') },
  });
  try {
    await win.loadURL(`https://127.0.0.1:${server.port}${job.url}`);
    const wc = win.webContents;
    await wc.executeJavaScript('document.fonts.ready.then(() => new Promise((r) => setTimeout(r, 300)))');
    const sheets = await wc.executeJavaScript('window.savveUkurLembar ? window.savveUkurLembar() : []');
    if (!Array.isArray(sheets) || sheets.length === 0) return { ok: false, message: 'Nota tidak bisa dibuka di komputer server.' };
    const res = await printSheets(wc, printer, 58, sheets.slice(0, 50).map((h) => Math.min(1000, Math.max(20, Number(h) || 150))));
    return res.success
      ? { ok: true, message: `${job.title || 'Nota'} dikirim ke printer ${printer} (${sheets.length} lembar).` }
      : { ok: false, message: res.reason || 'Printer menolak pekerjaan cetak.' };
  } finally {
    win.destroy();
  }
}

// ------------------------------------------------------------------ backup

function timestamp() {
  const d = new Date();
  const p = (n) => String(n).padStart(2, '0');
  return `${d.getFullYear()}${p(d.getMonth() + 1)}${p(d.getDate())}-${p(d.getHours())}${p(d.getMinutes())}`;
}

async function backupData() {
  const res = await dialog.showOpenDialog(mainWindow, {
    title: 'Pilih folder untuk menyimpan backup',
    properties: ['openDirectory', 'createDirectory'],
  });
  if (res.canceled || !res.filePaths[0]) return;
  const target = path.join(res.filePaths[0], `Savve-Backup-${timestamp()}`);
  try {
    await server.backup(target);
    dialog.showMessageBox(mainWindow, { type: 'info', message: 'Backup berhasil', detail: target });
  } catch (err) {
    dialog.showErrorBox('Backup gagal', err.message);
  }
}

async function restoreData() {
  const res = await dialog.showOpenDialog(mainWindow, {
    title: 'Pilih folder backup Savve (Savve-Backup-…)',
    properties: ['openDirectory'],
  });
  if (res.canceled || !res.filePaths[0]) return;
  const confirm = dialog.showMessageBoxSync(mainWindow, {
    type: 'warning',
    buttons: ['Pulihkan', 'Batal'],
    defaultId: 1,
    cancelId: 1,
    message: 'Semua data saat ini akan diganti dengan data dari backup.',
    detail: 'Sebaiknya buat backup data saat ini dulu. Server akan dimulai ulang.',
  });
  if (confirm !== 0) return;
  showStatus('Memulihkan data…');
  try {
    await server.stop();
    server.restore(res.filePaths[0]);
  } catch (err) {
    dialog.showErrorBox('Pemulihan gagal', err.message);
  }
  await startServerMode();
}

// ------------------------------------------------------------------ siklus aplikasi

if (!app.requestSingleInstanceLock()) {
  app.quit();
} else {
  app.on('second-instance', () => {
    if (mainWindow) {
      if (mainWindow.isMinimized()) mainWindow.restore();
      mainWindow.focus();
    }
  });

  app.whenReady().then(() => {
    loadConfig();
    setupPermissions();
    createMainWindow();
    if (config.mode === 'server') startServerMode();
    else if (config.mode === 'client' && config.serverUrl) startClientMode();
    else showSetup();
  });

  app.on('before-quit', () => {
    quitting = true;
  });

  app.on('will-quit', (e) => {
    if (server) {
      e.preventDefault();
      const s = server;
      server = null;
      s.stop().finally(() => app.quit());
    }
  });

  app.on('window-all-closed', () => app.quit());
}
