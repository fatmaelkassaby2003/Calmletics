<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\ComFree;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index(){
        return view('community');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'plan_id' => 'required|integer',
        ]);
    
        $user = new ComFree();
        $user->name = $request->name;
        $user->level = $request->level;
        $user->plan_id = $request->plan_id;
    
        $user->save();
    
        return redirect()->route('communities')->with('success', 'Community saved successfully!');
    }
    

}
