<?php

namespace SimpleCMS\Payment\Observers;

use SimpleCMS\Payment\Enums\PaymentEnum;
use SimpleCMS\Payment\Models\PaymentItem;
use SimpleCMS\Payment\Services\Private\PaymentService;

class PaymentItemObserver
{

    /**
     * Handle the PaymentItem "created" event.
     */
    public function created(PaymentItem $paymentItem): void
    {
        (new PaymentService())->pendingByPayment($paymentItem);
    }

    /**
     * Handle the PaymentItem "saved" event.
     */
    public function saved(PaymentItem $paymentItem): void
    {
        if ($paymentItem->getOriginal('status') != $paymentItem->status) {
            $status = PaymentEnum::fromValue($paymentItem->status);
            if ($status->isPending()) {
                (new PaymentService())->pendingByPayment($paymentItem);
            }
            if ($status->isPaid()) {
                (new PaymentService())->paidByPayment($paymentItem);
            }
        }
    }


    /**
     * Handle the PaymentItem "updated" event.
     */
    public function updated(PaymentItem $paymentItem): void
    {
        if ($paymentItem->getOriginal('status') != $paymentItem->status) {
            $status = PaymentEnum::fromValue($paymentItem->status);
            if ($status->isPending()) {
                (new PaymentService())->pendingByPayment($paymentItem);
            }
            if ($status->isPaid()) {
                (new PaymentService())->paidByPayment($paymentItem);
            }
        }
    }

}
