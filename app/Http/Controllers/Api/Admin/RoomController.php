<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    use GeneralTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms = Room::all('id','img','room_number','type','description','is_available');
        if($rooms){
            $result = [
                'success' => true,
                'data' => $rooms,
                'message' => 'rooms retrieved successfully.'
            ];
        }
        else{
            $result = [
                'success' => false,
                'message' => 'rooms not found.'

            ];
        }
        return response()->json($result);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
             // التحقق من البيانات
        $valid = Validator::make($request->all(), [
            'room_number' => 'required|unique:rooms,room_number',
            'description' => 'required|string',
            'is_available' => 'required|boolean|in:0,1',
            'type' => 'required|string',
            'img' => 'nullable|mimes:jpeg,png,jpg,svg|max:2048'
        ]);

        // إذا فشل التحقق، يتم إرجاع الأخطاء تلقائيًا
        if ($valid->fails()) {
            return response()->json($valid->errors(), 422);
        }

        // إذا نجح التحقق، يتم معالجة البيانات
        if($data = $valid->validated()){
        // تحميل الصورة إذا تم تقديمها
      $data=$this->SaveImage($request,$data,"Rooms");
}
        // إنشاء الغرفة
        Room::create($data);

        return response()->json("success", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // التحقق من صحة البيانات
        $request->validate(['room_number' => 'required',
            'description' => 'required|string',
            'is_available' => 'required|boolean',
            'type' => 'required|string',
            'img' => 'mimes:jpeg,png,jpg,svg|max:2048']);


        // البحث عن الغرفة
        $room = Room::find($id);
        if (!$room) {
            return $this->returnError(404, 'room not found');
        }

        // تحديث الصورة إذا كانت موجودة
        if ($request->hasFile('img')) {
            $room = $this->SaveImage($request, $room, "Rooms");
        }

        // تحديث الحقول الأخرى
        if ($request->has('room_number')) {
            $room->room_number = $request->room_number;
        }

        if ($request->has('description')) {
            $room->description = $request->description;
        }

        if ($request->has('is_available')) {
            $room->is_available = $request->is_available;
        }

        if ($request->has('type')) {
            $room->type = $request->type;
        }

        // حفظ التغييرات
        $room->update();

        return $this->returnSuccess(200, 'Room updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $room = Room::find($id);

        if($room){
        $room->delete();
        return response()->json("Room deleted Successfully", 204);
        }
        else{
            return response()->json("Room is not found", 404);
        }

    }
}
