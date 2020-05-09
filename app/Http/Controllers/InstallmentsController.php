<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use App\Models\InstallmentItem;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InstallmentsController extends Controller
{
    /**
     * 分期付款列表页
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(20);

        return view('installments.index', ['installments' => $installments]);
    }


    /**
     * 分期付款详情
     *
     * @param Installment $installment
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Installment $installment)
    {
        $this->authorize('own', $installment);

        $items = $installment->installmentItems()->orderBy('sequence')->get();

        return view('installments.show', [
            'items' => $items,
            'installment' => $installment,
            'nextItem' => $items->where('paid_at', null)->first()
        ]);
    }


    /**
     * 分期付款阿里支付
     *
     * @param Installment $installment
     * @return mixed
     * @throws InvalidRequestException
     */
    public function payByAlipay(Installment $installment)
    {
        if ($installment->order->closed) {
            throw new InvalidRequestException('该订单已被关闭');
        }

        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('该分期账单已结清');
        }

        // 获取当前付款计划中 最近的一个未付款的还款计划
        if (!$nextItem = $installment->installmentItems()->whereNull('paid_at')->orderBy('sequence')->first()) {
            // 如果没有未付款的还款，原则上不可能。因为如果分期结清，在上一个判断就退出了。
            throw new InvalidRequestException('该分期账单已结清');
        }

        return app('alipay')->web([
            // 支付订单号使用分期流水号+还款计划编号
            'out_trade_no' => $installment->no.'_'.$nextItem->sequence,
            'total_amount' => $nextItem->total,
            'subject'      => '支付'.config('app.name').'的分期订单：'.$installment->no,
            // 这里的 notify_url 和 return_url 可以覆盖掉在 AppServiceProvider 设置的回调地址
            'notify_url'   => ngrok_url('installments.alipay.notify'), // todo
            'return_url'   => route('installments.alipay.return') // todo,
        ]);
    }


    /**
     * 支付宝前端回调
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function alipayReturn()
    {
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);
    }


    /**
     * 支付宝后端回调
     *
     * @return string
     */
    public function alipayNotify()
    {
        // 校验支付宝回调参数验签
        $data = app('alipay')->verify();
        // 如果订单状态不是成功或者结束，则不走后续的逻辑
        if (!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }

        // 拉起支付时使用的支付订单号是由分期流水号 + 还款计划编号组成的
        // 因此可以通过支付订单号来还原出这笔还款是哪个分期付款的哪个还款计划
        list($no, $sequence) = explode('_', $data->out_trade_no);

        // 根据分期流水号查询对应的分期记录，原则上不会找不到，这里的判断只是增强代码健壮性
        if (!$installment = Installment::where('no', $no)->first()) {
            return 'fail';
        }

        // 根据还款计划编号查询对应的付款计划，原则上不会找不到，这里的判断只是增强代码健壮性
        if (!$item = $installment->installmentItems()->where('sequence', $sequence)->first()) {
            return 'fail';
        }

        // 如果这个还款计划的支付状态是已支付，则告知支付宝此订单已完成，并不再执行后续逻辑
        if ($item->paid_at) {
            return app('alipay')->success();
        }

        // 开启事务
        \DB::transaction(function () use($data, $no, $installment, $item) {
            // 更新对应的还款计划
            $item->update([
                'paid_at' => Carbon::now(),
                'payment_method' => 'alipay',
                'payment_no' => $data->trade_no
            ]);

            // 如果这是第一笔还款
            if ($item->sequence === 1) {
                // 将分期状态改为还款中
                $installment->update([
                    'status' => Installment::STATUS_REPAYING
                ]);
                // 将分期付款的对应商品订单改为已支付
                $installment->order->update([
                    'paid_at' => Carbon::now(),
                    'payment_method' => 'installment',
                    'payment_no' => $no
                ]);

                event(new OrderPaid($installment->order));
            }

            // 如果是最后一笔还款
            if ($item->sequence === $installment->count) {
                $installment->update([
                    'status' => Installment::STATUS_FINISHED
                ]);
            }
        });

        return app('alipay')->success();
    }


    public function wechatRefundNotify(Request $request)
    {
        // 给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        // 校验微信回调
        $data = app('wechat_pay')->verify(null, true);

        // 根据单号拆解出对应的商品退款单号和计划还款期数
        list($no, $sequence) = explode('_', $data['out_refund_no']);

        $item = InstallmentItem::query()
            ->whereHas('installment', function ($query) use($no) {
                $query->whereHas('order', function ($query) use($no) {
                    $query->where('refund_no', $no);    // 根据订单表的退款单号找到对应的还款计划
                });
            })
            ->where('sequence', $sequence)
            ->first();

        // 如果没有订单返回错误
        if (!$item) {
            return $failXml;
        }

        // 如果退款成功
        if ($data['refund_status'] === 'SUCCESS') {
            // 将还款计划退款状态改成退款成功
            $item->update([
                'refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS
            ]);

            $item->installment->refreshRefundStatus();
        }else{
            // 否则就将对应的该期退款状态改为退款失败
            $item->update([
                'refund_status' => InstallmentItem::REFUND_STATUS_FAILED
            ]);
        }

        return app('wechat_pay')->success();
    }
}
