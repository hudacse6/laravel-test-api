<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required',
            'email' => 'required',
            'avatar' => 'nullable|image',
        ]);

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            if (File::exists(public_path('uploads/' . $user->avatar))) {
                File::delete(public_path('uploads/' . $user->avatar));
            }
            $fileName = time() . '.' . $request->file('avatar')->extension();
            $request->file->move(public_path('uploads'), $fileName);
            $request['avatar'] = $fileName;
        }

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'avatar' => $request->avatar,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully updated profile",
            "data" => $user
        ]);
    }
}
