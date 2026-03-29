<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repository\Rooms\RoomRepository;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
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
            'priuce'=>'nullable|numeric',
            'perPage' => 'nullable|integer|min:1|max:50',
            'filter' => 'nullable|numeric|min:1|max:2',
        ]);
       return $this->RoomRepository->index($request);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|unique:rooms,room_number',
            'description' => 'required|string',
            'type' => 'required|in:single,double,suite',
            'price' => 'required|numeric|min:0',
            'is_available' => 'sometimes|boolean',
            'img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('img');
        return $this->RoomRepository->store($validated, $image);
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
    public function update(Request $request, $id)
    {
        return $this->RoomRepository->update($request, $id);
    }

    public function destroy(string $id)
    {
        return $this->RoomRepository->destroy($id);
    }
}
