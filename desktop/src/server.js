'use strict';

// Server Savve untuk WiFi lokal:
//   - beberapa proses `php -S` menjalankan Laravel di 127.0.0.1
//   - satu server HTTPS (sertifikat self-signed) di 0.0.0.0 meneruskan request ke proses PHP
//   - foto di storage/app/public disajikan langsung sebagai /storage/...
// Modul ini tidak bergantung pada Electron supaya bisa diuji dengan Node biasa.

const { spawn, execFile } = require('child_process');
const crypto = require('crypto');
const fs = require('fs');
const http = require('http');
const https = require('https');
const net = require('net');
const os = require('os');
const path = require('path');

const MIME = {
  '.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.gif': 'image/gif',
  '.webp': 'image/webp', '.svg': 'image/svg+xml', '.pdf': 'application/pdf',
};

const WINDOWS_PHP_EXTENSIONS = [
  'curl', 'fileinfo', 'gd', 'mbstring', 'openssl', 'pdo_sqlite', 'sqlite3', 'zip', 'intl',
];

function readJson(file, fallback) {
  try {
    return JSON.parse(fs.readFileSync(file, 'utf8'));
  } catch {
    return fallback;
  }
}

function freePort() {
  return new Promise((resolve, reject) => {
    const srv = net.createServer();
    srv.unref();
    srv.on('error', reject);
    srv.listen(0, '127.0.0.1', () => {
      const { port } = srv.address();
      srv.close(() => resolve(port));
    });
  });
}

function lanAddresses() {
  const result = [];
  for (const [name, list] of Object.entries(os.networkInterfaces())) {
    for (const addr of list || []) {
      if (addr.family === 'IPv4' && !addr.internal) result.push({ name, address: addr.address });
    }
  }
  // Jaringan WiFi/LAN biasa (192.168.x.x, 10.x.x.x) ditampilkan paling atas
  const score = (a) => (a.address.startsWith('192.168.') ? 0 : a.address.startsWith('10.') ? 1 : 2);
  return result.sort((a, b) => score(a) - score(b));
}

class SavveServer {
  /**
   * @param {object} opts
   * @param {string} opts.phpBinary   path php / php.exe
   * @param {string} opts.laravelDir  folder Laravel (boleh read-only)
   * @param {string} opts.dataDir     folder data yang bisa ditulis (database, foto, log, sertifikat)
   * @param {number} [opts.port]      port HTTPS untuk perangkat di WiFi
   * @param {number} [opts.httpPort]  port HTTP yang mengalihkan ke HTTPS
   * @param {number} [opts.workers]   jumlah proses PHP
   * @param {boolean} [opts.bundledPhp] true jika memakai PHP Windows yang dibundel (muat ekstensi)
   * @param {function} [opts.log]
   */
  constructor(opts) {
    this.phpBinary = opts.phpBinary;
    this.laravelDir = opts.laravelDir;
    this.dataDir = opts.dataDir;
    this.port = opts.port || 8443;
    this.httpPort = opts.httpPort === undefined ? 8080 : opts.httpPort;
    this.workerCount = opts.workers || 4;
    this.bundledPhp = !!opts.bundledPhp;
    this.version = opts.version || '1.0.0';
    this.log = opts.log || ((...a) => console.log('[savve]', ...a));
    // Dipanggil saat Laravel meminta nota dicetak di komputer ini: async (job) => ({ ok, message })
    this.onPrintJob = opts.onPrintJob || null;

    this.storageDir = path.join(this.dataDir, 'storage');
    this.dbFile = path.join(this.dataDir, 'savve.sqlite');
    this.stateFile = path.join(this.dataDir, 'server.json');

    this.workers = [];
    this.nextWorker = 0;
    this.httpsServer = null;
    this.httpServer = null;
  }

  // ---------------------------------------------------------------- persiapan

  ensureDirs() {
    for (const dir of [
      'app/public', 'app/private', 'framework/cache/data', 'framework/sessions',
      'framework/views', 'logs',
    ]) {
      fs.mkdirSync(path.join(this.storageDir, dir), { recursive: true });
    }
    if (!fs.existsSync(this.dbFile)) fs.writeFileSync(this.dbFile, '');
  }

  loadState() {
    this.state = readJson(this.stateFile, {});
    if (!this.state.appKey) {
      this.state.appKey = 'base64:' + crypto.randomBytes(32).toString('base64');
      this.saveState();
    }
  }

