<?php

namespace App\Http\Requests;


use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\UserAddress;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;

class SeckillOrderRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address_id' => [
                'required',
                Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id)
                ],
            'sku_id' => [
                'required',
                function($attribute, $value, $fail) {
                    // 从redis中读取数据
                    $stock = Redis::get('seckill_sku_'.$value);
                    // 如果是null就代表该商品不是秒杀商品
                    if (is_null($stock)) {
                        return $fail('该商品不存在');
                    }
                    // 判断库存
                    if ($stock < 1) {
                        return $fail('该商品已售完');
                    }

                    if (!$user = \Auth::user()) {
                        throw new AuthenticationException('请先登录');
                    }
                    if (!$user->email_verified_at) {
                        throw new InvalidRequestException('请先验证邮箱');
                    }

                    // 大多数用户在上面的逻辑里就被拒绝了
                    // 因此下方的 SQL 查询不会对整体性能有太大影响
                    $sku = ProductSku::find($value);
                    if ($sku->product->seckill->is_before_start) {
                        return $fail('秒杀尚未开始');
                    }
                    if ($sku->product->seckill->is_after_end) {
                        return $fail('秒杀已经结束');
                    }

                    if ($order = Order::query()
                    ->where('user_id', $this->user()->id)
                    ->whereHas('orderItems', function ($query) use($value) {
                        // 筛选包含当前SKUID的订单
                        $query->where('product_sku_id', $value);
                    })
                    ->where(function ($query) {
                        $query->whereNotNull('paid_at')
                            ->orWhere('closed', false);
                        })
                    ->first()){
                        if ($order->paid_at) {
                            return $fail('您已抢购该商品');
                        }
                        return $fail('该商品已在您的订单列表，请前往支付');
                    }
                }
            ]
        ];
    }
}
