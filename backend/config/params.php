<?php
return [
    /**-------------------开发者信息-------------------**/
    'exploitEmail'             => '751393839@qq.com',
    'exploitName'              => '简言',
    'exploitVersions'          => '1.1.21',
    'exploitSysName'           => 'RageFrame应用开发引擎',
    'exploitOfficialWebsite'   => '<a href="http://www.rageframe.com" target="_blank">www.rageframe.com</a>',
    'exploitGitHub'            => '<a href="https://github.com/jianyan74/rageframe.git" target="_blank">github.com/jianyan74/rageframe.git</a>',
    'exploitGit@OSC'           => '<a href="https://git.oschina.net/jianyan94/rageframe.git" target="_blank">git.oschina.net/jianyan94/rageframe.git</a>',

    /**-------------------后台网站基础配置-------------------**/
    'siteTitle'              => "RageFrame应用开发引擎",//后台系统名称
    'abbreviation'           => "让开发变得更简单！",//缩写
    'acronym'                => "RF",//拼音缩写

    /**-------------------配置管理类型-------------------**/
    'configTypeList'       => [
        '1'   => "文本框",
        '2'   => "密码框",
        '3'   => "密钥文本框",
        '4'   => "文本域",
        '5'   => "下拉文本框",
        '6'   => "单选按钮",
        '7'   => "百度编辑器",
        '8'   => "图片上传",
        '9'   => "多图上传",
    ],

    /**-------------------模块类别-------------------**/
    'addonsType'  => [
        'plug-in'   => [
            'name'  => "plug-in",
            'title' => "插件",
            'child' => [
                'plug'      => "功能插件",
            ],
        ],
        'addon'  => [
            'name'  => "addon",
            'title' => "模块",
            'child' => [
                'business'  => "主要业务",
                'customer'  => "客户关系",
                'activity'  => "营销及活动",
                'services'  => "常用服务及工具",
                'biz'       => "行业解决方案",
                'h5game'    => "H5游戏",
                'other'     => "其他",
            ],
        ],
    ],

    /**-------------------禁止删除的后台菜单id-------------------**/
    'noDeleteMenu' => [65,108],

    /**-------------------微信配置-------------------**/
    //素材类型
    'wechatMediaType' => [
        'news'  => '微信图文',
        'image' => '图片',
//        'voice' => '语音',
//        'video' => '视频',
    ],

    //微信级别
    'wechatLevel' => [
        '1' => '普通订阅号',
        '2' => '普通服务号',
        '3' => '认证订阅号',
        '4' => '认证服务号/认证媒体/政府订阅号',
    ],

    //不需要验证的路由全称
    'basicsNoAuthRoute' => [
        'main/index',//系统主页
        'main/system',//系统首页
        'sys/system/index',//系统入口
        'sys/addons/execute',//模块插件渲染
        'sys/addons/centre',//模块插件基础设置渲染
        'sys/addons/qr',//模块插件二维码渲染
        'sys/addons/cover',//模块插件导航
        'sys/addons/binding',//模块插件入口
        'sys/provinces/index',//省市区联动
        'wechat/default/index',//微信api
    ],

    //不需要验证的方法
    'basicsNoAuthAction' => [
        'upload',//百度编辑器上传
    ],
];
