<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use App\Events\MessageSent;
use App\Models\CommunityChat;
use App\Models\User;

class WebSocketController extends Controller
{

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);
    
        $user = auth()->user();
        $comPreId = $user->com_pre_id;
        $comFreeId = $user->com_free_id;    
        if (!$comPreId && !$comFreeId) {
            return response()->json(['error' => 'User is not part of any community'], 403);
        }
    
        $message = CommunityChat::create([
            'user_id' => $user->id,
            'com_pre_id' => $comPreId, 
            'com_free_id' => $comFreeId, 
            'message' => $request->message,
        ]);
        $communityId = $comPreId ?: $comFreeId;
        broadcast(new MessageSent($user, $message->message, $communityId))->toOthers();
    
        return response()->json(['success' => 'Message sent successfully']);
    }
    

    public function index()
    {
        $user = auth()->user();
        $comPreId = $user->com_pre_id;
        $comFreeId = $user->com_free_id;
    
        if (!$comPreId && !$comFreeId) {
            return response()->json(['error' => 'User is not part of any community'], 403);
        }
        $messages = CommunityChat::with('user:id,name,image') 
        ->where(function ($query) use ($comPreId, $comFreeId) {
            if ($comPreId) {
                $query->where('com_pre_id', $comPreId);
            } else {
                $query->where('com_free_id', $comFreeId);
            }
        })
        ->orderBy('created_at', 'desc')
        ->select ('user_id','message','com_pre_id','com_free_id')
        ->get()->map(function ($message) {
            $message->user->role_name = $message->user->role == 1 ? 'Coach' : 'Player';
            unset($message->user->role);
            return $message;
        });
    
        return response()->json(['userid' => auth()->id(),
            'messages' => $messages]);
    }
    
}
