<?php
namespace SimpleCMS\Framework\Traits;

use Illuminate\Support\Str;
use SimpleCMS\Payment\HasPayment;
use Maatwebsite\Excel\Facades\Excel;
use SimpleCMS\Payment\Models\Payment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * 支付模块
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
trait PaymentTrait
{

    public static function bootPaymentTrait()
    {
        static::deleting(function (HasPayment $model) {
            $model->payment && $model->payment->delete();
        });
    }

    /**
     * 支付订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return MorphOne
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'order');
    }

    /**
     * 获取支付订单Key
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return string
     */
    public function getOrderKey(): string
    {
        return defined('static::ORDER_KEY') ? static::ORDER_KEY : 'order_no';
    }

}