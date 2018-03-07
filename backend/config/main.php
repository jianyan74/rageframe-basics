<?php

$config = [
    'components' => [
        /** ------ 资源替换 ------ **/
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,
                    'js' => [
                        '/resource/backend/js/jquery-2.1.4.min.min.js',
                    ]
                ],
            ],
        ],
        /** ------ 后台操作日志 ------ **/
        'actionlog' => [
            'class' => 'jianyan\basics\common\models\sys\ActionLog',
        ],
        'qr' => [
            'class' => '\Da\QrCode\Component\QrCodeComponent',
            // ... you can configure more properties of the component here
        ]
    ],
    'modules' => [
        /* 系统模块 */
        'sys' => [
            'class' => 'jianyan\basics\backend\modules\sys\Module',
        ],
        /* 微信模块 */
        'wechat' => [
            'class' => 'jianyan\basics\backend\modules\wechat\Module',
        ],
    ],
];

return $config;
