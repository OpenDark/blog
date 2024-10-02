<?php
namespace support\exception;

use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;

class ApiException extends BusinessException
{
    public function render(Request $request): ?Response
    {
        $isJson = true;
        $msg = $this->getMessage();
        if (strpos($msg, '|array|') === 0) {
            $return = str_replace('|array|', '', $msg);
        } elseif (strpos($msg, '|encry|') === 0) {
            $isJson = false;
            $return = str_replace('|encry|', '', $msg);
        } else {
            $data = ['b' => -1, 't' => $this->getMessage()];
            $isEncrypt = !config('app.debug', true);
            $return = json_encode($data, 320);
            if ($isEncrypt) {
                $isJson = false;
                $return = response_encode($data);
            }
        }
        $response = response($return)->withHeaders(config('common.headers'));
        $isJson && $response->header('Content-Type', 'application/json');
        return $response;
    }
}
