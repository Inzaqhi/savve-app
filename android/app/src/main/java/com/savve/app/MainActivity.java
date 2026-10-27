package com.savve.app;

import android.Manifest;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.ContentValues;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Bitmap;
import android.net.Uri;
import android.net.http.SslCertificate;
import android.net.http.SslError;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Looper;
import android.print.PrintAttributes;
import android.print.PrintDocumentAdapter;
import android.print.PrintManager;
import android.provider.MediaStore;
import android.util.Base64;
import android.webkit.CookieManager;
import android.webkit.JavascriptInterface;
import android.webkit.PermissionRequest;
import android.webkit.SslErrorHandler;
import android.webkit.URLUtil;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.ByteArrayInputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.OutputStream;
import java.security.cert.X509Certificate;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

import javax.net.ssl.HttpsURLConnection;

public class MainActivity extends Activity {
    private static final String SETUP_URL = "file:///android_asset/setup.html";
    private static final String OFFLINE_URL = "file:///android_asset/offline.html";
    private static final int REQ_CAMERA = 10;
    private static final int REQ_FILE = 11;
    private static final int REQ_STORAGE = 12;

    /** Disuntikkan ke setiap halaman Savve: cetak & unduhan blob lewat Android. */
    private static final String INJECT_JS = "(function(){"
            + "if(window.__savveAndroid)return;window.__savveAndroid=true;"
            // window.print(): salin ukuran kertas dinamis nota agar tetap berlaku saat Android mencetak
            + "window.print=function(){"
            + " var d=document.getElementById('dynamic-page-size'),p=document.getElementById('savve-print-size');"
            + " if(p)p.remove();"
            + " if(d){p=document.createElement('style');p.id='savve-print-size';p.textContent=d.textContent;document.head.appendChild(p);}"
            + " SavveAndroid.print(document.title||'Savve');};"
            // unduhan blob:/data: (mis. tombol ekspor DataTables/pdfmake)
            + "function save(href,name){fetch(href).then(function(r){return r.blob();}).then(function(b){"
            + " var fr=new FileReader();fr.onloadend=function(){"
            + "  SavveAndroid.saveBase64(name||'savve-file',b.type||'application/octet-stream',String(fr.result).split(',')[1]||'');};"
            + " fr.readAsDataURL(b);});}"
            + "function isFile(a){return a&&a.hasAttribute&&a.hasAttribute('download')&&/^(blob|data):/.test(a.href||'');}"
            + "var oc=HTMLAnchorElement.prototype.click;"
            + "HTMLAnchorElement.prototype.click=function(){if(isFile(this)){save(this.href,this.download);return;}return oc.call(this);};"
            + "var od=HTMLAnchorElement.prototype.dispatchEvent;"
            + "HTMLAnchorElement.prototype.dispatchEvent=function(e){if(e&&e.type==='click'&&isFile(this)){save(this.href,this.download);return true;}return od.call(this,e);};"
            + "document.addEventListener('click',function(e){var a=e.target&&e.target.closest?e.target.closest('a'):null;"
            + " if(isFile(a)){e.preventDefault();save(a.href,a.download);}},true);"
            + "})();";

    private WebView webView;
    private ServerConfig config;
    private final Handler main = new Handler(Looper.getMainLooper());
    private final ExecutorService io = Executors.newCachedThreadPool();
    private PermissionRequest pendingPermission;
    private ValueCallback<Uri[]> fileCallback;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        config = new ServerConfig(this);

        webView = new WebView(this);
        setContentView(webView);

        WebSettings s = webView.getSettings();
        s.setJavaScriptEnabled(true);
        s.setDomStorageEnabled(true);
        s.setMediaPlaybackRequiresUserGesture(false);
        s.setAllowFileAccess(false);
        s.setAllowContentAccess(false);
        s.setLoadWithOverviewMode(true);
        s.setUseWideViewPort(true);
        s.setUserAgentString(s.getUserAgentString() + " SavveAndroid/" + BuildConfig.VERSION_NAME);

        CookieManager.getInstance().setAcceptCookie(true);
        webView.addJavascriptInterface(new Bridge(), "SavveAndroid");
        webView.setWebViewClient(new Client());
        webView.setWebChromeClient(new Chrome());
        webView.setDownloadListener((url, userAgent, contentDisposition, mimeType, length) ->
                onDownload(url, userAgent, contentDisposition, mimeType));

