/**
 * Pengujian Keamanan - Proteksi Akses Lintas Peran & IDOR
 *
 * File ini memperluas kasir-idor.cy.js dengan dua skenario yang belum diuji:
 *   1. Akses langsung via URL transaksi milik kasir lain (IDOR berbasis URL)
 *   2. Admin mencoba mengakses route khusus kasir (simetri proteksi role)
 */

describe('Keamanan - IDOR via Akses URL Langsung', () => {
    beforeEach(() => {
        cy.task('resetDb');
    });

    // TC-IDOR-02 | FS-TRANS-09 | Kasir tidak dapat mengakses URL transaksi milik kasir lain secara langsung
    it('Kasir Dua tidak dapat mengakses /kasir/transaksi/{id} milik Kasir Satu secara langsung', () => {
        // ── Step 1: Buat akun Kasir Dua ──
        cy.login('admin@savve.com', 'password123');
        cy.visit('/admin/users/create');
        cy.get('input[name="name"]').type('Kasir Dua IDOR');
        cy.get('input[name="email"]').type('kasir_idor2@savve.com');
        cy.get('input[name="password"]').type('password123');
        cy.get('input[name="password_confirmation"]').type('password123');
        cy.contains('button[type="submit"]', 'Simpan Kasir').click();
        cy.url().should('match', /\/admin\/users$/);

        // ── Step 2: Kasir Satu buat transaksi, catat ID-nya ──
        cy.login('kasir1@savve.com', 'password123');
        cy.pilihEvent();
        cy.visit('/kasir/transaksi/create');
        cy.get('input[name="nama_penitip"]').type('Mitra Rahayu');
        cy.get('input[name="no_whatsapp"]').type('081277770001');
        cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]').check({ force: true });
        cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]').check({ force: true });
        cy.get('input[type="checkbox"].jenis-checkbox').first().check({ force: true });
        cy.get('button[type="submit"]').contains('Simpan').click({ force: true });

        cy.url().should('match', /\/kasir\/transaksi\/\d+/).then((url) => {
            const transaksiId = url.match(/\/kasir\/transaksi\/(\d+)/)[1];

            // ── Step 3: Kasir Dua login dan coba akses URL transaksi milik Kasir Satu ──
            cy.login('kasir_idor2@savve.com', 'password123');
            cy.pilihEvent();

            cy.request({
                url: `/kasir/transaksi/${transaksiId}`,
                failOnStatusCode: false,
            }).then((resp) => {
                // Harus ditolak: 403 Forbidden ATAU redirect ke halaman lain (bukan 200 dengan data)
                expect(resp.status, 'Kasir lain tidak boleh mendapat HTTP 200 pada transaksi bukan miliknya').to.not.eq(200);
            });
        });
    });

    // TC-IDOR-03 | FS-TRANS-09 | Endpoint konfirmasi pengambilan menolak request dari kasir yang bukan pemilik
    it('Kasir Dua tidak dapat mengkonfirmasi pengambilan transaksi milik Kasir Satu via POST langsung', () => {
        // ── Step 1: Buat akun Kasir Dua ──
        cy.login('admin@savve.com', 'password123');
        cy.visit('/admin/users/create');
        cy.get('input[name="name"]').type('Kasir Dua Konfirmasi');
        cy.get('input[name="email"]').type('kasir_conf2@savve.com');
        cy.get('input[name="password"]').type('password123');
        cy.get('input[name="password_confirmation"]').type('password123');
        cy.contains('button[type="submit"]', 'Simpan Kasir').click();
        cy.url().should('match', /\/admin\/users$/);

        // ── Step 2: Kasir Satu buat transaksi ──
        cy.login('kasir1@savve.com', 'password123');
        cy.pilihEvent();
        cy.visit('/kasir/transaksi/create');
        cy.get('input[name="nama_penitip"]').type('Nadia Kusuma');
        cy.get('input[name="no_whatsapp"]').type('081277770002');
        cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]').check({ force: true });
        cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]').check({ force: true });
        cy.get('input[type="checkbox"].jenis-checkbox').first().check({ force: true });
        cy.get('button[type="submit"]').contains('Simpan').click({ force: true });

        cy.url().should('match', /\/kasir\/transaksi\/\d+/).then((url) => {
            const transaksiId = url.match(/\/kasir\/transaksi\/(\d+)/)[1];

            // ── Step 3: Kasir Dua login dan coba POST konfirmasi pengambilan ──
            cy.login('kasir_conf2@savve.com', 'password123');
            cy.pilihEvent();

            cy.visit('/kasir/pengambilan');
            cy.window().then((win) => {
                const csrfMatch = win.document.documentElement.innerHTML.match(
                    /X-CSRF-TOKEN['"]\s*:\s*['"]([^'"]+)['"]/
                );
                expect(csrfMatch, 'CSRF token harus ditemukan').to.not.be.null;
                const token = csrfMatch[1];

                return win.fetch(`/kasir/pengambilan/konfirmasi/${transaksiId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': token,
                    },
                    body: new URLSearchParams({ _method: 'POST' }),
                }).then((res) => {
                    // Endpoint harus menolak: 403, 404, atau redirect (bukan 200/201 sukses)
                    expect(res.status, 'Konfirmasi oleh kasir lain harus ditolak').to.not.be.oneOf([200, 201]);
                });
            });
        });
    });
});

describe('Keamanan - Proteksi Role: Admin tidak dapat akses route Kasir', () => {
    beforeEach(() => {
        cy.task('resetDb');
        cy.login('admin@savve.com', 'password123');
    });

    // TC-AUTH-08 | FS-AUTH-03 | Admin ditolak akses route khusus Kasir (form transaksi)
    it('Admin ditolak akses halaman input transaksi kasir (403 atau redirect)', () => {
        cy.request({
            url: '/kasir/transaksi/create',
            failOnStatusCode: false,
        }).then((resp) => {
            expect(resp.status).to.not.eq(200);
        });
    });

    // TC-AUTH-09 | FS-AUTH-03 | Admin ditolak akses halaman pengambilan barang kasir
    it('Admin ditolak akses halaman pengambilan barang kasir (403 atau redirect)', () => {
        cy.request({
            url: '/kasir/pengambilan',
            failOnStatusCode: false,
        }).then((resp) => {
            expect(resp.status).to.not.eq(200);
        });
    });
});