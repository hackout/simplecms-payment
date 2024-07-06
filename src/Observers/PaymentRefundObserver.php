<?php

namespace SimpleCMS\Payment\Observers;

use SimpleCMS\Payment\Enums\RefundEnum;
use SimpleCMS\Payment\Models\PaymentRefund;
use SimpleCMS\Payment\Services\Private\PaymentService;

class PaymentRefundObserver
{

    /**
     * Handle the PaymentRefund "created" event.
     */
    public function created(PaymentRefund $paymentRefund): void
    {
        (new PaymentService())->pendingByRefund($paymentRefund);
    }

    /**
     * Handle the PaymentRefund "saved" event.
     */
    public function saved(PaymentRefund $paymentRefund): void
    {
        if ($paymentRefund->getOriginal('status') != $paymentRefund->status) {
            $status = RefundEnum::fromValue($paymentRefund->status);
            if($status->isPending())
            {
                (new PaymentService())->pendingByRefund($paymentRefund);
            }
            if($status->isRefund())
            {
                (new PaymentService())->refundByRefund($paymentRefund);
            }
            if($status->isFailed())
            {
                (new PaymentService())->failedByRefund($paymentRefund);
            }
        }
    }


    /**
     * Handle the PaymentRefund "updated" event.
     */
    public function updated(PaymentRefund $paymentRefund): void
    {
        if ($paymentRefund->getOriginal('status') != $paymentRefund->status) {
            $status = RefundEnum::fromValue($paymentRefund->status);
            if($status->isPending())
            {
                (new PaymentService())->pendingByRefund($paymentRefund);
            }
            if($status->isRefund())
            {
                (new PaymentService())->refundByRefund($paymentRefund);
            }
            if($status->isFailed())
            {
                (new PaymentService())->failedByRefund($paymentRefund);
            }
        }
    }
}
