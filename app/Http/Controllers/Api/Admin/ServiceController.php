<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repository\Services\ServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public $serviceRepository;
    
    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }
    public function index(Request $request)
    {
        $request->validate([
            'price' => 'nullable|numeric',
            'perPage' => 'nullable|integer|min:1|max:50',
            'name' => 'nullable|string',
        ]);
        
        return $this->serviceRepository->index($request);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'is_available' => 'sometimes|boolean',
            'img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('img');
        return $this->serviceRepository->store($validated, $image);
    }

    public function show($id)
    {
        return $this->serviceRepository->show($id);
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'is_available' => 'sometimes|boolean',
            'img' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->hasFile('img') ? $request->file('img') : null;
        return $this->serviceRepository->update($request, $id);
    }
    public function destroy($id)
    {
        return $this->serviceRepository->destroy($id);
    }
}
