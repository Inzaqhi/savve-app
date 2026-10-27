<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengambilanController extends Controller
{
    private const MAX_FOTO_BASE64_LENGTH = 7_000_000;
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function index()
    {
        return view('kasir.pengambilan.index');
    }

    public function cari(Request $request)
    {
        $request->validate([
            'nama_penitip' => 'required|string|max:100',
        ]);

        $kasirId = auth()->id();
        $eventId = session('kasir_event_id');
        $nama    = $request->nama_penitip;

        $transaksis = Transaksi::with(['event', 'details', 'kasir'])
            ->where('kasir_id', $kasirId)
            ->where('event_id', $eventId)
            ->where('nama_penitip', 'like', '%' . $nama . '%')
            ->whereIn('status', ['dititip', 'terlambat'])
            ->get();

        if ($transaksis->isEmpty()) {
            return view('kasir.pengambilan.index', [
                'errorPencarian' => 'Nama penitip tidak ditemukan di event ini, atau barang sudah diambil.',
                'namaDicari'     => $nama,
            ]);
        }

        return view('kasir.pengambilan.index', [
            'transaksis' => $transaksis,
            'namaDicari' => $nama,
        ]);
    }

    public function konfirmasi(Request $request, Transaksi $transaksi)
    {
        if ($transaksi->kasir_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($transaksi->status === 'sudah_diambil') {
            return redirect()->route('kasir.pengambilan.index')
                ->with('error', 'Barang ini sudah diambil sebelumnya.');
        }

        $isTerlambat = $transaksi->status === 'terlambat';

        $fotoPath = null;
        if ($request->filled('foto_pengambilan')) {
            $fotoPath = $this->simpanFotoBase64(
                $request->foto_pengambilan,
                'foto-pengambilan'
            );

            if ($fotoPath === false) {
                return back()->with('error', 'Foto tidak valid atau ukuran terlalu besar (maks 5 MB).');
            }
        }

        $transaksi->update([
            'status'            => 'sudah_diambil',
            'waktu_pengambilan' => now(),
            'foto_pengambilan'  => $fotoPath,
        ]);

        $pesan = $isTerlambat
            ? "Barang atas nama {$transaksi->nama_penitip} berhasil diambil. ⚠️ Pengambilan melebihi batas waktu event."
            : "Barang atas nama {$transaksi->nama_penitip} berhasil diambil.";

        return redirect()->route('kasir.pengambilan.index')
            ->with('success', $pesan);
    }

    public function scanQr(Request $request)
    {
        $request->validate(['nomor_transaksi' => 'required|string']);

        $transaksi = Transaksi::with(['event', 'details'])
            ->where('nomor_transaksi', $request->nomor_transaksi)
            ->whereIn('status', ['dititip', 'terlambat'])
            ->first();

        if (!$transaksi) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'     => true,
            'transaksi' => [
                'id'                    => $transaksi->id,
                'nomor'                 => $transaksi->nomor_transaksi,
                'nama_penitip'          => $transaksi->nama_penitip,
                'no_whatsapp'           => $transaksi->no_whatsapp,
                'event'                 => $transaksi->event->nama_event,
                'status'                => $transaksi->status,
                'waktu_penitipan'       => $transaksi->waktu_penitipan->format('d M Y H:i'),
                'total_barang'          => $transaksi->details->count(),
                'foto_penitipan'        => $transaksi->foto_penitipan
                    ? asset('storage/' . $transaksi->foto_penitipan)
                    : null,
                'details'               => $transaksi->details->map(fn($d) => [
                    'nama'     => $d->jenis_barang_string,
                    'ukuran'   => $d->ukuran,
                    'subtotal' => 'Rp ' . number_format($d->subtotal, 0, ',', '.'),
                ]),
                'total_transaksi_aktif' => 1,
            ],
        ]);
    }

    private function simpanFotoBase64(string $base64, string $folder): string|false
    {
        if (strlen($base64) > self::MAX_FOTO_BASE64_LENGTH) {
            return false;
        }

        $raw        = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $binaryData = base64_decode($raw, strict: true);

        if ($binaryData === false) {
            return false;
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($binaryData);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, strict: true)) {
            return false;
        }

        $ext = match ($mimeType) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $path = 'foto-transaksi/' . $folder . '/' . $folder . '-' . uniqid() . '.' . $ext;
        Storage::disk('public')->put($path, $binaryData);

        return $path;
    }
}
