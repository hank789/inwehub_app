<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Flc\Alidayu\App;
use Flc\Alidayu\Client;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class SendPhoneMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $alidayu;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    protected $type;
    protected $phone;
    protected $code;

    /**
     * SendPhoneMessage constructor.
     * @param $phone
     * @param $code
     * @param string $type
     */
    public function __construct($phone,$code,$type='register')
    {
        $app = new App(config('alidayu'));
        $this->alidayu = new Client($app);
        $this->phone = $phone;
        $this->code = $code;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $freeSignName = config('alidayu.sign_name');

        switch($this->type){
            case 'register':
                $templateId = config('alidayu.verify_template_id');
                $params = ['code' => $this->code];
                break;
            default:
                $templateId = config('alidayu.verify_template_id');
                $params = ['code' => $this->code];
                break;
        }

        $request = new AlibabaAliqinFcSmsNumSend();
        $request->setSmsParam($params)
            ->setSmsFreeSignName($freeSignName)
            ->setSmsTemplateCode($templateId)
            ->setRecNum($this->phone);

        $response = $this->alidayu->execute($request);

        $result = isset($response->result) ? $response->result : null;
        $sub_code = isset($response->sub_code) ? $response->sub_code : null;
        $sub_msg = isset($response->sub_msg) ? $response->sub_msg : null;

        Cache::put(self::getCacheKey($this->type,$this->phone), $this->code, 600);

        if ($result && $result->success == true) {
            // 发送成功～
        }else{
            Log::error('短信验证码发送失败',[$result, $sub_code, $sub_msg]);
        }
    }

    public static function getCacheKey($type,$phone){
        return 'sendPhoneCode:'.$type.':'.$phone;
    }
}
