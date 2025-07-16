<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas'; // asegúrate de que coincida con tu tabla en la BD
    protected $fillable = [
        // aquí tus campos permitidos
        'user_id', 'total', 'fecha',
    ];
}
