<?php

namespace SimpleCMS\Payment\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use SimpleCMS\Framework\Traits\PrimaryKeyUuidTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * 支付订单-请求记录模块
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @property string $id 主键
 * @property string $payment_id 上级ID
 * @property string $payment_no 支付单号
 * @property string $amount 订单金额
 * @property string $paid_amount 支付金额
 * @property int $channel 支付渠道
 * @property int $status 订单状态
 * @property string $currency 货币种类
 * @property-read ?Carbon $paid_at 付款时间
 * @property-read ?Carbon $request_at 请求时间
 * @property-read ?Carbon $invalid_at 过期时间
 * @property-read ?Carbon $created_at 创建时间
 * @property-read ?Carbon $updated_at 更新时间
 * 
 * @property-read Payment $payment 支付订单
 */
class PaymentItem extends Model
{
    use PrimaryKeyUuidTrait;

    /**
     * 可输入字段
     */
    protected $fillable = [
        'id',
        'payment_id',
        'payment_no',
        'amount',
        'paid_amount',
        'channel',
        'status',
        'currency',
        'request_at',
        'paid_at',
        'invalid_at',
        'request_data',
        'notify_data'
    ];

    /**
     * 显示字段类型
     */
    public $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'channel' => 'integer',
        'status' => 'integer',
        'request_data' => 'array',
        'notify_data' => 'array',
        'paid_at' => 'datetime',
        'request_at' => 'datetime',
        'invalid_at' => 'datetime',
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
