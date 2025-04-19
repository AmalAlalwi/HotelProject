<?php
namespace App\Repository\Services;
use App\Interfaces\Services\ServiceRepositoryInterface;
use App\Models\Service;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Validator;


class ServiceRepository implements ServiceRepositoryInterface{
    use GeneralTrait;
    public function index($request)
    {
    $perPage = $request->query('perPage', 10);
    $price = $request->query('price');
    $name = $request->query('name');
    $query = Service::query();
    if($perPage){
        if($name){
           $query->where('name', 'like', "%$name%");
        }

        if ($price !== null) {
            $query->where('price', '<=', $price);
        }
        $service = $query->paginate($perPage);

        if($service->isNotEmpty()){
            return $this->returnData('services',$service,"Services retrieved successfully");
        }
        else{
            return $this->returnError('404','Services not found');
        }
    }
    }
    public function store($request)
{
    $service=new service();
    $service->name=$request->name;
    $service->description=$request->description;
    $service->price=$request->price;
    $service->is_available=$request->is_available;
    $service->img=$this->SaveImage($request,'Service');
    $service->save();
    return $this->returnData('service',$service,"Service saved successfully");
}
    public function show($id){
        $Service =Service::findorfail($id);
        if($Service){
            return $this ->returnData('Service',$Service,"Service retrieved successfully");

        }
        else{
            return $this->returnError('404','Service not found');
        }
    }
    public function update($request,$id)
    {

        if(Service::find($id)){
            $service=Service::findorfail($id);
            $service->name = $request->name;
            $service->description = $request->description;
            $service->is_available = $request->is_available;
            $service->price = $request->price;
            $service->img=$this->SaveImage($request,'Service');
            $service->save();
            return $this->returnData('Service',$service,"Service updated successfully");
        }
        else{
            return $this->returnError('404','Service not found');
        }

    }
    public function destroy($id){
        $service=Service::find($id);
        if($service){
            if($service->is_available==1){
                $service->delete();
                return response()->json("Service deleted successfully.", 201);
            }
            else{
                return response()->json("You can't delete this service,The service is already booked.",409);
            }
        }
    }

}
