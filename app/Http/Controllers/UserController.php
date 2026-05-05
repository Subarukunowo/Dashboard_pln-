<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile()
    {
        return view('user.Profile');
    }

    public function settings()
    {
        return view('user.Settings');
    }
    public function updateAvatar(Request $request) {
    $request->validate(['avatar' => 'required|image|max:2048']);
    $path = $request->file('avatar')->store('avatars', 'public');
    auth()->user()->update(['avatar' => Storage::url($path)]);
    return response()->json(['success' => true, 'avatar_url' => Storage::url($path)]);
}
}