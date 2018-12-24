<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */
use App\Logic\TagsLogic;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use Illuminate\Console\Command;

class ProductAlbum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:product-album';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化产品专辑';

    protected $ql;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

    }

}