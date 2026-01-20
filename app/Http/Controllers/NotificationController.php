<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->where('is_ignored', false)
            ->orderBy('timestamp', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'status' => 200,
            'message' => 'Notifications retrieved successfully'
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)->find($id);

        if (!$notification) {
            return response()->json([
                'status' => 404,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'notification' => $notification,
            'status' => 200,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)->find($id);

        if (!$notification) {
            return response()->json([
                'status' => 404,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Ignore a notification
     */
    public function ignore($id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)->find($id);

        if (!$notification) {
            return response()->json([
                'status' => 404,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->is_ignored = true;
        $notification->save();

        return response()->json([
            'notification' => $notification,
            'status' => 200,
            'message' => 'Notification ignored'
        ]);
    }

    /**
     * Clear all read notifications
     */
    public function clearRead()
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Read notifications cleared successfully'
        ]);
    }
}
