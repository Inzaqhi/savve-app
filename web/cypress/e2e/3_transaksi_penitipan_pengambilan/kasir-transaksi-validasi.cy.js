describe('Kasir - Validasi Form Transaksi Penitipan', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();
  });

  // TC-TRANS-03 | FS-TRANS-02 | Validasi form: nama_penitip & no_whatsapp wajib diisi
  it('menolak submit form penitipan tanpa nama dan no. WhatsApp', () => {
    cy.visit('/kasir/transaksi/create');

    // Pilih ukuran & barang minimal, tapi sengaja biarkan data customer kosong
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]')
      .check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]')
      .check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox')
      .first()
      .check({ force: true });

    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });

    // Tidak boleh redirect ke halaman detail; tetap di form create dengan pesan error
    cy.url().should('include', '/kasir/transaksi/create');
    cy.contains('⚠').should('exist');
  });

  // TC-TRANS-04 | FS-TRANS-02 | Validasi form: minimal satu jenis barang wajib dipilih
  it('menolak submit jika tidak ada jenis barang yang dipilih', () => {
    cy.visit('/kasir/transaksi/create');

    cy.get('input[name="nama_penitip"]').type('Gita Wulandari');
    cy.get('input[name="no_whatsapp"]').type('081277778888');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]')
      .check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]')
      .check({ force: true });
    // Sengaja tidak mencentang jenis barang apapun

    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });

    cy.url().should('include', '/kasir/transaksi/create');
    cy.contains('Pilih minimal satu jenis barang').should('exist');
  });

  // TC-TRANS-05 | FS-TRANS-12 | Input metode pembayaran (QRIS)
  it('menyimpan metode pembayaran QRIS sesuai pilihan Kasir', () => {
    cy.visit('/kasir/transaksi/create');

    cy.get('input[name="nama_penitip"]').type('Hendra Saputra');
    cy.get('input[name="no_whatsapp"]').type('081299990000');
    cy.get('input[type="radio"][name="metode_bayar"][value="QRIS"]')
      .check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]')
      .check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox')
      .first()
      .check({ force: true });

    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });

    cy.url().should('match', /\/kasir\/transaksi\/\d+/);
    cy.contains('Hendra Saputra');
    cy.contains('QRIS').should('exist');
  });
});
