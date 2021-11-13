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
namespace TencentCloud\Cwp\V20180228\Models;
use TencentCloud\Common\AbstractModel;

/**
 * 主机安全-漏洞管理-漏洞列表
 *
 * @method string getIds() 获取漏洞包含的事件id串，多个用“,”分割
 * @method void setIds(string $Ids) 设置漏洞包含的事件id串，多个用“,”分割
 * @method string getName() 获取漏洞名
 * @method void setName(string $Name) 设置漏洞名
 * @method integer getStatus() 获取0: 待处理 1:忽略  3:已修复  5:检测中 6:修复中 控制台仅处理0,1,3,5,6四种状态
 * @method void setStatus(integer $Status) 设置0: 待处理 1:忽略  3:已修复  5:检测中 6:修复中 控制台仅处理0,1,3,5,6四种状态
 * @method integer getVulId() 获取漏洞id
 * @method void setVulId(integer $VulId) 设置漏洞id
 * @method string getPublishTime() 获取漏洞披露事件
 * @method void setPublishTime(string $PublishTime) 设置漏洞披露事件
 * @method string getLastTime() 获取最后检测时间
 * @method void setLastTime(string $LastTime) 设置最后检测时间
 * @method integer getHostCount() 获取影响主机数
 * @method void setHostCount(integer $HostCount) 设置影响主机数
 * @method integer getLevel() 获取漏洞等级 1:低 2:中 3:高 4:提示
 * @method void setLevel(integer $Level) 设置漏洞等级 1:低 2:中 3:高 4:提示
 * @method integer getFrom() 获取废弃字段
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setFrom(integer $From) 设置废弃字段
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getDescript() 获取描述
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setDescript(string $Descript) 设置描述
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getPublishTimeWisteria() 获取废弃字段
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setPublishTimeWisteria(string $PublishTimeWisteria) 设置废弃字段
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getNameWisteria() 获取废弃字段
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setNameWisteria(string $NameWisteria) 设置废弃字段
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getDescriptWisteria() 获取废弃字段
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setDescriptWisteria(string $DescriptWisteria) 设置废弃字段
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getStatusStr() 获取聚合后事件状态串
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setStatusStr(string $StatusStr) 设置聚合后事件状态串
注意：此字段可能返回 null，表示取不到有效值。
 */
class VulInfoList extends AbstractModel
{
    /**
     * @var string 漏洞包含的事件id串，多个用“,”分割
     */
    public $Ids;

    /**
     * @var string 漏洞名
     */
    public $Name;

    /**
     * @var integer 0: 待处理 1:忽略  3:已修复  5:检测中 6:修复中 控制台仅处理0,1,3,5,6四种状态
     */
    public $Status;

    /**
     * @var integer 漏洞id
     */
    public $VulId;

    /**
     * @var string 漏洞披露事件
     */
    public $PublishTime;

    /**
     * @var string 最后检测时间
     */
    public $LastTime;

    /**
     * @var integer 影响主机数
     */
    public $HostCount;

    /**
     * @var integer 漏洞等级 1:低 2:中 3:高 4:提示
     */
    public $Level;

    /**
     * @var integer 废弃字段
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $From;

    /**
     * @var string 描述
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Descript;

    /**
     * @var string 废弃字段
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $PublishTimeWisteria;

    /**
     * @var string 废弃字段
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $NameWisteria;

    /**
     * @var string 废弃字段
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $DescriptWisteria;

    /**
     * @var string 聚合后事件状态串
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $StatusStr;

    /**
     * @param string $Ids 漏洞包含的事件id串，多个用“,”分割
     * @param string $Name 漏洞名
     * @param integer $Status 0: 待处理 1:忽略  3:已修复  5:检测中 6:修复中 控制台仅处理0,1,3,5,6四种状态
     * @param integer $VulId 漏洞id
     * @param string $PublishTime 漏洞披露事件
     * @param string $LastTime 最后检测时间
     * @param integer $HostCount 影响主机数
     * @param integer $Level 漏洞等级 1:低 2:中 3:高 4:提示
     * @param integer $From 废弃字段
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $Descript 描述
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $PublishTimeWisteria 废弃字段
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $NameWisteria 废弃字段
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $DescriptWisteria 废弃字段
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $StatusStr 聚合后事件状态串
注意：此字段可能返回 null，表示取不到有效值。
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
        if (array_key_exists("Ids",$param) and $param["Ids"] !== null) {
            $this->Ids = $param["Ids"];
        }

        if (array_key_exists("Name",$param) and $param["Name"] !== null) {
            $this->Name = $param["Name"];
        }

        if (array_key_exists("Status",$param) and $param["Status"] !== null) {
            $this->Status = $param["Status"];
        }

        if (array_key_exists("VulId",$param) and $param["VulId"] !== null) {
            $this->VulId = $param["VulId"];
        }

        if (array_key_exists("PublishTime",$param) and $param["PublishTime"] !== null) {
            $this->PublishTime = $param["PublishTime"];
        }

        if (array_key_exists("LastTime",$param) and $param["LastTime"] !== null) {
            $this->LastTime = $param["LastTime"];
        }

        if (array_key_exists("HostCount",$param) and $param["HostCount"] !== null) {
            $this->HostCount = $param["HostCount"];
        }

        if (array_key_exists("Level",$param) and $param["Level"] !== null) {
            $this->Level = $param["Level"];
        }

        if (array_key_exists("From",$param) and $param["From"] !== null) {
            $this->From = $param["From"];
        }

        if (array_key_exists("Descript",$param) and $param["Descript"] !== null) {
            $this->Descript = $param["Descript"];
        }

        if (array_key_exists("PublishTimeWisteria",$param) and $param["PublishTimeWisteria"] !== null) {
            $this->PublishTimeWisteria = $param["PublishTimeWisteria"];
        }

        if (array_key_exists("NameWisteria",$param) and $param["NameWisteria"] !== null) {
            $this->NameWisteria = $param["NameWisteria"];
        }

        if (array_key_exists("DescriptWisteria",$param) and $param["DescriptWisteria"] !== null) {
            $this->DescriptWisteria = $param["DescriptWisteria"];
        }

        if (array_key_exists("StatusStr",$param) and $param["StatusStr"] !== null) {
            $this->StatusStr = $param["StatusStr"];
        }
    }
}
