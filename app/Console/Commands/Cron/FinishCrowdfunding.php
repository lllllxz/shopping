<?php

namespace App\Console\Commands\Cron;

use App\Jobs\RefundCrowdfundingOrders;
use App\Models\CrowdfundingProduct;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FinishCrowdfunding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:finish-crowdfunding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '结束众筹';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        CrowdfundingProduct::query()
            ->where('end_at', '<=', Carbon::now())
            ->where('status', CrowdfundingProduct::STATUS_FUNDING)
            ->get()
            ->each(function (CrowdfundingProduct $crowdfunding) {
                if ($crowdfunding->target_amount > $crowdfunding->total_amount) {
                    $this->crowdfundingFail($crowdfunding);
                } else {
                    $this->crowdfundingSuccess($crowdfunding);
                }
            });
    }


    /**
     * 众筹成功
     *
     * @param CrowdfundingProduct $crowdfundingProduct
     */
    protected function crowdfundingSuccess(CrowdfundingProduct $crowdfundingProduct)
    {
        $crowdfundingProduct->update([
            'status' => CrowdfundingProduct::STATUS_SUCCESS
        ]);
    }


    /**
     * 众筹失败
     *
     * @param CrowdfundingProduct $crowdfundingProduct
     */
    protected function crowdfundingFail(CrowdfundingProduct $crowdfundingProduct)
    {
        // 将众筹商品改为众筹失败
        $crowdfundingProduct->update([
            'status' => CrowdfundingProduct::STATUS_FAIL
        ]);

        dispatch(new RefundCrowdfundingOrders($crowdfundingProduct));
    }
}
