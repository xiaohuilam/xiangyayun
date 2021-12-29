<?php

namespace App\Controller\User;


use App\Controller\Common\UserLoginBase;
use App\Service\QrcodeService;
use App\Service\RechargeService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Finance extends UserLoginBase
{

    /**
     * @Param(name="type",required="",inArray=["wechat","alipay_pc","alipay_h5"])
     * @Param(name="amount",integer="")
     * @Param(name="qrcode",inArray=[1,0])
     */
    public function recharge()
    {
        //充值金额 充值方式
        $type = $this->GetParam('type');
        $amount = $this->GetParam('amount');
        if ($amount < 1) {
            return $this->Error('充值金额不能小于1元');
        }
        $qrcode = $this->GetParam('qrcode');
        $user_id = $this->GetUserId();
        $ip = $this->GetClientIP();
        $url = RechargeService::Pay($type, $amount, $user_id, $ip);
        if (!$qrcode) {
            $data['url'] = $url;
            return $this->Success('获取充值链接成功!', $data);
        }
        $data['image'] = QrcodeService::Qrcode($url);
        return $this->Success('获取支付二维码成功', $data);
    }

    public function recharge_log()
    {
        $user_id = $this->GetUserId();
        $page = $this->GetParam('page') ?? 1;
        $size = $this->GetParam('size') ?? 15;
        $model = RechargeService::SelectRechargeLog($user_id, $page, $size);
        // 列表数据
        $data['list'] = $model->all(null);
        $result = $model->lastQueryResult();
        // 总条数
        $data['total'] = $result->getTotalCount();
        return $this->Success('获取充值记录成功', $data);
    }

    public function finance_log()
    {

        $user_id = $this->GetUserId();
        $page = $this->GetParam('page') ?? 1;
        $size = $this->GetParam('size') ?? 15;
        $model = RechargeService::SelectFinanceLog($user_id, $page, $size);
        // 列表数据
        $data['list'] = $model->all(null);
        $result = $model->lastQueryResult();
        // 总条数
        $data['total'] = $result->getTotalCount();
        return $this->Success('获取收支记录成功', $data);
    }
}