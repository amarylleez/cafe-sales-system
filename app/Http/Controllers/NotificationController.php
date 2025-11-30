<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsRead();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ]);
        }

        return back()->with('status', 'Notification marked as read');
    }
}
