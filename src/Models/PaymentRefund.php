<?php

namespace SimpleCMS\Payment\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use SimpleCMS\Framework\Traits\PrimaryKeyUuidTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 支付订单-退款记录模块
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @property string $id 主键
 * @property string $payment_id 上级ID
 * @property string $refund_no 退款单号
 * @property string $amount 订单金额
 * @property int $channel 支付渠道
 * @property int $status 订单状态
 * @property string $currency 货币种类
 * @property-read ?Carbon $request_at 请求时间
 * @property-read ?Carbon $refunded_at 退款时间
 * @property-read ?Carbon $processed_at 处理时间
 * @property-read ?Carbon $created_at 创建时间
 * @property-read ?Carbon $updated_at 更新时间
 * 
 * @property-read Payment $payment 支付订单
 */
class PaymentRefund extends Model
{
    use PrimaryKeyUuidTrait;

    /**
     * 可输入字段
     */
    protected $fillable = [
        'id',
        'payment_id',
        'refund_no',
        'amount',
        'channel',
        'status',
        'currency',
        'request_at',
        'refunded_at',
        'processed_at',
        'request_data',
        'notify_data'
    ];

    /**
     * 显示字段类型
     */
    public $casts = [
        'amount' => 'decimal:2',
        'channel' => 'integer',
        'status' => 'integer',
        'request_data' => 'array',
        'notify_data' => 'array',
        'request_at' => 'datetime',
        'refunded_at' => 'datetime',
        'processed_at' => 'datetime',
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
     * 请求订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return BelongsTo
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

}
