<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Balances;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BalancesController extends Controller
{
    /**
     * Menampilkan daftar semua saldo bulanan admin
     */
    public function index()
    {
        try {
            $adminId = Auth::id();

            $balances = Balances::where('id_admin_fk', $adminId)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $balances
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data saldo',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Detail Saldo Bulan Tertentu
     */
    public function show($year, $month)
    {
        try {
            $adminId = Auth::id();

            $balance = Balances::where('id_admin_fk', $adminId)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$balance) {
                return response()->json(['status' => 'error', 'message' => 'Laporan bulan ini belum ada'], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $balance
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}