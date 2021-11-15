<?php

namespace App\Service;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;


class QrcodeService
{
    public static function Qrcode($content)
    {
        $options = new QROptions([
            'version' => QRCode::VERSION_AUTO,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L, //ECC_L, ECC_M, ECC_Q, ECC_H  容错率 ECC-Level: 7%, 15%, 25%, 30% ，即二维码损坏 % 多少时仍然可以识别，损坏越多识别越慢
            'scale' => 6,
            'imageBase64' => false,//是否返回 base64
        ]);


        $qrcode = new QRCode($options);
        return $qrcode->render($content);
    }

}