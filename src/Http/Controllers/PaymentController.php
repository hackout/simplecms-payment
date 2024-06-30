<?php

namespace SimpleCMS\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\Rules\Enum;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Validator;
use SimpleCMS\Framework\Attributes\ApiName;
use SimpleCMS\Payment\Enums\ChannelEnum;
use Symfony\Component\HttpFoundation\JsonResponse;
use SimpleCMS\Payment\Services\Frontend\PaymentService;
use SimpleCMS\Framework\Http\Controllers\BackendController as BaseController;

class PaymentController extends BaseController
{

    /**
     * 获取支付参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  Request         $request
     * @param  PaymentService $service
     * @return JsonResponse
     */
    #[ApiName(name: '获取支付参数')]
    public function pay(string $order_no, Request $request, PaymentService $service): JsonResponse
    {
        $rules = [
            'order_no' => 'exists:payments,order_no',
            'channel' => [
                'sometimes',
                'nullable',
                new Enum(ChannelEnum::class)
            ],
            'appid' => 'sometimes|nullable',
            'sub_type' => 'sometimes|nullable'
        ];
        $messages = [
            'order_no.exists' => '订单不存在',
            'channel.enum' => '支付渠道不存在'
        ];
        $validator = Validator::make(array_merge([
            'order_no' => $order_no
        ], $request->post()), $rules, $messages);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $data = $validator->safe()->only([
            'order_no',
            'channel',
            'appid',
            'sub_type'
        ]);
        return $this->success($service->getPayUrl($data));
    }

    /**
     * 支付通知
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  Request         $request
     * @param  PaymentService $service
     * @return ResponseInterface
     */
    #[ApiName(name: '支付通知')]
    public function notify(string $order_no, Request $request, PaymentService $service): ResponseInterface|JsonResponse
    {
        $rules = [
            'order_no' => 'exists:payments,order_no',
        ];
        $messages = [
            'order_no.exists' => '订单不存在',
        ];
        $validator = Validator::make([
            'order_no' => $order_no
        ], $rules, $messages);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        return $service->notify($order_no);
    }

    /**
     * 同步回调
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  Request         $request
     * @param  PaymentService $service
     * @return mixed
     */
    #[ApiName(name: '同步回调')]
    public function async(string $order_no, Request $request, PaymentService $service)
    {
        $rules = [
            'order_no' => 'exists:payments,order_no',
        ];
        $messages = [
            'order_no.exists' => '订单不存在',
        ];
        $validator = Validator::make([
            'order_no' => $order_no
        ], $rules, $messages);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        Event::dispatch('payment.async.notify', $order_no);
        return $this->success();
    }

    /**
     * 退款通知
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  Request         $request
     * @param  PaymentService $service
     * @return ResponseInterface
     */
    #[ApiName(name: '退款通知')]
    public function refundNotify(string $order_no, Request $request, PaymentService $service): ResponseInterface|JsonResponse
    {
        $rules = [
            'order_no' => 'exists:payments,order_no',
        ];
        $messages = [
            'order_no.exists' => '订单不存在',
        ];
        $validator = Validator::make([
            'order_no' => $order_no
        ], $rules, $messages);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        return $service->refundNotify($order_no);
    }

}
