<?php

namespace SimpleCMS\Payment;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->bootConfig();
        $this->bindObservers();
        $this->loadFacades();
    }

    /**
     * 绑定Facades
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    protected function loadFacades(): void
    {
        $this->app->bind('payment', fn() => new \SimpleCMS\Payment\Packages\Payment\Payment);
    }

    /**
     * 加载路由
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    protected function loadRoutes(): void
    {
        $router = $this->app['router'];
        $router->post(config('cms_payment.route_prefix') . '/pay/{order_no}', '\SimpleCMS\Payment\Http\Controllers\PaymentController@pay')
            ->where('order_no', '[0-9A-Z]{20}')
            ->name('plugin.payment.pay');
        $router->post(config('cms_payment.route_prefix') . '/notify/{order_no}', '\SimpleCMS\Payment\Http\Controllers\PaymentController@notify')
            ->where('order_no', '[0-9A-Z]{20}')
            ->name('plugin.payment.notify');
        $router->get(config('cms_payment.route_prefix') . '/async/{order_no}', '\SimpleCMS\Payment\Http\Controllers\PaymentController@async')
            ->where('order_no', '[0-9A-Z]{20}')
            ->name('plugin.payment.async');
        $router->post(config('cms_payment.route_prefix') . '/refund/{order_no}', '\SimpleCMS\Payment\Http\Controllers\PaymentController@refundNotify')
            ->where('order_no', '[0-9A-Z]{20}')
            ->name('plugin.payment.refund');
    }

    /**
     * 加载模型事件
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    protected function bindObservers(): void
    {
        \SimpleCMS\Payment\Models\Payment::observe(\SimpleCMS\Payment\Observers\PaymentObserver::class);
        \SimpleCMS\Payment\Models\PaymentItem::observe(\SimpleCMS\Payment\Observers\PaymentItemObserver::class);
        \SimpleCMS\Payment\Models\PaymentRefund::observe(\SimpleCMS\Payment\Observers\PaymentRefundObserver::class);
    }

    /**
     * 初始化配置文件
     * @return void
     */
    protected function bootConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/cms_payment.php' => config_path('cms_payment.php'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../routes/backend.php' => config_path('../routes/backend/payment.php'),
            __DIR__ . '/../routes/console.php' => config_path('../routes/console/payment.php'),
            __DIR__ . '/../database/seeders' => database_path('seeders'),
        ], 'simplecms');
    }
}
