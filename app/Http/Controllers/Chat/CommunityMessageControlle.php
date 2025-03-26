<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommunityChat;

class CommunityMessageController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $communityId = $user->com_pre_id ?? $user->com_free_id;

        $messages = CommunityChat::where('community_id', $communityId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($messages);
    }
}
