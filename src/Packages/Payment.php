<?php
namespace SimpleCMS\Payment\Packages;

use SimpleCMS\Payment\HasPayment;
use SimpleCMS\Framework\Facades\Dict;
use SimpleCMS\Payment\Enums\StatusEnum;
use SimpleCMS\Payment\Models\PaymentItem;
use SimpleCMS\Payment\Models\PaymentRefund;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Exceptions\SimpleException;
use SimpleCMS\Payment\Services\Private\PaymentService;

class Payment
{

    private int $channel = 0;

    public function channels(): Collection
    {
        return Dict::getOptionsByCode('payment_channel');
    }

    protected function checkChannel(): bool
    {
        return $this->channels()->where('value', $this->channel)->first();
    }

    /**
     * 创建订单号
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return string
     */
    public function makeOrderNo(): string
    {
        $payment_no = (string) ($this->channel + 100);
        $payment_no .= date('YmdHis');
        $payment_no .= rand(100, 999);
        return $payment_no;
    }

    /**
     * 创建支付订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  HasPayment   $model
     * @param  float|string $amount
     * @param  integer      $channel
     * @param  string $subject
     * @return array
     */
    public function create(HasPayment $model, float|string $amount, int $channel = 1, string $subject = ''): array
    {
        $this->channel = $channel;
        if (!$this->checkChannel()) {
            throw new SimpleException('The channel does not exist.');
        }
        $sql = [
            'order_no' => $model->{$model->getOrderKey()},
            'amount' => $amount,
            'channel' => $channel,
            'subject' => $subject,
            'status' => StatusEnum::Creating->value,
            'currency' => config('cms_payment.currency'),
            'order_id' => $model->{$model->getKeyName()},
            'order_type' => get_class($model)
        ];
        $bool = (new PaymentService())->create($sql);
        return [
            'status' => $bool,
            'url' => route('plugin.payment.pay', ['order_no' => $sql['order_no']])
        ];
    }

    /**
     * 获取支付渠道支付参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentItem $payment
     * @param  mixed       $appid
     * @return array
     */
    public function makePayment(PaymentItem $payment, mixed $appid = null): array
    {
        $channelClass = Channel::getClass($payment->payment->channel);
        $paymentService = new $channelClass($payment->payment);
        return $paymentService->pay($appid);
    }

    /**
     * 请求退款
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentItem $payment
     * @param  float       $amount
     * @return array
     */
    public function makeRefund(PaymentItem $payment, float $amount = null): array
    {
        $channelClass = Channel::getClass($payment->payment->channel);
        $paymentService = new $channelClass($payment->payment);
        return $paymentService->refund($amount);
    }

    /**
     * 检查订单状态
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentItem   $payment
     * @return array|boolean
     */
    public function checkOrderStatus(PaymentItem $payment): array|bool
    {
        $channelClass = Channel::getClass($payment->payment->channel);
        $paymentService = new $channelClass($payment->payment);
        return $paymentService->checkOrderStatus($payment);
    }

    /**
     * 检查退款状态
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentRefund   $payment
     * @return array|boolean
     */
    public function checkRefundStatus(PaymentRefund $payment): array|bool
    {
        $channelClass = Channel::getClass($payment->payment->channel);
        $paymentService = new $channelClass($payment->payment);
        return $paymentService->checkRefundStatus($payment);
    }
}