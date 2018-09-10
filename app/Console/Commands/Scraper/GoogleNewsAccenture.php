<?php namespace App\Console\Commands\Scraper;
/**
 * @author: wanghui
 * @date: 2018/9/10 下午8:25
 * @email:    hank.HuiWang@gmail.com
 */

use Illuminate\Console\Command;
use QL\QueryList;

class GoogleNewsAccenture extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:google:news:accenture';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取google new的Accenture';
    /**
     * Create a new command instance.
     *
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
        $ql = QueryList::getInstance();
        $list = $ql->get('https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREZ5Y0RKakVnSmxiaWdBUAE',[],[
            'proxy' => 'socks5h://127.0.0.1:1080',
        ])->rules([
            'title' => ['span','text'],
            'link'  => ['a','href'],
            'description' => ['p','text']
        ])->range('div.ZulkBc.qNiaOd')->query()->getData();
        foreach ($list as &$item) {
            $item['href'] = $ql->get('https://news.google.com/'.$item['link'],[],[
                'proxy' => 'socks5h://127.0.0.1:1080',
            ])->find('div.m2L3rb.eLNT1d')->children('a')->attrs('href');
        }
        var_dump($list);
    }
}