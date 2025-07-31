<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function getProviders()
    {
        return Provider::all();
    }

    public function getProducts()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $total = 0;

            foreach ($validated['products'] as $item) {
                $product = Product::findOrFail($item['id']);
                $total += $product->price * $item['quantity'];
            }

            $purchase = Purchase::create([
                'provider_id' => $validated['provider_id'],
                'total' => $total,
                'purchase_date' => now(),
            ]);

            foreach ($validated['products'] as $item) {
                $product = Product::findOrFail($item['id']);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                ]);

                // Opcional: actualizar stock
                $product->stock += $item['quantity'];
                $product->save();
            }

            DB::commit();

            return response()->json(['message' => 'Compra registrada exitosamente.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar la compra.', 'error' => $e->getMessage()], 500);
        }
    }
}