        if (savedInstanceState != null && webView.restoreState(savedInstanceState) != null) return;
        openStart();
    }

    private void openStart() {
        String url = config.getUrl();
        if (url == null) webView.loadUrl(SETUP_URL);
        else webView.loadUrl(url + "/");
    }

    private void showOffline(String message) {
        webView.loadUrl(OFFLINE_URL + "?error=" + Uri.encode(message == null ? "" : message)
                + "&server=" + Uri.encode(config.getUrl() == null ? "" : config.getUrl()));
    }

    private void toast(String message) {
        main.post(() -> Toast.makeText(this, message, Toast.LENGTH_LONG).show());
    }

    private void js(String script) {
        main.post(() -> webView.evaluateJavascript(script, null));
    }

    // ------------------------------------------------------------------ WebView

    private boolean certificateMatches(SslCertificate certificate) {
        String expected = config.getFingerprint();
        if (expected == null || certificate == null) return false;
        byte[] der = null;
        if (Build.VERSION.SDK_INT >= 29) {
            X509Certificate x509 = certificate.getX509Certificate();
            if (x509 != null) {
                try {
                    der = x509.getEncoded();
                } catch (Exception ignored) {
                    // jatuh ke cara lama di bawah
                }
            }
        }
        if (der == null) der = SslCertificate.saveState(certificate).getByteArray("x509-certificate");
        return der != null && expected.equalsIgnoreCase(LanTrust.sha256Hex(der));
    }

    private class Client extends WebViewClient {
        @Override
        public void onReceivedSslError(WebView view, SslErrorHandler handler, SslError error) {
            if (config.isServerUrl(error.getUrl()) && certificateMatches(error.getCertificate())) {
                handler.proceed();
                return;
            }
            handler.cancel();
            if (config.isServerUrl(error.getUrl())) {
                showOffline("Sertifikat keamanan server berubah. Jika aplikasi Savve di komputer utama "
                        + "baru diinstal ulang, tekan \"Ganti Server\" lalu sambungkan lagi.");
            }
        }

        @Override
        public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
            Uri uri = request.getUrl();
            String url = uri.toString();
            if (url.startsWith("file:///android_asset/") || config.isServerUrl(url)) return false;
            // Tautan luar (mis. wa.me, tel:) dibuka di aplikasi lain
            try {
                startActivity(new Intent(Intent.ACTION_VIEW, uri));
            } catch (Exception e) {
                toast("Tidak ada aplikasi untuk membuka tautan ini.");
            }
            return true;
        }

        @Override
        public void onPageStarted(WebView view, String url, Bitmap favicon) {
            if (config.isServerUrl(url)) view.evaluateJavascript(INJECT_JS, null);
        }

        @Override
        public void onPageFinished(WebView view, String url) {
            if (config.isServerUrl(url)) view.evaluateJavascript(INJECT_JS, null);
            CookieManager.getInstance().flush();
        }

        @Override
        public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
            if (request.isForMainFrame() && config.isServerUrl(request.getUrl().toString())) {
                showOffline(String.valueOf(error.getDescription()));
            }
        }
    }

    private class Chrome extends WebChromeClient {
        @Override
        public void onPermissionRequest(PermissionRequest request) {
            main.post(() -> {
                boolean wantsCamera = false;
                for (String r : request.getResources()) {
                    if (PermissionRequest.RESOURCE_VIDEO_CAPTURE.equals(r)) wantsCamera = true;
                }
                if (!wantsCamera || !config.isServerUrl(request.getOrigin().toString())) {
                    request.deny();
                    return;
                }
                if (checkSelfPermission(Manifest.permission.CAMERA) == PackageManager.PERMISSION_GRANTED) {
                    request.grant(new String[] {PermissionRequest.RESOURCE_VIDEO_CAPTURE});
                } else {
                    if (pendingPermission != null) pendingPermission.deny();
                    pendingPermission = request;
                    requestPermissions(new String[] {Manifest.permission.CAMERA}, REQ_CAMERA);
                }
            });
        }

        @Override
        public boolean onShowFileChooser(WebView view, ValueCallback<Uri[]> callback, FileChooserParams params) {
            if (fileCallback != null) fileCallback.onReceiveValue(null);
            fileCallback = callback;
            try {
                startActivityForResult(params.createIntent(), REQ_FILE);
            } catch (Exception e) {
                fileCallback = null;
                toast("Tidak ada aplikasi galeri.");
                return false;
            }
            return true;
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        boolean granted = grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED;
        if (requestCode == REQ_CAMERA && pendingPermission != null) {
            if (granted) pendingPermission.grant(new String[] {PermissionRequest.RESOURCE_VIDEO_CAPTURE});
            else {
                pendingPermission.deny();
                toast("Izin kamera ditolak. Aktifkan di Pengaturan > Aplikasi > Savve > Izin.");
            }
            pendingPermission = null;
        } else if (requestCode == REQ_STORAGE && granted) {
            toast("Izin penyimpanan diberikan. Silakan unduh lagi.");
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == REQ_FILE && fileCallback != null) {
            fileCallback.onReceiveValue(WebChromeClient.FileChooserParams.parseResult(resultCode, data));
            fileCallback = null;
        }
    }

    // ------------------------------------------------------------------ unduhan

    private void onDownload(String url, String userAgent, String contentDisposition, String mimeType) {
        if (url.startsWith("blob:") || url.startsWith("data:")) {
            String name = URLUtil.guessFileName(url, contentDisposition, mimeType);
            webView.evaluateJavascript("(function(){fetch(" + JSONObject.quote(url) + ").then(function(r){return r.blob();})"
                    + ".then(function(b){var fr=new FileReader();fr.onloadend=function(){SavveAndroid.saveBase64("
                    + JSONObject.quote(name) + ",b.type||'application/octet-stream',String(fr.result).split(',')[1]||'');};"
                    + "fr.readAsDataURL(b);});})();", null);
            return;
        }
        if (!config.isServerUrl(url)) {
            try {
                startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(url)));
            } catch (Exception ignored) {
                toast("Tidak bisa mengunduh berkas ini.");
            }
            return;
        }
        toast("Mengunduh…");
        io.execute(() -> {
            HttpsURLConnection conn = null;
            try {
                conn = LanTrust.open(url, config.getFingerprint(), 15000);
                conn.setReadTimeout(120000);
                String cookie = CookieManager.getInstance().getCookie(url);
                if (cookie != null) conn.setRequestProperty("Cookie", cookie);
                conn.setRequestProperty("User-Agent", userAgent);
                int code = conn.getResponseCode();
                if (code != 200) throw new IllegalStateException("HTTP " + code);
                String disposition = conn.getHeaderField("Content-Disposition");
                String type = conn.getContentType() != null ? conn.getContentType() : mimeType;
                String name = URLUtil.guessFileName(url, disposition != null ? disposition : contentDisposition, type);
                saveToDownloads(name, type, conn.getInputStream());
            } catch (Exception e) {
                toast("Gagal mengunduh: " + e.getMessage());
            } finally {
                if (conn != null) conn.disconnect();
            }
        });
    }

    /** Simpan ke folder Download/Savve. Dipanggil dari thread latar. */
    private void saveToDownloads(String name, String mime, InputStream in) throws Exception {
        if (mime != null && mime.contains(";")) mime = mime.substring(0, mime.indexOf(';')).trim();
        Uri openUri = null;
        try (InputStream source = in) {
            if (Build.VERSION.SDK_INT >= 29) {
                ContentValues values = new ContentValues();
                values.put(MediaStore.MediaColumns.DISPLAY_NAME, name);
                if (mime != null) values.put(MediaStore.MediaColumns.MIME_TYPE, mime);
                values.put(MediaStore.MediaColumns.RELATIVE_PATH, Environment.DIRECTORY_DOWNLOADS + "/Savve");
                Uri uri = getContentResolver().insert(MediaStore.Downloads.EXTERNAL_CONTENT_URI, values);
                if (uri == null) throw new IllegalStateException("penyimpanan tidak tersedia");
                try (OutputStream out = getContentResolver().openOutputStream(uri)) {
                    copy(source, out);
                }
                openUri = uri;
            } else {
                if (checkSelfPermission(Manifest.permission.WRITE_EXTERNAL_STORAGE) != PackageManager.PERMISSION_GRANTED) {
                    main.post(() -> requestPermissions(
                            new String[] {Manifest.permission.WRITE_EXTERNAL_STORAGE}, REQ_STORAGE));
                    throw new IllegalStateException("izinkan akses penyimpanan lalu coba lagi");
                }
                File dir = new File(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS), "Savve");
                if (!dir.exists() && !dir.mkdirs()) throw new IllegalStateException("folder Download tidak bisa dibuat");
                File file = new File(dir, name);
                try (OutputStream out = new FileOutputStream(file)) {
                    copy(source, out);
                }
                android.media.MediaScannerConnection.scanFile(this, new String[] {file.getAbsolutePath()}, null, null);
            }
        }
        toast("Tersimpan di Download/Savve/" + name);
        if (openUri != null) {
            Intent view = new Intent(Intent.ACTION_VIEW).setDataAndType(openUri, mime)
                    .addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION | Intent.FLAG_ACTIVITY_NEW_TASK);
            main.post(() -> {
                try {
                    startActivity(view);
                } catch (Exception ignored) {
                    // tidak ada aplikasi untuk membuka berkas ini; berkas tetap tersimpan
                }
            });
        }
    }

    private static void copy(InputStream in, OutputStream out) throws Exception {
        byte[] buf = new byte[16384];
        int n;
        while ((n = in.read(buf)) > 0) out.write(buf, 0, n);
    }

    // ------------------------------------------------------------------ jembatan JavaScript

    private class Bridge {
        @JavascriptInterface
        public String getServer() {
            String url = config.getUrl();
            return url == null ? "" : url.replace("https://", "");
        }

        @JavascriptInterface
        public void discover() {
            io.execute(() -> {
                List<LanTrust.PingResult> found = ServerDiscovery.scan(MainActivity.this);
                JSONArray arr = new JSONArray();
                for (LanTrust.PingResult r : found) arr.put(r.url.replace("https://", ""));
                JSONArray own = new JSONArray();
                for (String ip : ServerDiscovery.localAddresses(MainActivity.this)) own.put(ip);
                js("window.onSavveDiscovered && window.onSavveDiscovered(" + arr + "," + own + ")");
            });
        }

        @JavascriptInterface
        public void connect(String input) {
            String url = ServerConfig.normalize(input);
            if (url == null) {
                js("window.onSavveConnectError && window.onSavveConnectError('Alamat server tidak valid.')");
                return;
            }
            io.execute(() -> {
                LanTrust.PingResult r = LanTrust.ping(url, 4000);
                if (r == null || r.fingerprint == null) {
                    js("window.onSavveConnectError && window.onSavveConnectError("
                            + JSONObject.quote("Server Savve tidak ditemukan di " + url.replace("https://", "")
                            + ". Pastikan aplikasi Savve di komputer utama terbuka dan HP tersambung ke WiFi yang sama.")
                            + ")");
                    return;
                }
                config.save(url, r.fingerprint);
                main.post(() -> {
                    webView.clearSslPreferences();
                    webView.clearHistory();
                    webView.loadUrl(url + "/");
                });
            });
        }

        @JavascriptInterface
        public void retry() {
            main.post(MainActivity.this::openStart);
        }

        @JavascriptInterface
        public void changeServer() {
            main.post(() -> webView.loadUrl(SETUP_URL));
        }

        @JavascriptInterface
        public void print(String title) {
            main.post(() -> {
                PrintManager pm = (PrintManager) getSystemService(PRINT_SERVICE);
                String job = (title == null || title.isEmpty()) ? "Savve" : title;
                PrintDocumentAdapter adapter = webView.createPrintDocumentAdapter(job);
                pm.print(job, adapter, new PrintAttributes.Builder().build());
            });
        }

        @JavascriptInterface
        public void saveBase64(String name, String mime, String base64) {
            io.execute(() -> {
                try {
                    String safe = (name == null || name.trim().isEmpty()) ? "savve-file" : name.replaceAll("[\\\\/:*?\"<>|]", "_");
                    saveToDownloads(safe, mime, new ByteArrayInputStream(Base64.decode(base64, Base64.DEFAULT)));
                } catch (Exception e) {
                    toast("Gagal menyimpan berkas: " + e.getMessage());
                }
            });
        }
    }

    // ------------------------------------------------------------------ siklus

    @Override
    public void onBackPressed() {
        if (webView.canGoBack()) {
            webView.goBack();
            return;
        }
        new AlertDialog.Builder(this)
                .setTitle("Savve")
                .setItems(new String[] {"Keluar dari aplikasi", "Ganti server", "Batal"}, (d, which) -> {
                    if (which == 0) finish();
                    else if (which == 1) webView.loadUrl(SETUP_URL);
                })
                .show();
    }

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);
        webView.saveState(outState);
    }

    @Override
    protected void onResume() {
        super.onResume();
        webView.onResume();
    }

    @Override
    protected void onPause() {
        webView.onPause();
        CookieManager.getInstance().flush();
        super.onPause();
    }

    @Override
    protected void onDestroy() {
        io.shutdownNow();
        webView.destroy();
        super.onDestroy();
    }
}
