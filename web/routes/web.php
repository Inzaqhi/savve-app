<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TransaksiController as AdminTransaksiController;
use App\Http\Controllers\Admin\LaporanController;

use App\Http\Controllers\Kasir\DashboardController as KasirDashboard;
use App\Http\Controllers\Kasir\TransaksiController;
use App\Http\Controllers\Kasir\PengambilanController;
use App\Http\Controllers\Kasir\EventSessionController;

/*
|--------------------------------------------------------------------------
| Redirect Awal
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Route Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboard::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Event
        |--------------------------------------------------------------------------
        */

        Route::resource('events', EventController::class);

        Route::get('/events/{event}/rekap', [EventController::class, 'rekap'])
            ->name('events.rekap');

        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        Route::resource('users', UserController::class);

        /*
|--------------------------------------------------------------------------
| Transaksi
|--------------------------------------------------------------------------
*/

        Route::get('/transaksis', [AdminTransaksiController::class, 'index'])
            ->name('transaksis.index');

        Route::get('/transaksis/{transaksi}', [AdminTransaksiController::class, 'show'])
            ->name('transaksis.show');

        Route::delete('/transaksis/{transaksi}', [AdminTransaksiController::class, 'destroy'])
            ->name('transaksis.destroy');

        /*
        |--------------------------------------------------------------------------
        | Laporan
        |--------------------------------------------------------------------------
        */

        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('laporan.index');

        Route::get('/laporan/export', [LaporanController::class, 'export'])
            ->name('laporan.export');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        Route::get('/search', [App\Http\Controllers\Admin\SearchController::class, 'index'])
            ->name('search');

        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'index'])
            ->name('profile');

        Route::put('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])
            ->name('profile.update');

        Route::put('/profile/password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])
            ->name('profile.password');
    });

/*
|--------------------------------------------------------------------------
| Route Pilih Event Kasir
| TANPA middleware event.selected
|--------------------------------------------------------------------------
*/

Route::prefix('kasir')
    ->middleware(['auth', 'role:kasir'])
    ->name('kasir.')
    ->group(function () {

        Route::get('/pilih-event', [EventSessionController::class, 'index'])
            ->name('event.select');

        Route::post('/pilih-event', [EventSessionController::class, 'pilih'])
            ->name('event.pilih');

        Route::post('/ganti-event', [EventSessionController::class, 'ganti'])
            ->name('event.ganti');
    });

/*
|--------------------------------------------------------------------------
| Route Kasir
| WAJIB memilih event terlebih dahulu
|--------------------------------------------------------------------------
*/

Route::prefix('kasir')
    ->middleware(['auth', 'role:kasir', 'event.selected'])
    ->name('kasir.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [KasirDashboard::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Transaksi
        |--------------------------------------------------------------------------
        */

        // Harus diletakkan sebelum resource
        Route::get('/transaksi/count-today', [TransaksiController::class, 'countToday'])
            ->name('transaksi.count-today');

        Route::resource('transaksi', TransaksiController::class);

        Route::get('/transaksi/{transaksi}/nota', [TransaksiController::class, 'nota'])
            ->name('transaksi.nota');

        Route::get('/transaksi/{transaksi}/tambah-barang', [TransaksiController::class, 'tambahBarang'])
            ->name('transaksi.tambah-barang');

        Route::post('/transaksi/{transaksi}/tambah-barang', [TransaksiController::class, 'simpanBarang'])
            ->name('transaksi.simpan-barang');

        Route::get('transaksi/{transaksi}/edit', [TransaksiController::class, 'edit'])->name('transaksi.edit');
        Route::put('transaksi/{transaksi}', [TransaksiController::class, 'update'])->name('transaksi.update');

        /*
        |--------------------------------------------------------------------------
        | Pengambilan Barang
        |--------------------------------------------------------------------------
        */

        Route::get('/pengambilan', [PengambilanController::class, 'index'])
            ->name('pengambilan.index');

        Route::get('/pengambilan/cari', function () {
            return redirect()->route('kasir.pengambilan.index');
        });

        Route::post('/pengambilan/cari', [PengambilanController::class, 'cari'])
            ->name('pengambilan.cari');

        Route::post('/pengambilan/scan-qr', [PengambilanController::class, 'scanQr'])
            ->name('pengambilan.scan-qr');

        Route::post('/pengambilan/konfirmasi/{transaksi}', [PengambilanController::class, 'konfirmasi'])
            ->name('pengambilan.konfirmasi');


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        Route::get('/search', [App\Http\Controllers\Kasir\SearchController::class, 'index'])
            ->name('search');

        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [App\Http\Controllers\Kasir\ProfileController::class, 'index'])
            ->name('profile');

        Route::put('/profile', [App\Http\Controllers\Kasir\ProfileController::class, 'update'])
            ->name('profile.update');

        Route::put('/profile/password', [App\Http\Controllers\Kasir\ProfileController::class, 'updatePassword'])
            ->name('profile.password');
    });

require __DIR__ . '/auth.php';
