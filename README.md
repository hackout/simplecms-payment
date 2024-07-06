# 支付模块

一个基于国家地理的地理信息表

## 环境配置要求

1. PHP 8.0+
2. SimpleCMS/Framework

## 安装

```bash
composer require simplecms/payment
```

## 使用方法

。。。。。

### 事件监听

```php
use SimpleCMS\Payment\Models\Payment;

//创建支付
Event::listen('plugin.payment.created',function(Payment $payment){
    //Todo...
});

//待支付
Event::listen('plugin.payment.pending',function(Payment $payment){
    //Todo...
});

//支付成功
Event::listen('plugin.payment.paid',function(Payment $payment){
    //Todo...
});

//退款申请中
Event::listen('plugin.payment.refunding',function(Payment $payment){
    //Todo...
});

//退款成功
Event::listen('plugin.payment.refunded',function(Payment $payment){
    //Todo...
});
Event::listen('plugin.payment.close',function(Payment $payment){
    //Todo...
});

//订单关闭
Event::listen('plugin.payment.pending',function(Payment $payment){
    //Todo...
});
```

## SimpleCMS

请先加载simplecms/framework

## Facades

```php
use SimpleCMS\Payment\Facades\Payment; #支付 
```

## 其他说明

更多操作参考IDE提示
