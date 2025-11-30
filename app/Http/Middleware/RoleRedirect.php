<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            if ($user->role === 'hq_admin') {
                return redirect()->route('hq-admin.dashboard');
            } elseif ($user->role === 'branch_manager') {
                return redirect()->route('branch-manager.dashboard');
            } elseif ($user->role === 'staff') {
                return redirect()->route('staff.dashboard');
            }
        }
        
        return $next($request);
    }
}