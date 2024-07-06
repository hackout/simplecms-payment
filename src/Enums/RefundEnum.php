<?php

namespace SimpleCMS\Payment\Enums;

/**
 * 退款
 */
enum RefundEnum: int
{
    /**
     * 待申请
     */
    case Creating = 0;
    /**
     * 处理中
     */
    case Pending = 1;
    /**
     * 已退款
     */
    case Refund = 2;
    /**
     * 退款失败
     */
    case Failed = 3;

    public static function fromValue(int $value): self
    {
        return match ($value) {
            0 => self::Creating,
            1 => self::Pending,
            2 => self::Refund,
            3 => self::Failed,
            default => self::Creating
        };
    }

    /**
     * 待回调
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public static function waitingStatus(): array
    {
        return [
            self::Pending->value,
        ];
    }

    /**
     * 已退款
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    public function isRefund():bool
    {
        return match($this){
            self::Refund => true,
            default => false
        };
    }

    /**
     * 退款中
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    public function isPending():bool
    {
        return match($this){
            self::Pending => true,
            default => false
        };
    }

    /**
     * 退款失败
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    public function isFailed():bool
    {
        return match($this){
            self::Failed => true,
            default => false
        };
    }
}