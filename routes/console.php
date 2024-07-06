<?php

use Illuminate\Support\Facades\Artisan;
use SimpleCMS\Payment\Services\Private\PaymentService;
use SimpleCMS\Payment\Services\Private\PaymentItemService;

Artisan::command('simplecms:paymentItemCheck', function () {
    (new PaymentItemService())->checkValid();
})->purpose('检查支付订单')->everyMinute();

Artisan::command('simplecms:paymentCheck', function () {
    (new PaymentService())->checkValid();
})->purpose('检查过期订单')->everyMinute();
