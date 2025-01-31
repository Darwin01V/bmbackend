<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function response($message , $code, $error = false,$data = null){
        return response()->json([
            'error' => $error,
            'message' => $message,
            'code' => $code,
            'data' => $data
        ], $code);
    }
}
