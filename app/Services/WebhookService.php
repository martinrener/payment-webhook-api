<?php

namespace App\Services;

use App\DTOs\EventLogDto;
use App\DTOs\PaymentDto;
use App\Contracts\EventLogRepositoryInterface;
use App\Contracts\PaymentRepositoryInterface;

class WebhookService
{
    public function __construct(
        private EventLogRepositoryInterface $event_log_repo,
        private PaymentRepositoryInterface $payment_repo,
    ){}

    public function receivePayment(EventLogDto $event): void 
    {
        $event_already_exists = $this->event_log_repo->existsEvent($event->event_id);
        if(!$event_already_exists){
            try{
                $this->event_log_repo->store($event);
                $new_payment_details = $this->buildPaymentDto($event);
                $this->payment_repo->upsert($new_payment_details);
            }catch(\Exception $e){
                \Log::error('Error upserting payment: ' . $e->getMessage());
            }
        }
    }
    private function buildPaymentDto(EventLogDto $event): PaymentDto
    {
        return new PaymentDto(
            payment_id: $event->payment_id,
            event: $event->event,
            currency: $event->currency,
            amount: $event->amount,
            user_id: $event->user_id,
            last_event_id: $event->event_id,
        );
    }

    public function getPayments(): array
    {
        return $this->payment_repo->list();
    }

    public function getPaymentEvents(string $payment_id): array 
    {
        return $this->event_log_repo->findByPaymentId($payment_id);
    }
}