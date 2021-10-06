<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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
        if($request->user()->role()->role != 'author'){
            return json_encode([
                "error"=> [
                    "role"=> ["User is not an author!"]
                ]
            ], 401);
        }
        return $next($request);
    }
}
