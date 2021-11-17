<?php


namespace App\Controller\Common;

use App\Languages\Dictionary;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\Exception\Annotation\ParamValidateError;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Jwt\Jwt;

class Base extends AnnotationController
{

    const  SecretKey = 'upu.cn';
    protected $data = [];

    protected function GetParam($key)
    {
        return $this->request()->getRequestParam($key);
    }

    protected function SetUserId($user_id)
    {
        $this->Set('user_id', $user_id);
    }

    protected function actionNotFound(?string $action)
    {
        $d['code'] = 404;
        $d['message'] = $action . "Not Found";
        $this->JsonWrite($d);
    }

    protected function GetClientIP()
    {
        $server_params = $this->request()->getServerParams();
        if (!config('is_cdn')) {
            return $server_params['remote_addr'];
        } else {
            $this->request()->getHeaderLine(config('real_ip_header'));
        }
    }

    protected function GetUserAgent()
    {
        return $this->request()->getHeaderLine('user-agent');
    }

    private function Guester()
    {
        $server_params = $this->request()->getServerParams();
        $user_agent = $this->request()->getHeaderLine('user-agent');
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($user_agent, $server_params) {
            //$data=$this->request()->getUri();
            $data['remote_addr'] = $server_params['remote_addr'];
            $data['request_uri'] = $server_params['request_uri'];
            $data['request_time'] = date('Y-m-d H:i:s', $server_params['master_time']);
            $data['user-agent'] = $user_agent;
            $redis->lPush('Guester', json_encode($data, true));
        });
        if ($user_agent) {//拦截user-agent

        }
        return true;
    }

    protected function onRequest(?string $action): ?bool
    {
        //var_dump($data = $this->request());
        //判断是否是浏览器或者是否攻击
        $flag = $this->Guester();
        if ($flag) {
            return parent::onRequest($action); // TODO: Change the autogenerated stub
        }
        return false;
    }

    protected function onException(\Throwable $throwable): void
    {
        if ($throwable instanceof ParamValidateError) {
            $this->Validate($throwable);
        } else {
            Trigger::getInstance()->throwable($throwable);
        }
    }

    protected function Validate($throwable)
    {
        $d['code'] = 2;
        $d['message'] = $throwable->getMessage();
        if ($throwable) {
            $d['data'] = [
                'field' => $throwable->getValidate()->getError()->getField(),
                'error' => $throwable->getValidate()->getError()->getErrorRuleMsg(),
            ];
        }
        $this->JsonWrite($d);
    }


    public function Permission()
    {
        $d['code'] = 403;//200 400/tip 401/Auth Expire 402 / quanxian /500
        $d['message'] = 'No Permission';
        $d['token'] = $this->token;
        $this->JsonWrite($d);
    }

    protected function Error($message = null, $data = null, $redirect = null)
    {
        $d['code'] = 3;
        $d['message'] = $message ?? 'Error';
        if ($data) {
            $d['data'] = $data;
        }
        if ($redirect) {
            $d['redirect'] = $redirect;
        }

        $this->JsonWrite($d);
        return false;
    }

    protected function Success($message = null, $data = null, $redirect = null)
    {
        $d['code'] = 1;
        $d['message'] = $message ?? 'Success';
        if ($data) {
            $d['data'] = $data;
        }
        if ($redirect) {
            $d['redirect'] = $redirect;
        }

        $this->JsonWrite($d);
    }

    protected function JsonWrite($data)
    {
        $this->SetData();
        $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
        $this->response()->withStatus(200);
    }

    protected function ImageWrite($byte)
    {
        $this->SetData();
        $this->response()->withStatus(200);
        $this->response()->withHeader('Content-Type', 'image/png');
        $this->response()->write($byte);
    }

    protected function TextWrite($string)
    {
        $this->SetData();
        $this->response()->withStatus(200);
        $this->response()->withHeader('Content-Type', 'text/plain');
        $this->response()->write($string);
    }

    protected function Get($key)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    protected function Set($key, $value)
    {
        $this->data[$key] = $value;
    }

    protected function GetData()
    {
        try {
            $token = $this->request()->getCookieParams('token');
            if (!$token) {
                return null;
            }
            $jwtObject = Jwt::getInstance()->setSecretKey(self::SecretKey)->decode($token);
            $status = $jwtObject->getStatus();
            switch ($status) {
                case  1:
                    Logger::getInstance()->info('验证通过');
                    $this->data = $jwtObject->getData();
                    break;
                case  -1:
                    Logger::getInstance()->info('无效');
                    break;
                case  -2:
                    Logger::getInstance()->info('TOKEN过期');
                    break;
            }
        } catch (\EasySwoole\Jwt\Exception $e) {
            Logger::getInstance()->error($e->getMessage());
        }
        echo(json_encode($this->data, true) . "\n");
    }

    protected function SetData()
    {
        $jwtObject = Jwt::getInstance()
            ->setSecretKey(self::SecretKey) // 秘钥
            ->publish();
        $jwtObject->setAlg('HMACSHA256'); // 加密方式
        $jwtObject->setExp(time() + 86400); // 过期时间
        $jwtObject->setIat(time()); // 发布时间
        $jwtObject->setJti(md5(time())); // jwt id 用于标识该jwt

        // 自定义数据
        info("SetData:" . json_encode($this->data, true));

        $jwtObject->setData($this->data);
        $token = $jwtObject->__toString();
        $this->response()->setCookie('token', $token);
        $this->token = $token;
    }
}