<?php

namespace App\Controller\Api;

use App\Controller\Common\Base;
use App\Service\UserLogService;
use App\Service\QrcodeService;
use App\Service\RedisService;
use App\Service\UcsService;
use App\Service\WechatService;
use EasySwoole\VerifyCode\Conf;

class Ucs extends Base
{
    public function region(){
        $region = UcsService::SelectRegion();
        return $this->Success('1',$region);
    }
    public function plan(){
        $ucs_region_id=$this->GetParam('ucs_region_id');
        $plan = UcsService::SelectPlanByUcsRegionId($ucs_region_id);
        return $this->Success('1',$plan);
    }
}