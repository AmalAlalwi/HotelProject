<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\User\Services\ServiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    protected $serviceRepository;
    public function __construct(ServiceRepositoryInterface $serviceRepository){
        $this->serviceRepository = $serviceRepository;
    }
    public function index(Request $request){
        $request->validate([
            'minPrice' => 'nullable|numeric',
            'maxPrice' => 'nullable|numeric',
            'perPage' => 'nullable|integer|min:1|max:50',
            'filter' => 'nullable|numeric|min:1|max:2',
        ]);
        return $this->serviceRepository->index($request);
    }
    public function store(Request $request){
        $valid = Validator::make($request->all(), [
            'name' => 'string|required|max:50',
            'description' => 'required|string',
            'is_available' => 'required|boolean|in:0,1',
            'img' => 'nullable|mimes:jpeg,png,jpg,svg|max:2048',
            'price' => 'required|numeric|min:0',
        ]);
        if ($valid->fails()) {
            return response(['errors' => $valid->errors()], 422);
        }
        return $this->serviceRepository->store($request);
    }
    public function show($id){
        return $this->serviceRepository->show($id);
    }
    public function update(Request $request,$id){
        $valid = Validator::make($request->all(), [

            'name' => 'string|required|max:50',
            'description' => 'required|string',
            'is_available' => 'required|boolean|in:0,1',
            'img' => 'nullable|mimes:jpeg,png,jpg,svg|max:2048',
            'price' => 'required|numeric|min:0',
        ]);
        if ($valid->fails()) {
            return response(['errors' => $valid->errors()], 422);
        }
        return $this->serviceRepository->update($request,$id);
    }
    public function destroy($id){
        return $this->serviceRepository->destroy($id);
    }
}
