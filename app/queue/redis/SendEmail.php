<?php

namespace app\queue\redis;

use extend\Email;
use Webman\RedisQueue\Consumer;

class SendEmail implements Consumer
{
    // 要消费的队列名
    public string $queue = 'send-email';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public string $connection = 'default';

    /**
     * 消费
     * email 接收邮箱
     * title  邮件标题
     * body  邮件内容
     */
    public function consume($data)
    {
        $result = (new Email())->send($data['email'], $data['title'], $data['body']);
        logger('邮件发送结果', $result, $data['title']);
    }

    // 消费失败回调
    /*
    $package = [
        'id' => 1357277951, // 消息ID
        'time' => 1709170510, // 消息时间
        'delay' => 0, // 延迟时间
        'attempts' => 2, // 消费次数
        'queue' => 'send-mail', // 队列名
        'data' => ['to' => 'tom@gmail.com', 'content' => 'hello'], // 消息内容
        'max_attempts' => 5, // 最大重试次数
        'error' => '错误信息' // 错误信息
    ]
    */
    public function onConsumeFailure(\Throwable $e, $package)
    {
        logger($this->queue . '队列消费失败回调：' . $e->getMessage(), $package);
    }

}
