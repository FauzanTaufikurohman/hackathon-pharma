<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppNotification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsAppQueueTest extends TestCase
{
    public function test_notification_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        SendWhatsAppNotification::dispatch(
            recipient: '6281234567890',
            message: 'Tes notifikasi',
        );

        Queue::assertPushed(
            SendWhatsAppNotification::class,
            function (SendWhatsAppNotification $job): bool {
                return $job->recipient === '6281234567890'
                    && $job->message === 'Tes notifikasi';
            }
        );
    }
}