<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\CrowdfundingProduct;
use App\Models\Product;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CrowdfundingProductsController extends CommonProductsController
{
    public function getProductType()
    {
        return Product::TYPE_CROWDFUNDING;
    }

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '众筹商品';

    /**
     * @param Grid $grid
     * @return Grid|mixed
     */
    protected function customGrid(Grid $grid)
    {
        $grid->model()->with(['category']);

        $grid->column('id', 'ID')->sortable();
        $grid->column('title', '商品名称');
        $grid->column('on_sale', '已上架')->display(function ($boolean) {
            return $boolean ? '是' : '否';
        });
        $grid->column('price', '价格');
        $grid->column('crowdfunding.target_amount', '目标金额');
        $grid->column('crowdfunding.end_at', '结束时间');
        $grid->column('crowdfunding.total_amount', '目前金额');
        $grid->column('crowdfunding.status', '状态')->display(function ($status) {
            return CrowdfundingProduct::$statusMap[$status];
        });

    }


    /**
     * @param Form $form
     * @return Form|mixed
     */
    protected function customForm(Form $form)
    {
        // 添加众筹字段
        $form->decimal('crowdfunding.target_amount', '众筹目标金额')->rules('required|numeric|min:0.01');
        $form->datetime('crowdfunding.end_at', '众筹结束时间')->rules('required|date');
    }
}
