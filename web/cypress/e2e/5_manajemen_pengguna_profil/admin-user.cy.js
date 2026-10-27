describe('Admin - Kelola Kasir', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/users');
  });

  // TC-USER-01 | FS-USER-01 | Tambah akun kasir baru
  it('membuat akun kasir baru', () => {
    cy.contains('a', 'Tambah Kasir').click();
    cy.url().should('include', '/admin/users/create');

    cy.get('input[name="name"]').type('Kasir Cypress Baru');
    cy.get('input[name="email"]').type('kasir_cy_baru@savve.com');
    cy.get('input[name="password"]').type('password123');
    cy.get('input[name="password_confirmation"]').type('password123');

    cy.contains('button[type="submit"]', 'Simpan Kasir').click();

    cy.url().should('match', /\/admin\/users$/);
    cy.contains('Kasir Cypress Baru').should('be.visible');
  });

  // TC-USER-02 | FS-USER-02 | Ubah data akun kasir
  it('mengedit akun kasir', () => {
    cy.contains('tr', 'Kasir Satu').contains('a', 'Edit').click();
    cy.url().should('include', '/edit');

    cy.get('input[name="name"]').clear().type('Kasir Satu Terupdate');
    cy.contains('button[type="submit"]', 'Update Kasir').click();

    cy.url().should('match', /\/admin\/users$/);
    cy.contains('Kasir Satu Terupdate').should('be.visible');
  });

  // TC-USER-03 | FS-USER-03 | Hapus akun kasir
  it('menghapus akun kasir', () => {
    cy.on('window:confirm', () => true);

    cy.contains('tr', 'Kasir Satu').contains('button', 'Hapus').click();

    cy.contains('Kasir Satu').should('not.exist');
  });

  // TC-USER-04 (negatif) | FS-USER-03 | Akun kasir yang dihapus tidak dapat login kembali
  it('akun kasir yang dihapus tidak dapat digunakan untuk login', () => {
    cy.on('window:confirm', () => true);

    // Hapus Kasir Satu
    cy.contains('tr', 'Kasir Satu').contains('button', 'Hapus').click();
    cy.contains('Kasir Satu').should('not.exist');

    // Coba login dengan akun yang sudah dihapus
    cy.clearAllCookies();
    cy.clearAllLocalStorage();
    cy.clearAllSessionStorage();
    cy.visit('/login');
    cy.get('input[name="email"]').type('kasir1@savve.com');
    cy.get('input[name="password"]').type('password123');
    cy.get('button[type="submit"]').click();

    // Harus tetap di halaman login — akun tidak dapat digunakan
    cy.url().should('include', '/login');
    cy.contains('Email atau password salah').should('be.visible');
  });
});