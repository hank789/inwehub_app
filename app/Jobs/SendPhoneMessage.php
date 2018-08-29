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
    protected $params;

    /**
     * SendPhoneMessage constructor.
     * @param $phone
     * @param $params
     * @param string $type
     */
    public function __construct($phone,array $params,$type='register')
    {
        $app = new App(config('alidayu'));
        $this->alidayu = new Client($app);
        $this->phone = $phone;
        $this->params = $params;
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
            case 'login':
            case 'register':
                $templateId = config('alidayu.verify_template_id');
                //$params = ['code' => $code]
                break;
            case '201802-happy-activity':
                $templateId = 'SMS_124425049';
                //$params = ['name' => $code]
                break;
            case 'invite_address_book_user':
                $templateId = 'SMS_134500006';
                break;
            case 'article_pending_alert':
                $templateId = 'SMS_143705956';
                break;
            default:
                $templateId = config('alidayu.verify_template_id');
                break;
        }

        $request = new AlibabaAliqinFcSmsNumSend();
        $request->setSmsParam($this->params)
            ->setSmsFreeSignName($freeSignName)
            ->setSmsTemplateCode($templateId)
            ->setRecNum($this->phone);

        $response = $this->alidayu->execute($request);

        $result = isset($response->result) ? $response->result : null;
        $sub_code = isset($response->sub_code) ? $response->sub_code : null;
        $sub_msg = isset($response->sub_msg) ? $response->sub_msg : null;

        if ($result && $result->success == true) {
            // 发送成功～
        }else{
            Log::error('短信验证码发送失败',[$this->type,$result, $sub_code, $sub_msg]);
        }
    }

    public static function getCacheKey($type,$phone){
        return 'sendPhoneCode:'.$type.':'.$phone;
    }
}
