<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('users', 'UsersController@index')->name('admin.users.index');
    $router->get('products', 'ProductsController@index')->name('admin.products.index');
    $router->get('products/create', 'ProductsController@create')->name('admin.products.create');
    $router->post('products', 'ProductsController@store')->name('admin.products.store');
    $router->get('products/{product}/edit', 'ProductsController@edit')->name('admin.products.edit');
    $router->put('products/{product}', 'ProductsController@update')->name('admin.products.update');

    // 订单
    $router->get('orders', 'OrderController@index')->name('admin.orders.index');
    $router->get('orders/{order}', 'OrderController@show')->name('admin.orders.show');
    $router->post('orders/{order}/ship', 'OrderController@ship')->name('admin.orders.ship');
    $router->post('orders/{order}/refund', 'OrderController@handleRefund')->name('admin.orders.handle_refund');

    // 优惠券
    $router->get('coupon-codes', 'CouponCodeController@index')->name('admin.coupon_codes.index');
    $router->get('coupon-codes/create', 'CouponCodeController@create')->name('admin.coupon_codes.create');
    $router->post('coupon-codes', 'CouponCodeController@store')->name('admin.coupon_codes.store');
    $router->get('coupon-codes/{id}/edit', 'CouponCodeController@edit')->name('admin.coupon_codes.edit');
    $router->put('coupon-codes/{id}', 'CouponCodeController@update')->name('admin.coupon_codes.update');

    // 商品类目
    $router->get('categories', 'CategoriesController@index');
    $router->get('categories/create', 'CategoriesController@create');
    $router->get('categories/{id}/edit', 'CategoriesController@edit');
    $router->post('categories', 'CategoriesController@store');
    $router->put('categories/{id}', 'CategoriesController@update');
    $router->delete('categories/{id}', 'CategoriesController@destroy');
    $router->get('api/categories', 'CategoriesController@apiIndex');

    // 众筹商品
    $router->get('crowdfunding-products', 'CrowdfundingProductsController@index');
    $router->get('crowdfunding-products/create', 'CrowdfundingProductsController@create');
    $router->post('crowdfunding-products', 'CrowdfundingProductsController@store');
    $router->get('crowdfunding-products/{id}/edit', 'CrowdfundingProductsController@edit');
    $router->put('crowdfunding-products/{id}', 'CrowdfundingProductsController@update');

    // 秒杀商品
    $router->get('seckill-products', 'SeckillProductsController@index');
    $router->get('seckill-products/create', 'SeckillProductsController@create');
    $router->post('seckill-products', 'SeckillProductsController@store');
    $router->get('seckill-products/{id}/edit', 'SeckillProductsController@edit');
    $router->put('seckill-products/{id}', 'SeckillProductsController@update');
});
