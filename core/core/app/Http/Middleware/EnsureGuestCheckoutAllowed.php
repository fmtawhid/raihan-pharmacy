<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestCheckoutAllowed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!guestCheckoutEnabled() && !auth()->check() ){
            return redirect()->route('user.login')->with('error', __('Please Login or register to complete your order.'));
        }
        
        return $next($request);
    }
}
