<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// 'a' 缺勤
// 'l' 迟到
// 'p' 到场

//'lal';
//
//'alpla';
//function test($string){
//    $hasLLL = substr_count($string, 'lll');
//    $hasA = substr_count($string, 'a');
//
//    if ($hasLLL && $hasA >= 2) {
//        return '扣钱';
//    }
//}



// 秒杀
Route::post('seckill-order', 'OrdersController@seckill')->name('seckill_order.store');
// 首页
//Route::get('/', function () {
//    $nums = [-1,0,3,5,9,12];
//    $target = 9;
//    $count = count($nums);
//    for($i=0; $i<$count; $i++) {
//        dump($nums[$i]);
//        if($target == $nums[$i]) {
//            return $i;
//        }
//    }
//    return -1;
//});
//Route::get('/a', function () {
//    $words = ["cat","bt","hat","tree"];
//    $chars = 'atach';
//
//        $charLen = strlen($chars);
//        $hashChars = [];
//
//        for($i = 0; $i < $charLen; $i++) {
//            $hashChars[$chars[$i]] = ($hashChars[$chars[$i]] ?? 0) + 1;
//        }
//
//        $wCount = count($words);
//        $count = 0;
//
//        for ($i = 0; $i < $wCount; $i++) {
//            $wLen = strlen($words[$i]);
//            $flag = 1;
//            $tempHash = $hashChars;
//
//            for($j = 0; $j < $wLen; $j++) {
//                if (isset($tempHash[$words[$i][$j]]) && $tempHash[$words[$i][$j]] > 0) {
//                    $tempHash[$words[$i][$j]]--;
//                } else {
//                    $flag = 0;
//                    break;
//                }
//            }
//
//            if ($flag == 1) {
//                $count += $wLen;
//            }
//        }
//        return $count;
//
//});
Route::redirect('/', '/products')->name('root');

Auth::routes(['verify' => true]);

// auth 中间件代表需要登录，verified中间件代表需要经过邮箱验证
Route::group(['middleware' => ['auth', 'verified']], function () {
    // 用户地址
    Route::get('user-addresses', 'UserAddressesController@index')->name('user_addresses.index');
    Route::get('user-addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
    Route::post('user-addresses', 'UserAddressesController@store')->name('user_addresses.store');
    Route::get('user-addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
    Route::put('user-addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
    Route::delete('user-addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');

    // 收藏商品
    Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
    Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
    Route::get('products/favorites', 'ProductsController@favorites')->name('products.favorites');

    // 购物车
    Route::post('cart', 'CartController@add')->name('cart.add');
    Route::get('cart', 'CartController@index')->name('cart.index');
    Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');

    // 订单
    Route::post('orders', 'OrdersController@store')->name('orders.store');
    Route::get('orders', 'OrdersController@index')->name('orders.index');
    Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');
    Route::post('orders/{order}/received', 'OrdersController@received')->name('orders.received');
    Route::get('orders/{order}/review', 'OrdersController@review')->name('orders.review.show');
    Route::post('orders/{order}/review', 'OrdersController@sendReview')->name('orders.review.store');
    Route::post('orders/{order}/apply_refund', 'OrdersController@applyRefund')->name('orders.apply_refund');

    // 订单-众筹
    Route::post('crowdfunding-orders', 'OrdersController@crowdfunding')->name('orders.crowdfunding');

    // 支付
    Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
    Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
    Route::post('payment/{order}/installment', 'PaymentController@payByInstallment')->name('payment.installment');

    // 优惠券
    Route::get('coupon_codes/{code}', 'CouponCodesController@show')->name('coupon_codes.show');

    // 分期
    Route::get('installments', 'InstallmentsController@index')->name('installments.index');
    Route::get('installments/{installment}', 'InstallmentsController@show')->name('installments.show');
    // 分期支付
    Route::get('installments/{installment}/alipay', 'InstallmentsController@payByAlipay')->name('installments.alipay');
    Route::get('installments/alipay/return', 'InstallmentsController@alipayReturn')->name('installments.alipay.return');

});

Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');
Route::post('payment/wechat/refund_notify', 'PaymentController@wechatRefundNotify')->name('payment.wechat.refund_notify');

// 分期付款-alipay后端回调不能放在 auth 中间件中
Route::post('installments/alipay/notify', 'InstallmentsController@alipayNotify')->name('installments.alipay.notify');

// 分期付款-WeChatPay退款后端回调
Route::post('installments/wechat/refund_notify', 'InstallmentsController@wechatRefundNotify')->name('installments.wechat.refund_notify');

Route::get('products', 'ProductsController@index')->name('products.index');
Route::get('products/{product}', 'ProductsController@show')->name('products.show');
