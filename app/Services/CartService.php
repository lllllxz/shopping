<?php

namespace App\Services;


use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function get()
    {
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }


    public function add($sku_id, $amount)
    {
        $user = Auth::user();

        if ($item = $user->cartItems()->where('product_sku_id', $sku_id)->first()) {
            // 如果存在则直接叠加商品数量
            $item->update([
                'amount' => $item->amount + $amount,
            ]);
        } else {
            // 否则创建一个新的购物车记录
            $item = new CartItem(['amount' => $amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($sku_id);
            $item->save();
        }

        return $item;
    }


    public function remove($sku_ids)
    {
        // 可以传单个ID 也可以穿ID数组
        if (!is_array($sku_ids)) {
            $sku_ids = [$sku_ids];
        }

        Auth::user()->cartItems()->whereIn('product_sku_id', $sku_ids)->delete();
    }
}
