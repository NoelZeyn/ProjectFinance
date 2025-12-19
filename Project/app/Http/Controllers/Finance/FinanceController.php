<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Balances;
use App\Models\Finance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $adminId = Auth::id();
            $finance = Finance::where('id_admin_fk', $adminId)->orderBy('date', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'data' => $finance,
                'message' => 'Data finance retrieved successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve data finance.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction(); // Mulai Transaksi Database

        try {
            // 1. Validasi Input
            $validate = $request->validate([
                'date' => 'required|date',
                'type' => 'required|in:income,expense', // Pastikan type dikirim
                'item' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'amount' => 'required|numeric|min:1', // Jumlah Barang (Qty)
                'price' => 'required|numeric|min:0',  // Harga Satuan
            ]);

            $adminId = Auth::id();

            // 2. Hitung Nominal Uang (Total Transaksi ini)
            // Contoh: Beli 2 barang @ 50.000 = 100.000
            $transactionTotal = $validate['amount'] * $validate['price'];

            // 3. LOGIKA SALDO BERJALAN (Running Balance)
            // Ambil saldo terakhir dari transaksi paling baru milik admin ini
            $lastTransaction = Finance::where('id_admin_fk', $adminId)
                ->latest('id_finance') // Ambil ID terakhir
                ->first();

            // Jika belum ada transaksi, saldo awal dianggap 0
            $lastBalance = $lastTransaction ? $lastTransaction->current_balance : 0;

            // Hitung Saldo Baru
            if ($validate['type'] == 'income') {
                $newCurrentBalance = $lastBalance + $transactionTotal;
            } else {
                // Opsional: Cek apakah saldo cukup
                if ($lastBalance < $transactionTotal) {
                    throw new \Exception("Saldo tidak mencukupi untuk pengeluaran ini.");
                }
                $newCurrentBalance = $lastBalance - $transactionTotal;
            }

            // 4. Simpan ke Tabel Finance
            $finance = Finance::create([
                'id_admin_fk'     => $adminId,
                'type'            => $validate['type'],
                'date'            => $validate['date'],
                'item'            => $validate['item'],
                'category'        => $validate['category'],
                'description'     => $validate['description'] ?? '-',
                'amount'          => $validate['amount'],
                'price'           => $validate['price'],
                'total'           => $transactionTotal,      // Uang keluar/masuk saat ini
                'current_balance' => $newCurrentBalance,     // Sisa saldo setelah transaksi ini
            ]);

            // 5. Update Rekap Bulanan (Monthly Balances)
            $date = Carbon::parse($validate['date']);
            $month = $date->month;
            $year = $date->year;
            $balance = Balances::where('id_admin_fk', $adminId)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            // Jika belum ada (ini transaksi pertama di bulan tersebut), kita buat baru
            if (!$balance) {
                // A. Cari Saldo Akhir Bulan Sebelumnya
                // Kita mundur 1 bulan ke belakang untuk cek saldo
                $prevDate = Carbon::create($year, $month, 1)->subMonth();
                $prevMonth = $prevDate->month;
                $prevYear  = $prevDate->year;

                $prevBalanceData = Balances::where('id_admin_fk', $adminId)
                    ->where('month', $prevMonth)
                    ->where('year', $prevYear)
                    ->first();

                // Jika ada bulan lalu, ambil ending_balance-nya. Jika tidak, 0.
                $initialBal = $prevBalanceData ? $prevBalanceData->ending_balance : 0;

                // B. Buat Row Baru dengan Initial Balance yang benar
                $balance = Balances::create([
                    'id_admin_fk'     => $adminId,
                    'month'           => $month,
                    'year'            => $year,
                    'initial_balance' => $initialBal,     // <-- Diambil dari bulan lalu
                    'total_income'    => 0,
                    'total_expense'   => 0,
                    'ending_balance'  => $initialBal,     // Start saldo akhir sama dengan awal
                    'is_finalized'    => false
                ]);
            }
            $balance = Balances::firstOrCreate(
                ['id_admin_fk' => $adminId, 'month' => $month, 'year' => $year],
                ['initial_balance' => 0, 'total_income' => 0, 'total_expense' => 0, 'ending_balance' => 0]
            );

            if ($validate['type'] == 'income') {
                $balance->total_income += $transactionTotal;
                $balance->ending_balance += $transactionTotal;
            } else {
                $balance->total_expense += $transactionTotal;
                $balance->ending_balance -= $transactionTotal;
            }
            $balance->save();

            DB::commit(); // Simpan permanen jika tidak ada error

            return response()->json([
                'status' => 'success',
                'data' => $finance,
                'message' => 'Transaksi berhasil disimpan. Saldo terupdate.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack(); // Batalkan semua jika error
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
    /**
     * Helper: Memperbaiki Initial & Ending Balance bulan-bulan ke depan
     * dipanggil setelah ada perubahan (Update/Delete) di bulan lalu.
     */
    private function updateFutureMonthsBalances($adminId, $startMonth, $startYear)
    {
        // 1. Ambil semua rekap bulan SETELAH bulan yang diedit
        // Mengurutkan berdasarkan Tahun lalu Bulan agar urut
        $futureBalances = Balances::where('id_admin_fk', $adminId)
            ->where(function ($query) use ($startMonth, $startYear) {
                $query->where('year', '>', $startYear)
                    ->orWhere(function ($q) use ($startMonth, $startYear) {
                        $q->where('year', $startYear)
                            ->where('month', '>', $startMonth);
                    });
            })
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // 2. Loop setiap bulan ke depan
        foreach ($futureBalances as $balance) {
            // A. Cari saldo akhir dari bulan sebelumnya persis
            //    (Bisa jadi bulan sebelumnya itu bulan yg baru kita edit, atau bulan loop sebelumnya)
            $prevDate = Carbon::create($balance->year, $balance->month, 1)->subMonth();

            $prevBalanceData = Balances::where('id_admin_fk', $adminId)
                ->where('month', $prevDate->month)
                ->where('year', $prevDate->year)
                ->first();

            // Jika ada bulan sebelumnya, ambil ending_balance-nya.
            $newInitialBalance = $prevBalanceData ? $prevBalanceData->ending_balance : 0;

            // B. Update data bulan ini
            $balance->initial_balance = $newInitialBalance;

            // Recalculate Ending Balance bulan ini
            // Rumus: Awal + Masuk - Keluar
            $balance->ending_balance = $balance->initial_balance + $balance->total_income - $balance->total_expense;

            $balance->save();
        }
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $finance = Finance::where('id_finance', $id)
                ->where('id_admin_fk', Auth::id())
                ->firstOrFail();

            return response()->json([
                'status' => 'success',
                'data' => $finance,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $adminId = Auth::id();

            // 1. Ambil Data Lama & Lock
            $finance = Finance::where('id_finance', $id)
                ->where('id_admin_fk', $adminId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Validasi (Sama seperti sebelumnya)
            $validate = $request->validate([
                'item'        => 'sometimes|string|max:255',
                'category'    => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:500',
                'amount'      => 'sometimes|numeric|min:1',
                'price'       => 'sometimes|numeric|min:0',
            ]);

            // 3. Hitung Selisih Nominal
            $oldTotal = $finance->total;
            $newAmount = $validate['amount'] ?? $finance->amount;
            $newPrice  = $validate['price']  ?? $finance->price;
            $newTotal  = $newAmount * $newPrice;

            $diff = $newTotal - $oldTotal;

            // 4. Update Data Transaksi Ini
            $finance->update([
                'item'        => $validate['item'] ?? $finance->item,
                'category'    => $validate['category'] ?? $finance->category,
                'description' => $validate['description'] ?? $finance->description,
                'amount'      => $newAmount,
                'price'       => $newPrice,
                'total'       => $newTotal,
                'current_balance' => ($finance->type == 'income')
                    ? $finance->current_balance + $diff
                    : $finance->current_balance - $diff
            ]);

            // 5. Update Running Balance Transaksi Masa Depan (Tabel Finance)
            if ($diff != 0) {
                $futureTransactions = Finance::where('id_admin_fk', $adminId)
                    ->where('id_finance', '>', $id)
                    ->get();

                foreach ($futureTransactions as $ft) {
                    if ($finance->type == 'income') {
                        $ft->current_balance += $diff;
                    } else {
                        $ft->current_balance -= $diff;
                    }
                    $ft->save();
                }

                // 6. Update Rekap Bulanan SAAT INI (Tabel Balances)
                $date  = Carbon::parse($finance->date);
                $balance = Balances::where('id_admin_fk', $adminId)
                    ->where('month', $date->month)
                    ->where('year', $date->year)
                    ->first();

                if ($balance) {
                    if ($finance->type == 'income') {
                        $balance->total_income += $diff;
                        $balance->ending_balance += $diff;
                    } else {
                        $balance->total_expense += $diff;
                        $balance->ending_balance -= $diff;
                    }
                    $balance->save();

                    // --- PERBAIKAN UTAMA DISINI ---
                    // 7. Panggil Helper untuk update bulan-bulan berikutnya
                    // (Karena ending_balance bulan ini berubah, initial_balance bulan depan harus berubah)
                    $this->updateFutureMonthsBalances($adminId, $date->month, $date->year);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data updated and all balances adjusted.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $adminId = Auth::id();

            // 1. Ambil Data
            $finance = Finance::where('id_finance', $id)
                ->where('id_admin_fk', $adminId)
                ->lockForUpdate()
                ->firstOrFail();

            $nominal = $finance->total;
            $type    = $finance->type;
            $date    = Carbon::parse($finance->date);

            // 2. Update Running Balance Transaksi Masa Depan (Tabel Finance)
            $futureTransactions = Finance::where('id_admin_fk', $adminId)
                ->where('id_finance', '>', $id)
                ->get();

            foreach ($futureTransactions as $ft) {
                if ($type == 'income') {
                    $ft->current_balance -= $nominal;
                } else {
                    $ft->current_balance += $nominal;
                }
                $ft->save();
            }

            // 3. Update Rekap Bulanan SAAT INI (Tabel Balances)
            $balance = Balances::where('id_admin_fk', $adminId)
                ->where('month', $date->month)
                ->where('year', $date->year)
                ->first();

            if ($balance) {
                if ($type == 'income') {
                    $balance->total_income -= $nominal;
                    $balance->ending_balance -= $nominal;
                } else {
                    $balance->total_expense -= $nominal;
                    $balance->ending_balance += $nominal;
                }
                $balance->save();
            }

            // 4. Hapus Data
            $finance->delete();

            // --- PERBAIKAN UTAMA DISINI ---
            // 5. Panggil Helper untuk update bulan-bulan berikutnya
            // (Karena bulan ini berubah, bulan depan initial-nya harus disesuaikan)
            if ($balance) {
                $this->updateFutureMonthsBalances($adminId, $date->month, $date->year);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Entry deleted and balances adjusted.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
