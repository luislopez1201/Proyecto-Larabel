<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas'; // nombre real de la tabla
    protected $fillable = [
        'venta_id',
        'product_id',
        'cantidad',
        'precio',
        'subtotal',
        // otros campos según tu base de datos
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
