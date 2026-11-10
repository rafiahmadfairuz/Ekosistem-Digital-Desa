<?php

namespace App\Notifications;

use App\Channels\CorrespondenceDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LetterWorkflowNotification extends Notification
{
    use Queueable;

    /** @param array{title: string, message: string, request_number: string, status: string, request_id: int} $data */
    public function __construct(private readonly array $data) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return [CorrespondenceDatabaseChannel::class];
    }

    /** @return array{title: string, message: string, request_number: string, status: string, request_id: int} */
    public function toArray(object $notifiable): array
    {
        return $this->data;
    }
}
