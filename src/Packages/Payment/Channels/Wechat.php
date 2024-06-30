<?php

namespace SimpleCMS\Payment\Packages\Payment\Channels;

use EasyWeChat\Pay\Server;
use EasyWeChat\Pay\Application;
use SimpleCMS\Payment\Models\Payment;

/**
 * 微信支付
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Wechat
{

    protected $config;

    protected $app;

    public function __construct(protected Payment $payment, protected string $openid)
    {
        $this->config = config('cms_payment.channel.wechat');
        $this->app = new Application($this->config);
    }

    /**
     * 获取支付参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public function pay(): array
    {
        $request = $this->getRequest();
        $result = [
            'request' => $request,
            'data' => $this->makePay($request)
        ];

        return $result;
    }

    /**
     * 获取支付请求参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    protected function getRequest(): array
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
                'openid' => $this->openid
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