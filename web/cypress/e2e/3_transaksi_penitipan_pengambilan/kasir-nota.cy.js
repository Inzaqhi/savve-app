describe('Kasir - Detail & Nota Transaksi', () => {
  // Gunakan satu beforeEach untuk membuat transaksi SEKALI,
  // lalu simpan URL detail ke alias agar tidak duplikasi setup di tiap test
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();

    cy.visit('/kasir/transaksi/create');
    cy.get('input[name="nama_penitip"]').type('Budi Santoso');
    cy.get('input[name="no_whatsapp"]').type('081234567890');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]').check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]').check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox').first().check({ force: true });
    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });

    cy.url().should('match', /\/kasir\/transaksi\/\d+/);
    // Simpan URL detail transaksi ke alias agar bisa dipakai antar test
    cy.url().as('detailUrl');
  });

  // TC-TRANS-06 | FS-TRANS-01 | Melihat detail transaksi setelah penitipan tersimpan
  it('menampilkan detail transaksi: nama, nomor SVV, metode bayar, dan status', () => {
    cy.contains('Detail Transaksi').should('exist');
    cy.contains('Budi Santoso').should('exist');
    cy.contains('081234567890').should('exist');
    cy.contains('Cash').should('exist');
    cy.contains('DITITIPKAN').should('exist');

    // Nomor transaksi berformat SVV-... harus tampil di halaman detail
    cy.get('body').invoke('text').then((text) => {
      const match = text.match(/SVV-[A-Za-z0-9]+-\d{4}/);
      expect(match, 'Nomor transaksi SVV-KODE-XXXX harus tampil di halaman detail').to.not.be.null;
    });

    // Link "Cetak Nota" mengarah ke route /nota
    cy.contains('a', 'Cetak Nota').should('have.attr', 'href').and('include', '/nota');
  });

  // TC-TRANS-07 | FS-TRANS-04 | Cetak nota/bukti transaksi (route kasir.transaksi.nota)
  it('halaman nota memuat nomor transaksi, nama penitip, dan detail barang', () => {
    // Ambil href link nota dari halaman detail, lalu navigasi ke sana
    cy.contains('a', 'Cetak Nota').invoke('attr', 'href').then((href) => {
      cy.visit(href);
    });

    cy.url().should('include', '/nota');
    cy.contains('Savve').should('exist');
    cy.contains('Budi Santoso').should('exist');
    cy.contains('Bukti Transaksi Penitipan').should('exist');
    cy.contains('Total Pembayaran').should('exist');

    // Nomor transaksi juga harus muncul di halaman nota
    cy.get('body').invoke('text').then((text) => {
      const match = text.match(/SVV-[A-Za-z0-9]+-\d{4}/);
      expect(match, 'Nomor transaksi harus ada di halaman nota').to.not.be.null;
    });
  });
});