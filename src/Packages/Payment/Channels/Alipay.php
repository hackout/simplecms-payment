<?php

namespace SimpleCMS\Payment\Packages\Payment\Channels;

use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Kernel\Factory;
use SimpleCMS\Payment\Models\Payment;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use SimpleCMS\Framework\Exceptions\SimpleException;

class Alipay
{
    protected $app;

    public function __construct(protected Payment $payment, protected string $openid)
    {
        Factory::setOptions($this->getOptions());
        $this->app = Factory::payment();
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
     * 获取公共参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return Config
     */
    protected function getOptions(): Config
    {
        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->signType = 'RSA2';

        $options->appId = config('cms_payment.channel.alipay.appid');

        $options->merchantPrivateKey = config('cms_payment.channel.alipay.merchant_key');
        $options->alipayPublicKey = config('cms_payment.channel.alipay.public_key');
        $options->notifyUrl = route('plugin.payment.notify', ['order_no' => $this->payment->order_no]);
        return $options;
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
            "timeout_express" => config('cms_payment.time_out'),
            "body" => $this->payment->subject,
            "out_trade_no" => $this->payment->order_no,
            "total_amount" => $this->payment->amount,
            "subject" => $this->payment->subject
        ];
        if ($this->payment->sub_type == 'jsapi') {
            $result['product_code'] = 'JSAPI_PAY';
            $result['buyer_open_id'] = $this->openid;
            $result['op_app_id'] = config('cms_payment.channel.alipay.op_app_id');
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
            $result = $this->app->common()
                ->batchOptional($request)
                ->create($request['subject'], $request['out_trade_no'], $request['total_amount'], $request['buyer_open_id']);
            $responseChecker = new ResponseChecker();
            if ($responseChecker->success($result)) {
                $result = [
                    'out_trade_no' => $result->outTradeNo,
                    'trade_no' => $result->tradeNo
                ];
            } else {
                throw new SimpleException("调用失败，原因：" . $result->msg . "，" . $result->subMsg);
            }
        }
        if ($this->payment->sub_type == 'app') {
            $result = $this->app->App()
                ->batchOptional($request)
                ->create($request['subject'], $request['out_trade_no'], $request['total_amount']);
            $responseChecker = new ResponseChecker();
            if ($responseChecker->success($result)) {
                $result = [
                    'out_trade_no' => $result->outTradeNo,
                    'trade_no' => $result->tradeNo,
                    'orderStr' => $result->orderStr
                ];
            } else {
                throw new SimpleException("调用失败，原因：" . $result->msg . "，" . $result->subMsg);
            }
        }
        if ($this->payment->sub_type == 'wap') {
            $result = $this->app->Wap()
                ->batchOptional($request)
                ->create($request['subject'], $request['out_trade_no'], $request['total_amount'], route('plugin.payment.async', ['order_no' => $this->payment->order_no]), route('plugin.payment.async', ['order_no' => $this->payment->order_no]));
            $responseChecker = new ResponseChecker();
            if ($responseChecker->success($result)) {
                $result = [
                    'out_trade_no' => $result->outTradeNo,
                    'trade_no' => $result->tradeNo,
                    'orderStr' => $result->orderStr
                ];
            } else {
                throw new SimpleException("调用失败，原因：" . $result->msg . "，" . $result->subMsg);
            }
        }
        if ($this->payment->sub_type == 'page') {
            $result = $this->app->Page()
                ->batchOptional($request)
                ->create($request['subject'], $request['out_trade_no'], $request['total_amount'], route('plugin.payment.async', ['order_no' => $this->payment->order_no]), route('plugin.payment.async', ['order_no' => $this->payment->order_no]));
            $responseChecker = new ResponseChecker();
            if ($responseChecker->success($result)) {
                $result = [
                    'out_trade_no' => $result->outTradeNo,
                    'trade_no' => $result->tradeNo,
                    'orderStr' => $result->orderStr
                ];
            } else {
                throw new SimpleException("调用失败，原因：" . $result->msg . "，" . $result->subMsg);
            }
        }
        if ($this->payment->sub_type == 'face_to_face') {
            $result = $this->app->FaceToFace()
                ->batchOptional($request)
                ->create($request['subject'], $request['out_trade_no'], $request['total_amount'], route('plugin.payment.async', ['order_no' => $this->payment->order_no]), route('plugin.payment.async', ['order_no' => $this->payment->order_no]));
            $responseChecker = new ResponseChecker();
            if ($responseChecker->success($result)) {
                $result = [
                    'out_trade_no' => $result->outTradeNo,
                    'trade_no' => $result->tradeNo,
                    'orderStr' => $result->orderStr
                ];
            } else {
                throw new SimpleException("调用失败，原因：" . $result->msg . "，" . $result->subMsg);
            }
        }
        return $result;
    }

}