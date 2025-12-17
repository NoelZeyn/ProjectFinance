<?php

namespace App\Http\Controllers\finance;

use App\Http\Controllers\Controller;
use App\Models\Finance;
use Illuminate\Http\Request;

class financeController extends Controller
{
    public function index()
    {
        try {
            $finance = Finance::with('admin')->get();
            return response()->json([
                'status' => 'success',
                'data' => $finance,
                'message' => 'Data finance retrieved successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve data finance.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validate = $request->validate([
                'id_admin_fk' => 'required|exists:admin,id',
                'date' => 'required|date',
                'item' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'description' => 'required|string|max:500',
                'amount' => 'required|numeric|min:0',
                'price' => 'nullable|numeric|min:0',
            ]);
            $validate['total'] = $validate['amount'] * $validate['price'];

            $finance = Finance::create($validate);

            return response()->json([
                'status' => 'success',
                'data' => $finance,
                'message' => 'Finance entry created successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $finance = Finance::with('admin')->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $finance,
                'message' => 'Finance entry retrieved successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve finance entry.'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validate = $request->validate([
                'id_admin_fk' => 'sometimes|exists:admin,id',
                'date' => 'sometimes|date',
                'item' => 'sometimes|string|max:255',
                'category' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|max:500',
                'amount' => 'sometimes|numeric|min:0',
                'price' => 'sometimes|numeric|min:0',
                'total' => 'sometimes|numeric|min:0',
            ]);
            $finance = Finance::findOrFail($id);
            if (isset($validate['amount']) || isset($validate['price'])) {
                $amount = $validate['amount'] ?? $finance->amount;
                $price  = $validate['price']  ?? $finance->price;
                $validate['total'] = $amount * $price;
            }

            $finance->update($validate);
            return response()->json([
                'status' => 'success',
                'data' => $finance,
                'message' => 'Finance entry updated successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update finance entry.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $finance = Finance::findOrFail($id);
            $finance->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Finance entry deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete finance entry.'
            ], 500);
        }
    }
}
