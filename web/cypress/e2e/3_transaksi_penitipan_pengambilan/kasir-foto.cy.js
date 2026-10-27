describe('Kasir - Upload Foto Bukti Transaksi', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();
  });

  // TC-TRANS-08 | FS-TRANS-10 | Upload foto bukti format gambar valid (positif)
  it('mengupload foto bukti dengan format gambar valid (PNG) berhasil tersimpan', () => {
    cy.visit('/kasir/transaksi/create');

    cy.get('input[name="nama_penitip"]').type('Dewi Anggraini');
    cy.get('input[name="no_whatsapp"]').type('081211112222');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]')
      .check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]')
      .check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox')
      .first()
      .check({ force: true });

    // Upload via "Pilih dari Galeri" -> memicu pilihDariGaleri() -> FileReader -> hidden input
    cy.contains('label', 'Pilih dari Galeri')
      .find('input[type="file"]')
      .selectFile('cypress/fixtures/sample.png', { force: true });

    cy.get('#foto-preview-wrapper').should('not.have.class', 'hidden');
    cy.get('#foto_penitipan_input').should('not.have.value', '');

    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });

    cy.url().should('match', /\/kasir\/transaksi\/\d+/);
    cy.contains('Dewi Anggraini');

    // Foto bukti harus tampil di halaman detail karena tersimpan dengan MIME valid
    cy.get('img[alt="Foto Barang"]').should('exist');
  });

  // TC-TRANS-09 | FS-TRANS-10 | Upload file bukan gambar ditolak (negatif, validasi MIME)
  it('mengupload file bukan gambar tidak tersimpan sebagai foto bukti', () => {
    cy.visit('/kasir/transaksi/create');

    cy.get('input[name="nama_penitip"]').type('Eko Prasetyo');
    cy.get('input[name="no_whatsapp"]').type('081233334444');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]')
      .check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]')
      .check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox')
      .first()
      .check({ force: true });

    // Set langsung hidden input dengan data URL palsu (MIME text/plain),
    // mensimulasikan payload yang lolos validasi accept="image/*" di client
    // namun seharusnya ditolak oleh validasi finfo::buffer() di server.
    cy.fixture('sample-invalid.txt', 'base64').then((b64) => {
      cy.get('#foto_penitipan_input')
        .invoke('val', `data:text/plain;base64,${b64}`)
        .trigger('change', { force: true });
    });

    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });

    cy.url().should('match', /\/kasir\/transaksi\/\d+/);
    cy.contains('Eko Prasetyo');

    // Transaksi tersimpan, namun TANPA foto karena MIME tidak valid
    cy.get('img[alt="Foto Barang"]').should('not.exist');
  });
});
