<?php

namespace SimpleCMS\Payment\Enums;

/**
 * 货币种类
 */
enum CurrencyEnum: string
{
    /**
     * 人民币
     */
    case CNY = 'CNY';
    /**
     * USDT
     */
    case USDT = 'USDT';

    /**
     * 积分
     */
    case POINT = 'POINT';

    /**
     * 未知
     */
    case UNKNOWN = 'UNKNOWN';

    public static function fromValue(string $value): self
    {
        return match ($value) {
            'CNY' => self::CNY,
            'USDT' => self::USDT,
            'POINT' => self::POINT,
            default => self::UNKNOWN
        };
    }

    /**
     * 获取法定货币
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public static function legalCurrencies(): array
    {
        return [
            self::CNY->value
        ];
    }
    /**
     * 获取数字货币
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public static function digitalCurrencies(): array
    {
        return [
            self::USDT->value
        ];
    }

    /**
     * 获取积分货币
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public static function pointCurrencies(): array
    {
        return [
            self::POINT->value
        ];
    }

    /**
     * 是否法币
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    public function isLegal(): bool
    {
        return match ($this) {
            self::CNY => true,
            default => false
        };
    }

    /**
     * 是否数字货币
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    public function isDigital(): bool
    {
        return match ($this) {
            self::USDT => true,
            default => false
        };
    }

    /**
     * 是否积分
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    public function isPoint(): bool
    {
        return match ($this) {
            self::POINT => true,
            default => false
        };
    }

}