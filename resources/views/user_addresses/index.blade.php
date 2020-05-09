@extends('layouts.app')

@section('title', '收货地址列表')

@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card panel-default">
                <div class="card-header">收货地址列表
                    <a class="float-right" href="{{ route('user_addresses.create') }}">添加收货地址</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>收货人</th>
                            <th>地址</th>
                            <th>邮编</th>
                            <th>电话</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($addresses as $address)
                            <tr>
                                <td>{{ $address->contact_name }}</td>
                                <td>{{ $address->full_address }}</td>
                                <td>{{ $address->zip }}</td>
                                <td>{{ $address->contact_tel }}</td>
                                <td>
                                    <a class="btn btn-primary" href="{{ route('user_addresses.edit', ['user_address' => $address->id]) }}">修改</a>
                                    <button class="btn btn-danger btn-del-address" type="button" data-id="{{ $address->id }}">删除</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptAfterJs')
<script>
    $(".btn-del-address").click(function () {
        var id = $(this).data('id')

        swal({
            title: '确定删除该地址吗？',
            text: '删除地址后无法恢复哦',
            icon: 'warning',
            buttons: ['取消', '确定'],
            dangerMode: true,
        })
            .then(function (willDelete) {
                if (!willDelete) {
                    return
                }

                axios.delete('/user-addresses/' + id)
                    .then(function () {
                        location.reload()
                    })
            })
    })
</script>
@stop
