<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use SimpleCMS\Framework\Models\Dict;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = $this->getList();
        foreach ($data as $sql) {
            if (!Dict::where('code', $sql['code'])->first()) {
                if ($dict = Dict::create(['name' => $sql['name'], 'code' => $sql['code']])) {
                    $dict->items()->createMany($sql['children']);
                }
            }
        }
    }

    public function getList()
    {
        return [
            [
                'name' => '支付渠道',
                'code' => 'payment_channel',
                'children' => [
                    [
                        'name' => '微信支付',
                        'content' => 1,
                    ],
                    [
                        'name' => '支付宝',
                        'content' => 2,
                    ]
                ]
            ],
        ];
    }
}
