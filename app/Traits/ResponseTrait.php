<?php
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait ResponseTrait {

    /**
     * @param array $data
     * @param string $message
     * @param int $code
     * @return Response
     */
    public function customResponse($data, $message = '', int $code = 200 ) {
        $res['data'] = $data;
        $res['message'] = $message;
        return response()->json($res, $code);
    }
}
