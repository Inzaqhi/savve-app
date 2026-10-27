describe('Admin - Export Laporan Excel & Hapus Transaksi', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('admin@savve.com', 'password123');

    cy.on('uncaught:exception', () => {
      return false;
    });
  });

  // TC-LAPORAN-02 | FS-LAPORAN-03 | Ekspor laporan ke Excel (nama file berformat timestamp)
  it('mengekspor laporan ke Excel dengan nama file berformat timestamp', () => {
    cy.visit('/admin/laporan');
    cy.get('#form-filter-laporan').should('exist');

    cy.contains('a', 'Export Excel')
      .should('have.attr', 'href')
      .then((href) => {
        cy.request({ url: href, encoding: 'binary' }).then((resp) => {
          expect(resp.status).to.eq(200);
          expect(resp.headers['content-type']).to.include('spreadsheet');

          const disposition = resp.headers['content-disposition'] || '';
          expect(disposition).to.match(
            /laporan-savve-\d{8}-\d{6}\.xlsx/
          );
        });
      });
  });
});

