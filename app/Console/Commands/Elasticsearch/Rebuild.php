<?php

namespace App\Console\Commands\Elasticsearch;

use Illuminate\Console\Command;

class Rebuild extends Command
{

    protected $signature = 'es:rebuild';
    protected $description = '重建索引结构并初始化数据';
    protected $es;

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
        $this->es = app('es');
        // 索引类数组，先留空
        $indices = [Indices\ProjectIndex::class];
        // 遍历索引类数组
        foreach ($indices as $indexClass) {
            // 调用类数组的 getAliasName() 方法来获取索引别名
            $aliasName = $indexClass::getAliasName();
            $this->info('正在重建索引 '.$aliasName);
            $this->reCreateIndex($aliasName, $indexClass);
            $this->info($aliasName.' 操作成功');
        }
    }


    // 重建索引
    protected function reCreateIndex($aliasName, $indexClass)
    {
        // 获取索引信息，返回结构的 key 为索引名称，value 为别名
        $indexInfo     = $this->es->indices()->getAliases(['index' => $aliasName]);
        // 取出第一个 key 即为索引名称
        $indexName = array_keys($indexInfo)[0];
        // 用正则判断索引名称是否以 _数字 结尾
        if (!preg_match('~_(\d+)$~', $indexName, $m)) {
            $msg = '索引名称不正确:'.$indexName;
            $this->error($msg);
            throw new \Exception($msg);
        }
        // 新的索引名称
        $newIndexName = $aliasName.'_'.($m[1] + 1);
        $this->info('正在创建索引'.$newIndexName);
        $this->es->indices()->create([
            'index' => $newIndexName,
            'body'  => [
                'settings' => $indexClass::getSettings(),
                'mappings' => [
                    '_doc' => [
                        'properties' => $indexClass::getProperties(),
                    ],
                ],
            ],
        ]);
        $this->info('创建成功，准备重建数据');
        $indexClass::rebuild($newIndexName);
        $this->info('重建成功，准备修改别名');
        $this->es->indices()->putAlias(['index' => $newIndexName, 'name' => $aliasName]);
        $this->info('修改成功，准备删除旧索引');
        $this->es->indices()->delete(['index' => $indexName]);
        $this->info('删除成功');
    }
}
