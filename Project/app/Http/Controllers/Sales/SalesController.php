<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\BarangSales;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Random\Randomizer;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $sales = Sales::with('admin', 'barangSales')->get();
            return response()->json([
                'status' => 'success',
                'data' => $sales
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve sales data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validate = $request->validate([
                'id_admin_fk' => 'required|exists:admin,id',
                'customer_name' => 'required|string|max:255',
                'customer_contact' => 'nullable|string|max:255',
                'payment_method' => 'required|string|max:100',
                'payment_status' => 'nullable|string|max:100',
                'sales_status' => 'nullable|string|max:100',
                'shipping_status' => 'nullable|string|max:100',
                'tracking_number' => 'nullable|string|max:255',
                'shipping_address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
                'courier' => 'nullable|string|max:100',
                'shipping_cost' => 'nullable|integer|min:0',
                'discount' => 'nullable|integer|min:0',
                'total_payment' => 'required|integer|min:0',
                'notes' => 'nullable|string|max:500',
                'date' => 'required|date',
                'id_barang_sales' => 'required|exists:barang_sales,id_barang_sales',
                'quantity' => 'required|integer|min:1',
            ]);

            // 🔒 Lock row agar aman dari race condition
            $barang = BarangSales::where('id_barang_sales', $validate['id_barang_sales'])
                ->lockForUpdate()
                ->firstOrFail();

            // ❌ Cek stok cukup atau tidak
            if ($barang->quantity < $validate['quantity']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Stock barang tidak mencukupi.'
                ], 400);
            }

            // Hitung harga & total
            $validate['price'] = $barang->price;
            $validate['total'] = $barang->price * $validate['quantity'];
            $validate['invoice_number'] = $this->generateUniqueInvoice();

            $sales = Sales::create($validate);

            $barang->quantity -= $validate['quantity'];
            $barang->save();

            // ✅ Commit jika semua sukses
            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $sales,
                'message' => 'Sales entry created successfully.'
            ], 201);
        } catch (\Throwable $th) {
            // ❌ Rollback jika ada error APAPUN
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    private function generateUniqueInvoice()
    {
        do {
            $invoice = 'INV-' . strtoupper(Str::random(8));
        } while (Sales::where('invoice_number', $invoice)->exists());

        return $invoice;
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $sales = Sales::with('admin', 'barangSales')->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $sales,
                'message' => 'Sales entry retrieved successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
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
                'id_admin_fk' => 'required|exists:admin,id',
                'invoice_number' => 'required|string|max:100',
                'customer_name' => 'required|string|max:255',
                'customer_contact' => 'nullable|string|max:255',
                'payment_method' => 'required|string|max:100',
                'payment_status' => 'nullable|string|max:100',
                'sales_status' => 'nullable|string|max:100',
                'shipping_status' => 'nullable|string|max:100',
                'tracking_number' => 'nullable|string|max:255',
                'shipping_address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
                'courier' => 'nullable|string|max:100',
                'shipping_cost' => 'nullable|integer|min:0',
                'discount' => 'nullable|integer|min:0',
                'total_payment' => 'required|integer|min:0',
                'notes' => 'nullable|string|max:500',
                'date' => 'required|date',
                'quantity' => 'required|integer|min:1',
                'id_barang_sales' => 'required|exists:barang_sales,id_barang_sales',
            ]);

            // 🔒 Lock sales
            $sales = Sales::lockForUpdate()->findOrFail($id);

            // 🔒 Lock barang lama
            $oldBarang = BarangSales::where('id_barang_sales', $sales->id_barang_sales)
                ->lockForUpdate()
                ->first();

            // 🔒 Lock barang baru
            $newBarang = BarangSales::where('id_barang_sales', $validate['id_barang_sales'])
                ->lockForUpdate()
                ->firstOrFail();

            // 🔁 Jika barang berubah
            if ($sales->id_barang_sales != $validate['id_barang_sales']) {

                // Kembalikan stok barang lama
                $oldBarang->stock += $sales->quantity;
                $oldBarang->save();

                // Cek stok barang baru
                if ($newBarang->stock < $validate['quantity']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Stock barang baru tidak mencukupi.'
                    ], 400);
                }

                // Kurangi stok barang baru
                $newBarang->stock -= $validate['quantity'];
                $newBarang->save();
            } else {
                // 🔄 Barang sama → hitung selisih quantity
                $diffQty = $validate['quantity'] - $sales->quantity;

                if ($diffQty > 0 && $newBarang->stock < $diffQty) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Stock barang tidak mencukupi.'
                    ], 400);
                }

                $newBarang->stock -= $diffQty;
                $newBarang->save();
            }

            // Hitung ulang harga & total
            $validate['price'] = $newBarang->price;
            $validate['total'] = $newBarang->price * $validate['quantity'];

            // Update sales
            $sales->update($validate);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sales entry updated successfully.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $sales = Sales::findOrFail($id);
            $sales->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Sales entry deleted successfully.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete sales entry'
            ], 500);
        }
    }
}
