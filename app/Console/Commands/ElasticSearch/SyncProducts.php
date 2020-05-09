<?php

namespace App\Console\Commands\ElasticSearch;

use App\Models\Product;
use Illuminate\Console\Command;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // 添加一个名为 index，默认值为 products 的参数
    protected $signature = 'es:sync-products {--index=products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步产品数据到elasticsearch';

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
        $es = app('es');

        Product::query()
            ->with(['skus', 'properties'])
            ->chunkById(1000, function ($products) use ($es) {
                $this->info(sprintf('正在同步ID范围为 %s 到 %s 的商品', $products->first()->id, $products->last()->id));

                // 初始化请求体
                $req = ['body' => []];

                // 遍历商品
                foreach ($products as $product) {
                    $data = $product->toESArray();

                    $req['body'][] = [
                        'index' => [
                            '_index' => $this->option('index'),
                            '_id'    => $data['id'],
                        ],
                    ];
                    $req['body'][] = $data;
                }

                try {
                    // 使用bulk方法批量创建
                    $es->bulk($req);
                }catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            });

        $this->info('同步完成');
    }
}
