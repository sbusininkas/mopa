<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return back()->with('success', 'Pranešimas pažymėtas kaip perskaitytas');
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        
        return back()->with('success', 'Visi pranešimai pažymėti kaip perskaityti');
    }

    public function unread()
    {
        $unreadNotifications = auth()->user()->unreadNotifications;
        
        return response()->json([
            'count' => $unreadNotifications->count(),
            'notifications' => $unreadNotifications->take(5)->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'data' => $notification->data,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            })
        ]);
    }
}
