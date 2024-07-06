<?php
namespace SimpleCMS\Payment\Packages;

use SimpleCMS\Payment\Enums\ChannelEnum;

class Channel
{
    /**
     * 获取支付渠道模型路径
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  integer $channel
     * @return string
     */
    public static function getClass(int $channel): string
    {
        $status = ChannelEnum::fromValue($channel);
        return match ($status) {
            ChannelEnum::Wechat => Channels\Wechat::class,
            ChannelEnum::Alipay => Channels\Alipay::class,
            default => false
        };
    }
}