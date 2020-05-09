<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Installment extends Model
{
    protected $fillable = [
        'no', 'total_amount', 'count', 'fee_rate', 'fine_rate', 'status'
    ];


    const STATUS_PENDING = 'pending';
    const STATUS_REPAYING = 'repaying';
    const STATUS_FINISHED = 'finished';

    public static $statusMap = [
        self::STATUS_PENDING  => '未执行',
        self::STATUS_REPAYING => '还款中',
        self::STATUS_FINISHED => '已完成'
    ];


    protected static function boot()
    {
        parent::boot();

        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            if (!$model->no) {
                $model->no = static::findAvailableNo();
                // 创建失败则终止创建订单
                if (!$model->no) {
                    return false;
                }
            }
        });
    }


    public static function findAvailableNo()
    {
        $prefix = date('YmdHis');

        for ($i = 0; $i < 10; $i++) {
            // 前缀+随机生成6位数
            $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断该单号是否已存在
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
        }

        \Log::warning(sprintf('find installment no failed'));

        return false;
    }


    /**
     * Relation with the model of User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Relation with the model of Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }


    /**
     * Relation with the model of InstallmentItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function installmentItems()
    {
        return $this->hasMany(InstallmentItem::class);
    }


    public function refreshRefundStatus()
    {
        $allSuccess = true;

        // 重新加载installmentItems，保证与数据库数据同步
        $this->load(['installmentItems']);

        foreach ($this->installmentItems as $item) {
            if ($item->paid_at && $item->refund_status !== InstallmentItem::REFUND_STATUS_SUCCESS) {
                $allSuccess = false;
                break;
            }
        }

        if ($allSuccess) {
            $this->order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS
            ]);
        }
    }
}
