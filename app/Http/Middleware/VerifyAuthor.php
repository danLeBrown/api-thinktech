<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyAuthor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user =  User::find(Auth::user()->id)->role;
        if($user->role == 'author'){
            return $next($request);
        }            
        return response(json_encode([
            "errors"=> [
                "role"=> ["User is not an author!"]
            ]
        ]),  401);
    }
}
