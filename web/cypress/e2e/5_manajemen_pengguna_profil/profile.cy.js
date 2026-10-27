describe('User Profile - Edit & Password Update', () => {
  beforeEach(() => {
    cy.task('resetDb');
  });

  // TC-PROFILE-01 | FS-PROFILE-01 | Admin mengubah nama profil dan email
  it('Admin - Edit nama profil dan email', () => {
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/profile');

    cy.get('input[name="name"]').clear().type('Admin Terupdate');
    cy.get('input[name="email"]').clear().type('admin_new@savve.com');
    cy.contains('button[type="submit"]', 'Simpan Perubahan').click();

    // Pastikan muncul pesan sukses
    cy.contains('✓').should('exist');
    cy.get('input[name="name"]').should('have.value', 'Admin Terupdate');
  });

  // TC-PROFILE-02 | FS-PROFILE-01 | Admin mengganti password
  it('Admin - Ganti password', () => {
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/profile');

    // Buka tab Ganti Password
    cy.get('#tab-password').click();
    cy.get('#panel-password').should('not.have.class', 'hidden');

    cy.get('input[name="current_password"]').type('password123');
    cy.get('input[name="password"]').type('password1234');
    cy.get('input[name="password_confirmation"]').type('password1234');
    cy.contains('button[type="submit"]', 'Update Password').click();

    cy.contains('✓').should('exist');

    // Login ulang dengan password baru untuk verifikasi
    cy.login('admin@savve.com', 'password1234');
    cy.url().should('not.include', '/login');
  });

  // TC-PROFILE-03 | FS-PROFILE-01 | Kasir mengubah nama profil dan email
  it('Kasir - Edit nama profil dan email', () => {
    cy.login('kasir1@savve.com', 'password123');
    // Kasir wajib pilih event dulu
    cy.pilihEvent();
    cy.visit('/kasir/profile');

    cy.get('input[name="name"]').clear().type('Kasir Satu Terupdate');
    cy.get('input[name="email"]').clear().type('kasir1_new@savve.com');
    cy.contains('button[type="submit"]', 'Simpan Perubahan').click();

    cy.contains('✓').should('exist');
    cy.get('input[name="name"]').should('have.value', 'Kasir Satu Terupdate');
  });
});
