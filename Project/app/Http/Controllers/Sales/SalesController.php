<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\BarangSales;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     * Pagination + server-side filtering.
     */
    public function index(Request $request)
    {
        try {
            $query = Sales::with(['admin', 'barangSales']);

            // Filter: product name
            if ($request->filled('search_product')) {
                $query->whereHas('barangSales', function ($q) use ($request) {
                    $q->where('product_name', 'like', '%' . $request->search_product . '%');
                });
            }

            // Filter: customer name
            if ($request->filled('search_customer')) {
                $query->where('customer_name', 'like', '%' . $request->search_customer . '%');
            }

            // Filter: date
            if ($request->filled('search_date')) {
                $query->whereDate('date', $request->search_date);
            }

            $perPage = $request->input('per_page', 10);

            $sales = $query
                ->latest('date')
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data'   => $sales,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data sales',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $adminId = Auth::id();

        $val = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_contact' => 'nullable|string|max:255',
            'payment_method'   => 'required|string|max:100',
            'payment_status'   => 'nullable|string|max:100',
            'sales_status'     => 'nullable|string|max:100',
            'shipping_status'  => 'nullable|string|max:100',
            'tracking_number'  => 'nullable|string|max:255',

            'id_barang_sales'  => 'required|exists:barang_sales,id_barang_sales',
            'quantity'         => 'required|integer|min:1',
            'total_payment'    => 'required|integer|min:0',
            'date'             => 'required|date',

            'shipping_address' => 'nullable|string|max:500',
            'city'             => 'nullable|string|max:100',
            'province'         => 'nullable|string|max:100',
            'postal_code'      => 'nullable|string|max:20',
            'country'          => 'nullable|string|max:100',
            'courier'          => 'nullable|string|max:100',
            'shipping_cost'    => 'nullable|integer|min:0',
            'discount'         => 'nullable|integer|min:0',
            'notes'            => 'nullable|string|max:500',
        ]);

        $val['id_admin_fk'] = $adminId;

        DB::beginTransaction();

        try {
            $barang = BarangSales::where('id_barang_sales', $val['id_barang_sales'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($barang->quantity < $val['quantity']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Stok barang tidak mencukupi. Sisa stok: ' . $barang->quantity,
                ], 400);
            }

            $val['price']          = $barang->price;
            $val['total']          = $barang->price * $val['quantity'];
            $val['invoice_number'] = $this->generateUniqueInvoice();

            $sales = Sales::create($val);

            $barang->decrement('quantity', $val['quantity']);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'data'    => $sales,
                'message' => 'Sales berhasil dibuat.',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $sales = Sales::with(['admin', 'barangSales'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data'   => $sales,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, string $id)
    {
        $adminId    = Auth::id();
        $salesCheck = Sales::findOrFail($id);

        $val = $request->validate([
            'invoice_number'   => ['required', 'string', Rule::unique('sales')->ignore($salesCheck->id_sales)],
            'customer_name'    => 'required|string|max:255',
            'customer_contact' => 'nullable|string|max:255',
            'payment_method'   => 'required|string|max:100',
            'payment_status'   => 'nullable|string|max:100',
            'sales_status'     => 'nullable|string|max:100',
            'shipping_status'  => 'nullable|string|max:100',
            'tracking_number'  => 'nullable|string|max:255',

            'id_barang_sales'  => 'required|exists:barang_sales,id_barang_sales',
            'quantity'         => 'required|integer|min:1',
            'total_payment'    => 'required|integer|min:0',
            'date'             => 'required|date',

            'shipping_address' => 'nullable|string|max:500',
            'city'             => 'nullable|string|max:100',
            'province'         => 'nullable|string|max:100',
            'postal_code'      => 'nullable|string|max:20',
            'country'          => 'nullable|string|max:100',
            'courier'          => 'nullable|string|max:100',
            'shipping_cost'    => 'nullable|integer|min:0',
            'discount'         => 'nullable|integer|min:0',
            'notes'            => 'nullable|string|max:500',
        ]);

        $val['id_admin_fk'] = $adminId;

        DB::beginTransaction();

        try {
            $sales = Sales::lockForUpdate()->findOrFail($id);

            if ($sales->id_barang_sales != $val['id_barang_sales']) {
                // Kembalikan stok lama
                $oldBarang = BarangSales::where('id_barang_sales', $sales->id_barang_sales)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldBarang->increment('quantity', $sales->quantity);

                // Ambil barang baru
                $newBarang = BarangSales::where('id_barang_sales', $val['id_barang_sales'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($newBarang->quantity < $val['quantity']) {
                    throw new \Exception('Stok barang baru tidak mencukupi.');
                }

                $newBarang->decrement('quantity', $val['quantity']);
                $val['price'] = $newBarang->price;
            } else {
                $barang = BarangSales::where('id_barang_sales', $sales->id_barang_sales)
                    ->lockForUpdate()
                    ->firstOrFail();

                $diffQty = $val['quantity'] - $sales->quantity;

                if ($diffQty > 0 && $barang->quantity < $diffQty) {
                    throw new \Exception('Stok tidak mencukupi untuk penambahan.');
                }

                $barang->quantity -= $diffQty;
                $barang->save();

                $val['price'] = $barang->price;
            }

            $val['total'] = $val['price'] * $val['quantity'];

            $sales->update($val);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data sales berhasil diperbarui.',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            $code = in_array(
                $th->getMessage(),
                ['Stok barang baru tidak mencukupi.', 'Stok tidak mencukupi untuk penambahan.']
            ) ? 400 : 500;

            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], $code);
        }
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $sales = Sales::lockForUpdate()->findOrFail($id);

            $barang = BarangSales::where('id_barang_sales', $sales->id_barang_sales)
                ->lockForUpdate()
                ->first();

            if ($barang) {
                $barang->increment('quantity', $sales->quantity);
            }

            $sales->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Sales dihapus dan stok telah dikembalikan.',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus data sales',
            ], 500);
        }
    }

    /**
     * Generate unique invoice number.
     */
    private function generateUniqueInvoice(): string
    {
        do {
            $invoice = 'INV-' . strtoupper(Str::random(8));
        } while (Sales::where('invoice_number', $invoice)->exists());

        return $invoice;
    }
}
