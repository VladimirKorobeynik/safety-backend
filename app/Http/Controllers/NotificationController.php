<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use \App\Models\Notification;
use \App\Models\User;
use App\Traits\ApiHelper;


class NotificationController extends Controller
{
    use ApiHelper;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->onSuccess(Notification::all(), '');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!empty(User::find($request->input('user_id')))) {
            $notifyTime = Carbon::now()->toDateTimeString();

            $notification = Notification::create([
                'user_id' => $request->input('user_id'),
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'time' => $notifyTime
            ]);
    
            return $this->onSuccess($notification, 'Created notification');
        }
        return $this->onError(400, 'Bad request');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($notification_id)
    {
        if (!empty(Notification::find($notification_id))) {
            $notification = Notification::find($notification_id);
            return $this->onSuccess($notification, 'Notification found');
        }
        return $this->onError(404, 'This notification not found');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $notification_id)
    {
        if (!empty(Notification::find($notification_id))) {
            $updatedNotification = Notification::find($notification_id)->update([
                "user_id" => $request->user_id,
                "title" => $request->title,
                "content" => $request->content
            ]);
            return $this->onSuccess($updatedNotification, 'Update notification');
        }
        return $this->onError(404, 'This notification not found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($notification_id)
    {
        if (Notification::find($notification_id)) {
            Notification::where('notification_id', $notification_id)->delete();
            return $this->onSuccess('', 'Notification deleted');
        }
        return $this->onError(404, 'This notification not found');
    }

    public function getUserNotifications($user_id)
    {
        if (!empty(User::find($user_id))) {
            $notifications = Notification::where('user_id', $user_id)->get();
            return $this->onSuccess($notifications, '');
        }
        return $this->onError(404, 'This user not found');
    }
}
