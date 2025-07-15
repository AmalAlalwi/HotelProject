<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function unreadNotifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->unreadNotifications;
        if($notifications->count() > 0){
        return response()->json(['Notifications'=>$notifications],201);
        }
        return response()->json('Not Found Notifications',404);
    }
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
    }
    public function readNotifications(Request $request){
        $user = $request->user();
        $notifications= $user->readNotifications;
        if($notifications->count() > 0){
            return response()->json(['Notifications'=>$notifications],201);
        }
        return response()->json('Not Found Notifications',404);
    }

}
