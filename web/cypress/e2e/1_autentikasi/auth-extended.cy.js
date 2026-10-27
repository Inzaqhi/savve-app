describe('Auth - Pembatasan Akses Role & Logout', () => {
  beforeEach(() => {
    cy.task('resetDb');
  });

  // TC-AUTH-04 | FS-AUTH-03 | Kasir ditolak akses ke halaman khusus Admin (Kelola Event)
  it('Kasir ditolak akses ke halaman Kelola Event milik Admin (403)', () => {
    cy.login('kasir1@savve.com', 'password123');

    cy.request({
      url: '/admin/events',
      failOnStatusCode: false,
    }).then((resp) => {
      expect(resp.status).to.eq(403);
    });
  });

  // TC-AUTH-05 | FS-AUTH-03 | Kasir ditolak akses ke halaman Kelola Pengguna milik Admin
  it('Kasir ditolak akses ke halaman Kelola Pengguna milik Admin (403)', () => {
    cy.login('kasir1@savve.com', 'password123');

    cy.request({
      url: '/admin/users',
      failOnStatusCode: false,
    }).then((resp) => {
      expect(resp.status).to.eq(403);
    });
  });

  // TC-AUTH-06 | FS-AUTH-03 | Kasir ditolak akses ke halaman Laporan milik Admin
  it('Kasir ditolak akses ke halaman Laporan milik Admin (403)', () => {
    cy.login('kasir1@savve.com', 'password123');

    cy.request({
      url: '/admin/laporan',
      failOnStatusCode: false,
    }).then((resp) => {
      expect(resp.status).to.eq(403);
    });
  });

  // TC-AUTH-07 | FS-AUTH-04 | Logout mengakhiri sesi dan mencegah akses ulang tanpa login
  it('logout mengakhiri sesi dan mencegah akses ke halaman terproteksi', () => {
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/dashboard');

    cy.get('form[action*="logout"]').first().submit();

    cy.url().should('include', '/login');

    // Setelah logout, akses langsung ke halaman admin harus ditolak/redirect ke login
    cy.visit('/admin/dashboard');
    cy.url().should('include', '/login');
  });
});
