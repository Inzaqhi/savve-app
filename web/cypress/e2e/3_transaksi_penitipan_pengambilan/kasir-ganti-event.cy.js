describe('Kasir - Ganti Event', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();
  });

  // TC-TRANS-02 | FS-TRANS-01 (prasyarat) | Pemilihan/ganti event aktif Kasir
  it('ganti event dan pilih event baru', () => {
    cy.visit('/kasir/pilih-event');
    cy.url().should('include', '/kasir/pilih-event');
    cy.contains('Event Aktif Saat Ini').should('exist');

    // Stub window.confirm to accept the event switch confirmation
    cy.on('window:confirm', () => true);

    // Click "Ganti" button
    cy.contains('button', 'Ganti').click();

    // After switching, the active event card should no longer show,
    // and we should be presented with the event selection list.
    cy.contains('Event Aktif Saat Ini').should('not.exist');
    cy.get('form[action*="pilih-event"] button[type="submit"]')
      .first()
      .click({ force: true });

    // Should redirect back to dashboard
    cy.url().should('include', '/kasir/dashboard');
  });
});
