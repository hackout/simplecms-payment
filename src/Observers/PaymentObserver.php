<?php

namespace SimpleCMS\Payment\Observers;

use SimpleCMS\Payment\Models\Payment;
use SimpleCMS\Payment\Enums\StatusEnum;
use SimpleCMS\Payment\Models\PaymentItem;
use SimpleCMS\Payment\Models\PaymentRefund;

class PaymentObserver
{

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        event('plugin.payment.created', $payment);
    }

    /**
     * Handle the Payment "saved" event.
     */
    public function saved(Payment $payment): void
    {
        if ($payment->getOriginal('status') != $payment->status) {
            if ($payment->status == StatusEnum::Pending->value) {
                event('plugin.payment.pending', $payment);
            }
            if ($payment->status == StatusEnum::Paid->value) {
                event('plugin.payment.paid', $payment);
            }
            if ($payment->status == StatusEnum::Refunding->value) {
                event('plugin.payment.refunding', $payment);
            }
            if ($payment->status == StatusEnum::Refunded->value) {
                event('plugin.payment.refunded', $payment);
            }
            if ($payment->status == StatusEnum::Close->value) {
                event('plugin.payment.close', $payment);
            }
        }
    }


    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        if ($payment->getOriginal('status') != $payment->status) {
            if ($payment->status == StatusEnum::Pending->value) {
                event('plugin.payment.pending', $payment);
            }
            if ($payment->status == StatusEnum::Paid->value) {
                event('plugin.payment.paid', $payment);
            }
            if ($payment->status == StatusEnum::Refunding->value) {
                event('plugin.payment.refunding', $payment);
            }
            if ($payment->status == StatusEnum::Refunded->value) {
                event('plugin.payment.refunded', $payment);
            }
            if ($payment->status == StatusEnum::Close->value) {
                event('plugin.payment.close', $payment);
            }
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        $payment->requests && $payment->requests->each(fn(PaymentItem $item) => $item->delete());
        $payment->refunds && $payment->refunds->each(fn(PaymentRefund $refund) => $refund->delete());
        event('plugin.payment.deleted', $payment);
    }
}
