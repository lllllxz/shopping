<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Product extends Model
{
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL = 'seckill';

    public static $typeMap = [
        self::TYPE_NORMAL       => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品',
        self::TYPE_SECKILL      => '秒杀商品'
    ];

    protected $fillable = [
        'title',
        'long_title',
        'description',
        'price',
        'image',
        'on_sale',
        'rating',
        'sold_count',
        'review_count',
        'category_id',
        'type'
    ];

    protected $casts = [
        'on_sale' => 'boolean'
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    public function getImageUrlAttribute()
    {
        if (Str::startsWith($this->attributes['image'], ['https://', 'http://'])) {
            return $this->attributes['image'];
        }

        return \Storage::disk('public')->url($this->attributes['image']);
    }


    /**
     * Relation with the model of category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    /**
     * Relation with the model of CrowdfundingProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function crowdfunding()
    {
        return $this->hasOne(CrowdfundingProduct::class);
    }


    /**
     * Relation with the model of properties
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function properties()
    {
        return $this->hasMany(ProductProperty::class);
    }


    /**
     * Relation with the model of SeckillProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seckill()
    {
        return $this->hasOne(SeckillProducts::class);
    }


    /**
     * 返回更新到ElasticSearch的数据
     *
     * @return array
     */
    public function toESArray()
    {
        $arr = Arr::only($this->toArray(), [
            'id',
            'title',
            'long_title',
            'category_id',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price'
        ]);

        // 如果有商品类目，则返回商品类目的数组
        $arr['category'] = $this->category ? explode('-', $this->category->full_name) : '';
        // 类目的path
        $arr['category_path'] = $this->category ? $this->category->path : '';
        // strip_tags 可以将html标签去除
        $arr['description'] = strip_tags($this->description, '<img>');
        // 只取出需要的sku字段
        $arr['skus'] = $this->skus->map(function ($sku) {
            return Arr::only($sku->toArray(), ['title', 'description', 'price']);
        });
        // 取出properties
        $arr['properties'] = $this->properties->map(function ($item) {
            // 对应地增加一个 search_value 字段，用符号 : 将属性名和属性值拼接起来
            return array_merge(Arr::only($item->toArray(), ['name', 'value']), [
                'search_value' => $item->name . ':' . $item->value,
            ]);
        });

        return $arr;
    }


    public function scopeByIds($query, $ids)
    {
        return $query->whereIn('id', $ids)->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $ids)));
    }
}
