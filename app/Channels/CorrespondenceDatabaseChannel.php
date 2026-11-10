<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CorrespondenceDatabaseChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $data = $notification->toArray($notifiable);
        DB::table('notifications')->insert([
            'user_id' => $notifiable->getKey(),
            'title' => $data['title'],
            'message' => $data['message'],
            'reference_type' => 'letter_request',
            'reference_id' => $data['request_id'],
            'created_at' => now(),
        ]);
    }
}
