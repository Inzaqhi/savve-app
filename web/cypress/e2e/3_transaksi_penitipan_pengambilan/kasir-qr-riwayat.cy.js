describe('Kasir - Scan QR Pengambilan & Riwayat Transaksi', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login('kasir1@savve.com', 'password123');
    cy.pilihEvent();

    cy.on('uncaught:exception', (err) => {
      if (err.message.includes('isScanning') || err.message.includes('Cannot access')) {
        return false;
      }
    });
  });

  // TC-AMBIL-03 | FS-TRANS-08 | Scan QR dengan nomor transaksi valid (positif)
  it('scan QR dengan nomor transaksi valid mengembalikan detail transaksi (JSON)', () => {
    // ── Step 1: Buat transaksi agar punya nomor_transaksi untuk di-scan ──
    cy.visit('/kasir/transaksi/create');
    cy.get('input[name="nama_penitip"]').type('Indah Permata');
    cy.get('input[name="no_whatsapp"]').type('081244445555');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]')
      .check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]')
      .check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox')
      .first()
      .check({ force: true });
    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });
    cy.url().should('match', /\/kasir\/transaksi\/\d+/);

    cy.get('body').invoke('text').then((fullText) => {
      const match = fullText.match(/SVV-[A-Za-z0-9]+-\d{4}/);
      expect(match, 'nomor transaksi ditemukan di halaman detail').to.not.be.null;
      const nomorTransaksi = match[0];

      // ── Step 2: Panggil endpoint scanQr() dari context browser yang sudah
      // login (cookie session + csrf_token() inline ikut terbawa) ──
      cy.visit('/kasir/pengambilan');
      cy.window().then((win) => {
        const csrfMatch = win.document.documentElement.innerHTML.match(
          /X-CSRF-TOKEN['"]\s*:\s*['"]([^'"]+)['"]/
        );
        expect(csrfMatch, 'csrf token ditemukan di halaman pengambilan').to.not.be.null;
        const token = csrfMatch[1];

        return win
          .fetch('/kasir/pengambilan/scan-qr', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-CSRF-TOKEN': token,
            },
            body: new URLSearchParams({ nomor_transaksi: nomorTransaksi }),
          })
          .then((res) => {
            if (!res.headers.get('content-type') || !res.headers.get('content-type').includes('application/json')) {
              return res.text().then((text) => {
                throw new Error(`Expected JSON but got status ${res.status} and body: ${text.slice(0, 400)}`);
              });
            }
            return res.json();
          })
          .then((json) => {
            expect(json.found).to.eq(true);
            expect(json.transaksi.nomor).to.eq(nomorTransaksi);
            expect(json.transaksi.nama_penitip).to.eq('Indah Permata');
            expect(json.transaksi.status).to.eq('dititip');
          });
      });
    });
  });

  // TC-AMBIL-04 | FS-TRANS-08 | Scan QR dengan nomor transaksi tidak ditemukan (negatif)
  it('scan QR dengan nomor transaksi tidak ditemukan mengembalikan found:false', () => {
    cy.visit('/kasir/pengambilan');
    cy.window().then((win) => {
      const csrfMatch = win.document.documentElement.innerHTML.match(
        /X-CSRF-TOKEN['"]\s*:\s*['"]([^'"]+)['"]/
      );
      const token = csrfMatch[1];

      return win
        .fetch('/kasir/pengambilan/scan-qr', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': token,
          },
          body: new URLSearchParams({ nomor_transaksi: 'SVV-TIDAKADA-9999' }),
        })
        .then((res) => res.json())
        .then((json) => {
          expect(json.found).to.eq(false);
        });
    });
  });

  // TC-AMBIL-05 | FS-TRANS-11 | Filter riwayat transaksi kasir berdasarkan status
  it('memfilter riwayat transaksi berdasarkan status DITITIPKAN', () => {
    cy.visit('/kasir/transaksi/create');
    cy.get('input[name="nama_penitip"]').type('Joko Widodo Santoso');
    cy.get('input[name="no_whatsapp"]').type('081266667777');
    cy.get('input[type="radio"][name="metode_bayar"][value="Cash"]')
      .check({ force: true });
    cy.get('input[type="radio"][name="items[0][ukuran]"][value="S"]')
      .check({ force: true });
    cy.get('input[type="checkbox"].jenis-checkbox')
      .first()
      .check({ force: true });
    cy.get('button[type="submit"]').contains('Simpan').click({ force: true });
    cy.url().should('match', /\/kasir\/transaksi\/\d+/);

    cy.visit('/kasir/transaksi?status=dititip');
    cy.url().should('include', 'status=dititip');
    cy.contains('Joko Widodo Santoso').should('exist');
  });
});
