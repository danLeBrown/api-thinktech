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
        if(User::find(auth("sanctum")->user()->id)->role()->role == 'author'){
            return $next($request);
        }            
        return response(json_encode([
            "error"=> [
                "role"=> ["User is not an author!"]
            ]
        ], 401));
    }
}
