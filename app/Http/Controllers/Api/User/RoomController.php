<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    use GeneralTrait;
    public function index(Request $request)
    {
        $request->validate([
            'price' => 'nullable|numeric',
            'perPage' => 'nullable|integer|min:1|max:50',
            'filter' => 'nullable|numeric|min:1|max:2',
        ]);
        $perPage = $request->query('perPage', 10);
        $price = $request->query('price');
        $filter = $request->query('filter');
        $query = Room::query();
        $query=$query->where('is_available', 1);
        if($perPage){
            if($filter){
                if($filter =="1"){
                    $query->where('type', 'single');
                }
                else if($filter =="2"){
                    $query->where('type', 'double');
                }
            }

            if ($price !== null) {
                $query->where('price', '<=', $price);
            }
            $rooms = $query->paginate($perPage);
            if($rooms){
                return $this->returnData('rooms',$rooms,"Rooms retrieved successfully");
            }
            else{
                return $this->returnError('404','Rooms not found');
            }
        }
    }
}
