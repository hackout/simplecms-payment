<?php

namespace SimpleCMS\Payment\Enums;

/**
 * 支付渠道
 */
enum ChannelEnum: int
{
    /**
     * 微信
     */
    case Wechat = 1;
    /**
     * 支付宝
     */
    case Alipay = 1;

    public static function fromValue(int $value): self
    {
        return match ($value) {
            1 => self::Wechat,
            2 => self::Alipay,
            default => self::Wechat
        };
    }

}