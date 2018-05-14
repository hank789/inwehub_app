<?php

namespace App\Traits;

use App\Exceptions\ApiException;

trait CreateJsonResponseData
{
    public static function createJsonData(bool $success,array $data=[],$code = ApiException::SUCCESS,$message='ok',$refresh=false)
    {
        $data = [
            'status'  => $success,
            'needRefresh' => $refresh,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ];

        return response()->json($data);
    }
}
