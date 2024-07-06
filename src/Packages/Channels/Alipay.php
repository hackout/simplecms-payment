<?php

namespace SimpleCMS\Payment\Packages\Channels;

use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Kernel\Factory;
use Carbon\Carbon;
use SimpleCMS\Payment\Enums\PaymentEnum;
use SimpleCMS\Payment\Enums\RefundEnum;
use SimpleCMS\Payment\Models\Payment;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use SimpleCMS\Framework\Exceptions\SimpleException;
use SimpleCMS\Payment\Models\PaymentItem;
use SimpleCMS\Payment\Models\PaymentRefund;

class Alipay
{
    protected $app;

    public function __construct(protected Payment $payment)
    {
        Factory::setOptions($this->getOptions());
        $this->app = Factory::payment();
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
    protected function getRequest(string $openid = null): array
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
            $result['buyer_open_id'] = $openid;
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