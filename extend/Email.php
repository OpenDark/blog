<?php

namespace extend;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * 发送邮件
 */
class Email
{
    /**
     * 邮箱配置
     */
    private $config;

    public function __construct()
    {
        $this->config = config('common.mail_config', []);
        if (empty($this->config)) {
            return ['code' => 10001, 'msg' => '发件邮箱未配置'];
        }
    }

    public function send($tomail, $title = '邮件标题', $content = '邮件内容', $toname = ''): array
    {
        if (empty($tomail)) {
            return ['code' => 10002, 'msg' => '未发现收件人邮箱'];
        }
        // 实例化PHPMailer核心类
        $mail = new PHPMailer();
        // 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        // 设置错误中文提示
        $mail->setLanguage('zh_cn');
        // 使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        // smtp需要鉴权 这个必须是true
        $mail->SMTPAuth = true;
        // 链接qq域名邮箱的服务器地址
        $mail->Host = $this->config['smtp'];
        // 设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';
        // 设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
        $mail->Port = $this->config['port'];
        // 设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
        $mail->Hostname = config('common.domain', 'System');
        // 设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
        $mail->CharSet = 'UTF-8';
        // smtp登录的账号
        $mail->Username = $this->config['user'];
        // smtp登录的密码 使用生成的授权码 你的最新的授权码 注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码！
        $mail->Password = $this->config['pwd'];
        // 设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
        // $mail->From = $this->config['user'];
        // 设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
        // $mail->FromName = $this->config['name'];
        // 设置发件人信息，如邮件格式说明中的发件人,
        $mail->setFrom($this->config['user'], $this->config['name']);
        // 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
        $mail->addReplyTo($this->config['user'], $this->config['name']);
        // 邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
        $mail->isHTML(true);
        // 设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
        $mail->addAddress($tomail, $toname);
        // 添加多个收件人 则多次调用方法即可
        // $mail->addAddress('xxx@xxx.com','测试');
        // 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
        // $mail->addCC("xxx@163.com");
        // 设置秘密抄送人(这个人也能收到邮件)
        // $mail->addBCC("xxx@163.com");
        // 添加该邮件的主题
        $mail->Subject = $title;
        // 添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
        $mail->Body = $content;
        // 为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
        //  $mail->addAttachment('./d.jpg','mm.jpg');
        // 同样该方法可以多次调用 上传多个附件
        //  $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');
        $status = $mail->send();
        // 简单的判断与提示信息
        if ($status) {
            return ['code' => 0, 'msg' => '发送成功'];
        } else {
            return ['code' => 10000, 'msg' => $mail->ErrorInfo];
        }
    }

}
