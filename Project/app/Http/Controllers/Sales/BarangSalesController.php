<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\BarangSales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $barangSales = BarangSales::all();
            return response()->json([
                'status' => 'success',
                'data' => $barangSales
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve barang sales data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validate = $request->validate([
                'id_sales_fk' => 'required|exists:sales,id',
                'item_name' => 'required|string|max:255',
                'item_code' => 'nullable|string|max:100',
                'quantity' => 'required|integer|min:1',
                'price' => 'required|integer|min:0',
            ]);

            $barangSales = BarangSales::create($validate);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Barang sales entry created successfully.'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create barang sales entry',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $barangSales = BarangSales::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $barangSales,
                'message' => 'Barang sales entry retrieved successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve barang sales entry.'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $validate = $request->validate([
                'id_sales_fk' => 'sometimes|exists:sales,id',
                'item_name' => 'sometimes|string|max:255',
                'item_code' => 'sometimes|string|max:100',
                'quantity' => 'sometimes|integer|min:1',
                'price' => 'sometimes|integer|min:0',
            ]);
            $barangSales = BarangSales::lockForUpdate()->findOrFail($id);
            $barangSales->update($validate);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Barang sales entry updated successfully.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update barang sales entry.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $barangSales = BarangSales::findOrFail($id);
            $barangSales->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Barang sales entry deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete barang sales entry.'
            ], 500);
        }
    }
}
