<?php

namespace SimpleCMS\Payment\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SimpleCMS\Framework\Traits\PrimaryKeyUuidTrait;

/**
 * 支付订单
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @property string $id 主键
 * @property string $order_no 订单编号
 * @property ?string $payment_no 支付单号
 * @property ?string $refund_no 退款单号
 * @property ?string $subject 标题
 * @property ?float $amount 订单金额
 * @property ?float $paid_amount 支付金额
 * @property ?float $refund_amount 退款金额
 * @property int $channel 支付渠道
 * @property int $status 订单状态
 * @property string $currency 货币种类
 * @property ?string $sub_type 货币种类
 * @property ?string $order_id 上级ID
 * @property ?string $order_type 上级模型
 * @property-read ?Carbon $paid_at 付款时间
 * @property-read ?Carbon $refund_at 退款时间
 * @property-read ?Carbon $invalid_at 过期时间
 * @property-read ?Carbon $deleted_at 删除时间
 * @property-read ?Carbon $created_at 创建时间
 * @property-read ?Carbon $updated_at 更新时间
 * 
 * @property-read MorphTo $order 上级订单
 * @property-read PaymentItem $pay 最后支付
 * @property-read PaymentRefund $refund 最后退款
 * @property-read Collection<PaymentItem> $requests 支付订单
 * @property-read Collection<PaymentRefund> $refunds 退款订单
 */
class Payment extends Model
{
    use PrimaryKeyUuidTrait, SoftDeletes;

    /**
     * 可输入字段
     */
    protected $fillable = [
        'id',
        'order_no',
        'payment_no',
        'refund_no',
        'amount',
        'paid_amount',
        'refund_amount',
        'channel',
        'status',
        'sub_type',
        'subject',
        'currency',
        'paid_at',
        'refund_at',
        'invalid_at',
        'order_id',
        'order_type'
    ];

    /**
     * 显示字段类型
     */
    public $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'channel' => 'integer',
        'status' => 'integer',
        'paid_at' => 'datetime',
        'refund_at' => 'datetime',
        'invalid_at' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 追加字段
     */
    public $appends = [];


    /**
     * 隐藏关系
     */
    public $hidden = [];

    /**
     * 上级订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return MorphTo
     */
    public function order()
    {
        return $this->morphTo('order');
    }

    /**
     * 请求订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return HasMany
     */
    public function requests()
    {
        return $this->hasMany(PaymentItem::class);
    }

    /**
     * 最后支付订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return HasOne
     */
    public function pay()
    {
        return $this->hasOne(PaymentItem::class)->latest();
    }

    /**
     * 退款订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return HasMany
     */
    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class);
    }

    /**
     * 最后退款订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return HasOne
     */
    public function refund()
    {
        return $this->hasOne(PaymentRefund::class)->latest();
    }
}
