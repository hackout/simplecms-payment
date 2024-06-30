<?php
namespace SimpleCMS\Payment\Services\Frontend;

use SimpleCMS\Framework\Exceptions\SimpleException;
use SimpleCMS\Payment\Enums\PaymentEnum;
use SimpleCMS\Payment\Enums\StatusEnum;
use SimpleCMS\Payment\Models\Payment as PaymentModel;
use SimpleCMS\Payment\Models\PaymentItem;
use SimpleCMS\Framework\Services\SimpleService;
use SimpleCMS\Payment\Facades\Payment;

class PaymentItemService extends SimpleService
{
    public ?string $className = PaymentItem::class;

    /**
     * 获取支付参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentModel $payment
     * @param  mixed $appid
     * @return array
     * 
     * @throws SimpleException
     */
    public function makePayData(PaymentModel $payment, mixed $appid = null): array
    {
        $sql = [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'channel' => $payment->channel,
            'status' => PaymentEnum::Creating->value,
            'currency' => config('cms_payment.currency'),
        ];
        $result = [
            'status' => false,
            'data' => null
        ];
        tap(parent::create($sql), function (bool $status) use (&$status, $payment, $appid, &$result) {
            if (!$status) {
                (new PaymentService)->closePayment($payment);
            } else {
                if ($this->item instanceof PaymentItem) {
                    $paymentData = Payment::makePayment($this->item, $appid);
                    $sql = [
                        'request_at' => now(),
                        'invalid_at' => now()->addMinutes(config('cms_payment.time_out')),
                        'request_data' => $paymentData['request']
                    ];
                    parent::update($this->item->id, $sql);
                    $result['data'] = $paymentData['data'];
                    $result['status'] = true;
                }
            }
        });
        return $result;
    }
}
