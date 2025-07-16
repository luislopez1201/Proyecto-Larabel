<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /**
     * Procesa la compra y registra Venta + DetalleVenta
     */

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function store(Request $request)
    {
        try {
            // Valida los datos
            $validated = $request->validate([
                'items' => 'required|array',
                'total' => 'required|numeric',
            ]);

            // Crea la venta
            $venta = Venta::create([
                'total' => $validated['total'],
                // agrega aquí otros campos necesarios
            ]);

            // Opcional: Crear detalles de venta (si manejas tabla detalles)
            foreach ($validated['items'] as $item) {
                $venta->detalles()->create([
                    'producto_id' => $item['id'],
                    'cantidad' => $item['quantity'],
                    'precio' => $item['price'],
                ]);
            }

            return response()->json([
                'message' => 'Venta creada correctamente.',
                'venta' => $venta,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la venta.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function checkout(Request $request)
    {
        // Validación
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Crear la venta
            $venta = Venta::create([
                'user_id' => auth()->id(),
                'total' => $request->total,
            ]);
            // Registrar cada detalle de venta
            foreach ($request->items as $item) {
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'product_id' => $item['id'],
                    'cantidad' => $item['quantity'],
                    'precio' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => '✅ Compra realizada con éxito.',
                'venta_id' => $venta->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json([
                'message' => '❌ Error al procesar la compra.',
                'error' => $e->getMessage(),
            ], 500);
            }
    }
}