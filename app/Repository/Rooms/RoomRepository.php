<?php

namespace App\Repository\Rooms;

use App\Interfaces\User\Rooms\RoomRepositoryInterface;
use App\Models\Room;
use App\Traits\GeneralTrait;
use App\Traits\ImageTrait;

class RoomRepository implements RoomRepositoryInterface
{
    use GeneralTrait, ImageTrait;
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
            if ($room->img) {
                $room->img = $this->getImageUrl($room->img, 'rooms');
            }
            return $room;
        });

        if ($rooms->isNotEmpty()) {
            return $this->returnData('rooms', $rooms, "Rooms retrieved successfully");
        } else {
            return $this->returnError('404', 'Rooms not found');
        }
    }
    public function store($validatedData, $image = null)
    {
        $room = new Room();
        $room->room_number = $validatedData['room_number'];
        $room->description = $validatedData['description'];
        $room->type = $validatedData['type'];
        $room->price = $validatedData['price'];
        $room->is_available = $validatedData['is_available'] ?? true;
        
        if ($image) {
            $room->img = $this->uploadImage($image, 'rooms');
        }
        
        $room->save();
        
        return $this->returnData('room', $room, 'Room created successfully', 201);

    }
    public function show($id){
        $room = Room::find($id);
        
        if($room) {
            if ($room->img) {
                $room->img = $this->getImageUrl($room->img, 'rooms');
            }
            return $this->returnData('room', $room, 'Room retrieved successfully');
        }
        
        return $this->returnError('404', 'Room not found');
    }
    public function update($request, $id)
    {
        $room = Room::find($id);
        
        if (!$room) {
            return $this->returnError('404', 'Room not found');
        }
        
        $validated = $request->validate([
            'room_number' => 'required|string|unique:rooms,room_number,' . $id,
            'description' => 'required|string',
            'type' => 'required|in:single,double,suite',
            'price' => 'required|numeric|min:0',
            'is_available' => 'sometimes|boolean',
            'img' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $room->room_number = $validated['room_number'];
        $room->description = $validated['description'];
        $room->is_available = $validated['is_available'] ?? $room->is_available;
        $room->type = $validated['type'];
        $room->price = $validated['price'];
        
        // Update image if a new one is provided
        if ($request->hasFile('img')) {
            // Delete old image if exists
            if ($room->img) {
                $this->deleteImage($room->img, 'rooms');
            }
            $room->img = $this->uploadImage($request->file('img'), 'rooms');
        }
        
        $room->save();
        
        // Return the updated room with full image URL
        $room->img = $room->img ? $this->getImageUrl($room->img, 'rooms') : null;
        
        return $this->returnData('room', $room, 'Room updated successfully');

    }
    public function destroy($id)
    {
        $room = Room::find($id);
        
        if (!$room) {
            return $this->returnError('404', 'Room not found');
        }
        
        if ($room->is_available == 1) {
            // Delete the associated image
            if ($room->img) {
                $this->deleteImage($room->img, 'rooms');
            }
            
            $room->delete();
            return $this->returnSuccess('Room deleted successfully');
        }
        
        return $this->returnError('409', 'You cannot delete this room. The room is already booked.');
    }

}
