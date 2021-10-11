<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use App\Traits\TimeagoTrait;
use Illuminate\Http\Request;
use App\Http\Resources\DataResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    use TimeagoTrait;

    public function register(Request $request)
    {
        $request->validate([
            "name"=> "required|string|max:255",
            "email"=> "required|email:filter|max:255|unique:users",
            "password"=> "required|min:8|string"
        ]);

        $data = $request->all();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']), 
        ]);

        $token = $user->createToken('admin-thinktech');
        UserRole::create([
            "user_id"=> $user->id,
            "role"=> 'author'
        ]);
        return $request->wantsJson() ? new DataResource(["token"=> $token->plainTextToken, 'message'=> "Welcome to Think Tech, ".$user->name]) : \redirect('https://thinktech.com');
    }

    public function login(Request $request)
    {
        $request->validate([
            "email"=> "required|email:filter",
            "password"=> "required|string"
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = User::find(Auth::user()->id);
            $token = $user->createToken('admin-thinktech');
            return $request->wantsJson() ? new DataResource(["token"=> $token->plainTextToken, 'message'=> "Welcome to Think Tech, ".$user->name]) : \redirect('https://thinktech.com');
        }
        return response(json_encode([
            "errors"=> [
                "email"=> ["Your credentials do not match!"]
            ]
        ]),  422);
    }
    public function getUser(Request $request)
    {
        $user =  User::where('id', $request->user()->id)->with('role')->first();
        $user->relative_at = $this->timeago($user->created_at);
        return new DataResource(['user'=> $user]);
    }
}
