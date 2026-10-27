// Login helper - bersihkan semua storage dulu agar session fresh setelah resetDb
Cypress.Commands.add('login', (email, password) => {
  cy.clearAllCookies();
  cy.clearAllLocalStorage();
  cy.clearAllSessionStorage();
  cy.visit('/login');
  cy.get('input[name="email"]').should('be.visible').type(email);
  cy.get('input[name="password"]').should('be.visible').type(password);
  cy.get('button[type="submit"]').click();
  // Timeout 30s untuk handle server lambat setelah banyak resetDb
  cy.url().should('not.include', '/login', { timeout: 30000 });
});

// Helper: pilih event pertama di halaman /kasir/pilih-event
// Route: kasir.event.pilih -> POST /kasir/pilih-event
Cypress.Commands.add('pilihEvent', () => {
  cy.visit('/kasir/pilih-event');
  cy.url().should('include', '/kasir/pilih-event');
  // Selector: form action = /kasir/pilih-event (bukan /kasir/event/pilih)
  // force:true karena elemen animasi opacity:0
  cy.get('form[action*="pilih-event"] button[type="submit"]')
    .first()
    .click({ force: true });
  cy.url().should('include', '/kasir/dashboard');
});