  saveState() {
    fs.writeFileSync(this.stateFile, JSON.stringify(this.state, null, 2));
  }

  async ensureCertificate() {
    const certFile = path.join(this.dataDir, 'cert.pem');
    const keyFile = path.join(this.dataDir, 'key.pem');
    if (!fs.existsSync(certFile) || !fs.existsSync(keyFile)) {
      this.log('Membuat sertifikat HTTPS lokal...');
      const selfsigned = require('selfsigned');
      const notAfter = new Date();
      notAfter.setFullYear(notAfter.getFullYear() + 10);
      const pems = await selfsigned.generate([{ name: 'commonName', value: 'Savve Local Server' }], {
        keySize: 2048,
        algorithm: 'sha256',
        notAfterDate: notAfter,
      });
      fs.writeFileSync(certFile, pems.cert);
      fs.writeFileSync(keyFile, pems.private);
    }
    this.cert = fs.readFileSync(certFile, 'utf8');
    this.key = fs.readFileSync(keyFile, 'utf8');
    const der = new crypto.X509Certificate(this.cert).raw;
    // Format sama dengan certificate.fingerprint di Electron
    this.fingerprint = 'sha256/' + crypto.createHash('sha256').update(der).digest('base64');
    // Format hex untuk aplikasi Android
    this.fingerprintHex = crypto.createHash('sha256').update(der).digest('hex');
  }

  phpEnv() {
    return {
      ...process.env,
      APP_NAME: 'Savve',
      APP_ENV: 'production',
      APP_DEBUG: 'false',
      APP_KEY: this.state.appKey,
      APP_URL: `https://localhost:${this.port}`,
      APP_TIMEZONE: 'Asia/Jakarta',
      APP_LOCALE: 'id',
      APP_FALLBACK_LOCALE: 'en',
      LARAVEL_STORAGE_PATH: this.storageDir,
      SAVVE_DESKTOP: 'true',
      // bootstrap/cache (packages.php, services.php) sudah dibuat saat build oleh prepare.mjs,
      // karena folder instalasi read-only. APP_*_CACHE tidak dipakai: Laravel tidak mengenali
      // path absolut Windows (C:\...) di sana dan menggabungkannya dengan folder Laravel.
      DB_CONNECTION: 'sqlite',
      DB_DATABASE: this.dbFile,
      DB_BUSY_TIMEOUT: '15000',
      DB_JOURNAL_MODE: 'wal',
      DB_SYNCHRONOUS: 'normal',
      // Kunci tulis di awal transaksi: pengganti lockForUpdate() (tidak ada di SQLite)
      // agar dua kasir yang menyimpan bersamaan tidak mendapat nomor transaksi yang sama.
      DB_TRANSACTION_MODE: 'IMMEDIATE',
      SESSION_DRIVER: 'database',
      SESSION_LIFETIME: '720',
      CACHE_STORE: 'database',
      QUEUE_CONNECTION: 'sync',
      FILESYSTEM_DISK: 'local',
      LOG_CHANNEL: 'single',
      LOG_LEVEL: 'warning',
      MAIL_MAILER: 'log',
      BROADCAST_CONNECTION: 'log',
    };
  }

  phpArgs() {
    const args = [
      '-d', 'variables_order=EGPCS',
      '-d', 'memory_limit=512M',
      '-d', 'upload_max_filesize=25M',
      '-d', 'post_max_size=30M',
      '-d', 'max_execution_time=120',
      '-d', 'display_errors=0',
    ];
    if (this.bundledPhp) {
      args.push('-n', '-d', `extension_dir=${path.join(path.dirname(this.phpBinary), 'ext')}`);
      for (const ext of WINDOWS_PHP_EXTENSIONS) args.push('-d', `extension=${ext}`);
      args.push('-d', 'opcache.enable_cli=0');
    }
    return args;
  }

  artisan(args) {
    return new Promise((resolve, reject) => {
      execFile(
        this.phpBinary,
        [...this.phpArgs(), path.join(this.laravelDir, 'artisan'), ...args, '--no-interaction'],
        { cwd: this.laravelDir, env: this.phpEnv(), windowsHide: true, maxBuffer: 10 * 1024 * 1024 },
        (err, stdout, stderr) => {
          if (err) {
            err.message += `\n${stdout}\n${stderr}`;
            reject(err);
          } else {
            resolve(stdout);
          }
        },
      );
    });
  }

