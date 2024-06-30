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
        Schema::create('payment_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment("主键");
            $table->uuid("payment_id")->comment("支付信息表");
            $table->string("payment_no")->index()->unique()->comment("支付单号");
            $table->decimal("amount", 16, 2)->default(0)->comment("订单金额");
            $table->decimal("paid_amount", 16, 2)->default(0)->comment("支付金额");
            $table->tinyInteger('channel')->default(0)->comment("支付渠道");
            $table->tinyInteger('status')->default(0)->comment("订单状态");
            $table->string("currency")->nullable()->comment("币种");
            $table->timestamp("request_at")->nullable()->comment("请求时间");
            $table->timestamp("paid_at")->nullable()->comment("支付时间");
            $table->timestamp("invalid_at")->nullable()->comment("过期时间");
            $table->json('request_data')->nullable()->comment("请求参数");
            $table->json('notify_data')->nullable()->comment("回调参数");
            $table->timestamps();
            $table->comment("支付订单-请求表");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_items');
    }
};
