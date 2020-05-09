<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Product;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class ProductsController extends CommonProductsController
{
    public function getProductType()
    {
        return Product::TYPE_NORMAL;
    }

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品';

    /**
     * @param Grid $grid
     * @return Grid|mixed
     */
    protected function customGrid(Grid $grid)
    {
        // 使用 with 来预加载商品类目数据，减少 SQL 查询
        $grid->model()->with(['category']);

        $grid->id('ID')->sortable();
        $grid->title('商品名称');
        $grid->column('category.name', '类目');
        $grid->on_sale('已上架')->display(function ($on_sale) {
            return $on_sale ? '是' : '否';
        });
        $grid->price('价格');
        $grid->rating('评分');
        $grid->sold_count('销量');
        $grid->review_count('评论数');


        return $grid;
    }

    /**
     * @param Form $form
     * @return Form|mixed
     */
    protected function customForm(Form $form)
    {

    }
}
