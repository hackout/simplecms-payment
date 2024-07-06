<?php

namespace SimpleCMS\Payment\Enums;

/**
 * 支付订单状态
 */
enum StatusEnum: int
{
    /**
     * 待请求
     */
    case Creating = 0;
    /**
     * 待支付
     */
    case Pending = 1;
    /**
     * 已付款
     */
    case Paid = 2;
    /**
     * 退款中
     */
    case Refunding = 3;
    /**
     * 已退款
     */
    case Refunded = 4;
    /**
     * 已关闭
     */
    case Close = 5;

    public static function fromValue(int $value): self
    {
        return match ($value) {
            0 => self::Creating,
            1 => self::Pending,
            2 => self::Paid,
            3 => self::Refunding,
            4 => self::Refunded,
            5 => self::Close,
            default => self::Creating
        };
    }

    /**
     * 可删除订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public static function deleteStatus(): array
    {
        return [
            self::Creating->value,
            self::Close->value,
        ];
    }

    /**
     * 无效订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public static function invalidOrder(): array
    {
        return [
            self::Creating->value,
            self::Pending->value,
            self::Close->value
        ];
    }

    /**
     * 有效订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public static function validOrder(): array
    {
        return [
            self::Paid->value,
            self::Refunding->value,
            self::Refunded->value
        ];
    }

    /**
     * 等待订单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public static function waitingOrder(): array
    {
        return [
            self::Creating->value,
            self::Pending->value
        ];
    }

    /**
     * 是否已支付
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    public function isPaid(): bool
    {
        return match ($this) {
            self::Paid => true,
            self::Refunding => true,
            self::Refunded => true,
            default => false
        };
    }

    /**
     * 是否可关闭
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    public function canClose():bool
    {
        return match($this){
            self::Creating => true,
            self::Pending => true,
            default => false
        };
    }
}