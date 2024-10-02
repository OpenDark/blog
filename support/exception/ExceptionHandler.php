<?php
namespace support\exception;

use Next\VarDumper\Dumper;
use Next\VarDumper\DumperHandler;
use support\exception\Handler;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;

class ExceptionHandler extends Handler
{

    use DumperHandler;

    public function render(Request $request, Throwable $exception): Response
    {
        if ($exception instanceof Dumper) {
            return \response(self::convertToHtml($exception));
        }
        return parent::render($request, $exception);
    }
}
