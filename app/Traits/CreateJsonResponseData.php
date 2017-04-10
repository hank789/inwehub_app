<?php

namespace App\Traits;

use App\Exceptions\ApiException;

trait CreateJsonResponseData
{
    public static function createJsonData(bool $success,array $data=[],$code = ApiException::SUCCESS,$message='ok')
    {
        $data = [
            'status'  => $success,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ];

        return response()->json($data);
    }
}
