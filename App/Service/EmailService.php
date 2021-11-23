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
        $mail = new \EasySwoole\Smtp\Mailer(true);
        $mail->setTimeout(5);
        $mail->setMaxPackage(1024 * 1024 * 2);
        $mail->setHost($config['HOST']);
        $mail->setPort($config['PORT']);
        $mail->setSsl($config['SSL']);
        $mail->setUsername($config['USERNAME']);
        $mail->setPassword($config['PASSWORD']);
        $mail->setFrom($config['FROM']);;
        $mail->addAddress($email);
        return $mail;
    }

    public static function SendEmailJob($email, $action, $params)
    {
        EmailJob([
            'email' => $email,
            'action' => $action,
            'params' => $params,
        ]);
    }

    //上层必须判断是否有邮件
    public static function SendEmail($email, $action, $params)
    {
        try {
            $template = EmailTemplate::create()->get(['action' => $action]);
            $body = $template->body;
            $title = $template->title;
            //递归参数 进行替换
            foreach ($params as $key => $value) {
                $title = str_replace("{" . $key . "}", $value, $title);
                $body = str_replace("{" . $key . "}", $value, $body);
            }
            $email = self::GetMailClient($email);
            $html = new \EasySwoole\Smtp\Request\Html();
            $html->setSubject($title);
            $html->setBody($body);
            $html->setContentTransferEncoding("UTF-8");
            $email->send($html);
        } catch (\EasySwoole\Smtp\Exception\Exception $exception) {
            error("邮件CODE:" . $exception->getCode());
        }
    }
}