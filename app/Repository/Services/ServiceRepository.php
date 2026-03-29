<?php

namespace App\Repository\Services;

use App\Interfaces\User\Services\ServiceRepositoryInterface;
use App\Models\Service;
use App\Traits\GeneralTrait;
use App\Traits\ImageTrait;

class ServiceRepository implements ServiceRepositoryInterface
{
    use GeneralTrait, ImageTrait;
    public function index($request)
    {
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
            if ($service->img) {
                $service->img = $this->getImageUrl($service->img, 'services');
            }
            return $service;
        });

        if ($services->isNotEmpty()) {
            return $this->returnData('services', $services, "Services retrieved successfully");
        } else {
            return $this->returnError('404', 'Services not found');
        }
    }
    public function store($validatedData, $image = null)
    {
        $service = new Service();
        $service->name = $validatedData['name'];
        $service->description = $validatedData['description'];
        $service->price = $validatedData['price'];
        $service->is_available = $validatedData['is_available'] ?? true;
        
        if ($image) {
            $service->img = $this->uploadImage($image, 'services');
        }
        
        $service->save();
        
        // Return the service with full image URL
        $service->img = $this->getImageUrl($service->img, 'services');
        
        return $this->returnData('service', $service, 'Service created successfully', 201);
}
    public function show($id)
    {
        $service = Service::find($id);
        
        if ($service) {
            if ($service->img) {
                $service->img = $this->getImageUrl($service->img, 'services');
            }
            return $this->returnData('service', $service, 'Service retrieved successfully');
        }
        
        return $this->returnError('404', 'Service not found');
    }
    public function update($request, $id)
    {
        $service = Service::find($id);
        
        if (!$service) {
            return $this->returnError('404', 'Service not found');
        }

        $service->name = $request->name;
        $service->description = $request->description;
        $service->is_available = $request->is_available;
        $service->price = $request->price;
        
        if ($request->hasFile('img')) {
            // Delete old image if exists
            if ($service->img) {
                $this->deleteImage($service->img, 'services');
            }
            
            $uploadedImage = $this->uploadImage($request->file('img'), 'services');
            if (!$uploadedImage) {
                return $this->returnError('400', 'Failed to upload new image');
            }
            $service->img = $uploadedImage;
        }
        
        $service->save();
        
        // Return the updated service with full image URL
        $service->img = $this->getImageUrl($service->img, 'services');
        
        return $this->returnData('service', $service, "Service updated successfully");

    }
    public function destroy($id)
    {
        $service = Service::find($id);
        
        if (!$service) {
            return $this->returnError('404', 'Service not found');
        }
        
        if ($service->is_available == 1) {
            // Delete the associated image
            if ($service->img) {
                $this->deleteImage($service->img, 'services');
            }
            
            $service->delete();
            return $this->returnSuccess('Service deleted successfully');
        }
        
        return response()->json("You can't delete this service. The service is already booked.", 409);
    }

}
