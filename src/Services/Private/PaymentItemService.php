<?php
namespace SimpleCMS\Payment\Services\Private;

use SimpleCMS\Payment\Facades\Payment;
use SimpleCMS\Payment\Enums\PaymentEnum;
use SimpleCMS\Payment\Models\PaymentItem;
use SimpleCMS\Framework\Services\SimpleService;

class PaymentItemService extends SimpleService
{
    public ?string $className = PaymentItem::class;

    public function checkValid(): void
    {
        parent::setQuery(function ($query) {
            $query->whereIn('status', PaymentEnum::waitingStatus())
                ->where('invalid_at', '<', now());
        });
        $orders = parent::getAll();
        $orders->each(function (PaymentItem $paymentItem) {
            if ($paymentItem->status == PaymentEnum::Pending->value) {
                $result = Payment::checkOrderStatus($paymentItem);
                if ($result) {
                    parent::update($paymentItem->id, $result);
                } else {
                    parent::update($paymentItem->id, [
                        'status' => PaymentEnum::Close->value
                    ]);
                }
            } else {
                parent::update($paymentItem->id, [
                    'status' => PaymentEnum::Close->value
                ]);
            }
        });
    }
}
