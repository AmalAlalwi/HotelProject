<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InvoiceItem;

class Booking extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=['user_id','room_id','check_in_date','check_out_date','total_price'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function invoice()
    {
        return $this->hasOneThrough(Invoice::class, InvoiceItem::class,
            'item_id', 'id', 'id', 'invoice_id')
            ->where('item_type', 'room');
    }
}
