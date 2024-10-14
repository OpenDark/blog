<?php

namespace app\controller;

use think\facade\Db;
use support\Request;
use support\Response;
use Tinywan\Validate\Validate;
use support\exception\ApiException;
use Tinywan\Validate\Exception\ValidateException;

class BaseController
{
    /**
     * Request实例
     */
    protected Request|null $request;

    /**
     * 是否批量验证
     */
    protected bool $batchValidate = false;

    /**
     * 是否需要登录
     */
    protected bool|array $needLogin = false;

    /**
     * 是否不需要验证
     */
    protected array $notNeedVerify = [];

    /**
     * POST方法
     */
    protected array $postAction = [];

    /**
     * 当前域名
     */
    protected string $domain;

    public function __construct()
    {
        $this->request = request();
        $this->domain = config('common.domain') . '/';
        $this->initialize();
    }

    /**
     * 初始化
     * @throws ApiException
     */
    protected function initialize()
    {
        // 过滤非POST方法
        $action = $this->request->action;
        if (in_array($action, $this->postAction) && $this->request->method() !== 'POST') {
            throw new ApiException('非法请求');
        }

        // 未登录则跳转登录页面
        $userInfo = session('userInfo') ?: [];
        if (($this->needLogin === true || (is_array($this->needLogin) && in_array($action, $this->needLogin)))
            && empty($userInfo)) {
            throw new ApiException('请登录后操作', 403);
        }

        // 验证数据
        if (!in_array($action, $this->notNeedVerify)) {
            $param = $this->request->all();
            $controller = str_replace(["app\\controller\\", 'Controller'], '', $this->request->controller);
            try {
                $this->validate($param, $controller . '.' . $action);
            } catch (ValidateException $e) {
                throw new ApiException($e->getMessage());
            }
        }

        // 用户信息
        $this->request->userInfo = $userInfo;
    }

    /**
     * 成功返回json内容
     * @param array|string $data
     * @param string $msg
     * @throws ApiException
     */
    public function success(array|string $data = [], string $msg = 'success')
    {
        if (is_string($data)) {
            $msg = $data;
            $data = [];
        }
        $data = ['code' => 200, 'msg' => $msg, 'data' => $data];
        throw new ApiException(json_encode($data, 320));
    }

    /**
     * 失败返回json内容
     * @param string $msg
     * @throws ApiException
     */
    public function failure(string $msg = 'failure')
    {
        $data = ['code' => 400, 'msg' => $msg];
        throw new ApiException(json_encode($data, 320));
    }

    /**
     * 验证数据
     * @param array $data 数据
     * @param array|string $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return bool
     * @throws ApiException
     */
    protected function validate(array $data, array|string $validate, array $message = [], bool $batch = false): bool
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = "\app\\validate\\" . $validate;
            // 未定义验证器
            if (!class_exists($class)) {
                $this->failure('系统错误：文件缺失');
            }
            $v = new $class();
            if (!empty($scene)) {
                if (property_exists($v, 'customer')) {
                    $v->setRule($scene);
                }
                $v->scene($scene);
            }
        }

        !empty($message) && $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 事务处理
     * @param string $action
     * @param callable $callback
     * @param bool $return
     * @return array
     * @throws ApiException
     */
    protected function transaction(string $action, callable $callback, bool $return = false): array
    {
        $data = [];
        $code = 0;
        Db::startTrans();
        try {
            if (is_callable($callback)) {
                $data = $callback();
            }
            Db::commit();
            $msg = "{$action}成功";
        } catch (\Exception $e) {
            Db::rollback();
            $msg = "系统错误：{$action}失败" . mt_rand(10000, 99999);
            $this->logger($msg, $this->param ?? [], $this->auth ?? [], $e, 'error');
            $code = 1;
        }
        if ($return) {
            $arr = ['code' => $code, 'msg' => $msg];
            $data && is_array($data) && $arr['data'] = $data;
            return $arr;
        } else {
            if ($code) {
                $this->failure($msg);
            } elseif ($data && is_array($data)) {
                empty($data['t']) && $data['t'] = $msg;
                $this->success($data);
            } else {
                $this->success($msg);
            }
        }
        return [];
    }

    /**
     * 记录日志
     */
    protected function logger($message = '', $param = [], $auth = [], $error = null, $suffix = '')
    {
        if (!empty($message) || !empty($param) || !empty($auth)) {
            $param = !empty($param) ? ' 请求参数：' . json_encode($param, 320) : '';
            $auth = !empty($auth) ? ' 登录信息：' . json_encode($auth, 320) : '';
            $errorMsg = '';
            if ($error) {
                $arr = [
                    $error->getMessage(),
                    $error->getFile(),
                    $error->getLine()
                ];
                $errorMsg = ' 异常信息：' . implode(' ', $arr);
            }
            logger($message . $param . $auth . $errorMsg, [], $suffix);
        }
    }

    public function upload(): Response
    {
        $file = $this->request->file('file');
        if ($file && $file->isValid()) {
            $name = 'test.' . $file->getUploadExtension();
            $path = public_path('upload') . DIRECTORY_SEPARATOR . $name;
            $image = config('common.domain') . '/upload/' . $name;
            $file->move($path);
            return json([
                'code' => 0,
                'msg' => 'Upload Success',
                'id' => 1,
                'data' => $image
            ]);
        }
        return json(['code' => 1, 'msg' => 'File Not Found']);
    }

}
