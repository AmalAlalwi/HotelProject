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
        $rooms = Room::all('img','room_number','type','description','is_available');
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
        if ($image = $request->file('img')) {
            $destinationPath = 'images/Rooms';
            $roomImage = 'room' . date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move(public_path($destinationPath), $roomImage);
            $data['img'] = $roomImage; // تأكد من أن اسم الحقل يتطابق مع اسم العمود في قاعدة البيانات
        }
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

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $room = Room::findOrFail($id);
        $room->delete();
        return response()->json(null, 204);

    }
}
