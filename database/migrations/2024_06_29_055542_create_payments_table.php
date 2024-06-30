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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment("主键");
            $table->string("order_no")->index()->unique()->comment("订单编号");
            $table->string("payment_no")->index()->nullable()->comment("支付订单号");
            $table->string("refund_no")->index()->nullable()->comment("退款订单号");
            $table->string("subject")->comment("支付名称");
            $table->decimal("amount", 16, 2)->default(0)->comment("订单金额");
            $table->decimal("paid_amount", 16, 2)->default(0)->comment("支付金额");
            $table->decimal("refund_amount", 16, 2)->default(0)->comment("退款金额");
            $table->string("currency")->nullable()->comment("币种");
            $table->tinyInteger('channel')->default(0)->comment("支付渠道");
            $table->tinyInteger('status')->default(0)->comment("订单状态");
            $table->string("sub_type")->nullable()->comment("支付渠道子选项");
            $table->timestamp("paid_at")->nullable()->comment("支付时间");
            $table->timestamp("refund_at")->nullable()->comment("退款时间");
            $table->timestamp("invalid_at")->nullable()->comment("失效时间");
            $table->nullableUuidMorphs("order");
            $table->softDeletes();
            $table->timestamps();
            $table->comment("支付订单信息表");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
