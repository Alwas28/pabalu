<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Outlet;
use App\Models\OutletType;
use App\Models\ProPlan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// Bersihkan seluruh data outlet (dan semua yang mengikutinya lewat foreign key
// cascadeOnDelete: produk, transaksi, shift, pelanggan, laundry, sewa, dsb),
// SELURUH data Karyawan, dan akun kasir/admin_outlet — SEBELUM aplikasi
// diserahkan ke pengguna asli untuk uji coba.
// TIDAK menyentuh: akun admin & owner, Role & Permission, Jenis Outlet, Paket
// Pro — ini data referensi/konfigurasi sistem, bukan data uji coba, sesuai
// instruksi eksplisit user.
#[Signature('app:reset-test-data {--force : Lewati konfirmasi interaktif}')]
#[Description('Hapus semua outlet + turunannya, data Karyawan, dan akun kasir/admin_outlet — mempertahankan admin, owner, Role, Jenis Outlet, dan Paket Pro.')]
class ResetTestData extends Command
{
    public function handle(): int
    {
        $outletCount   = Outlet::count();
        $employeeCount = Employee::count();
        $staffCount    = User::whereHas('roleRelation', fn ($q) => $q->whereIn('slug', ['kasir', 'admin_outlet']))->count();

        if ($outletCount === 0 && $employeeCount === 0 && $staffCount === 0) {
            $this->info('Tidak ada data untuk dihapus — sudah bersih.');

            return self::SUCCESS;
        }

        $this->warn("Perintah ini akan menghapus PERMANEN:");
        $this->line("  - {$outletCount} outlet beserta SELURUH data turunannya (produk, transaksi, shift,");
        $this->line('    pelanggan, order laundry, data sewa, stok opname, pengeluaran, dan lainnya)');
        $this->line("  - {$employeeCount} data Karyawan");
        $this->line("  - {$staffCount} akun Kasir/Admin Outlet");
        $this->newLine();
        $this->line('YANG TETAP DIPERTAHANKAN: akun Admin & Owner, Role & Permission, Jenis Outlet, Paket Pro.');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Tindakan ini TIDAK BISA dibatalkan (tidak ada soft-delete). Lanjutkan?', false)) {
                $this->info('Dibatalkan, tidak ada yang dihapus.');

                return self::SUCCESS;
            }
        }

        $adminCountBefore = User::whereHas('roleRelation', fn ($q) => $q->where('slug', 'admin'))->count();
        $ownerCountBefore = User::whereHas('roleRelation', fn ($q) => $q->where('slug', 'owner'))->count();

        DB::transaction(function () {
            // rental_transactions WAJIB dihapus manual duluan, SEBELUM outlet —
            // customer_id & rental_unit_id di tabel itu RESTRICT (bukan cascade),
            // jadi kalau outlet dihapus dulu, MySQL keburu coba hapus customers/
            // rental_units yang masih direferensikan rental_transactions dan gagal
            // dengan error constraint (sudah kejadian & diverifikasi lewat tes nyata).
            // rental_payments ikut cascade lewat rental_transaction_id, tidak perlu
            // baris terpisah.
            DB::table('rental_transactions')->delete();

            // Outlet dulu (cascade produk/transaksi/dst), baru Employee, baru akun
            // staff — urutan ini penting: employees.outlet_id cuma nullOnDelete
            // (bukan cascade), jadi harus dihapus manual terpisah, bukan ikut
            // otomatis lewat penghapusan outlet.
            Outlet::query()->chunkById(50, function ($outlets) {
                foreach ($outlets as $outlet) {
                    $outlet->delete();
                }
            });

            Employee::query()->delete();

            User::whereHas('roleRelation', fn ($q) => $q->whereIn('slug', ['kasir', 'admin_outlet']))
                ->get()
                ->each(fn (User $u) => $u->delete());
        });

        $adminCountAfter = User::whereHas('roleRelation', fn ($q) => $q->where('slug', 'admin'))->count();
        $ownerCountAfter = User::whereHas('roleRelation', fn ($q) => $q->where('slug', 'owner'))->count();

        $this->newLine();
        $this->info('Selesai — outlet, data turunannya, Karyawan, dan akun Kasir/Admin Outlet berhasil dihapus.');
        $this->line('Akun admin sebelum: ' . $adminCountBefore . ' | sesudah: ' . $adminCountAfter . ($adminCountBefore === $adminCountAfter ? ' (aman, tidak berubah)' : ' (‼ BERUBAH, cek manual!)'));
        $this->line('Akun owner sebelum: ' . $ownerCountBefore . ' | sesudah: ' . $ownerCountAfter . ($ownerCountBefore === $ownerCountAfter ? ' (aman, tidak berubah)' : ' (‼ BERUBAH, cek manual!)'));
        $this->line('Jenis Outlet tersisa: ' . OutletType::count());
        $this->line('Paket Pro tersisa: ' . ProPlan::count());
        $this->line('Role tersisa: ' . Role::count());
        $this->line('Total user tersisa (semua role): ' . User::count());

        return self::SUCCESS;
    }
}
