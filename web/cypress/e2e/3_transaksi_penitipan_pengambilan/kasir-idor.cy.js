describe('Kasir - Proteksi Kepemilikan Transaksi (IDOR)', () => {
  beforeEach(() => {
    cy.task('resetDb');
  });

  // TC-AMBIL-02 | FS-TRANS-09, FS-TRANS-06 | Proteksi kepemilikan transaksi (IDOR) antar kasir
  it('kasir lain tidak dapat menemukan/mengonfirmasi transaksi milik kasir lain', () => {
    // ── Step 1: Admin membuat akun Kasir Dua ──
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/users/create');
    cy.get('input[name="name"]').type('Kasir Dua');
    cy.get('input[name="email"]').type('kasir2@savve.com');
    cy.get('input[name="password"]').type('password123');
    cy.get('input[name="password_confirmation"]').type('password123');
    cy.contains('button[type="submit"]', 'Simpan Kasir').click();
    cy.url().should('match', /\/admin\/users$/);

    // ── Step 2: Kasir Satu login dan membuat transaksi pada event aktif ──
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();
    cy.visit('/kasir/transaksi/create');
    cy.get('input[name="nama_penitip"]').type('Citra Lestari');
    cy.get('input[name="no_whatsapp"]').type('081298765432');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]')
      .check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]')
      .check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox')
      .first()
      .check({ force: true });
    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });
    cy.url().should('match', /\/kasir\/transaksi\/\d+/);
    cy.contains('Citra Lestari');

    // ── Step 3: Kasir Dua login pada event yang sama dan mencari nama tersebut ──
    cy.login('kasir2@savve.com', 'password123');
    cy.pilihEvent();

    cy.on('uncaught:exception', (err) => {
      if (err.message.includes('isScanning') || err.message.includes('Cannot access')) {
        return false;
      }
    });

    cy.visit('/kasir/pengambilan');
    cy.get('#tab-nama').click();
    cy.get('#panel-nama').should('not.have.class', 'hidden');
    cy.get('#panel-nama input[name="nama_penitip"]').type('Citra Lestari');
    cy.get('#panel-nama button[type="submit"]').click();

    // Transaksi milik Kasir Satu tidak boleh muncul di pencarian Kasir Dua
    cy.url().should('include', '/kasir/pengambilan');
    cy.contains('tidak ditemukan', { timeout: 10000 }).should('exist');
    cy.get('table').should('not.exist');
  });
});
