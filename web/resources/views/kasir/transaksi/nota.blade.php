@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;

    // Susun urutan halaman: 1 nota + N label kategori barang
    $halaman = collect([['tipe' => 'nota']])->concat(
        $transaksi->details->map(fn($detail) => ['tipe' => 'label', 'detail' => $detail]),
    );
    $totalLembar = $halaman->count();
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota {{ $transaksi->nomor_transaksi }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
    <style>
        :root {
            --lebar-kertas: 58mm;
            --lebar-cetak-aman: 46mm;
            /* ganti dari --offset-cetak jadi ini */
            --pad-x: 3.5mm;
            --pad-y: 3mm;
            --pad-x-print: 2.5mm;
            --pad-y-print: 2mm;

            --tinggi-nota: 200mm;
            --tinggi-label: 120mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Verdana', 'Tahoma', 'Segoe UI', sans-serif;
            font-size: 9px;
            line-height: 1.5;
            letter-spacing: 0.15px;
            background: #ddd;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 28px 16px 48px;
            min-height: 100vh;
        }

        /* ===== Wrapper & separator ===== */
        .nota-wrapper {
            width: var(--lebar-kertas);
            background: #fff;
            padding: var(--pad-y) var(--pad-x);
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.18);
        }

        .nota-separator {
            width: var(--lebar-kertas);
            display: flex !important;
            padding: 14px 0 !important;
            align-items: center;
            gap: 6px;
            padding: 10px 0;
            color: #aaa;
            font-size: 7px;
            letter-spacing: 1px;
            text-transform: uppercase;
            user-select: none;
        }

        .nota-separator::before,
        .nota-separator::after {
            content: '';
            flex: 1;
            border-top: 1px dashed #bbb;
        }

        /* ===== Title bar tiap lembar ===== */
        .doc-title-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1.5px solid #000;
            padding: 3px 5px;
            margin-bottom: 5px;
        }

        .doc-title-bar .doc-title {
            font-size: 7.5px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .doc-title-bar .doc-lembar {
            font-size: 6.5px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #555;
            border-left: 1px solid #ccc;
            padding-left: 5px;
        }

        /* ===== Header logo + brand ===== */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 4px;
            margin-bottom: 4px;
            border-bottom: 2px solid #000;
            gap: 5px;
        }

        .header img {
            height: 24px;
            width: auto;
            object-fit: contain;
            filter: grayscale(100%) contrast(160%);
        }

        .header-right {
            text-align: right;
        }

        .header-right .brand {
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            line-height: 1;
        }

        .header-right .tagline {
            font-size: 5.5px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #666;
            margin-top: 2px;
        }

        /* ===== Identitas penitip + QR ===== */
        .identity-block {
            display: flex;
            align-items: stretch;
            border: 1.5px solid #000;
            margin-bottom: 5px;
        }

        .identity-left {
            flex: 1;
            min-width: 0;
            padding: 4px 5px;
            border-right: 1.5px solid #000;
        }

        /* Lembar label tidak punya QR, jadi identity-left full-width tanpa garis pemisah */
        .identity-block--label .identity-left {
            border-right: none;
        }

        .identity-left .lbl {
            font-size: 6px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 1px;
        }

        .identity-left .nama {
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            line-height: 1.2;
            word-break: break-word;
        }

        .identity-left .kode-wrap {
            margin-top: 4px;
            padding-top: 3px;
            border-top: 1px dashed #bbb;
        }

        .identity-left .kode {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.8px;
        }

        .identity-right {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4px;
            gap: 2px;
        }

        /*
             * PERBAIKAN KUALITAS QR (1/3):
             * Perbesar ukuran fisik QR dari 72px -> 92px (~24mm pada cetak).
             * QR kecil + padat = modul rapat = mudah blur di printer thermal
             * resolusi rendah (180-203 dpi). Ukuran lebih besar = lebih toleran.
             */
        .identity-right svg {
            display: block;
            width: 92px !important;
            height: 92px !important;
            flex-shrink: 0;
        }

        .identity-right .qr-label {
            font-size: 5px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            color: #666;
            text-align: center;
            line-height: 1.2;
        }

        /* ===== Badge ukuran (lembar label) ===== */
        .ukuran-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #000;
            font-weight: 900;
            text-align: center;
            margin-bottom: 3px;
            box-sizing: border-box;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .ukuran-badge--short {
            min-width: 34px;
            height: 34px;
            padding: 0 2px;
            font-size: 22px;
        }

        .ukuran-badge--long {
            min-width: 34px;
            height: 22px;
            padding: 0 6px;
            font-size: 9px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .kategori-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 1px 8px;
        }

        .kategori-row .info-jenis-label {
            font-size: 7.5px;
            color: #666;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .kategori-row .info-jenis-nilai {
            font-size: 15px;
            font-weight: 900;
            line-height: 1.4;
        }

        .kategori-row .info-jenis-tarif {
            font-size: 8px;
            color: #555;
            margin-top: 4px;
        }

        .barang-detail-block {
            margin-bottom: 4px;
        }

        .barang-detail-block:last-child {
            margin-bottom: 0;
        }

        .nomor-label-badge {
            display: inline-block;
            margin-left: 4px;
            padding: 1px 6px;
            border: 1.3px solid #000;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 900;
            vertical-align: middle;
        }

        .info-jenis-keterangan {
            font-size: 8px;
            color: #555;
            margin-top: 1px;
        }

        .col-detail-ket {
            font-size: 7px;
            color: #666;
            font-weight: 400;
            display: block;
            margin-top: 1px;
        }

        /* ===== Garis & judul section ===== */
        .divider {
            border: none;
            border-top: 1px dashed #aaa;
            margin: 4px 0;
        }

        .divider-solid {
            border: none;
            border-top: 1.5px solid #000;
            margin: 4px 0;
        }

        .section-title {
            font-size: 6.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            text-align: center;
            margin: 4px 0;
        }

        /* ===== Info baris (event, waktu, dst) ===== */
        .info-section {
            margin-bottom: 4px;
        }

        .info-row {
            display: flex;
            align-items: baseline;
            padding: 1.5px 0;
            font-size: 8px;
            letter-spacing: 0.2px;
            gap: 2px;
        }

        .info-row .lbl {
            color: #555;
            width: 38%;
            flex-shrink: 0;
        }

        .info-row .sep {
            color: #999;
            flex-shrink: 0;
        }

        .info-row .val {
            font-weight: 700;
            flex: 1;
            text-align: right;
        }

        /* ===== Tabel daftar barang ===== */
        .barang-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            letter-spacing: 0.2px;
            margin-bottom: 2px;
        }

        .barang-table thead th {
            font-size: 6.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #555;
            padding: 2px 1px 3px;
            border-bottom: 1px solid #000;
            text-align: left;
        }

        .barang-table thead th:last-child {
            text-align: right;
        }

        .barang-table tbody td {
            padding: 3px 1px;
            vertical-align: top;
            border-bottom: 1px dotted #ccc;
        }

        .barang-table tbody tr:last-child td {
            border-bottom: none;
        }

        .barang-table .col-no {
            width: 8%;
            color: #555;
        }

        .barang-table .col-nama {
            width: 44%;
            font-weight: 700;
            font-size: 10px;
        }

        .barang-table .col-detail {
            width: 24%;
            color: #555;
            font-size: 7px;
        }

        /* Ukuran barang (S/M/L/XL/XXL) dicetak besar agar mudah dibaca petugas */
        .barang-table td.col-detail {
            color: #000;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.1;
            text-align: center;
            white-space: nowrap;
        }

        .barang-table td.col-detail.col-detail--panjang {
            font-size: 10px;
        }

        .barang-table .col-harga {
            width: 24%;
            font-family: 'Consolas', 'Courier New', monospace;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-align: right;
        }

        /* ===== Total pembayaran ===== */
        .total-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1.5px solid #000;
            padding: 4px 5px;
            margin: 5px 0;
        }

        .total-box .total-label {
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .total-box .total-value {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        /* ===== Footer nota & label ===== */
        .footer,
        .footer-barang {
            text-align: center;
            margin-top: 6px;
            padding-top: 5px;
        }

        .footer {
            border-top: 1.5px solid #000;
        }

        .footer-barang {
            border-top: 1px dashed #000;
        }

        .footer .f-warning,
        .footer-barang .f-warning {
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .footer .f-note,
        .footer-barang .f-note {
            font-size: 6.5px;
            color: #444;
            line-height: 1.5;
        }

        .footer .f-thanks {
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .footer .f-copy {
            font-size: 6px;
            color: #999;
            margin-top: 2px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ===== Tombol aksi (tidak ikut tercetak) ===== */
        .print-actions {
            margin-top: 20px;
            /* selalu terlihat di bawah layar, walau nota panjang */
            position: sticky;
            bottom: 0;
            z-index: 10;
            padding: 10px 0 12px;
            background: inherit;
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: var(--lebar-kertas);
            min-width: 220px;
        }

        .print-actions-row {
            display: flex;
            gap: 8px;
        }

        .btn-print,
        .btn-close {
            flex: 1;
            padding: 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            font-family: sans-serif;
            letter-spacing: 0.5px;
            transition: opacity .2s, background .15s;
        }

        .btn-print {
            background: #000;
            color: #fff;
            border: none;
        }

        .btn-print:hover {
            opacity: .8;
        }

        .btn-close {
            background: #fff;
            color: #000;
            border: 2px solid #000;
        }

        .btn-close:hover {
            background: #f0f0f0;
        }

        /* ===== Loading indicator saat tombol cetak ditekan ===== */
        .btn-print.loading {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        .print-hint {
            font-size: 10.5px;
            color: #666;
            text-align: center;
            font-family: sans-serif;
        }

        /* Dipakai sesaat oleh tombol cetak untuk mengukur tinggi nota dengan ukuran cetak */
        .ukur-cetak .nota-wrapper {
            width: var(--lebar-cetak-aman) !important;
            margin: 0 !important;
            padding: var(--pad-y-print) var(--pad-x-print) !important;
        }

        /* ===== PRINT STYLES ===== */
        @media print {

            html,
            body {
                width: 58mm !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }

            .nota-wrapper {
                width: var(--lebar-cetak-aman) !important;
                /* Di tengah kertas 58 mm: printer thermal hanya mencetak ±48 mm bagian tengah,
                   jadi isi yang rata kiri terpotong di sisi kiri. */
                margin: 0 auto !important;
                padding: var(--pad-y-print) var(--pad-x-print) !important;
            }
        }

        .nota-wrapper--akhir {
            page-break-after: avoid;
            break-after: avoid;
        }

        /*
             * TAHAP 2: Named pages untuk ukuran berbeda per tipe lembar.
             * Didukung Chrome 85+. Fallback ukuran @page global di bawah
             * tetap aktif untuk browser lain.
             */
        .nota-wrapper[data-tipe="nota"] {
            page: halaman-nota;
        }

        .nota-wrapper[data-tipe="label"] {
            page: halaman-label;
        }

        @page halaman-nota {
            margin: 0;
            size: 58mm var(--tinggi-nota);
        }

        @page halaman-label {
            margin: 0;
            size: 58mm var(--tinggi-label);
        }

        /* Fallback: ukuran @page global (diisi JS, dipakai browser tanpa named page) */
        @page {
            margin: 0;
            size: 58mm var(--tinggi-nota);
        }

        /* Aturan khusus cetak: tombol disembunyikan, warna dipaksa hitam-putih.
           (Sebelumnya berada di luar @media print sehingga tombol cetak ikut hilang di layar.) */
        @media print {
            .print-actions {
                display: none !important;
            }

            /*
                 * TAHAP 3: Override warna global, tapi kecualikan area QR code
                 * agar path SVG QR tidak rusak saat dicetak.
                 */
            *:not(.identity-right):not(.identity-right *) {
                background: #fff !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /*
                 * PERBAIKAN KUALITAS QR (2/3):
                 * - shape-rendering: crispEdges mencegah anti-aliasing yang
                 *   membuat tepi modul QR jadi abu-abu/blur saat dicetak.
                 * - image-rendering: pixelated sebagai cadangan untuk browser
                 *   yang merender SVG sebagai raster saat print.
                 * - vector-effect: non-scaling-stroke menjaga ketebalan garis
                 *   tetap konsisten walau SVG di-scale oleh CSS width/height.
                 */
            .identity-right svg,
            .identity-right svg * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                shape-rendering: crispEdges !important;
                image-rendering: pixelated !important;
                vector-effect: non-scaling-stroke;
            }

            /* Hanya paksa putih pada elemen background asli */
            .identity-right svg rect[fill="#ffffff"],
            .identity-right svg rect[fill="#FFFFFF"],
            .identity-right svg rect[fill="white"] {
                fill: #fff !important;
            }

            /*
                 * PERBAIKAN KUALITAS QR (3/3):
                 * Aturan lama hanya menyasar path[fill="#000000"]. Library QR
                 * (BaconQrCode) kadang merender modul sebagai <rect>, bukan
                 * <path>, sehingga aturan lama tidak kena dan modul ikut
                 * di-grayscale oleh browser saat print (jadi pudar/abu-abu).
                 * Aturan di bawah memaksa SEMUA elemen gambar (rect & path)
                 * yang bukan background menjadi hitam solid, apa pun fill
                 * aslinya.
                 */
            .identity-right svg rect:not([fill="#ffffff"]):not([fill="#FFFFFF"]):not([fill="white"]),
            .identity-right svg path:not([fill="none"]) {
                fill: #000 !important;
                stroke: none !important;
            }
        }
    </style>
</head>

<body>

    @foreach ($halaman as $h)
        @if ($h['tipe'] === 'label')
            {{--
                Separator hanya tampil di layar (di-hide via CSS saat print).
                Tidak perlu lagi menjadi pemisah visual antar halaman thermal.
            --}}
            <div class="nota-separator">
                &#8212; potong di sini &nbsp;&middot;&nbsp; lembar {{ $loop->iteration }} ditempel pada barang &#8212;
            </div>
        @endif

        {{--
            TAHAP 2: Tambahkan data-tipe untuk named page CSS.
            TAHAP 1: nota-wrapper--akhir tetap dipakai untuk break-after: avoid.
        --}}
        <div class="nota-wrapper @if ($loop->last) nota-wrapper--akhir @endif"
            data-tipe="{{ $h['tipe'] }}">
            <div class="doc-title-bar">
                <span class="doc-title">
                    {{ $h['tipe'] === 'nota' ? 'Bukti Transaksi Penitipan' : 'Label Identifikasi Barang' }}
                </span>
                <span class="doc-lembar">Lembar {{ $loop->iteration }} / {{ $totalLembar }}</span>
            </div>

            <div class="header">
                <img src="{{ asset('images/Logo.png') }}" alt="Savve">
                <div class="header-right">
                    <div class="brand">Savve</div>
                    <div class="tagline">Layanan Penitipan Barang</div>
                </div>
            </div>

            <div class="identity-block @if ($h['tipe'] !== 'nota') identity-block--label @endif">
                <div class="identity-left">
                    <div class="lbl">Nama Penitip</div>
                    <div class="nama">{{ $transaksi->nama_penitip }}</div>
                    <div class="kode-wrap">
                        <div class="lbl">Kode Penitipan</div>
                        <div class="kode">{{ $transaksi->nomor_transaksi }}</div>
                    </div>
                </div>
                @if ($h['tipe'] === 'nota')
                    {{--
                        QR HANYA DI LEMBAR NOTA (lembar 1):
                        Lembar label barang tidak lagi menampilkan QR, cukup
                        nama penitip + kode penitipan saja. QR untuk verifikasi
                        pengambilan tetap ada di nota utama.
                    --}}
                    <div class="identity-right">
                        {!! QrCode::size(300)->margin(1)->errorCorrection('H')->style('square')->generate($transaksi->nomor_transaksi) !!}
                        <div class="qr-label">Scan saat<br>pengambilan</div>
                    </div>
                @endif
            </div>

            @if ($h['tipe'] === 'nota')
                <div class="info-section">
                    <div class="info-row">
                        <span class="lbl">Event</span>
                        <span class="sep">:</span>
                        <span class="val">{{ $transaksi->event->nama_event }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Waktu Penitipan</span>
                        <span class="sep">:</span>
                        <span class="val">{{ $transaksi->waktu_penitipan->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">No. WhatsApp</span>
                        <span class="sep">:</span>
                        <span class="val">{{ $transaksi->no_whatsapp }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Metode Bayar</span>
                        <span class="sep">:</span>
                        <span class="val">{{ $transaksi->metode_bayar }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Kasir</span>
                        <span class="sep">:</span>
                        <span class="val">{{ $transaksi->kasir->name }}</span>
                    </div>
                </div>

                <hr class="divider">

                <div class="section-title">— Daftar Barang —</div>
                <table class="barang-table">
                    <thead>
                        <tr>
                            <th class="col-no">#</th>
                            <th class="col-nama">Nama Barang</th>
                            <th class="col-detail" style="text-align:center">Ukuran</th>
                            <th class="col-harga">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi->details as $i => $detail)
                            <tr>
                                <td class="col-no">{{ $i + 1 }}</td>
                                <td class="col-nama">
                                    @foreach ($detail->jenisBarangFormatted() as $jb)
                                        <div>
                                            {{ $jb['nama'] }}@if (!empty($jb['nomor_label']))
                                                <span class="nomor-label-badge">#{{ $jb['nomor_label'] }}</span>
                                            @endif
                                            @if (!empty($jb['keterangan']))
                                                <span class="col-detail-ket">{{ $jb['keterangan'] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                                <td class="col-detail {{ strlen($detail->ukuran) > 3 ? 'col-detail--panjang' : '' }}">{{ $detail->ukuran }}</td>
                                <td class="col-harga">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <hr class="divider-solid">

                <div class="total-box">
                    <span class="total-label">Total Pembayaran</span>
                    <span class="total-value">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
                </div>

                <div class="footer">
                    <div class="f-warning">Simpan nota ini sebagai bukti transaksi</div>
                    <div class="f-note">
                        Tunjukkan nota atau scan QR saat pengambilan barang.<br>
                        Barang tidak akan diserahkan tanpa bukti transaksi ini.
                    </div>
                    <div class="f-thanks">Terima Kasih</div>
                    <div class="f-copy">© {{ date('Y') }} Vendor Savve</div>
                </div>
            @else
                <div class="info-section">
                    <div class="info-row">
                        <span class="lbl">Event</span>
                        <span class="sep">:</span>
                        <span class="val">{{ $transaksi->event->nama_event }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Waktu Penitipan</span>
                        <span class="sep">:</span>
                        <span class="val">{{ $transaksi->waktu_penitipan->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">No. WhatsApp</span>
                        <span class="sep">:</span>
                        <span class="val">{{ $transaksi->no_whatsapp }}</span>
                    </div>
                </div>

                <hr class="divider">

                <div class="section-title">— Kategori Barang Ini —</div>
                @php
                    $ukuranLabel = $h['detail']->ukuran;
                    $kelasBadge = strlen($ukuranLabel) > 3 ? 'ukuran-badge--long' : 'ukuran-badge--short';
                @endphp
                <div class="kategori-row">
                    <div class="ukuran-badge {{ $kelasBadge }}">{{ $ukuranLabel }}</div>
                    <div style="flex: 1; min-width: 0">
                        <div class="info-jenis-label">Jenis Barang</div>
                        @foreach ($h['detail']->jenisBarangFormatted() as $jb)
                            <div class="barang-detail-block">
                                <div class="info-jenis-nilai">
                                    {{ $jb['nama'] }}
                                    @if (!empty($jb['nomor_label']))
                                        <span class="nomor-label-badge">No. {{ $jb['nomor_label'] }}</span>
                                    @endif
                                </div>
                                @if (!empty($jb['keterangan']))
                                    <div class="info-jenis-keterangan">{{ $jb['keterangan'] }}</div>
                                @endif
                            </div>
                        @endforeach
                        <div class="info-jenis-tarif">Tarif: Rp
                            {{ number_format($h['detail']->subtotal, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="footer-barang">
                    <div class="f-warning">Tempelkan pada barang titipan kategori {{ $h['detail']->ukuran }}</div>
                    <div class="f-note">
                        Jangan lepaskan sebelum barang diambil oleh penitip.<br>
                        Lembar ini digunakan untuk identifikasi saat pengambilan.
                    </div>
                </div>
            @endif
        </div>
    @endforeach

    <div class="print-actions">
        <div class="print-actions-row">
            <button id="btnCetakPdf" class="btn-print" type="button">
                🖶 Cetak {{ $totalLembar }} Lembar
            </button>
            <button class="btn-close" type="button" onclick="window.close()">✕ Tutup</button>
        </div>
        <div class="print-hint">
            Tips: nonaktifkan opsi "Headers and footers" pada dialog print agar ukuran hasil cetak tetap pas.
        </div>
    </div>

    <script>
        /**
         * Konversi px (96 dpi layar) ke milimeter.
         */
        function pxToMm(px) {
            return px / 3.7795275591;
        }

        window.addEventListener('load', function() {
            const btn = document.getElementById('btnCetakPdf');

            btn.addEventListener('click', async function() {
                const wrappers = document.querySelectorAll('.nota-wrapper');
                if (!wrappers.length) return;

                btn.classList.add('loading');
                btn.textContent = '⏳ Menyiapkan...';

                const LEBAR_MM = 58;
                const JARAK_MM = 8; // ruang kosong per lembar + garis potong

                // Tinggi tiap lembar diukur dengan lebar & padding yang sama seperti saat dicetak
                // (lebih sempit daripada di layar, sehingga teks membungkus lebih banyak), ditambah
                // cadangan kecil agar tidak meluber ke halaman berikutnya.
                document.documentElement.classList.add('ukur-cetak');
                const tinggiLembar = Array.from(wrappers).map(function(wrapper) {
                    return Math.ceil((pxToMm(wrapper.getBoundingClientRect().height) + JARAK_MM) * 1.03 + 5);
                });
                document.documentElement.classList.remove('ukur-cetak');
                const totalMm = tinggiLembar.reduce((a, b) => a + b, 0);

                const existingStyle = document.getElementById('dynamic-page-size');
                if (existingStyle) existingStyle.remove();

                // Untuk browser & Android: semua lembar dicetak bersambung dalam satu halaman.
                // Ukuran halaman harus angka pasti: var() tidak berlaku di dalam @page.
                const style = document.createElement('style');
                style.id = 'dynamic-page-size';
                style.textContent = `
            @media print {
                @page { margin: 0; size: ${LEBAR_MM}mm ${totalMm}mm; }
                .nota-wrapper { page: auto !important; break-before: auto !important; break-after: auto !important; }
                .nota-wrapper + .nota-separator { display: block !important; height: 0; margin: 4mm 0 !important; border-top: 1px dashed #000; font-size: 0; }
                .nota-separator::before, .nota-separator::after { display: none !important; }
            }
        `;
                document.head.appendChild(style);

                const selesai = function() {
                    btn.classList.remove('loading');
                    btn.textContent = '🖶 Cetak {{ $totalLembar }} Lembar';
                };

                await new Promise((r) => requestAnimationFrame(() => setTimeout(r, 300)));

                try {
                    if (window.savveDesktop && window.savveDesktop.printNota) {
                        // Aplikasi Windows: ukuran kertas dikirim langsung ke driver printer,
                        // supaya printer thermal tidak memakai kertas default (mis. 58 x 3276 mm).
                        // Tiap lembar dicetak terpisah dengan panjangnya sendiri dan skala 100%.
                        const hasil = await window.savveDesktop.printNota({ widthMm: LEBAR_MM, sheets: tinggiLembar });
                        if (hasil && !hasil.success && hasil.reason && hasil.reason !== 'Print job canceled' && hasil.reason !== 'cancelled') {
                            alert('Gagal mencetak: ' + hasil.reason);
                        }
                    } else if (window.SavveAndroid && window.SavveAndroid.printSized) {
                        window.SavveAndroid.printSized(document.title || 'Nota Savve', LEBAR_MM, totalMm);
                    } else {
                        // Browser biasa: ukuran halaman tetap berlaku sampai dialog print ditutup
                        window.print();
                    }
                } finally {
                    selesai();
                }
            });
        });

        function pxToMm(px) {
            return px / 3.7795275591;
        }

        // Dipanggil aplikasi Windows: tampilkan hanya lembar ke-i saat dicetak (i < 0 = semua lembar).
        window.savvePilihLembar = function(i, tinggiMm) {
            const root = document.documentElement;
            document.querySelectorAll('.nota-wrapper').forEach(function(w, idx) {
                w.classList.toggle('lembar-aktif', idx === i);
            });
            let style = document.getElementById('savve-lembar-size');
            if (i < 0) {
                root.classList.remove('satu-lembar');
                if (style) style.remove();
                return;
            }
            root.classList.add('satu-lembar');
            if (!style) {
                style = document.createElement('style');
                style.id = 'savve-lembar-size';
                document.head.appendChild(style);
            }
            style.textContent = `
            @media print {
                @page { margin: 0; size: 58mm ${tinggiMm}mm; }
                .satu-lembar .nota-wrapper:not(.lembar-aktif), .satu-lembar .nota-separator { display: none !important; }
            }`;
        };
    </script>
</body>

</html>
