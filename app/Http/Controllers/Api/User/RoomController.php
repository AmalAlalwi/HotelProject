<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Service;
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

        // استعلام الغرف المتاحة فقط
        $query = Room::where('is_available', 1);

        // فلترة حسب النوع (single أو double)
        if (!empty($filter)) {
            if ($filter == "1") {
                $query->where('type', 'single');
            } elseif ($filter == "2") {
                $query->where('type', 'double');
            }
        }


        if (!is_null($price)) {
            $query->where('price', '<=', $price);
        }


        $rooms = $query->paginate($perPage);


        $rooms->getCollection()->transform(function ($room) {
            $room->img = asset('storage/'.$room->img);
            return $room;
        });


        if ($rooms->isNotEmpty()) {
            return $this->returnData('rooms', $rooms, "Rooms retrieved successfully");
        } else {
            return $this->returnError('404', 'Rooms not found');
        }
    }

    public function indexService(Request $request){
        $request->validate([
            'price' => 'nullable|numeric',
            'perPage' => 'nullable|integer|min:1|max:50',
            'name' => 'nullable|string',
        ]);

        $perPage = $request->query('perPage', 10);
        $price = $request->query('price');
        $name = $request->query('name');

        $query = Service::query();


        if (!empty($name)) {
            $query->where('name', 'like', '%' . $name . '%');
        }


        if (!is_null($price)) {
            $query->where('price', '<=', $price);
        }


        $services = $query->paginate($perPage);

        $services->getCollection()->transform(function ($service) {
            $service->img =asset('storage/'.$service->img);
            return $service;
        });
        if ($services->isNotEmpty()) {
            return $this->returnData('services', $services, "Services retrieved successfully");
        } else {
            return $this->returnError('404', 'Services not found');
        }
    }
}
