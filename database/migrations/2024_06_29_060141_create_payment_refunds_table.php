<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment("主键");
            $table->uuid("payment_id")->comment("支付信息表");
            $table->string("refund_no")->index()->unique()->comment("退款单号");
            $table->decimal("amount", 16, 2)->default(0)->comment("退款金额");
            $table->tinyInteger('channel')->default(0)->comment("退款渠道");
            $table->tinyInteger('status')->default(0)->comment("订单状态");
            $table->string("currency")->nullable()->comment("币种");
            $table->timestamp("request_at")->nullable()->comment("请求时间");
            $table->timestamp("refunded_at")->nullable()->comment("到账时间");
            $table->timestamp("processed_at")->nullable()->comment("处理时间");
            $table->json('request_data')->nullable()->comment("请求参数");
            $table->json('notify_data')->nullable()->comment("回调参数");
            $table->timestamps();
            $table->comment("支付订单-退款记录表");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
    }
};
