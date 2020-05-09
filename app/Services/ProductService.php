<?php


namespace App\Services;


use App\Models\Product;
use App\SearchBuilders\ProductSearchBuilder;

class ProductService
{
    public function getSimilarProducts(Product $product, $amount)
    {
        // 如果商品没有商品属性 则直接返回空数组
        if (count($product->properties) === 0) {
            return [];
        }

        // 创建一个查询构造器，只搜索上架的商品，取搜索结果的前 4 个商品
        $esBuilder = (new ProductSearchBuilder())->onSale()->paginate($amount, 1);
        // 遍历当前商品的属性
        foreach ($product->properties as $property) {
            // 添加到 should 条件中
            $esBuilder->propertyFilter($property->name, $property->value, 'should');
        }
        // 设置最少匹配一般的属性.   ceil 向上取整
        $esBuilder->minShouldMatch(ceil(count($product->properties) / 2));
        $params = $esBuilder->getParams();
        // 同时将当前商品的ID排除
        $params['body']['query']['bool']['must_not'] = [['term' => ['_id' => $product->id]]];
        // 搜索
        $result = app('es')->search($params);

        return collect($result['hits']['hits'])->pluck('_id')->all();
    }
}
