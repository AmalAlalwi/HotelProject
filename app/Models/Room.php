<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    protected $fillable=['room_number','type','description','is_available','img','price'];
    use HasFactory;
    use SoftDeletes;
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
