package com.savve.app;

import android.content.Context;
import android.net.ConnectivityManager;
import android.net.LinkAddress;
import android.net.LinkProperties;
import android.net.Network;

import java.net.Inet4Address;
import java.net.InetAddress;
import java.net.InetSocketAddress;
import java.net.Socket;
import java.util.ArrayList;
import java.util.Collections;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Set;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;

/** Mencari komputer yang menjalankan server Savve di jaringan WiFi yang sama (subnet /24). */
final class ServerDiscovery {
    static final int DEFAULT_PORT = 8443;

    private ServerDiscovery() {}

    /** Alamat IPv4 perangkat ini di semua jaringan yang aktif. */
    static List<String> localAddresses(Context context) {
        Set<String> result = new LinkedHashSet<>();
        ConnectivityManager cm = (ConnectivityManager) context.getSystemService(Context.CONNECTIVITY_SERVICE);
        if (cm != null) {
            for (Network network : cm.getAllNetworks()) {
                LinkProperties lp = cm.getLinkProperties(network);
                if (lp == null) continue;
                for (LinkAddress la : lp.getLinkAddresses()) {
                    InetAddress a = la.getAddress();
                    if (a instanceof Inet4Address && !a.isLoopbackAddress() && a.isSiteLocalAddress()) {
                        result.add(a.getHostAddress());
                    }
                }
            }
        }
        return new ArrayList<>(result);
    }

    /** Pindai x.y.z.1 - x.y.z.254 untuk tiap alamat lokal. Berjalan memblokir, panggil dari thread lain. */
    static List<LanTrust.PingResult> scan(Context context) {
        List<String> candidates = new ArrayList<>();
        Set<String> self = new LinkedHashSet<>(localAddresses(context));
        for (String ip : self) {
            String prefix = ip.substring(0, ip.lastIndexOf('.') + 1);
            for (int i = 1; i <= 254; i++) {
                String host = prefix + i;
                if (!candidates.contains(host)) candidates.add(host);
            }
        }

        List<LanTrust.PingResult> found = Collections.synchronizedList(new ArrayList<>());
        ExecutorService pool = Executors.newFixedThreadPool(48);
        for (String host : candidates) {
            pool.execute(() -> {
                if (!portOpen(host, DEFAULT_PORT, 600)) return;
                LanTrust.PingResult r = LanTrust.ping("https://" + host + ":" + DEFAULT_PORT, 2500);
                if (r != null) found.add(r);
            });
        }
        pool.shutdown();
        try {
            pool.awaitTermination(30, TimeUnit.SECONDS);
        } catch (InterruptedException ignored) {
            Thread.currentThread().interrupt();
        }
        pool.shutdownNow();
        return new ArrayList<>(found);
    }

    private static boolean portOpen(String host, int port, int timeoutMs) {
        try (Socket s = new Socket()) {
            s.connect(new InetSocketAddress(host, port), timeoutMs);
            return true;
        } catch (Exception e) {
            return false;
        }
    }
}
