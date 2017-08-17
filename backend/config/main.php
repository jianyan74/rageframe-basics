<?php

$config = [
    'modules'              => [
        /* 系统 modules */
        'sys' => [
            'class' => 'jianyan\basics\backend\modules\sys\index',
        ],
        /* 微信 modules */
        'wechat' => [
            'class' => 'jianyan\basics\backend\modules\wechat\index',
        ],
    ],
    'components' => [

    ],
];

return $config;