  async migrate() {
    this.log('Menyiapkan database...');
    await this.artisan(['migrate', '--force']);
    if (!this.state.seeded) {
      this.log('Mengisi data awal (akun admin & kasir)...');
      await this.artisan(['db:seed', '--class=Database\\Seeders\\DesktopSeeder', '--force']);
      this.state.seeded = true;
      this.saveState();
    }
  }

  // ---------------------------------------------------------------- proses PHP

  async startWorkers() {
    const router = path.join(
      this.laravelDir, 'vendor', 'laravel', 'framework', 'src', 'Illuminate', 'Foundation', 'resources', 'server.php',
    );
    for (let i = 0; i < this.workerCount; i++) {
      const port = await freePort();
      const worker = { port, proc: null, ready: false };
      this.workers.push(worker);
      this.spawnWorker(worker, router);
    }
    await Promise.all(this.workers.map((w) => this.waitForPort(w.port)));
    this.workers.forEach((w) => { w.ready = true; });
  }

  spawnWorker(worker, router) {
    const proc = spawn(
      this.phpBinary,
      [...this.phpArgs(), '-S', `127.0.0.1:${worker.port}`, router],
      { cwd: path.join(this.laravelDir, 'public'), env: this.phpEnv(), windowsHide: true },
    );
    worker.proc = proc;
    proc.stdout.on('data', () => {});
    proc.stderr.on('data', () => {}); // php -S mencatat setiap request ke stderr
    proc.on('exit', (code) => {
      if (worker.stopped) return;
      this.log(`Proses PHP di port ${worker.port} berhenti (kode ${code}), menjalankan ulang...`);
      worker.ready = false;
      setTimeout(() => {
        this.spawnWorker(worker, router);
        this.waitForPort(worker.port).then(() => { worker.ready = true; }).catch(() => {});
      }, 1000);
    });
  }

  waitForPort(port, timeoutMs = 20000) {
    const started = Date.now();
    return new Promise((resolve, reject) => {
      const tryOnce = () => {
        const sock = net.connect(port, '127.0.0.1');
        sock.once('connect', () => { sock.destroy(); resolve(); });
        sock.once('error', () => {
          sock.destroy();
          if (Date.now() - started > timeoutMs) reject(new Error(`PHP tidak merespons di port ${port}`));
          else setTimeout(tryOnce, 150);
        });
      };
      tryOnce();
    });
  }

  pickWorker() {
    for (let i = 0; i < this.workers.length; i++) {
      const w = this.workers[this.nextWorker];
      this.nextWorker = (this.nextWorker + 1) % this.workers.length;
      if (w.ready) return w;
    }
    return null;
  }

  // ---------------------------------------------------------------- HTTP(S)

  handle(req, res) {
    const url = new URL(req.url, 'https://savve.local');

    if (url.pathname === '/__savve/ping') {
      res.writeHead(200, {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'Cache-Control': 'no-store',
      });
      res.end(JSON.stringify({ app: 'savve', version: this.version, fingerprint: this.fingerprintHex }));
      return;
    }

    if (url.pathname.startsWith('/storage/') && (req.method === 'GET' || req.method === 'HEAD')) {
      this.serveStorage(url.pathname, req, res);
      return;
    }

    this.proxy(req, res, 0);
  }

  serveStorage(pathname, req, res) {
    const root = path.join(this.storageDir, 'app', 'public');
    let rel;
    try {
      rel = decodeURIComponent(pathname.slice('/storage/'.length));
    } catch {
      res.writeHead(400).end();
      return;
    }
    const file = path.resolve(root, rel);
    if (!file.startsWith(root + path.sep)) {
      res.writeHead(403).end();
      return;
    }
    fs.stat(file, (err, stat) => {
      if (err || !stat.isFile()) {
        res.writeHead(404, { 'Content-Type': 'text/plain' }).end('Not found');
        return;
      }
      res.writeHead(200, {
        'Content-Type': MIME[path.extname(file).toLowerCase()] || 'application/octet-stream',
        'Content-Length': stat.size,
        'Cache-Control': 'private, max-age=86400',
      });
      if (req.method === 'HEAD') res.end();
      else fs.createReadStream(file).pipe(res);
    });
  }

