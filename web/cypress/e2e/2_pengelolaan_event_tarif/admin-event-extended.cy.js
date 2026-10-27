describe('Admin - Edit/Hapus/Rekap Event', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/events');
  });

  // TC-EVENT-02 | FS-EVENT-02 | Ubah data event
  it('mengedit event yang ada', () => {
    cy.contains('tr', 'Event Test E2E').contains('a', 'Edit').click();
    cy.url().should('include', '/edit');

    cy.get('input[name="nama_event"]').clear().type('Event Test E2E Terupdate');
    cy.contains('button[type="submit"]', 'Update Event').click();

    cy.url().should('match', /\/admin\/events$/);
    cy.contains('Event Test E2E Terupdate').should('exist');
  });

  // TC-EVENT-03 | FS-EVENT-03 | Hapus data event tanpa transaksi aktif
  it('menghapus event tanpa transaksi aktif', () => {
    cy.on('window:confirm', () => true);

    cy.contains('tr', 'Event Test E2E').contains('button', 'Hapus').click();

    cy.contains('Event Test E2E').should('not.exist');
  });

  // TC-EVENT-04 | FS-REKAP-01 | Rekap statistik per event
  it('melihat rekap event', () => {
    cy.contains('tr', 'Event Test E2E').contains('a', '📊 Rekap').click();
    cy.url().should('match', /\/admin\/events\/\d+\/rekap/);

    cy.contains('h1', 'Event Test E2E').should('exist');
    cy.contains('Rekap per Ukuran').should('exist');
    cy.contains('Performa Kasir').should('exist');
    cy.contains('Rekap per Kategori Barang').should('exist');
  });
});
