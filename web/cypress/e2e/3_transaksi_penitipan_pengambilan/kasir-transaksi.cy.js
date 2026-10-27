describe('Kasir - Transaksi Penitipan (Input & Generate Nomor)', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();
  });

  // TC-TRANS-01 | FS-TRANS-01, FS-TRANS-02, FS-TRANS-03
  // Input transaksi penitipan & verifikasi nomor otomatis berformat SVV-{KODE}-XXXX
  it('membuat transaksi dan nomor otomatis berformat SVV-KODE-XXXX (4 digit)', () => {
    cy.visit('/kasir/transaksi/create');

    cy.get('input[name="nama_penitip"]').type('Budi Santoso');
    cy.get('input[name="no_whatsapp"]').type('081234567890');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]').check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]').check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox').first().check({ force: true });
    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });

    cy.url().should('match', /\/kasir\/transaksi\/\d+/);
    cy.contains('Budi Santoso').should('exist');

    // FS-TRANS-02: verifikasi format nomor transaksi SVV-{KODE_EVENT}-XXXX
    // XXXX = 4 digit dengan leading zero
    cy.get('body').invoke('text').then((text) => {
      const match = text.match(/SVV-[A-Za-z0-9]+-\d{4}/);
      expect(match, 'Nomor transaksi format SVV-{KODE}-XXXX harus ditemukan di halaman').to.not.be.null;
    });
  });
});