  proxy(req, res, attempt) {
    const worker = this.pickWorker();
    if (!worker) {
      res.writeHead(503, { 'Content-Type': 'text/plain; charset=utf-8' });
      res.end('Server Savve sedang memulai ulang, coba lagi sebentar.');
      return;
    }
    const headers = { ...req.headers };
    headers['x-forwarded-proto'] = 'https';
    headers['x-forwarded-port'] = String(this.port);
    headers['x-forwarded-for'] = req.socket.remoteAddress || '';
    delete headers['x-forwarded-host'];

    const upstream = http.request(
      { host: '127.0.0.1', port: worker.port, method: req.method, path: req.url, headers },
      (up) => {
        if (up.headers['x-savve-print-job']) {
          this.handlePrintJob(up, res);
          return;
        }
        res.writeHead(up.statusCode, up.statusMessage, up.headers);
        up.pipe(res);
      },
    );
    upstream.on('error', (err) => {
      // Hanya ulangi request yang belum mengirim body (GET/HEAD) jika proses PHP sedang mati
      if (!res.headersSent && attempt < this.workers.length && (req.method === 'GET' || req.method === 'HEAD')) {
        worker.ready = false;
        this.waitForPort(worker.port).then(() => { worker.ready = true; }).catch(() => {});
        this.proxy(req, res, attempt + 1);
        return;
      }
      this.log('Gagal meneruskan request:', err.message);
      if (!res.headersSent) res.writeHead(502, { 'Content-Type': 'text/plain; charset=utf-8' });
      res.end('Server Savve tidak merespons.');
    });
    req.pipe(upstream);
  }

  /**
   * Laravel (route kasir.transaksi.cetak-server) sudah memeriksa login kasir dan menyertakan tautan
   * nota bertanda tangan di header X-Savve-Print-Job. Nota dicetak di komputer ini, lalu isi respons
   * diganti dengan hasilnya. Header ini tidak pernah diteruskan ke perangkat kasir.
   */
  async handlePrintJob(up, res) {
    up.resume();
    const headers = { ...up.headers };
    delete headers['x-savve-print-job'];
    delete headers['content-length'];
    delete headers['transfer-encoding'];
    headers['content-type'] = 'application/json';

    let result;
    if (up.statusCode !== 200 || !this.onPrintJob) {
      result = { ok: false, message: 'Fitur cetak di komputer server tidak tersedia.' };
    } else {
      try {
        const job = JSON.parse(Buffer.from(String(up.headers['x-savve-print-job']), 'base64').toString('utf8'));
        if (typeof job.url !== 'string' || !job.url.startsWith('/nota-cetak/')) throw new Error('tautan nota tidak valid');
        result = await Promise.race([
          this.onPrintJob(job),
          new Promise((r) => setTimeout(() => r({ ok: false, message: 'Komputer server terlalu lama mencetak. Periksa printer.' }), 90000)),
        ]);
      } catch (err) {
        this.log('Gagal mencetak nota:', err.message);
        result = { ok: false, message: err.message };
      }
    }
    const body = JSON.stringify(result);
    headers['content-length'] = Buffer.byteLength(body);
    res.writeHead(200, headers);
    res.end(body);
  }

  listen(server, port) {
    return new Promise((resolve, reject) => {
      server.once('error', reject);
      server.listen(port, '0.0.0.0', () => {
        server.off('error', reject);
        resolve();
      });
    });
  }

  async startHttp() {
    this.httpsServer = https.createServer({ cert: this.cert, key: this.key }, (req, res) => this.handle(req, res));
    this.httpsServer.headersTimeout = 120000;
    this.httpsServer.requestTimeout = 300000;
    await this.listen(this.httpsServer, this.port);

    if (this.httpPort) {
      // Pengguna yang mengetik http://IP:8080 dialihkan ke HTTPS (kamera butuh HTTPS)
      this.httpServer = http.createServer((req, res) => {
        const host = (req.headers.host || 'localhost').replace(/:\d+$/, '');
        res.writeHead(301, { Location: `https://${host}:${this.port}${req.url}` });
        res.end();
      });
      try {
        await this.listen(this.httpServer, this.httpPort);
      } catch (err) {
        this.log(`Port HTTP ${this.httpPort} tidak bisa dipakai (${err.code}), dilewati.`);
        this.httpServer = null;
      }
    }
  }

