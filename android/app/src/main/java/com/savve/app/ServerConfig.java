package com.savve.app;

import android.content.Context;
import android.content.SharedPreferences;
import android.net.Uri;

/** Alamat server Savve dan sidik jari sertifikatnya, disimpan di perangkat. */
final class ServerConfig {
    private static final String PREFS = "savve";
    private static final String KEY_URL = "server_url";
    private static final String KEY_FINGERPRINT = "server_fingerprint";

    private final SharedPreferences prefs;

    ServerConfig(Context context) {
        prefs = context.getSharedPreferences(PREFS, Context.MODE_PRIVATE);
    }

    /** contoh: https://192.168.1.10:8443 (tanpa garis miring di akhir), atau null */
    String getUrl() {
        return prefs.getString(KEY_URL, null);
    }

    String getFingerprint() {
        return prefs.getString(KEY_FINGERPRINT, null);
    }

    String getHost() {
        String url = getUrl();
        return url == null ? null : Uri.parse(url).getHost();
    }

    int getPort() {
        String url = getUrl();
        return url == null ? -1 : Uri.parse(url).getPort();
    }

    void save(String url, String fingerprint) {
        prefs.edit().putString(KEY_URL, url).putString(KEY_FINGERPRINT, fingerprint).apply();
    }

    void clear() {
        prefs.edit().clear().apply();
    }

    /** Apakah url ini menuju server Savve yang tersimpan (host dan port sama). */
    boolean isServerUrl(String url) {
        String saved = getUrl();
        if (saved == null || url == null) return false;
        Uri a = Uri.parse(saved);
        Uri b = Uri.parse(url);
        return "https".equals(b.getScheme())
                && a.getHost() != null && a.getHost().equalsIgnoreCase(b.getHost())
                && a.getPort() == b.getPort();
    }

    /**
     * Ubah input pengguna ("192.168.1.10", "192.168.1.10:8443", "https://...") menjadi
     * https://host:port. Mengembalikan null jika tidak valid.
     */
    static String normalize(String input) {
        if (input == null) return null;
        String s = input.trim();
        if (s.isEmpty()) return null;
        if (!s.contains("://")) s = "https://" + s;
        Uri uri = Uri.parse(s);
        String host = uri.getHost();
        if (host == null || host.isEmpty()) return null;
        int port = uri.getPort() > 0 ? uri.getPort() : 8443;
        return "https://" + host + ":" + port;
    }
}
