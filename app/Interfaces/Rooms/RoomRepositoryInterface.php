<?php
namespace App\Interfaces\Rooms;
interface RoomRepositoryInterface
{
    public function index($request);
    public function show($id);
    public function store($request);
    public function update($request,$id);
    public function destroy($id);
}
