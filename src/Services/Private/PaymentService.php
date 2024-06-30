<?php
namespace SimpleCMS\Payment\Services\Private;

use SimpleCMS\Payment\Models\Payment;
use SimpleCMS\Framework\Services\SimpleService;

class PaymentService extends SimpleService
{
    public ?string $className = Payment::class;

}
