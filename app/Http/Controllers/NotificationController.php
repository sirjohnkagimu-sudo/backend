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

        // For now, return all notifications (site-wide)
        // TODO: Implement proper notification targeting
        $notifications = Notification::where('is_ignored', false)
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
        $notification = Notification::find($id);

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
        $notification = Notification::find($id);

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
        $notification = Notification::find($id);

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

    /**
     * Create sample notifications for testing
     */
    public function createSample()
    {
        $samples = [
            [
                'type' => 'system',
                'title' => 'System Maintenance',
                'message' => 'Scheduled maintenance will occur tonight from 2 AM to 4 AM',
                'details' => 'Services may be temporarily unavailable',
                'priority' => 'low',
            ],
            [
                'type' => 'alert',
                'title' => 'Security Update',
                'message' => 'New security features have been implemented',
                'details' => 'Please review your account settings',
                'priority' => 'medium',
            ],
            [
                'type' => 'subscription',
                'title' => 'Feature Update',
                'message' => 'New lab management features are now available',
                'details' => 'Check out the latest updates in your dashboard',
                'priority' => 'low',
            ],
        ];

        foreach ($samples as $sample) {
            Notification::create(array_merge($sample, [
                'user_id' => null,
                'is_read' => false,
                'is_ignored' => false,
                'timestamp' => now(),
                'related_item' => null,
            ]));
        }

        return response()->json([
            'status' => 200,
            'message' => 'Sample notifications created'
        ]);
    }
}
