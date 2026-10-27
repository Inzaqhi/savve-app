<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\JenisBarang;
use App\Models\Tarif;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransaksiController extends Controller
{
    private const MAX_FOTO_BASE64_LENGTH = 7_000_000;
    private const ALLOWED_MIME_TYPES     = ['image/jpeg', 'image/png', 'image/webp'];

    public function index(Request $request)
    {
        $eventId    = session('kasir_event_id');
        $events     = Event::orderBy('nama_event')->get();
        $eventAktif = Event::find($eventId);

        $query = Transaksi::with(['event', 'details.kategori'])
            ->where('kasir_id', auth()->id())
            ->where('event_id', $eventId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_penitip', 'like', "%{$search}%")
                    ->orWhere('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhere('no_whatsapp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('waktu_penitipan', [
                $request->tanggal_mulai . ' 00:00:00',
                $request->tanggal_selesai . ' 23:59:59',
            ]);
        } elseif ($request->filled('tanggal_mulai')) {
            $query->whereDate('waktu_penitipan', '>=', $request->tanggal_mulai);
        } elseif ($request->filled('tanggal_selesai')) {
            $query->whereDate('waktu_penitipan', '<=', $request->tanggal_selesai);
        }

        $transaksis = $query->latest('waktu_penitipan')->get();

        return view('kasir.transaksi.index', compact(
            'transaksis',
            'events',
            'eventAktif'
        ));
    }

    public function create()
    {
        $event = Event::find(session('kasir_event_id'));

        if (!$event || $event->status !== 'aktif') {
            return redirect()->route('kasir.event.select')
                ->with('warning', 'Event tidak aktif. Pilih event terlebih dahulu.');
        }

        // Jenis barang dikelompokkan per ukuran
        $jenisBarangs = JenisBarang::where('is_active', true)
            ->orderBy('ukuran')
            ->orderBy('urutan')
            ->get()
            ->groupBy('ukuran');

        // Tarif per ukuran untuk event ini
        $tarifs = Tarif::where('event_id', $event->id)
            ->get()
            ->keyBy('ukuran');

        return view('kasir.transaksi.create', compact('event', 'jenisBarangs', 'tarifs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_penitip'                        => 'required|string|max:255',
            'no_whatsapp'                         => 'required|string|max:20',
            'metode_bayar'                        => 'required|in:QRIS,Cash,Web',
            'items'                               => 'required|array|min:1',
            'items.*.ukuran'                      => 'required|in:S,M,L,XL,Gadget',
            'items.*.barang'                      => 'nullable|array',
            'items.*.barang.*.nama'               => 'required_with:items.*.barang|string|max:100',
            'items.*.barang.*.selected'           => 'nullable|in:0,1',
            'items.*.barang.*.keterangan'         => 'nullable|string|max:255',
            'items.*.barang.*.nomor_label'        => 'nullable|string|max:50',
            'items.*.jenis_barang_lainnya'        => 'nullable|string|max:500',
        ]);

        $items = $request->input('items');

        foreach ($items as $idx => &$item) {
            $jenis = [];

            // Barang dari checkbox terpilih, lengkap dengan keterangan & nomor label (opsional)
            foreach (($item['barang'] ?? []) as $row) {
                if (($row['selected'] ?? '0') === '1') {
                    $jenis[] = [
                        'nama'        => $row['nama'] ?? '-',
                        'keterangan'  => !empty($row['keterangan']) ? trim($row['keterangan']) : null,
                        'nomor_label' => !empty($row['nomor_label']) ? trim($row['nomor_label']) : null,
                    ];
                }
            }

            // Barang dari input manual "Lainnya" (tanpa keterangan/nomor, dipisah koma)
            if (!empty($item['jenis_barang_lainnya'])) {
                $extras = array_map('trim', explode(',', $item['jenis_barang_lainnya']));
                $extras = array_filter($extras);
                foreach ($extras as $extra) {
                    $jenis[] = ['nama' => $extra, 'keterangan' => null, 'nomor_label' => null];
                }
            }

            // Validasi manual: setiap item minimal harus punya 1 jenis barang
            if (empty($jenis)) {
                return back()->withInput()
                    ->withErrors([
                        "items.{$idx}.jenis_barang" => 'Pilih minimal satu jenis barang atau isi kolom "Lainnya".',
                    ]);
            }

            $item['jenis_barang'] = array_values($jenis);
            unset($item['barang'], $item['jenis_barang_lainnya']);
        }
        unset($item);

        $event = Event::find(session('kasir_event_id'));

        if (!$event || $event->status !== 'aktif') {
            return back()->withInput()
                ->with('error', 'Event tidak aktif.');
        }

        $tarifs = Tarif::where('event_id', $event->id)->get()->keyBy('ukuran');

        $transaksi = null;
        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                DB::transaction(function () use ($request, $event, $tarifs, $items, &$transaksi) {
                    $nomor = Transaksi::generateNomor($event);

                    $fotoPath = null;
                    if ($request->filled('foto_penitipan')) {
                        $fotoPath = $this->simpanFotoBase64($request->foto_penitipan, 'foto-penitipan');
                    }

                    $transaksi = Transaksi::create([
                        'nomor_transaksi'  => $nomor,
                        'event_id'         => $event->id,
                        'kasir_id'         => auth()->id(),
                        'nama_penitip'     => $request->nama_penitip,
                        'no_whatsapp'      => $request->no_whatsapp,
                        'metode_bayar'     => $request->metode_bayar,
                        'status'           => 'dititip',
                        'waktu_penitipan'  => now(),
                        'foto_penitipan'   => $fotoPath,
                    ]);

                    foreach ($items as $item) {
                        $ukuran      = $item['ukuran'];
                        $jenisBarang = $item['jenis_barang'];
                        $tarif       = $tarifs[$ukuran] ?? null;
                        $harga       = $tarif ? $tarif->harga : 0;

                        DetailTransaksi::create([
                            'transaksi_id' => $transaksi->id,
                            'ukuran'       => $ukuran,
                            'jenis_barang' => $jenisBarang,
                            'harga_satuan' => $harga,
                            'subtotal'     => $harga,
                        ]);
                    }
                });

                break; // sukses, keluar loop

            } catch (\Illuminate\Database\QueryException $e) {
                // 1062 = duplicate entry
                if ($e->errorInfo[1] == 1062 && $attempt < $maxAttempts) {
                    continue; // coba lagi dengan nomor baru
                }
                throw $e;
            }
        }

        return redirect()->route('kasir.transaksi.show', $transaksi)
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    public function edit(Transaksi $transaksi)
    {
        $this->authorizeEdit($transaksi);

        $transaksi->load('details');
        $event = $transaksi->event;

        $jenisBarangs = JenisBarang::where('is_active', true)
            ->orderBy('ukuran')
            ->orderBy('urutan')
            ->get()
            ->groupBy('ukuran');

        $tarifs = Tarif::where('event_id', $transaksi->event_id)
            ->get()
            ->keyBy('ukuran');

        return view('kasir.transaksi.edit', compact('transaksi', 'event', 'jenisBarangs', 'tarifs'));
    }

    public function update(Request $request, Transaksi $transaksi)
    {
        $this->authorizeEdit($transaksi);

        $request->validate([
            'nama_penitip'                 => 'required|string|max:255',
            'no_whatsapp'                  => 'required|string|max:20',
            'metode_bayar'                 => 'required|in:QRIS,Cash,Web',
            'items'                        => 'required|array|min:1',
            'items.*.ukuran'               => 'required|in:S,M,L,XL,Gadget',
            'items.*.barang'               => 'nullable|array',
            'items.*.barang.*.nama'        => 'required_with:items.*.barang|string|max:100',
            'items.*.barang.*.selected'    => 'nullable|in:0,1',
            'items.*.barang.*.keterangan'  => 'nullable|string|max:255',
            'items.*.barang.*.nomor_label' => 'nullable|string|max:50',
            'items.*.jenis_barang_lainnya' => 'nullable|string|max:500',
        ]);

        $items = $request->input('items');

        foreach ($items as $idx => &$item) {
            $jenis = [];

            foreach (($item['barang'] ?? []) as $row) {
                if (($row['selected'] ?? '0') === '1') {
                    $jenis[] = [
                        'nama'        => $row['nama'] ?? '-',
                        'keterangan'  => !empty($row['keterangan']) ? trim($row['keterangan']) : null,
                        'nomor_label' => !empty($row['nomor_label']) ? trim($row['nomor_label']) : null,
                    ];
                }
            }

            if (!empty($item['jenis_barang_lainnya'])) {
                $extras = array_map('trim', explode(',', $item['jenis_barang_lainnya']));
                $extras = array_filter($extras);
                foreach ($extras as $extra) {
                    $jenis[] = ['nama' => $extra, 'keterangan' => null, 'nomor_label' => null];
                }
            }

            if (empty($jenis)) {
                return back()->withInput()
                    ->withErrors([
                        "items.{$idx}.jenis_barang" => 'Pilih minimal satu jenis barang atau isi kolom "Lainnya".',
                    ]);
            }

            $item['jenis_barang'] = array_values($jenis);
            unset($item['barang'], $item['jenis_barang_lainnya']);
        }
        unset($item);

        $tarifs = Tarif::where('event_id', $transaksi->event_id)->get()->keyBy('ukuran');

        DB::transaction(function () use ($request, $transaksi, $tarifs, $items) {
            $fotoPath = $transaksi->foto_penitipan;

            if ($request->boolean('hapus_foto_penitipan')) {
                $fotoPath = null;
            }
            if ($request->filled('foto_penitipan')) {
                $fotoPath = $this->simpanFotoBase64($request->foto_penitipan, 'foto-penitipan');
            }

            $transaksi->update([
                'nama_penitip'   => $request->nama_penitip,
                'no_whatsapp'    => $request->no_whatsapp,
                'metode_bayar'   => $request->metode_bayar,
                'foto_penitipan' => $fotoPath,
            ]);

            // Hapus detail lama, ganti dengan yang baru
            $transaksi->details()->delete();

            foreach ($items as $item) {
                $ukuran      = $item['ukuran'];
                $jenisBarang = $item['jenis_barang'];
                $tarif       = $tarifs[$ukuran] ?? null;
                $harga       = $tarif ? $tarif->harga : 0;

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'ukuran'       => $ukuran,
                    'jenis_barang' => $jenisBarang,
                    'harga_satuan' => $harga,
                    'subtotal'     => $harga,
                ]);
            }
        });

        return redirect()->route('kasir.transaksi.show', $transaksi)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    private function authorizeEdit(Transaksi $transaksi): void
    {
        if ($transaksi->kasir_id !== auth()->id()) {
            abort(403, 'Anda tidak punya akses untuk mengedit transaksi ini.');
        }
        if (!in_array($transaksi->status, ['dititip', 'terlambat'])) {
            abort(403, 'Transaksi yang sudah diambil tidak bisa diedit.');
        }
    }

    public function show(Transaksi $transaksi)
    {
        if ($transaksi->kasir_id !== auth()->id()) {
            abort(403, 'Anda tidak punya akses untuk melihat transaksi ini.');
        }

        $transaksi->load(['event', 'kasir', 'details']);
        return view('kasir.transaksi.show', compact('transaksi'));
    }

    public function nota(Transaksi $transaksi)
    {
        $transaksi->load(['event', 'kasir', 'details']);
        return view('kasir.transaksi.nota', compact('transaksi'));
    }

    public function countToday()
    {
        $eventId = session('kasir_event_id');
        $count   = Transaksi::where('event_id', $eventId)->count();
        return response()->json(['count' => $count]);
    }

    private function simpanFotoBase64(string $base64, string $folder): ?string
    {
        if (strlen($base64) > self::MAX_FOTO_BASE64_LENGTH) return null;

        $raw        = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $binaryData = base64_decode($raw, strict: true);
        if (!$binaryData) return null;

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($binaryData);
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) return null;

        $ext      = match ($mimeType) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $path = 'foto-transaksi/' . $folder . '/' . $folder . '-' . uniqid() . '.' . $ext;
        Storage::disk('public')->put($path, $binaryData);

        return $path;
    }
}
