describe('Admin - Kelola Event', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/events');
  });

  // TC-EVENT-01 | FS-EVENT-01 | Tambah data event baru beserta tarif (5 ukuran)
  it('membuat event baru', () => {
    cy.get('a[href*="/admin/events/create"]').first().click();
    cy.get('input[name="nama_event"]').type('Konser Test Cypress');
    cy.get('input[name="kode_event"]').type('CYP01');
    cy.get('input[name="tanggal_mulai"]').type('2026-07-01');
    cy.get('input[name="tanggal_selesai"]').type('2026-07-02');
    cy.get('input[name="tarif[S]"]').type('10000');
    cy.get('input[name="tarif[M]"]').type('15000');
    cy.get('input[name="tarif[L]"]').type('20000');
    cy.get('input[name="tarif[XL]"]').type('25000');
    cy.get('input[name="tarif[Gadget]"]').type('5000');
    cy.contains('button[type="submit"]', 'Simpan').click();
    cy.contains('Konser Test Cypress');
  });

  // TC-EVENT-02 | FS-EVENT-02 | Ubah data event
  it('mengedit event yang ada', () => {
    cy.contains('tr', 'Event Test E2E').contains('a', 'Edit').click();
    cy.url().should('include', '/edit');

    cy.get('input[name="nama_event"]').clear().type('Event Test E2E Terupdate');
    cy.contains('button[type="submit"]', 'Update Event').click();

    cy.url().should('match', /\/admin\/events$/);
    cy.contains('Event Test E2E Terupdate').should('exist');
  });

  // TC-EVENT-03 | FS-EVENT-03 | Hapus data event tanpa transaksi aktif
  it('menghapus event tanpa transaksi aktif', () => {
    cy.on('window:confirm', () => true);

    cy.contains('tr', 'Event Test E2E').contains('button', 'Hapus').click();

    cy.contains('Event Test E2E').should('not.exist');
  });

  // TC-EVENT-04 | FS-EVENT-04 | Tampil daftar event dengan kolom nama, tanggal, status
  it('menampilkan daftar event dengan informasi lengkap', () => {
    cy.url().should('include', '/admin/events');
    // Tabel event harus ada dan memuat data event seeder
    cy.get('table').should('exist');
    cy.contains('Event Test E2E').should('exist');
    // Kolom status harus tampil
    cy.contains('aktif').should('exist');
  });

  // TC-EVENT-05 (negatif) | FS-EVENT-03 | Hapus event yang masih memiliki transaksi aktif harus dicegah atau dikonfirmasi
  it('mencoba menghapus event yang punya transaksi — sistem menampilkan konfirmasi atau menolak', () => {
    // Step 1: Kasir buat transaksi terlebih dahulu agar event punya transaksi
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();
    cy.visit('/kasir/transaksi/create');
    cy.get('input[name="nama_penitip"]').type('Penitip Hapus Event');
    cy.get('input[name="no_whatsapp"]').type('081200001111');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]').check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]').check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox').first().check({ force: true });
    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });
    cy.url().should('match', /\/kasir\/transaksi\/\d+/);

    // Step 2: Admin coba hapus event yang sudah punya transaksi
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/events');

    // Stub confirm agar auto-accept jika ada dialog konfirmasi
    cy.on('window:confirm', () => true);

    cy.contains('tr', 'Event Test E2E').contains('button', 'Hapus').click();

    // Sistem harus menolak penghapusan (event masih ada di daftar)
    // ATAU menampilkan pesan error — salah satu harus terpenuhi
    cy.contains('Event Test E2E').should('exist');
  });
});

describe('Admin - Edit/Hapus/Rekap Event', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('admin@savve.com', 'password123');
    cy.visit('/admin/events');
  });

  // TC-EVENT-04 | FS-REKAP-01 | Rekap statistik per event
  it('melihat rekap event', () => {
    cy.contains('tr', 'Event Test E2E').contains('a', '📊 Rekap').click();
    cy.url().should('match', /\/admin\/events\/\d+\/rekap/);

    cy.contains('h1', 'Event Test E2E').should('exist');
    cy.contains('Rekap per Ukuran').should('exist');
    cy.contains('Performa Kasir').should('exist');
    cy.contains('Rekap per Kategori Barang').should('exist');
  });
});