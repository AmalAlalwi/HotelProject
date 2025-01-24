<?php
namespace App\Traits;

trait GeneralTrait{
public function returnError($errNum, $msg){
    return response()->json([
        'status' => false,
        'errNum' => $errNum,
        'msg' => $msg
    ]);
}
public function returnSuccess($msg="",$errNum="S000"){
    return response()->json([
        'status' => true,
        'errNum' => $errNum,
        'msg' => $msg
    ]);
}
public function returnData($key,$value,$errNum="S000",$msg="")
{
    return response()->json([
        'status' => true,
        'errNum' => $errNum,
        'msg' => $msg,
        'key' => $value,
    ]);
}
}
