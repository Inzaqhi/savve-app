package com.savve.app;

import java.io.ByteArrayOutputStream;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.cert.Certificate;
import java.security.cert.X509Certificate;

import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSocketFactory;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

/**
 * Server Savve di WiFi lokal memakai sertifikat self-signed. Sertifikat itu dipercaya
 * berdasarkan sidik jari SHA-256 yang disimpan saat pertama kali tersambung (seperti SSH),
 * bukan dengan mematikan pemeriksaan sertifikat sepenuhnya.
 */
final class LanTrust {
    private LanTrust() {}

    static String sha256Hex(byte[] data) {
        try {
            byte[] digest = MessageDigest.getInstance("SHA-256").digest(data);
            StringBuilder sb = new StringBuilder();
            for (byte b : digest) sb.append(String.format("%02x", b));
            return sb.toString();
        } catch (Exception e) {
            throw new IllegalStateException(e);
        }
    }

    static String fingerprint(X509Certificate cert) {
        try {
            return sha256Hex(cert.getEncoded());
        } catch (Exception e) {
            return null;
        }
    }

    /** TrustManager yang hanya menerima sertifikat dengan sidik jari tertentu. */
    private static final class PinnedTrustManager implements X509TrustManager {
        private final String expected; // null = terima apa saja (hanya untuk mencari server)

        PinnedTrustManager(String expected) {
            this.expected = expected;
        }

        @Override
        public void checkClientTrusted(X509Certificate[] chain, String authType) {}

        @Override
        public void checkServerTrusted(X509Certificate[] chain, String authType)
                throws java.security.cert.CertificateException {
            if (chain == null || chain.length == 0) throw new java.security.cert.CertificateException("no cert");
            if (expected != null && !expected.equalsIgnoreCase(fingerprint(chain[0]))) {
                throw new java.security.cert.CertificateException("Sertifikat server berbeda");
            }
        }

        @Override
        public X509Certificate[] getAcceptedIssuers() {
            return new X509Certificate[0];
        }
    }

    static SSLSocketFactory socketFactory(String expectedFingerprint) {
        try {
            SSLContext ctx = SSLContext.getInstance("TLS");
            ctx.init(null, new TrustManager[] {new PinnedTrustManager(expectedFingerprint)}, null);
            return ctx.getSocketFactory();
        } catch (Exception e) {
            throw new IllegalStateException(e);
        }
    }

    /**
     * Buka koneksi HTTPS ke server Savve. expectedFingerprint null hanya untuk ping
     * (mencari/menambah server); hasilnya tetap dicek lewat {@link PingResult#fingerprint}.
     */
    static HttpsURLConnection open(String url, String expectedFingerprint, int timeoutMs) throws Exception {
        HttpsURLConnection conn = (HttpsURLConnection) new URL(url).openConnection();
        conn.setSSLSocketFactory(socketFactory(expectedFingerprint));
        // Server lokal diakses lewat alamat IP, jadi nama host tidak dicocokkan dengan sertifikat;
        // keaslian server dijamin oleh sidik jari di atas.
        conn.setHostnameVerifier((hostname, session) -> true);
        conn.setConnectTimeout(timeoutMs);
        conn.setReadTimeout(Math.max(timeoutMs, 5000));
        conn.setInstanceFollowRedirects(false);
        return conn;
    }

    static final class PingResult {
        final String url;
        final String fingerprint;

        PingResult(String url, String fingerprint) {
            this.url = url;
            this.fingerprint = fingerprint;
        }
    }

    /** Cek apakah ada server Savve di baseUrl. Mengembalikan null jika bukan server Savve. */
    static PingResult ping(String baseUrl, int timeoutMs) {
        HttpsURLConnection conn = null;
        try {
            conn = open(baseUrl + "/__savve/ping", null, timeoutMs);
            conn.setReadTimeout(timeoutMs * 3);
            if (conn.getResponseCode() != HttpURLConnection.HTTP_OK) return null;
            Certificate[] certs = conn.getServerCertificates();
            String body = readAll(conn.getInputStream());
            if (!body.contains("\"app\":\"savve\"")) return null;
            if (certs.length == 0 || !(certs[0] instanceof X509Certificate)) return null;
            return new PingResult(baseUrl, fingerprint((X509Certificate) certs[0]));
        } catch (Exception e) {
            return null;
        } finally {
            if (conn != null) conn.disconnect();
        }
    }

    static String readAll(InputStream in) throws Exception {
        try (InputStream is = in) {
            ByteArrayOutputStream out = new ByteArrayOutputStream();
            byte[] buf = new byte[8192];
            int n;
            while ((n = is.read(buf)) > 0) out.write(buf, 0, n);
            return out.toString(StandardCharsets.UTF_8.name());
        }
    }
}
