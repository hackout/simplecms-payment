<?php
namespace SimpleCMS\Payment\Services\Frontend;

use SimpleCMS\Payment\Enums\StatusEnum;
use SimpleCMS\Framework\Services\SimpleService;
use SimpleCMS\Framework\Exceptions\SimpleException;
use SimpleCMS\Payment\Models\Payment as PaymentModel;

class PaymentService extends SimpleService
{
    public ?string $className = PaymentModel::class;

    /**
     * 获取支付参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array $data
     * @return array
     * 
     * @throws SimpleException
     */
    public function getPayUrl(array $data): array
    {
        $order_no = trim($data['order_no']);
        $channel = array_key_exists('channel', $data) ? intval($data['channel']) : null;
        $appid = array_key_exists('appid', $data) ? intval($data['appid']) : null;
        $sub_type = array_key_exists('sub_type', $data) ? intval($data['sub_type']) : null;
        $result = [];
        tap(parent::find(['order_no' => $order_no]), function (PaymentModel $payment) use (&$result, $channel, $appid, $sub_type) {
            $paymentStatus = StatusEnum::fromValue($payment->status);
            if ($paymentStatus->isPaid()) {
                throw new SimpleException('The order has been paid already.');
            }
            if ($paymentStatus == StatusEnum::Close) {
                throw new SimpleException('The order has been closed already.');
            }
            if ($payment->invalid_at->lt(now())) {
                throw new SimpleException('The order is invalid already.');
            }
            if (!is_null($channel) && !is_null($sub_type)) {
                $payment->fill(['channel' => $channel, 'sub_type' => $sub_type])->save();
            }
            $result = (new PaymentItemService())->makePayData($payment, $appid);
            if (!$result['status']) {
                throw new SimpleException('The order has been closed, cause the order is not valid.');
            }
            $result = $result['data'];
        });
        return $result;
    }

    /**
     * 关闭订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentModel $payment
     * @return void
     */
    public function closePayment(PaymentModel $payment): void
    {
        $paymentStatus = StatusEnum::fromValue($payment->status);
        if ($paymentStatus->canClose()) {
            parent::setValue($payment->id, 'status', StatusEnum::Close->value);
        }
    }
}
