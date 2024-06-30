<?php

namespace SimpleCMS\Payment\Enums;

/**
 * 支付请求
 */
enum PaymentEnum: int
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
     * 支付成功
     */
    case Paid = 2;
    /**
     * 支付失败
     */
    case Failed = 3;
    /**
     * 已关闭
     */
    case Close = 4;

    public static function fromValue(int $value): self
    {
        return match ($value) {
            0 => self::Creating,
            1 => self::Pending,
            2 => self::Paid,
            3 => self::Failed,
            4 => self::Close,
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
            self::Failed->value,
        ];
    }

    /**
     * 是否允许关闭
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    public function canClose(): bool
    {
        return match ($this) {
            self::Pending => true,
            self::Failed => true,
            self::Creating => true,
            default => false
        };
    }
}