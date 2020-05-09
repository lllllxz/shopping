<?php

namespace App\Admin\Controllers;


use App\Jobs\SyncOneProductToES;
use App\Models\Category;
use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;

abstract class CommonProductsController extends AdminController
{
    // 定义一个抽象方法，返回当前管理的商品类型
    abstract public function getProductType();

    /**
     * 列表页
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->model()->where('type', $this->getProductType())->orderBy('id', 'desc');
        $this->customGrid($grid);

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * 定义一个抽象方法，各个类型的控制器将实现本方法来定义列表应该展示哪些字段
     *
     * @param Grid $grid
     * @return mixed
     */
    abstract protected function customGrid(Grid $grid);


    /**
     * 新增&编辑页
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        // 在表单页面中添加一个名为 type 的隐藏字段，值为当前商品类型
        $form->hidden('type')->value($this->getProductType());
        $form->text('title', '商品名称')->rules('required');
        // 放在商品名称后面
        $form->text('long_title', '商品长标题')->rules('required');
        $form->multipleSelect('category_id', '商品类目')->options(function ($id) {
            $category = Category::find($id);
            if ($category) {
                return [$category->id => $category->name];
            }
        })->ajax('/admin/api/categories?is_directory=0');
        $form->quill('description', '商品描述');
        $form->image('image', '封面图片')->rules('required|image');
        $form->radio('on_sale', '是否上架')->options([1 => '是', 0 => '否'])->default(0);

        $this->customForm($form);

        $form->hasMany('skus', '商品 SKU', function (Form\NestedForm $form) {
            $form->text('title', 'SKU 名称')->rules('required');
            $form->text('description', 'SKU 描述')->rules('required');
            $form->decimal('price', '单价')->rules('required|numeric|min:0.01');
            $form->number('stock', '剩余库存')->rules('required|integer|min:0');
        });

        $form->hasMany('properties', '商品属性', function (Form\NestedForm $form) {
            $form->text('name', '属性名')->rules('required');
            $form->text('value', '属性值')->rules('required');
        });

        $form->saving(function (Form $form) {
            $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price');
        });

        $form->saved(function (Form $form) {
            $product = $form->model();

            dispatch(new SyncOneProductToES($product));
        });

        return $form;
    }


    /**
     * 表单抽象方法
     *
     * @return mixed
     */
    abstract protected function customForm(Form $form);

}
