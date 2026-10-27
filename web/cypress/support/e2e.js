// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import './commands'

// Abaikan uncaught exception dari library pihak ketiga
// (DataTables, jQuery, script cross-origin) agar tidak menggagalkan test
// yang sesungguhnya tidak terkait dengan error tersebut.
Cypress.on('uncaught:exception', (err) => {
  // Script error tanpa detail = cross-origin script (bukan app error)
  if (err.message && err.message.includes('Script error')) return false
  // DataTables column count mismatch (TN/18)
  if (err.message && err.message.includes('DataTables')) return false
  // Izinkan Cypress menangani error lain dari app
  return true
})

// Pindah ke about:blank agar browser tidak melakukan request ke app
// saat database sedang di-reset di hook beforeEach milik spec.
beforeEach(() => {
  cy.window().then((win) => {
    win.location.href = 'about:blank';
  });
});