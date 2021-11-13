<?php
/*
 * Copyright (c) 2017-2018 THL A29 Limited, a Tencent company. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace TencentCloud\Iotvideo\V20191126\Models;
use TencentCloud\Common\AbstractModel;

/**
 * DescribeStorageService请求参数结构体
 *
 * @method string getServiceId() 获取云存服务ID
 * @method void setServiceId(string $ServiceId) 设置云存服务ID
 * @method boolean getGetFinishedOrder() 获取是否返回已结束的订单信息(已过期/已退订/已转移)
 * @method void setGetFinishedOrder(boolean $GetFinishedOrder) 设置是否返回已结束的订单信息(已过期/已退订/已转移)
 */
class DescribeStorageServiceRequest extends AbstractModel
{
    /**
     * @var string 云存服务ID
     */
    public $ServiceId;

    /**
     * @var boolean 是否返回已结束的订单信息(已过期/已退订/已转移)
     */
    public $GetFinishedOrder;

    /**
     * @param string $ServiceId 云存服务ID
     * @param boolean $GetFinishedOrder 是否返回已结束的订单信息(已过期/已退订/已转移)
     */
    function __construct()
    {

    }

    /**
     * For internal only. DO NOT USE IT.
     */
    public function deserialize($param)
    {
        if ($param === null) {
            return;
        }
        if (array_key_exists("ServiceId",$param) and $param["ServiceId"] !== null) {
            $this->ServiceId = $param["ServiceId"];
        }

        if (array_key_exists("GetFinishedOrder",$param) and $param["GetFinishedOrder"] !== null) {
            $this->GetFinishedOrder = $param["GetFinishedOrder"];
        }
    }
}
