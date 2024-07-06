<?php

namespace SimpleCMS\Payment\Packages\Channels;

use Carbon\Carbon;
use EasyWeChat\Pay\Server;
use EasyWeChat\Pay\Application;
use SimpleCMS\Payment\Enums\PaymentEnum;
use SimpleCMS\Payment\Enums\RefundEnum;
use SimpleCMS\Payment\Models\Payment;
use SimpleCMS\Payment\Models\PaymentItem;
use SimpleCMS\Payment\Models\PaymentRefund;

/**
 * 微信支付
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Wechat
{

    protected $config;

    protected $app;

    public function __construct(protected Payment $payment)
    {
        $this->config = config('cms_payment.channel.wechat');
        $this->app = new Application($this->config);
    }

    /**
     * 获取支付参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string|null $openid
     * @return array
     */
    public function pay(string $openid = null): array
    {
        $request = $this->getRequest($openid);
        $result = [
            'request' => $request,
            'data' => $this->makePay($request)
        ];

        return $result;
    }

    /**
     * 申请退款
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $refund_no
     * @param  float $amount
     * @return array
     */
    public function refund(string $refund_no, float $amount = 0): array
    {
        $client = $this->app->getClient();
        $response = $client->post("v3/refund/domestic/refunds", [
            'body' => [
                'out_trade_no' => $this->payment->order_no,
                'out_refund_no' => $refund_no,
                'notify_url' => route('plugin.payment.refund', ['order_no' => $this->payment->order_no]),
                'amount' => [
                    'refund' => intval(bcmul($amount, 100, 2)),
                    'total' => intval(bcmul($this->payment->amount, 100, 2)),
                    'currency' => config('cms_payment.currency')
                ]
            ]
        ])->toArray(false);
        $status = ['SUCCESS', 'CLOSED', 'PROCESSING', 'ABNORMAL'];
        if ($response && array_key_exists('status', $response)) {
            if (in_array($response['status'], $status)) {
                $result = [
                    'order_no' => $refund_no,
                    'refund_no' => $response['refund_id'],
                    'notify_data' => $response,
                    'amount' => $amount
                ];
                if ($response['status'] == 'SUCCESS') {
                    $result['refunded_at'] = Carbon::parse($response['success_time']);
                    $result['status'] = RefundEnum::Refund->value;
                }
                if ($response['status'] == 'PROCESSING') {
                    $result['status'] = RefundEnum::Pending->value;
                }
                if ($response['status'] == 'CLOSED' || $response['status'] == 'CLOSED') {
                    $result['refunded_at'] = Carbon::parse($response['success_time']);
                    $result['status'] = RefundEnum::Failed->value;
                }
                return $result;
            }
        }
        return [];
    }

    /**
     * 检查订单状态
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentItem   $paymentItem
     * @return array|boolean
     */
    public function checkOrderStatus(PaymentItem $paymentItem): array|bool
    {
        $client = $this->app->getClient();
        $merchant = $this->app->getMerchant();
        $response = $client->get("v3/pay/transactions/out-trade-no/{$this->payment->order_no}", [
            'query' => [
                'mchid' => $merchant->getMerchantId()
            ]
        ])->toArray(false);
        if ($response && array_key_exists('trade_state', $response)) {
            if ($response['trade_state'] == 'SUCCESS') {
                return [
                    'status' => PaymentEnum::Paid->value,
                    'order_no' => $response['out_trade_no'],
                    'payment_no' => $response['transaction_id'],
                    'paid_amount' => (float) bcdiv($response['amount']['payer_total'], 100, 2),
                    'paid_at' => Carbon::parse($response['success_time']),
                    'notify_data' => $response
                ];
            }
            if ($response['trade_state'] == 'PAYERROR') {
                return [
                    'status' => PaymentEnum::Paid->value,
                    'order_no' => $response['out_trade_no'],
                    'payment_no' => $response['transaction_id'],
                    'notify_data' => $response
                ];
            }
        }
        return false;
    }

    /**
     * 检查退款信息
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  PaymentRefund $paymentRefund
     * @return array|boolean
     */
    public function checkRefundStatus(PaymentRefund $paymentRefund): array|bool
    {
        $client = $this->app->getClient();
        $response = $client->get("v3/refund/domestic/refunds/{$paymentRefund->refund_no}")->toArray(false);
        if ($response && array_key_exists('status', $response)) {
            if ($response['status'] == 'SUCCESS') {
                return [
                    'status' => RefundEnum::Refund->value,
                    'order_no' => $response['out_refund_no'],
                    'refund_no' => $response['refund_id'],
                    'refunded_at' => Carbon::parse($response['success_time']),
                    'notify_data' => $response
                ];
            }
            if ($response['status'] == 'PROCESSING') {
                return [
                    'status' => RefundEnum::Failed->value,
                    'order_no' => $response['out_refund_no'],
                    'refund_no' => $response['refund_id'],
                    'notify_data' => $response
                ];
            }
            if ($response['status'] == 'CLOSED' || $response['status'] == 'ABNORMAL') {
                return [
                    'status' => RefundEnum::Failed->value,
                    'order_no' => $response['out_refund_no'],
                    'refund_no' => $response['refund_id'],
                    'notify_data' => $response
                ];
            }
        }
        return false;
    }

    /**
     * 获取支付请求参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    protected function getRequest(string $openid = null): array
    {
        $result = [
            "mchid" => $this->config['mch_id'],
            "out_trade_no" => $this->payment->order_no,
            "appid" => $this->config['app_id'],
            "description" => $this->payment->subject,
            "notify_url" => route('plugin.payment.notify', ['order_no' => $this->payment->order_no]),
            "amount" => [
                "total" => (int) bcmul($this->payment->amount, 100, 2),
                "currency" => config('cms_payment.currency')
            ]
        ];
        if ($this->payment->sub_type == 'jsapi') {
            $result['payer'] = [
                'openid' => $openid
            ];
        }
        if ($this->payment->sub_type == 'h5') {
            $result['scene_info'] = [
                'payer_client_ip' => request()->getClientIp(),
                'h5_info' => [
                    'type' => 'Wap'
                ]
            ];
        }
        return $result;
    }

    /**
     * 创建预支付
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array $request
     * @return array
     */
    protected function makePay(array $request): array
    {
        $result = [];
        if ($this->payment->sub_type == 'jsapi') {
            $prepay_id = $this->jsApiPay($request);
            $result = $this->makeJSAPI($prepay_id);
        }
        if ($this->payment->sub_type == 'app') {
            $prepay_id = $this->appPay($request);
            $result = $this->makeAPP($prepay_id);
        }
        if ($this->payment->sub_type == 'h5') {
            $result = ['url' => $this->h5Pay($request)];
        }
        if ($this->payment->sub_type == 'native') {

            $result = ['url' => $this->appNative($request)];
        }
        return $result;
    }

    /**
     * 获取支付地址-H5
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array $request
     * @return string
     */
    protected function h5Pay(array $request): string
    {
        $client = $this->app->getClient();
        $response = $client->postJson("v3/pay/transactions/h5", $request);
        return $response->toArray(false)['h5_url'];
    }

    /**
     * 获取预下单订单号-Native
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array $request
     * @return string
     */
    protected function appNative(array $request): string
    {
        $client = $this->app->getClient();
        $response = $client->postJson("v3/pay/transactions/native", $request);
        return $response->toArray(false)['code_url'];
    }

    /**
     * 获取预下单订单号-App
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array $request
     * @return string
     */
    protected function appPay(array $request): string
    {
        $client = $this->app->getClient();
        $response = $client->postJson("v3/pay/transactions/app", $request);
        return $response->toArray(false)['prepay_id'];
    }

    /**
     * 获取预下单订单号-JSAPI
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array $request
     * @return string
     */
    protected function jsApiPay(array $request): string
    {
        $client = $this->app->getClient();
        $response = $client->postJson("v3/pay/transactions/jsapi", $request);
        return $response->toArray(false)['prepay_id'];
    }

    /**
     * 获取APP支付代码
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $prepay_id
     * @return array
     */
    protected function makeAPP(string $prepay_id): array
    {
        $utils = $this->app->getUtils();
        $config = $utils->buildAppConfig($prepay_id, $this->config['app_id']);
        return $config;
    }

    /**
     * 获取JS支付代码
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $prepay_id
     * @return array
     */
    protected function makeJSAPI(string $prepay_id): array
    {
        $utils = $this->app->getUtils();
        $config = $utils->buildMiniAppConfig($prepay_id, $this->config['app_id'], 'RSA');
        return $config;
    }

    /**
     * 检查订单状态
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $trade_no
     * @return array
     */
    public function checkOrder(string $trade_no): array
    {
        $client = $this->app->getClient();
        $merchant = $this->app->getMerchant();

        $response = $client->get("v3/pay/transactions/out-trade-no/{$trade_no}", [
            'query' => [
                'mchid' => $merchant->getMerchantId()
            ]
        ]);

        return $response->toArray();
    }


    /**
     * 获取支付请求参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string       $order_no
     * @param  float|string $amount
     * @param  string       $subject
     * @param  string       $openid
     * @return array
     */
    public function getRefundRequestArray(string $order_no, float|string $amount, string $refund_no): array
    {
        return [
            "out_trade_no" => $order_no,
            "out_refund_no" => $refund_no,
            "amount" => [
                "refund" => (int) bcmul($amount, 100, 2),
                "total" => (int) bcmul($amount, 100, 2),
                "currency" => "CNY"
            ],
            "notify_url" => url('/payment/wechat/refund')
        ];
    }

    /**
     * 请求退款
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array $request
     * @return array
     */
    public function makeRefund(array $request): array
    {
        $client = $this->app->getClient();
        $response = $client->postJson("v3/refund/domestic/refunds", $request);
        return $response->toArray(false);
    }

    /**
     * 检查退款状态
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $trade_no
     * @return array
     */
    public function checkRefund(string $trade_no): array
    {
        $client = $this->app->getClient();

        $response = $client->get("v3/refund/domestic/refunds/{$trade_no}");

        return $response->toArray();
    }

    /**
     * 获取服务
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->app->getServer();
    }
}