describe('Global Search', () => {
  beforeEach(() => {
    cy.task('resetDb');

    // Buat satu transaksi terlebih dahulu agar ada data pencarian
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();
    cy.visit('/kasir/transaksi/create');
    cy.get('input[name="nama_penitip"]').type('Budi Santoso');
    cy.get('input[name="no_whatsapp"]').type('081234567890');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]')
      .check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]')
      .check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox')
      .first()
      .check({ force: true });
    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });
    cy.url().should('match', /\/kasir\/transaksi\/\d+/);
  });

  // TC-SEARCH-01 | FS-SEARCH-01 | Admin melakukan pencarian global (transaksi, event, kasir)
  it('Admin - Melakukan pencarian global (transaksi, event, kasir)', () => {
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/dashboard');

    // Cari 'Budi' via input header
    cy.get('input[name="q"]').first().type('Budi{enter}');

    cy.url().should('include', '/admin/search');
    cy.contains('Hasil untuk "Budi"').should('exist');
    cy.contains('Budi Santoso').should('exist');

    // Cari 'Event'
    cy.get('input[name="q"]').first().clear().type('Event{enter}');
    cy.contains('Hasil untuk "Event"').should('exist');
    cy.contains('Event Test E2E').should('exist');

    // Cari 'Kasir'
    cy.get('input[name="q"]').first().clear().type('Kasir{enter}');
    cy.contains('Hasil untuk "Kasir"').should('exist');
    cy.contains('Kasir Satu').should('exist');
  });

  // TC-SEARCH-02 | FS-SEARCH-01 | Kasir melakukan pencarian transaksi (scope milik sendiri)
  it('Kasir - Melakukan pencarian transaksi', () => {
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();
    cy.visit('/kasir/dashboard');

    // Cari 'Budi' via input header
    cy.get('input[name="q"]').first().type('Budi{enter}');

    cy.url().should('include', '/kasir/search');
    cy.contains('Hasil untuk "Budi"').should('exist');
    cy.contains('Budi Santoso').should('exist');
  });
});
