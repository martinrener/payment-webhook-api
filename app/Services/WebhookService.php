<?php

namespace App\Services;

use App\DTOs\EventLogDto;
use App\DTOs\PaymentDto;
use App\Contracts\EventLogRepositoryInterface;
use App\Contracts\PaymentRepositoryInterface;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;

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

    public function getPayments(int $page = 1, int $perPage = 10, string $event = null, string $user_id = null, string $currency = null, string $dateFrom = null, string $dateTo = null): array
    {
        return $this->paymentRepo->list($page, $perPage, $event, $user_id, $currency, $dateFrom, $dateTo);
    }

    public function getPaymentEvents(string $paymentId): array
    {
        return $this->eventLogRepo->findByPaymentId($paymentId);
    }

    public function refundPayment(string $paymentId): void
    {
        $payment = $this->paymentRepo->findByPaymentId($paymentId);
        if (!$payment) {
            throw new \Exception('Payment not found');
        }
        $event = $this->buildEventLogDtoFromRefund($payment);
        $this->eventLogRepo->store($event);
        $payment = $this->buildPaymentDto($event);
        $this->paymentRepo->upsert($payment);
    }

    private function buildEventLogDtoFromRefund(Payment $payment): EventLogDto
    {
        return new EventLogDto(
            eventId: uniqid('evt_', true),
            paymentId: $payment->payment_id,
            event: 'payment.refunded',
            amount: $payment->amount,
            currency: $payment->currency,
            userId: $payment->user_id,
            timestamp: now()->toDateTimeString(),
            receivedAt: now()->toDateTimeString(),
        );
    }
}