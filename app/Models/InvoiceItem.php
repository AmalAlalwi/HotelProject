<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['invoice_id', 'item_type', 'item_id', 'description', 'quantity', 'unit_price', 'total_price'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
