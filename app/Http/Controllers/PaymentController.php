<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * 支付宝支付
     *
     * @param Order $order
     * @return mixed
     * @throws InvalidRequestException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function payByAlipay(Order $order)
    {
        // 判断订单是否属于当前用户
        $this->authorize('own', $order);

        // 判断订单状态
        if ($order->closed || $order->paid_at) {
            throw new InvalidRequestException('订单状态不正确');
        }

        // 调用支付宝网页支付
        return app('alipay')->web([
            'out_trade_no' => $order->no, // 订单编号，需保证在商户端不重复
            'total_amount' => $order->total_amount, // 订单金额，单位元，支持小数点后两位
            'subject'      => '支付 ' . config('app.name') . ' 的订单：' . $order->no
        ]);
    }


    public function alipayReturn()
    {
        // 检验提交的参数是否合法
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);
    }


    public function alipayNotify()
    {
        // 校验输入参数
        $data = app('alipay')->verify();
        // 如果订单状态不是成功或者结束，则不走后续的逻辑
        // 所有交易状态：https://docs.open.alipay.com/59/103672
        if (!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }

        // $data->out_trade_no 拿到订单流水号，并在数据库中查询
        $order = Order::where('no', $data->out_trade_no)->first();
        // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
        if (!$order) {
            return 'fail';
        }

        // 如果这笔订单的状态已经是已支付
        if ($order->paid_at) {
            // 返回数据给支付宝
            return app('alipay')->success();
        }

        $order->update([
            'paid_at'        => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no'     => $data->trade_no, // 支付宝订单号
        ]);

        $this->afterPaid($order);

        return app('alipay')->success();
    }


    public function payByInstallment(Order $order, Request $request)
    {
        $this->authorize('own', $order);

        // 判断订单支付状态
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        // 判断订单是否满足分期要求
        if ($order->total_amount < config('app.min_intallment_amount')) {
            throw new InvalidRequestException('订单金额低于最低分期金额');
        }

        // validate验证
        $this->validate($request, [
            'count' => ['required', Rule::in(array_keys(config('app.installment_fee_rate')))],
        ]);

        // 删除同一笔商品订单发起过其他的未支付的分期付款，避免同一笔商品订单有多个分期付款
        Installment::query()
            ->where('order_id', $order->id)
            ->where('status', Installment::STATUS_PENDING)
            ->delete();

        $count = $request->input('count');

        // 创建一个新的分期付款对象
        $installment = new Installment([
            'total_amount' => $order->total_amount,
            'count'        => $count,
            'fee_rate'     => config('app.installment_fee_rate')[$count],
            'fine_rate'    => config('app.installment_fine_rate'),
        ]);

        $installment->user()->associate($request->user());
        $installment->order()->associate($order);
        $installment->save();

        // 第一期的还款截止日期为明天凌晨0点
        $due_date = Carbon::tomorrow();

        // 计算每一期的本金
        $base = big_number($order->total_amount)->divide($count)->getValue();

        // 计算每一期的手续费
        $fee = big_number($base)->multiply($installment->fee_rate)->divide(100)->getValue();

        // 根据用户选择的还款期数，创建还款计划
        for ($i = 1; $i <= $count; $i++) {
            // 最后一期的本金需要用总本金 减去 之前的本金
            if ($i === $count) {
                $base = big_number($order->total_amount)->subtract(big_number($base)->multiply($count-1));
            }

            $installment->installmentItems()->create([
                'sequence' => $i,
                'base' => $base,
                'fee' => $fee,
                'due_date' => $due_date
            ]);

            $due_date = $due_date->copy()->addMonth();
        }

        return $installment;
    }

    protected function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }


    public function wechatRefundNotify(Request $request)
    {
        // 给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        $data = app('wechat_pay')->verify(null, true);

        // 没有找到对应的订单，原则上不可能发生，保证代码健壮性
        if (!$order = Order::where('no', $data['out_trade_no'])->first()) {
            return $failXml;
        }

        if ($data['refund_status'] === 'SUCCESS') {
            // 退款成功，将订单退款状态改成退款成功
            $order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
            ]);
        } else {
            // 退款失败，将具体状态存入 extra 字段，并表退款状态改成失败
            $extra = $order->extra;
            $extra['refund_failed_code'] = $data['refund_status'];
            $order->update([
                'refund_status' => Order::REFUND_STATUS_FAILED,
                'extra'         => $extra
            ]);
        }

        return app('wechat_pay')->success();
    }
}
