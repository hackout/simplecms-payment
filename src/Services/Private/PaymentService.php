<?php
namespace SimpleCMS\Payment\Services\Private;

use SimpleCMS\Payment\Enums\StatusEnum;
use SimpleCMS\Payment\Models\PaymentItem;
use SimpleCMS\Payment\Models\PaymentRefund;
use SimpleCMS\Framework\Services\SimpleService;

class PaymentService extends SimpleService
{
    public ?string $className = Payment::class;

    /**
     * 检查过期订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    public function checkValid(): void
    {
        parent::updateV2([
            [
                function ($query) {
                    $query->whereIn('status', StatusEnum::waitingOrder())
                        ->where('invalid_at', '<', now());
                }
            ]
        ], [
            'status' => StatusEnum::Close->value
        ]);
    }

    /**
     * 创建订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentItem $paymentItem
     * @return void
     */
    public function pendingByPayment(PaymentItem $paymentItem): void
    {
        parent::updateV2([
            ['id', '=', $paymentItem->payment_id],
            [
                function ($query) {
                    $query->whereIn('status', StatusEnum::waitingOrder());
                }
            ]
        ], [
            'status' => StatusEnum::Pending->value
        ]);
    }

    /**
     * 付款成功
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentItem $paymentItem
     * @return void
     */
    public function paidByPayment(PaymentItem $paymentItem): void
    {
        parent::update($paymentItem->payment_id, [
            'status' => StatusEnum::Paid->value,
            'paid_amount' => $paymentItem->paid_amount,
            'payment_no' => $paymentItem->payment_no
        ]);
    }

    /**
     * 申请退款
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentRefund $paymentRefund
     * @return void
     */
    public function pendingByRefund(PaymentRefund $paymentRefund): void
    {
        parent::update($paymentRefund->payment_id, [
            'status' => StatusEnum::Refunding->value,
            'refund_no' => $paymentRefund->refund_no
        ]);
    }

    /**
     * 申请退款
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentRefund $paymentRefund
     * @return void
     */
    public function refundByRefund(PaymentRefund $paymentRefund): void
    {
        parent::update($paymentRefund->payment_id, [
            'status' => StatusEnum::Refunded->value,
            'refund_at' => $paymentRefund->refunded_at
        ]);
    }

    /**
     * 申请退款
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentRefund $paymentRefund
     * @return void
     */
    public function failedByRefund(PaymentRefund $paymentRefund): void
    {
        parent::update($paymentRefund->payment_id, [
            'status' => StatusEnum::Paid->value
        ]);
    }
}
