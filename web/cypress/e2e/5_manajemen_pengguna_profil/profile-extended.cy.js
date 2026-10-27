/**
 * Pengujian Profil Pengguna — Kasus Negatif (Melengkapi profile.cy.js)
 *
 * profile.cy.js sudah mencakup TC-PROFILE-01, 02, 03 (kasus positif).
 * File ini menambahkan kasus negatif yang belum diuji sesuai FS-PROFILE-01:
 *   - Ganti password dengan current_password salah harus ditolak
 *   - Update email dengan email milik pengguna lain harus ditolak (unique constraint)
 */

describe('Profil - Kasus Negatif (FS-PROFILE-01)', () => {
    beforeEach(() => {
        cy.task('resetDb');
    });

    // TC-PROFILE-04 (negatif) | FS-PROFILE-01 | Ganti password dengan current_password yang salah harus ditolak
    it('Admin - menolak ganti password jika current_password salah', () => {
        cy.login('admin@savve.com', 'password123');
        cy.visit('/admin/profile');

        cy.get('#tab-password').click();
        cy.get('#panel-password').should('not.have.class', 'hidden');

        // Isi current_password dengan nilai yang SALAH
        cy.get('input[name="current_password"]').type('password_salah_banget');
        cy.get('input[name="password"]').type('passwordbaru999');
        cy.get('input[name="password_confirmation"]').type('passwordbaru999');
        cy.contains('button[type="submit"]', 'Update Password').click();

        // Harus tetap di halaman profile, muncul pesan error
        cy.url().should('include', '/admin/profile');
        cy.contains('salah').should('exist');
    });

    // TC-PROFILE-05 (negatif) | FS-PROFILE-01 | Update email dengan email yang sudah dipakai pengguna lain harus ditolak
    it('Admin - menolak update email jika email sudah digunakan pengguna lain', () => {
        cy.login('admin@savve.com', 'password123');
        cy.visit('/admin/profile');

        // Coba ganti email admin dengan email kasir1 yang sudah ada di DB
        cy.get('input[name="email"]').clear().type('kasir1@savve.com');
        cy.contains('button[type="submit"]', 'Simpan Perubahan').click();

        // Harus tetap di halaman profile dengan pesan validasi email sudah dipakai
        cy.url().should('include', '/admin/profile');
        cy.contains('sudah').should('exist');
    });

    // TC-PROFILE-06 (negatif) | FS-PROFILE-01 | Ganti password dengan konfirmasi tidak cocok harus ditolak
    it('Admin - menolak ganti password jika konfirmasi tidak cocok', () => {
        cy.login('admin@savve.com', 'password123');
        cy.visit('/admin/profile');

        cy.get('#tab-password').click();
        cy.get('#panel-password').should('not.have.class', 'hidden');

        cy.get('input[name="current_password"]').type('password123');
        cy.get('input[name="password"]').type('passwordbaru999');
        // Sengaja beda dengan password di atas
        cy.get('input[name="password_confirmation"]').type('passwordbaru_BEDA');
        cy.contains('button[type="submit"]', 'Update Password').click();

        cy.url().should('include', '/admin/profile');
        cy.contains('konfirmasi').should('exist');
    });

    // TC-PROFILE-07 | FS-PROFILE-01 | Kasir mengganti password berhasil dan dapat login ulang
    it('Kasir - ganti password dan berhasil login ulang dengan password baru', () => {
        cy.login('kasir1@savve.com', 'password123');
        cy.pilihEvent();
        cy.visit('/kasir/profile');

        cy.get('#tab-password').click();
        cy.get('#panel-password').should('not.have.class', 'hidden');

        cy.get('input[name="current_password"]').type('password123');
        cy.get('input[name="password"]').type('kasirpass999');
        cy.get('input[name="password_confirmation"]').type('kasirpass999');
        cy.contains('button[type="submit"]', 'Update Password').click();

        cy.contains('✓').should('exist');

        // Verifikasi login ulang dengan password baru berhasil
        cy.login('kasir1@savve.com', 'kasirpass999');
        cy.url().should('include', '/kasir');
    });
});