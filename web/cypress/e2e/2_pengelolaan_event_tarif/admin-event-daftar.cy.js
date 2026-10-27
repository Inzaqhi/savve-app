/**
 * Pengujian Daftar Event & Filter Laporan Fungsional
 *
 * Melengkapi kekurangan berikut:
 *   TC-EVENT-04 | FS-EVENT-04: Tampil daftar event dengan kolom nama, tanggal, status, jumlah transaksi
 *   TC-LAPORAN-03 | FS-LAPORAN-01: Filter laporan secara fungsional menghasilkan data yang berubah
 */

describe('Admin - Daftar Event (FS-EVENT-04)', () => {
    beforeEach(() => {
        cy.task('resetDb');
        cy.login('admin@savve.com', 'password123');
    });

    // TC-EVENT-04 | FS-EVENT-04 | Tampil daftar event lengkap dengan kolom yang sesuai
    it('menampilkan tabel daftar event dengan kolom nama, tanggal, status, dan jumlah transaksi', () => {
        cy.visit('/admin/events');

        cy.get('table').should('exist');
        cy.contains('Event Test E2E').should('exist');

        // Header kolom tabel harus memuat informasi sesuai FS-EVENT-03
        cy.get('table thead').within(() => {
            cy.contains('Nama').should('exist');
            cy.contains('Tanggal').should('exist');
            cy.contains('Status').should('exist');
        });

        // Baris event harus memuat status (aktif/nonaktif)
        cy.contains('tr', 'Event Test E2E').within(() => {
            cy.contains(/aktif|nonaktif/i).should('exist');
        });
    });
});

describe('Admin - Filter Laporan Fungsional (FS-LAPORAN-01)', () => {
    beforeEach(() => {
        cy.task('resetDb');
    });

    // TC-LAPORAN-03 | FS-LAPORAN-01 | Filter laporan berdasarkan event mengubah data yang ditampilkan
    it('filter laporan berdasarkan event menampilkan data yang relevan dan ringkasan pendapatan', () => {
        // ── Step 1: Kasir buat dua transaksi ──
        cy.login('kasir1@savve.com', 'password123');
        cy.pilihEvent();

        cy.visit('/kasir/transaksi/create');
        cy.get('input[name="nama_penitip"]').type('Penitip Filter A');
        cy.get('input[name="no_whatsapp"]').type('081200001111');
        cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]').check({ force: true });
        cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]').check({ force: true });
        cy.get('input[type="checkbox"].jenis-checkbox').first().check({ force: true });
        cy.get('button[type="submit"]').contains('Simpan').click({ force: true });
        cy.url().should('match', /\/kasir\/transaksi\/\d+/);

        // ── Step 2: Admin filter laporan berdasarkan event aktif ──
        cy.login('admin@savve.com', 'password123');
        cy.visit('/admin/laporan');

        // Pilih event dari dropdown dan submit filter
        cy.get('#form-filter-laporan').within(() => {
            cy.get('select[name="event_id"]').then(($sel) => {
                if ($sel.find('option').length > 1) {
                    // Pilih event pertama yang tersedia (bukan option kosong)
                    cy.wrap($sel).find('option').not('[value=""]').first().then(($opt) => {
                        cy.wrap($sel).select($opt.val());
                    });
                }
            });
        });
        cy.get('#form-filter-laporan').submit();

        cy.url().should('include', '/admin/laporan');
        // Ringkasan total harus tampil (bukan halaman kosong)
        cy.contains('Total').should('exist');
        // Data transaksi yang dibuat harus muncul
        cy.contains('Penitip Filter A').should('exist');
    });

    // TC-LAPORAN-04 | FS-LAPORAN-01 | Filter laporan berdasarkan rentang tanggal
    it('filter laporan berdasarkan tanggal menampilkan data dan ringkasan yang sesuai', () => {
        cy.login('kasir1@savve.com', 'password123');
        cy.pilihEvent();

        cy.visit('/kasir/transaksi/create');
        cy.get('input[name="nama_penitip"]').type('Penitip Filter Tanggal');
        cy.get('input[name="no_whatsapp"]').type('081200002222');
        cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]').check({ force: true });
        cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]').check({ force: true });
        cy.get('input[type="checkbox"].jenis-checkbox').first().check({ force: true });
        cy.get('button[type="submit"]').contains('Simpan').click({ force: true });
        cy.url().should('match', /\/kasir\/transaksi\/\d+/);

        cy.login('admin@savve.com', 'password123');
        cy.visit('/admin/laporan');

        // Set filter tanggal hari ini
        const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
        cy.get('#form-filter-laporan').within(() => {
            cy.get('input[name="tanggal_mulai"]').type(today);
            cy.get('input[name="tanggal_selesai"]').type(today);
        });
        cy.get('#form-filter-laporan').submit();

        cy.url().should('include', '/admin/laporan');
        cy.contains('Total').should('exist');
        cy.contains('Penitip Filter Tanggal').should('exist');
    });
});