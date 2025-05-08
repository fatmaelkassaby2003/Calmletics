<?php

namespace App\Http\Controllers\coach;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BooksessionController extends Controller
{
    public function booksession(Request $request)
{
    if (!Auth::check() || Auth::user()->role != 1) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $year = $request->year;
    $day = $request->day;

    $bookedSessions = DB::table('session_books')
        ->join('users', 'session_books.user_id', '=', 'users.id')
        ->leftJoin('compres', 'users.com_pre_id', '=', 'compres.id')
        ->where('session_books.year', $year)
        ->where('session_books.day', $day)
        ->select(
            'session_books.time as booked_hour',
            'users.name as user_name',
            'users.image as user_image',
            'compres.name as community_name',
            'compres.level as community_level'
        )
        ->get();

    if ($bookedSessions->isEmpty()) {
        return response()->json([
            'message' => 'No sessions booked for this date.'
        ]);
    }

    return response()->json([
        'booked_sessions' => $bookedSessions
    ]);
}

}
