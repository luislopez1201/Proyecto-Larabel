<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\DetalleVentaController;

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'user',
    ]);

    return response()->json($user);
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Credenciales incorrectas.'],
        ]);
    }

    $token = $user->createToken('token')->plainTextToken;

    return response()->json(['token' => $token, 'user' => $user, 'role' => $user->role]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ¿Está esta ruta pública?
Route::get('/products', [ProductController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
});

Route::delete('/products/{id}', [ProductController::class, 'destroy']);

Route::get('/products/{id}', [ProductController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('/products/{id}', [ProductController::class, 'update']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
});

Route::delete('/products/{id}/image', [ProductController::class, 'deleteImage']);

Route::middleware('auth:sanctum')->post('/checkout', [VentaController::class, 'checkout']);
Route::post('/detalle-ventas', [DetalleVentaController::class, 'store']);
Route::post('/ventas', [VentaController::class, 'store']);

Route::middleware('auth:sanctum')->get('/compras', [VentaController::class, 'getComprasUsuario']);

Route::middleware('auth:sanctum')->get('/admin/ventas', [VentaController::class, 'getAllVentasAdmin']);
// O protegida solo para admins:
Route::middleware('auth:sanctum')->get('/products', [ProductController::class, 'index']);

