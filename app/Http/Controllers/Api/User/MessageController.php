<?php

namespace App\Http\Controllers\Api\User;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    use GeneralTrait;
    public function sendMessage(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);
        if ($valid->fails()) {
            return response(['errors'=>$valid->errors()],422);
        }
        $sender = $request->user();
        // التأكد من أن المستقبل هو موظف (role = 1)
        $receiver = User::findOrFail($request->receiver_id);
        if ($receiver->role != 1&&$sender->role!=1) {
            return $this->returnError(400,'Receiver must be Employee');
         }
        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $request->message,
        ]);
        return $this->returnData('message',$message,"Message sent successfully");
       // return response()->json(['message' => 'تم إرسال الرسالة بنجاح', 'data' => $message], 201);
    }

    // استرجاع المحادثة بين المستخدم والموظف
    public function getConversation($receiverId)
    {
        $user = Auth::user();
        $receiver = User::findOrFail($receiverId);
        // استرجاع الرسائل بين المستخدم الحالي والمستقبل
        $messages = Message::where(function ($query) use ($user, $receiver) {
            $query->where('sender_id', $user->id)->where('receiver_id', $receiver->id);
        })->orWhere(function ($query) use ($user, $receiver) {
            $query->where('sender_id', $receiver->id)->where('receiver_id', $user->id);
        })->orderBy('created_at', 'asc')->get();
        return $this->returnData('messages',$messages,"Messages returned successfully");
    }
}
