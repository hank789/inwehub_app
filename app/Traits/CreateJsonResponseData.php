<?php

namespace App\Traits;

use App\Exceptions\ApiException;

trait CreateJsonResponseData
{
    public static function createJsonData(bool $success,$code = ApiException::SUCCESS,$message='ok',array $data=[])
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
