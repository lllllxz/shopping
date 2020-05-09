<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// Laravel 会默认执行监听器的 handle 方法，触发的事件会作为 handle 方法的参数
class UpdateProductSoldCount implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        // 从事件对象中取出对应的订单
        $order = $event->getOrder();
        // 预加载商品数据
        $order->load('orderItems.product');
        // 循环遍历订单的商品
        foreach ($order->orderItems as $item) {
            $product = $item->product;
            // 计算对应商品的销量
            $soldCount = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');    // 关联的订单是已支付
                })->sum('amount');

            $product->update([
                'sold_count' => $soldCount
            ]);
        }
    }
}
