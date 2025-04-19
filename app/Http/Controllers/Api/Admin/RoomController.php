<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repository\Rooms\RoomRepository;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public $RoomRepository;
    public function __construct(RoomRepository $RoomRepository){
        $this->RoomRepository = $RoomRepository;
    }
    use GeneralTrait;
    public function index(Request $request)
    {
        $request->validate([
            'minPrice' => 'nullable|numeric',
            'maxPrice' => 'nullable|numeric',
            'perPage' => 'nullable|integer|min:1|max:50',
            'filter' => 'nullable|numeric|min:1|max:2',
        ]);
       return $this->RoomRepository->index($request);
    }
    public function store(Request $request)
    {

        $valid = Validator::make($request->all(), [
            'room_number' => 'required|unique:rooms,room_number',
            'description' => 'required|string',
            'is_available' => 'required|boolean|in:0,1',
            'type' => 'required|string',
            'img' => 'nullable|mimes:jpeg,png,jpg,svg|max:2048',
            'price' => 'required|numeric'
        ]);
        if ($valid->fails()) {
            return response(['errors' => $valid->errors()], 422);
        }
        return $this->RoomRepository->store($request);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

       return $this->RoomRepository->show($id);

    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $valid = Validator::make($request->all(), [
            'room_number' => 'required',
            'description' => 'required|string',
            'is_available' => 'required|boolean',
            'type' => 'required|string',
            'img' => 'mimes:jpeg,png,jpg,svg|max:2048',
            'price'=>'required|numeric'
        ]);
        if ($valid->fails()) {
            return response(['errors'=>$valid->errors()],422);
        }
       return $this->RoomRepository->update($request,$id);
    }

    public function destroy(string $id)
    {
        return $this->RoomRepository->destroy($id);
    }
}
