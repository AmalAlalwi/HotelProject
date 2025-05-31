<?php
namespace App\Repository\Rooms;
use App\Interfaces\User\Rooms\RoomRepositoryInterface;
use App\Models\Room;
use App\Traits\GeneralTrait;


class RoomRepository implements RoomRepositoryInterface{
    use GeneralTrait;
    public function index($request)
    {
        $perPage = $request->query('perPage', 10);
        $price = $request->query('price');
        $filter = $request->query('filter');

        $query = Room::query();


        if ($filter === "1") {
            $query->where('type', 'single');
        } elseif ($filter === "2") {
            $query->where('type', 'double');
        }


        if ($price !== null) {
            $query->where('price', '<=', $price);
        }


        $rooms = $query->paginate($perPage);


        $rooms->getCollection()->transform(function ($room) {
            $room->img = asset('images/Rooms/'.$room->img);
            return $room;
        });

        if ($rooms->isNotEmpty()) {
            return $this->returnData('rooms', $rooms, "Rooms retrieved successfully");
        } else {
            return $this->returnError('404', 'Rooms not found');
        }
    }
    public function store($request)
    {
        $room = new Room();
        $room->room_number=$request->input('room_number');
        $room->description=$request->input('description');
        $room->type=$request->input('type');
        $room->img=$this->SaveImage($request,"Rooms");
        $room->price=$request->input('price');
        $room->is_available=$request->input('is_available');
        $room->save();
        return response()->json("success", 201);

    }
    public function show($id){
        $room =Room::find($id);
        $room->img =asset('images/Rooms/'.$room->img);
        if($room){
            return $this ->returnData('Room',$room,"Room retrieved successfully");

        }
        else{
            return $this->returnError('404','Room not found');
        }
    }
    public function update($request,$id)
    {
        $room=Room::find($id);
        if($room){
            $room->room_number = $request->room_number;
            $room->description = $request->description;
            $room->is_available = $request->is_available;
            $room->type = $request->type;
            $room->price = $request->price;
            $room->img=$this->SaveImage($request,'Rooms');
            $room->save();
            return $this->returnData('Service',$room,"Room updated successfully");
        }
        else{
            return $this->returnError('404','Room not found');
        }

    }
    public function destroy($id){
        $room=Room::find($id);
        if($room){
            if($room->is_available==1){
                $room->delete();
                return response()->json("Room deleted successfully.", 201);
            }
            else{
                return response()->json("You can't delete this room,The room is already booked.",409);
            }
        }
    }

}
