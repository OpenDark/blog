<?php

use Bilulanlv\ThinkCache\facade\ThinkCache;

function formatNum($num): string
{
    if ($num > 9999) {
        $num = round($num / 10000, 1) . 'W';
    } elseif ($num > 999) {
        $num = round($num / 1000, 1) . 'K';
    }
    return strval($num);
}

/**
 * 生成账号密码
 */
function create_password($pwd): string
{
    return password_hash($pwd . config('common.authcode_key', 'blog'), PASSWORD_DEFAULT);
}

/**
 * 验证账号密码
 */
function check_password($pwd, $old): bool
{
    return password_verify($pwd . config('common.authcode_key', 'blog'), $old);
}

/**
 * IP归属地
 */
if (!function_exists('local')) {
    function local($ip = ''): array|string
    {
        return (new \extend\QQWry)->getDetail($ip);
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存管理
     * @param string|null $name 缓存名称
     * @param mixed $value 缓存值
     * @param mixed $options 缓存参数
     * @param mixed $tag 缓存标签
     * @return mixed
     */
    function cache(string|null $name = null, mixed $value = '', mixed $options = null, mixed $tag = null)
    {
        if (is_null($name)) {
            return ThinkCache::handler();
        }

        if ('' === $value) {
            // 获取缓存
            return str_starts_with($name, '?') ? ThinkCache::has(substr($name, 1)) : ThinkCache::get($name);
        } elseif (is_null($value)) {
            // 删除缓存
            return ThinkCache::delete($name);
        }

        // 缓存数据
        if (is_array($options)) {
            $expire = $options['expire'] ?? null; //修复查询缓存无法设置过期时间
        } else {
            $expire = $options;
        }

        if (is_null($tag)) {
            return ThinkCache::set($name, $value, $expire);
        } else {
            return ThinkCache::tag($tag)->set($name, $value, $expire);
        }
    }
}

/**
 * 记录日志
 */
if (!function_exists('logger')) {
    function logger($str, $data = [], $suffix = '', $warp = 0)
    {
        $date = date('Y-m-d');
        $log = str_replace(["\n", "\r"], '', $str);
        !empty($data) && $log .= ': ' . json_encode($data, 320);
        $filename = $suffix ? $date . '-' . $suffix : $date;
        $prefix = $warp ? PHP_EOL : '';
        $path = runtime_path('logs') . DIRECTORY_SEPARATOR;
        !is_dir($path) && mkdir($path, 0777, true);
        error_log($prefix . '[' . date('Y-m-d H:i:s', time()) . '] ' . $log . PHP_EOL, 3, $path . $filename . '.log');
    }
}

if (!function_exists('logs')) {
    function logs($msg, $extend = [], $level = 'info', $channel = 'logger')
    {
        $requestParams = [];
        $line = "\n---------------------------------------------------------------\n";
        if (empty(request())) {
            $logInfo = $line;
        } else {
            $method = request()->method();
            $requestInfo = [
                'ip' => request()->getRealIp(),
                'method' => $method,
                'uri' => ltrim(request()->fullUrl(), '/')
            ];
            $logInfo = implode(' ', $requestInfo) . $line;
            $method = strtolower($method);
            $requestParams = in_array($method, ['get', 'post']) ? request()->{$method}() : request()->all();
        }

        $logInfo .= var_export([
            'msg' => $msg,
            'extend' => $extend,
            'params' => $requestParams
        ], true);
        $logLevelStatus = [
            'debug' => 100,
            'info' => 200,
            'warning' => 300,
            'error' => 400,
            'critical' => 500,
            'alert' => 550,
            'emergency' => 600
        ];
        \support\Log::channel($channel)->addRecord($logLevelStatus[$level], $logInfo);
    }
}

if (!function_exists('bcmath')) {
    function bcmath($left = 0, $symbol = '+', $right = 0, $default = 2): float|bool
    {
        $left = strval(floatval($left));
        $right = strval(floatval($right));
        bcscale($default);
        switch ($symbol) {
            case '+':
                $res = bcadd($left, $right);
                break;
            case '-':
                $res = bcsub($left, $right);
                break;
            case '*':
                $res = bcmul($left, $right);
                break;
            case '/':
                $res = '0' === $right ? 0 : bcdiv($left, $right);
                break;
            case '%':
                $res = '0' === $right ? 0 : bcmod($left, $right);
                break;
            case '>':
                $res = bccomp($left, $right);
                return $res > 0;
                break;
            default:
                $res = 0;
                break;
        }
        return floatval($res);
    }
}

