<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBranchAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // HQ Admin can access everything
        if ($user->role === 'hq_admin') {
            return $next($request);
        }
        
        // Branch Manager and Staff must have a branch assigned
        if (!$user->branch_id) {
            abort(403, 'No branch assigned to your account.');
        }
        
        // If route has branch_id parameter, verify access
        if ($request->route('branch_id')) {
            $requestedBranchId = $request->route('branch_id');
            
            if ($user->branch_id != $requestedBranchId) {
                abort(403, 'You do not have access to this branch.');
            }
        }
        
        return $next($request);
    }
}