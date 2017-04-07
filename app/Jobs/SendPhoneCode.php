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


class SendPhoneCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $alidayu;

    public function __construct($phone,$code)
    {
        $app = new App(config('alidayu'));
        $this->alidayu = new Client($app);
        $this->phone = $phone;
        $this->code = $code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $freeSignName = config('alidayu.sign_name');
        $verifyTemplateId = config('alidayu.verify_template_id');

        $request = new AlibabaAliqinFcSmsNumSend();
        $request->setSmsParam([
            'code' => $this->code,
        ])
            ->setSmsFreeSignName($freeSignName)
            ->setSmsTemplateCode($verifyTemplateId)
            ->setRecNum($this->phone);

        $response = $this->alidayu->execute($request);

        $result = isset($response->result) ? $response->result : null;
        $sub_code = isset($response->sub_code) ? $response->sub_code : null;
        $sub_msg = isset($response->sub_msg) ? $response->sub_msg : null;

        if ($result && $result->success == true) {
            // 发送成功～
        }else{
            Log::error('短信验证码发送失败',[$result, $sub_code, $sub_msg]);
        }
    }
}
