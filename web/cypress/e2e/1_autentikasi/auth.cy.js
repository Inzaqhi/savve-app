describe('Auth - Login & Logout', () => {
  beforeEach(() => {
    cy.task('resetDb');
  });

  // TC-AUTH-01 | FS-AUTH-01 | Login dengan kredensial valid (Admin)
  it('login sebagai admin berhasil', () => {
    cy.visit('/login');
    cy.get('input[name="email"]').type('admin@savve.com');
    cy.get('input[name="password"]').type('password123');
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/admin');
  });

  // TC-AUTH-02 | FS-AUTH-01 | Login dengan kredensial valid (Kasir)
  it('login sebagai kasir berhasil dan diarahkan ke dashboard kasir', () => {
    cy.visit('/login');
    cy.get('input[name="email"]').type('kasir1@savve.com');
    cy.get('input[name="password"]').type('password123');
    cy.get('button[type="submit"]').click();
    // Lebih spesifik: kasir harus diarahkan ke /kasir/..., bukan sekadar "bukan /login"
    cy.url().should('include', '/kasir');
  });

  // TC-AUTH-03 | FS-AUTH-02 | Login dengan kredensial tidak valid
  it('login dengan kredensial salah menampilkan pesan error dan tetap di halaman login', () => {
    cy.visit('/login');
    cy.get('input[name="email"]').type('salah@email.com');
    cy.get('input[name="password"]').type('wrongpassword');
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/login');
    // Verifikasi pesan error benar-benar muncul sesuai FS-AUTH-02
    cy.contains('Email atau password salah').should('be.visible');
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