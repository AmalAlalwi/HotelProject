<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['user_id', 'total_price','paid_amount', 'payment_status', 'payment_method', 'issued_at', 'notes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    protected static function booted()
    {
        static::creating(function ($invoice) {
            $invoice->payment_reference = strtoupper(uniqid('PAY-'));
        });
    }
}
