<?php

namespace SimpleCMS\Payment;

use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * 
 * @property ?\SimpleCMS\Payment\Models\Payment $payment
 */
interface HasPayment
{
    public function payment(): MorphOne;

    public function getOrderKey(): string;
}
