<?php

namespace App\Traits;

use App\Exceptions\ApiException;

trait CreateJsonResponseData
{

    public static $needRefresh = false;

    public static function createJsonData(bool $success,$data=[],$code = ApiException::SUCCESS,$message='ok')
    {
        $data = [
            'status'  => $success,
            'needRefresh' => self::$needRefresh,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ];

        return response()->json($data);
    }

}
