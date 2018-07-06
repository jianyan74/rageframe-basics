<?php
namespace jianyan\basics\common\controllers;

use yii;
use yii\web\BadRequestHttpException;

/**
 * 微信基类控制器
 *
 * Class WechatController
 * @package common\controllers
 */
class WechatController extends \common\controllers\BaseController
{
    /**
     * 实例化SDK
     *
     * @var
     */
    protected $_app;

    /**
     * @var bool
     */
    protected $_debug = true;

    /**
     * 网页授权类别
     *
     * 当值未 snsapi_base 静默获取只获取openid
     * @var array
     */
    protected $_scopes = ['snsapi_userinfo'];

    /**
     * 支付回调地址
     *
     * @var
     */
    protected $_notifyUrl;

    /**
     * 当前进入微信用户信息
     *
     * @var
     */
    protected $_wechatMember;

    /**
     * 当前登录的系统用户信息
     *
     * @var
     */
    protected $_member;

    /**
     * 默认检测到微信进入自动获取用户信息
     *
     * @var bool
     */
    protected $_openGetWechatUser = true;

    /**
     * @throws yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        
        /**
         * 微信参数配置
         * Debug 模式，bool 值：true/false
         *
         * 当值为 false 时，所有的日志都不会记录
         */
        Yii::$app->params['wechatConfig']['debug'] = $this->_debug;
        /**
         * OAuth 配置
         *
         * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
         * callback：OAuth授权完成后的回调页地址
         */
        Yii::$app->params['wechatConfig']['oauth'] = [
            'scopes'   => $this->_scopes,
            'callback' => Yii::$app->request->getUrl(),
        ];

        // 微信支付参数配置
        Yii::$app->params['wechatPaymentConfig']['notify_url'] = $this->_notifyUrl ?? Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['we-notify/notify']);

        // 实例化EasyWechat SDK
        $this->_app = Yii::$app->wechat->app;

        /** 检测到微信进入自动获取用户信息 **/
        $this->_openGetWechatUser && $this->getWechatUser();

        /** 当前进入微信用户信息 **/
        $this->_wechatMember = json_decode(Yii::$app->session->get('wechatUser'), true);
    }

    /**
     * 微信网页授权
     *
     * @return bool
     */
    public function getWechatUser()
    {
        if(Yii::$app->wechat->isWechat && !Yii::$app->wechat->isAuthorized())
        {
            return Yii::$app->wechat->authorizeRequired()->send();
        }

        return false;
    }

    /**
     * 创建微信支付订单
     *
     * @param array $attributes
     * @return mixed
     * @throws BadRequestHttpException
     */
    protected function wechatPay(array $attributes)
    {
        $attributes['trade_type'] = 'JSAPI';
        $payment = Yii::$app->wechat->payment;
        $result = $payment->order->unify($attributes);

        if ($result['return_code'] == 'SUCCESS')
        {
            $prepayId = $result['prepay_id'];
            $config = $payment->jssdk->sdkConfig($prepayId);
            return $config;
        }

        throw new BadRequestHttpException($result['return_msg']);
    }

    /**
     * 二维码支付
     *
     * @param $attributes
     * @return mixed
     * @throws BadRequestHttpException
     */
    protected function wechatQrPay(array $attributes)
    {
        $attributes['trade_type'] = 'NATIVE';
        $payment = Yii::$app->wechat->payment;
        $result = $payment->order->unify($attributes);

        if ($result['return_code'] == 'SUCCESS')
        {
            // $prepayId = $result['prepay_id'];
            return $result['code_url'];
        }

        throw new BadRequestHttpException($result['return_msg']);
    }
}