<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user (with caching and pagination)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);
        $cacheKey = "notifications_{$userId}_{$page}_{$perPage}";

        // Check cache for first page only
        if ($page === 1 && $userId) {
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }
        }

        $query = Notification::where('is_ignored', false)
            ->orderBy('timestamp', 'desc');

        // Add pagination
        $notifications = $query->paginate($perPage);

        $response = [
            'notifications' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
            ],
            'status' => 200,
            'message' => 'Notifications retrieved successfully'
        ];

        // Cache first page for 60 seconds
        if ($page === 1 && $userId) {
            Cache::put($cacheKey, $response, 60);
        }

        return response()->json($response);
    }

    /**
     * Helper method to clear notification cache
     */
    private function clearNotificationCache($userId)
    {
        // Clear all notification cache keys for this user
        Cache::forget("notifications_{$userId}_1_20");
        Cache::forget("notifications_{$userId}_1_10");
        Cache::forget("notifications_{$userId}_1_50");
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'status' => 404,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->is_read = true;
        $notification->save();

        // Clear cache
        if ($user) {
            $this->clearNotificationCache($user->id);
        }

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
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'status' => 404,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        // Clear cache
        if ($user) {
            $this->clearNotificationCache($user->id);
        }

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
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'status' => 404,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->is_ignored = true;
        $notification->save();

        // Clear cache
        if ($user) {
            $this->clearNotificationCache($user->id);
        }

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

        // Clear cache
        if ($user) {
            $this->clearNotificationCache($user->id);
        }

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
        $user = Auth::user();
        $userId = $user ? $user->id : null;

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

        // Clear cache
        if ($userId) {
            $this->clearNotificationCache($userId);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Sample notifications created'
        ]);
    }
}
