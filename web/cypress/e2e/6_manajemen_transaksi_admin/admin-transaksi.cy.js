describe('Admin - Manajemen Transaksi', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('admin@savve.com', 'password123');

    cy.on('uncaught:exception', () => {
      return false;
    });
  });

  // TC-ADMIN-01 | FS-ADMIN-01 | Admin melihat daftar transaksi dari semua kasir
  it('melihat daftar transaksi', () => {
    cy.visit('/admin/transaksis');
    cy.url().should('include', '/admin/transaksis');
    cy.contains('h1', 'Data Transaksi').should('exist');
    cy.get('#form-filter').should('exist');
  });

  // TC-ADMIN-02 | FS-ADMIN-01 | Admin menghapus transaksi milik kasir (dengan konfirmasi modal)
  it('Admin menghapus transaksi milik kasir dengan konfirmasi modal', () => {
    // Buat satu transaksi via Kasir Satu terlebih dahulu
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();
    cy.visit('/kasir/transaksi/create');
    cy.get('input[name="nama_penitip"]').type('Fajar Nugroho');
    cy.get('input[name="no_whatsapp"]').type('081255556666');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]').check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]').check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox').first().check({ force: true });
    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });
    cy.url().should('match', /\/kasir\/transaksi\/\d+/);

    // Admin login dan menghapus transaksi tersebut
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/transaksis');
    cy.contains('Fajar Nugroho').should('exist');

    cy.contains('tr', 'Fajar Nugroho')
      .find('button.btn-hapus-transaksi')
      .click();

    cy.get('#modal-hapus-transaksi').should('be.visible');
    cy.contains('button', 'Ya, Hapus').click();

    cy.url().should('include', '/admin/transaksis');
    cy.contains('Fajar Nugroho').should('not.exist');
  });
});
