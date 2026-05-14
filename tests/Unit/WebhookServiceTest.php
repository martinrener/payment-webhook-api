<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\WebhookService;
use App\Contracts\EventLogRepositoryInterface;
use App\Contracts\PaymentRepositoryInterface;
use App\DTOs\EventLogDto;
use App\Models\Payment;

class WebhookServiceTest extends TestCase
{
    private WebhookService $service;
    private EventLogRepositoryInterface $eventLogRepo;
    private PaymentRepositoryInterface $paymentRepo;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->eventLogRepo = $this->createMock(EventLogRepositoryInterface::class);
        $this->paymentRepo = $this->createMock(PaymentRepositoryInterface::class);
        
        $this->service = new WebhookService($this->eventLogRepo, $this->paymentRepo);
    }

    private function makeDto(): EventLogDto
    {
        return new EventLogDto(
            eventId: 'evt_123',
            paymentId: 'pay_123',
            event: 'payment.created',
            currency: 'USD',
            amount: 500,
            timestamp: '2024-01-01 00:00:00',
            userId: 'user_123',
            receivedAt: '2024-01-01 00:00:00',
        );
    }

    public function test_receivePayment_newEvent_storesIt(): void
    {
        $dto = $this->makeDto();

        $this->eventLogRepo
            ->method('existsEvent')
            ->willReturn(false);

        $this->eventLogRepo
            ->expects($this->once())
            ->method('store');

        $this->paymentRepo
            ->expects($this->once())
            ->method('upsert');

        $this->service->receivePayment($dto);
    }

    public function test_receivePayment_repeatedEvent_doesNotStoreIt(): void
    {
        $dto = $this->makeDto();

        $this->eventLogRepo
            ->method('existsEvent')
            ->willReturn(true);

        $this->eventLogRepo
            ->expects($this->never())
            ->method('store');
        
        $this->paymentRepo
            ->expects($this->never())
            ->method('upsert');

        $this->service->receivePayment($dto);
    }

    public function test_refundPayment_existingPayment_storesAndUpserts(): void
    {
        $payment = new Payment();
        $payment->payment_id = 'pay_123';
        $payment->amount = 500;
        $payment->currency = 'USD';
        $payment->user_id = 'user_123';

        $this->paymentRepo->method('findByPaymentId')->willReturn($payment);

        $this->eventLogRepo
            ->expects($this->once())
            ->method('store');

        $this->paymentRepo
            ->expects($this->once())
            ->method('upsert');

        $this->service->refundPayment('pay_123');
    }

    public function test_refundPayment_NonExistingPayment_thorwsExepction(): void
    {
        $this->paymentRepo->method('findByPaymentId')->willReturn(null);

        $this->eventLogRepo
            ->expects($this->never())
            ->method('store');

        $this->paymentRepo
            ->expects($this->never())
            ->method('upsert');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment not found');

        $this->service->refundPayment('pay_123');
    }
}
