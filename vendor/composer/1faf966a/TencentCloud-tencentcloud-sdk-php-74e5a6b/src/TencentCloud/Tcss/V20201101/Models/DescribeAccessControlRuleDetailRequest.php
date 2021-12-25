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
namespace TencentCloud\Tcss\V20201101\Models;
use TencentCloud\Common\AbstractModel;

/**
 * DescribeAccessControlRuleDetail请求参数结构体
 *
 * @method string getRuleId() 获取策略唯一id
 * @method void setRuleId(string $RuleId) 设置策略唯一id
 * @method string getImageId() 获取镜像id, 仅仅在事件加白的时候使用
 * @method void setImageId(string $ImageId) 设置镜像id, 仅仅在事件加白的时候使用
 */
class DescribeAccessControlRuleDetailRequest extends AbstractModel
{
    /**
     * @var string 策略唯一id
     */
    public $RuleId;

    /**
     * @var string 镜像id, 仅仅在事件加白的时候使用
     */
    public $ImageId;

    /**
     * @param string $RuleId 策略唯一id
     * @param string $ImageId 镜像id, 仅仅在事件加白的时候使用
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
        if (array_key_exists("RuleId",$param) and $param["RuleId"] !== null) {
            $this->RuleId = $param["RuleId"];
        }

        if (array_key_exists("ImageId",$param) and $param["ImageId"] !== null) {
            $this->ImageId = $param["ImageId"];
        }
    }
}
