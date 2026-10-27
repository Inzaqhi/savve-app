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
    if (url.startsWith('https://127.0.0.1') || (config.serverUrl && url.startsWith(config.serverUrl))) {
      return { action: 'allow' };
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

function buildMenu() {
  const serverMenu = config.mode === 'server'
    ? [{
      label: 'Server',
      submenu: [
        { label: 'Sambungkan HP / Perangkat Lain…', accelerator: 'F2', click: openInfoWindow },
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
