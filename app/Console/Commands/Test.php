<?php

namespace App\Console\Commands;

use App\Events\Frontend\System\Push;
use App\Models\Answer;
use App\Models\Authentication;
use App\Models\Question;
use App\Models\User;
use App\Models\UserDevice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Getui;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $devices = UserDevice::where('user_id',2)->get();

        $data = [
            'title' => 'hello',
            'body'  => 'body:nihao',
            'content' => '{payload:"通知去干嘛这里可以自定义"}',
            'text'=>'text:这是内容',
            'payload' => '{title:"title",content:"content",payload:"ppppp"}'
        ];
        event(new Push(User::find(2),'有人向您发起了回答邀请',
            'content:问题内容,有人向您发起了回答邀请,有人向您发起了回答邀请,有人向您发起了回答邀请',['payload'=>['object_type'=>'question','object_id'=>123]],[],1));
    }
}
