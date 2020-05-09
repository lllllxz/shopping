<?php


namespace App\Admin\Controllers;


use App\Models\Product;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\Redis;

class SeckillProductsController extends CommonProductsController
{
    protected $title = '秒杀商品';

    public function getProductType()
    {
        return Product::TYPE_SECKILL;
    }

    protected function customGrid(Grid $grid)
    {
        $grid->column('id', 'ID');
        $grid->column('title', '商品名称');
        $grid->column('on_sale', '已上架')->display(function ($bool) {
            return $bool ? '是' : '否';
        });
        $grid->column('price', '价格');
        $grid->column('seckill.start_at', '开始时间');
        $grid->column('seckill.end_at', '结束时间');
        $grid->column('sold_count', '销量');
    }

    protected function customForm(Form $form)
    {
        $form->datetime('seckill.start_at', '秒杀开始时间')->rules('required|date');
        $form->datetime('seckill.end_at', '秒杀结束时间')->rules('required|date');

        // 当商品表单保存完毕时触发
        $form->saved(function (Form $form) {
            $product = $form->model();
            // 商品重新加载秒杀和sku字段
            $product->load(['seckill', 'skus']);
            // 获取当前时间与秒杀结束时间的差值
            $diffTime = $product->seckill->end_at->getTimestamp() - time();
            // 遍历商品sku
            $product->skus->each(function ($sku) use($diffTime, $product) {
                // 如果秒杀商品是上架且并未到结束时间
                if ($product->on_sale && $diffTime > 0) {
                    // 将剩余库存写入到 Redis 中, 并设置该值过期时间为秒杀截止时间
                    Redis::setex('seckill_sku_'.$sku->id, $diffTime, $sku->stock);
                } else {
                    // 否则将值从 Redis 中删除
                    Redis::del('seckill_sku_'.$sku->id);
                }
            });
        });
    }
}