  // ---------------------------------------------------------------- siklus hidup

  async start() {
    this.ensureDirs();
    this.loadState();
    await this.ensureCertificate();
    await this.migrate();
    await this.startWorkers();
    await this.startHttp();
    this.log(`Server Savve berjalan di https://0.0.0.0:${this.port}`);
  }

  async stop() {
    const exits = [];
    for (const w of this.workers) {
      w.stopped = true;
      w.ready = false;
      if (w.proc && w.proc.exitCode === null && w.proc.signalCode === null) {
        exits.push(new Promise((r) => w.proc.once('exit', r)));
        w.proc.kill();
      }
    }
    this.workers = [];
    const close = (srv) => new Promise((r) => (srv ? srv.close(() => r()) : r()));
    if (this.httpsServer) this.httpsServer.closeAllConnections?.();
    if (this.httpServer) this.httpServer.closeAllConnections?.();
    await Promise.all([close(this.httpsServer), close(this.httpServer), ...exits]);
    this.httpsServer = null;
    this.httpServer = null;
  }

  info() {
    return {
      port: this.port,
      addresses: lanAddresses().map((a) => ({ ...a, url: `https://${a.address}:${this.port}` })),
      fingerprint: this.fingerprintHex,
      dataDir: this.dataDir,
    };
  }

  // ---------------------------------------------------------------- backup

  /** Salin database (konsisten, lewat VACUUM INTO) + foto ke folder tujuan. */
  async backup(targetDir) {
    fs.mkdirSync(targetDir, { recursive: true });
    const dbTarget = path.join(targetDir, 'savve.sqlite');
    if (fs.existsSync(dbTarget)) fs.unlinkSync(dbTarget);
    await new Promise((resolve, reject) => {
      const code = '$db = new PDO("sqlite:" . $argv[1]); $db->exec("VACUUM INTO " . $db->quote($argv[2]));';
      execFile(this.phpBinary, [...this.phpArgs(), '-r', code, this.dbFile, dbTarget], { windowsHide: true },
        (err, _out, stderr) => (err ? reject(new Error(stderr || err.message)) : resolve()));
    });
    fs.cpSync(path.join(this.storageDir, 'app', 'public'), path.join(targetDir, 'foto'), { recursive: true });
    fs.writeFileSync(path.join(targetDir, 'savve-backup.json'), JSON.stringify({
      app: 'savve', version: this.version, createdAt: new Date().toISOString(),
    }, null, 2));
  }

  /** Daftar akun kasir (untuk pengaturan printer per kasir). */
  listKasir() {
    return new Promise((resolve, reject) => {
      const code = '$db = new PDO("sqlite:" . $argv[1]);'
        + 'echo json_encode($db->query("select id, name, email from users where role = \'kasir\' order by name")->fetchAll(PDO::FETCH_ASSOC));';
      execFile(this.phpBinary, [...this.phpArgs(), '-r', code, this.dbFile], { windowsHide: true },
        (err, out, stderr) => {
          if (err) return reject(new Error(stderr || err.message));
          try {
            resolve(JSON.parse(out));
          } catch (e) {
            reject(e);
          }
        });
    });
  }

  /** Kembalikan backup. Server harus dihentikan dulu. */
  restore(sourceDir) {
    const dbSource = path.join(sourceDir, 'savve.sqlite');
    if (!fs.existsSync(path.join(sourceDir, 'savve-backup.json')) || !fs.existsSync(dbSource)) {
      throw new Error('Folder ini bukan backup Savve.');
    }
    for (const suffix of ['', '-wal', '-shm']) fs.rmSync(this.dbFile + suffix, { force: true });
    fs.copyFileSync(dbSource, this.dbFile);
    const fotoTarget = path.join(this.storageDir, 'app', 'public');
    fs.rmSync(fotoTarget, { recursive: true, force: true });
    if (fs.existsSync(path.join(sourceDir, 'foto'))) {
      fs.cpSync(path.join(sourceDir, 'foto'), fotoTarget, { recursive: true });
    } else {
      fs.mkdirSync(fotoTarget, { recursive: true });
    }
    // Database hasil restore sudah berisi akun, jangan di-seed ulang
    this.loadState();
    this.state.seeded = true;
    this.saveState();
  }
}

module.exports = { SavveServer, lanAddresses };
