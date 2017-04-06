<?php

namespace App\Traits;

trait CreateJsonResponseData
{
    protected static function createJsonData(bool $success,$code,$message,array $data)
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
