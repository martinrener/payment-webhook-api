<?php

namespace App\Services;

use App\DTOs\EventLogDto;
use App\DTOs\PaymentDto;
use App\Contracts\EventLogRepositoryInterface;
use App\Contracts\PaymentRepositoryInterface;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function __construct(
        private EventLogRepositoryInterface $eventLogRepo,
        private PaymentRepositoryInterface $paymentRepo,
    ){}

    public function receivePayment(EventLogDto $event): void
    {
        $eventAlreadyExists = $this->eventLogRepo->existsEvent($event->eventId);
        $this->eventLogRepo->store($event);
        if(!$eventAlreadyExists){
            try{
                $newPaymentDetails = $this->buildPaymentDto($event);
                $this->paymentRepo->upsert($newPaymentDetails);
            }catch(\Exception $e){
                Log::error('Error upserting payment: ' . $e->getMessage());
            }
        }
    }

    private function buildPaymentDto(EventLogDto $event): PaymentDto
    {
        return new PaymentDto(
            paymentId: $event->paymentId,
            event: $event->event,
            currency: $event->currency,
            amount: $event->amount,
            userId: $event->userId,
            lastEventId: $event->eventId,
        );
    }

    public function getPayments(int $page = 1, int $perPage = 10): array
    {
        return $this->paymentRepo->list($page, $perPage);
    }

    public function getPaymentEvents(string $paymentId): array
    {
        return $this->eventLogRepo->findByPaymentId($paymentId);
    }
}