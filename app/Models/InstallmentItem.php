<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Moontoast\Math\BigNumber;
use function Sodium\add;

class InstallmentItem extends Model
{
    protected $fillable = [
        'sequence', 'base', 'fee', 'fine', 'due_date', 'paid_at', 'payment_method', 'payment_no', 'refund_status'
    ];

    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING    => '未退款',
        self::REFUND_STATUS_PROCESSING => '退款中',
        self::REFUND_STATUS_SUCCESS    => '退款成功',
        self::REFUND_STATUS_FAILED     => '退款失败'
    ];

    protected $dates = ['due_date', 'paid_at'];


    /**
     * Relation with the model of Installment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }


    /**
     * 创建一个访问器，返回当前还款计划需还款的总金额(当期本金+利息）
     *
     * @return string
     */
    public function getTotalAttribute()
    {
        $total = (big_number($this->base, 2))->add($this->fee);
        if (!is_null($this->fine)) {
            $total->add($this->fine);
        }

        return $total->getValue();
    }


    /**
     * 判断是否逾期
     *
     * @return bool
     */
    public function getIsOverdueAttribute()
    {
        return Carbon::now()->gt($this->due_date);
    }
}
