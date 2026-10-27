import { defineConfig } from "cypress";
import { spawnSync } from "child_process";

const APP_DIR = "C:\\xampp3\\htdocs\\skripsi\\savve-app";

function runMigration() {
  return spawnSync(
    "php",
    ["artisan", "migrate:fresh", "--seed", "--force"],
    {
      cwd: APP_DIR,
      encoding: "utf8",
      timeout: 90000,
    }
  );
}

function sleepSync(ms) {
  const start = Date.now();
  while (Date.now() - start < ms) { /* busy wait */ }
}

export default defineConfig({
  e2e: {
    baseUrl: "http://127.0.0.1:8000",
    defaultCommandTimeout: 10000,
    pageLoadTimeout: 30000,
    setupNodeEvents(on, config) {
      on("task", {
        resetDb() {
          // 1. Hapus semua session Laravel agar tidak ada stale session/CSRF
          spawnSync(
            "cmd",
            ["/c", "if exist storage\\framework\\sessions\\* del /f /q storage\\framework\\sessions\\*"],
            { cwd: APP_DIR, encoding: "utf8", shell: true }
          );

          // 2. Jalankan migrate:fresh --seed
          let result = runMigration();

          // 3. Retry sekali jika gagal (race condition MySQL)
          if (result.status !== 0) {
            sleepSync(3000);
            result = runMigration();
          }

          if (result.status !== 0) {
            const errMsg = (result.stderr || "") + "\n" + (result.stdout || "");
            throw new Error("migrate:fresh gagal:\n" + errMsg.trim());
          }

          return null;
        },
      });
      return config;
    },
  },
});