<?php

namespace App\Service;

use App\Model\EmailTemplate;

class EmailService
{

    public function __construct()
    {
    }

    public static function GetMailClient($email)
    {
        $config = config('EMAIL');
        $mail = new \EasySwoole\Smtp\Mailer(false);
        $mail->setTimeout(5);
        $mail->setMaxPackage(1024 * 1024 * 2);
        $mail->setHost($config['HOST']);
        $mail->setPort($config['PORT']);
        $mail->setSsl($config['SSL']);
        $mail->setUsername($config['USERNAME']);
        $mail->setPassword($config['PASSWORD']);
        $mail->setFrom($config['FROM']);
        return $mail;
    }

    public static function SendEmailJob()
    {

    }

    //上层必须判断是否有邮件
    public static function SendEmail($email, $action, $params)
    {
        $template = EmailTemplate::create()->get(['action' => $action]);
        $html = $template->body;
        $title = $template->title;
        //递归参数 进行替换
        foreach ($params as $key => $value) {
            $title = str_replace("{" . $key . "}", $value, $title);
            $html = str_replace("{" . $key . "}", $value, $html);
        }
        $email = self::GetMailClient($email);
        $html = new \EasySwoole\Smtp\Request\Html();
        $html->setSubject($title);
        $html->setBody($html);
        $email->send($html);
    }
}