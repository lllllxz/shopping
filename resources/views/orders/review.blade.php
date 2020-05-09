@extends('layouts.app')

@section('title', '评价')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">
                    商品评价
                    <a href="{{ route('orders.index') }}" class="float-right">返回订单列表</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('orders.review.store', ['order' => $order->id]) }}" method="post">
                        {{ csrf_field() }}
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>商品名称</td>
                                    <td>打分</td>
                                    <td>评价</td>
                                </tr>
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td class="product-info">
                                            <div class="preview">
                                                <a href="{{ route('products.show', ['product' => $item->product_id]) }}" target="_blank">
                                                    <img src="{{ $item->product->image_url }}" alt="">
                                                </a>
                                            </div>
                                            <div>
                                                <span class="product-title">
                                                    <a href="{{ route('products.show', ['product' => $item->product_id]) }}" target="_blank">
                                                        {{ $item->product->title }}
                                                    </a>
                                                </span>
                                                <span class="sku-title">
                                                    {{ $item->productSku->title }}
                                                </span>
                                            </div>
                                            <input type="hidden" name="reviews[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                        </td>
                                        <td class="vertical-middle">
                                            <!-- 如果订单已经评价 则显示评价分 -->
                                            @if($order->reviewed)
                                                <span class="rating-star-yes">{{ str_repeat('★', $item->rating) }}</span><span class="rating-star-no">{{ str_repeat('★', 5 - $item->rating) }}</span>
                                            @else
                                                <ul class="rate-area">
                                                    <input type="radio" id="5-star-{{$loop->index}}" name="reviews[{{$loop->index}}][rating]" value="5" checked /><label for="5-star-{{$loop->index}}"></label>
                                                    <input type="radio" id="4-star-{{$loop->index}}" name="reviews[{{$loop->index}}][rating]" value="4" /><label for="4-star-{{$loop->index}}"></label>
                                                    <input type="radio" id="3-star-{{$loop->index}}" name="reviews[{{$loop->index}}][rating]" value="3" /><label for="3-star-{{$loop->index}}"></label>
                                                    <input type="radio" id="2-star-{{$loop->index}}" name="reviews[{{$loop->index}}][rating]" value="2" /><label for="2-star-{{$loop->index}}"></label>
                                                    <input type="radio" id="1-star-{{$loop->index}}" name="reviews[{{$loop->index}}][rating]" value="1" /><label for="1-star-{{$loop->index}}"></label>
                                                </ul>
                                            @endif
                                        </td>
                                        <td>
                                            @if($order->reviewed)
                                                {{ $item->review }}
                                            @else
                                                <textarea class="form-control {{ $errors->has('reviews.'.$loop->index.'.review') ? 'is-invalid' : '' }}" name="reviews[{{$loop->index}}][review]"></textarea>
                                                @if($errors->has('reviews.'.$loop->index.'.review'))
                                                    @foreach($errors->get('reviews.'.$loop->index.'.review') as $msg)
                                                        <span class="invalid-feedback" role="alert"><strong>{{ $msg }}</strong></span>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        @if(!$order->reviewed)
                                            <button type="submit" class="btn btn-primary center-block">提交</button>
                                        @else
                                            <a href="{{ route('orders.show', [$order->id]) }}" class="btn btn-primary">查看订单</a>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
