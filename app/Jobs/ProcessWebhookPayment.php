<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\DTOs\EventLogDto;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Log;

class ProcessWebhookPayment implements ShouldQueue
{
    use Queueable;

    private EventLogDto $event;

    public $tries = 3;
    public array $backoff = [1, 5, 10];

    /**
     * Create a new job instance.
     */
    public function __construct(EventLogDto $event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(WebhookService $webhookService): void
    {
        Log::info('Webhook received', [
            'event_id' => $this->event->eventId,
            'payment_id' => $this->event->paymentId,
            'user_id' => $this->event->userId,
        ]);
        try{
            $webhookService->receivePayment($this->event);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            throw $e;
        }
    }
}
