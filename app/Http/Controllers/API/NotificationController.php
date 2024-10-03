<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function notificationList(Request $request)
    {
        $data = $request->all();
        $user = auth()->user();

        $user->last_notification_seen = now();
        $user->save();

        $type = isset($request->type) ? $request->type : null;

        $notifications = Notification::where('notifiable_id', $user->id);

        if (!empty($data['notification_ids'])) {
            $notifications = $notifications->whereIn('id', $data['notification_ids']);
        }

        switch ($type) {
            case 'markas_read':
                if (count($user->unreadNotifications) > 0) {
                    if (!empty($data['notification_ids'])) {
                        $user->unreadNotifications->whereIn('id', $data['notification_ids'])->markAsRead();
                    } else {
                        $user->unreadNotifications->markAsRead();
                    }
                }
                break;
            case 'favourite':
                $notifications->update([
                    'is_favourite' => 1
                ]);
                break;
            case 'un-favourite':
                $notifications->update([
                    'is_favourite' => 0
                ]);
                break;
            case 'delete':
                $notifications->delete();
                break;
        }

        $page = 1;
        $limit = 100;

        $notifications = $user->Notifications->sortByDesc('created_at')->forPage($page,$limit);

        $all_unread_count = isset($user->unreadNotifications) ? $user->unreadNotifications->count() : 0;

        $items = NotificationResource::collection($notifications);
        $response = [
            'notification_data' => $items,
            'all_unread_count' => $all_unread_count,
        ];
        return comman_custom_response($response);
    }

}
