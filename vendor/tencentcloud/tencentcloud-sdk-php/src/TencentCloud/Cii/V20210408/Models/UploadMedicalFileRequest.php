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
namespace TencentCloud\Cii\V20210408\Models;
use TencentCloud\Common\AbstractModel;

/**
 * UploadMedicalFile请求参数结构体
 *
 * @method string getFile() 获取文件内容的base64的值。FileBase64与FileURL有一个不为空即可，若FileURL同时存在，那么取FileBase64。
 * @method void setFile(string $File) 设置文件内容的base64的值。FileBase64与FileURL有一个不为空即可，若FileURL同时存在，那么取FileBase64。
 * @method string getFileURL() 获取文件的URL地址。FileBase64与FileURL有一个不为空即可，若FileBase64同时存在，那么取FileBase64。
 * @method void setFileURL(string $FileURL) 设置文件的URL地址。FileBase64与FileURL有一个不为空即可，若FileBase64同时存在，那么取FileBase64。
 */
class UploadMedicalFileRequest extends AbstractModel
{
    /**
     * @var string 文件内容的base64的值。FileBase64与FileURL有一个不为空即可，若FileURL同时存在，那么取FileBase64。
     */
    public $File;

    /**
     * @var string 文件的URL地址。FileBase64与FileURL有一个不为空即可，若FileBase64同时存在，那么取FileBase64。
     */
    public $FileURL;

    /**
     * @param string $File 文件内容的base64的值。FileBase64与FileURL有一个不为空即可，若FileURL同时存在，那么取FileBase64。
     * @param string $FileURL 文件的URL地址。FileBase64与FileURL有一个不为空即可，若FileBase64同时存在，那么取FileBase64。
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
        if (array_key_exists("File",$param) and $param["File"] !== null) {
            $this->File = $param["File"];
        }

        if (array_key_exists("FileURL",$param) and $param["FileURL"] !== null) {
            $this->FileURL = $param["FileURL"];
        }
    }
}
