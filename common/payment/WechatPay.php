<?php
namespace jianyan\basics\common\payment;

use Yii;
use yii\helpers\ArrayHelper;
use Omnipay\Omnipay;

/**
 * 微信支付类
 *
 * Class WechatPay
 * @package jianyan\basics\common\payment
 */
class WechatPay extends BasePay
{
    protected $_order;

    /**
     * WechatPay constructor.
     */
    public function __construct()
    {
        $this->_order = [
            'spbill_create_ip' => Yii::$app->request->userIP,
            'fee_type' => 'CNY',
            'notify_url' => Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['we-notify/notify']),
        ];

        parent::__construct();
    }

    /**
     * 实例化类
     *
     * @param $type
     * @return mixed
     */
    private function create($type)
    {
        $gateway = Omnipay::create($type);
        $gateway->setAppId($this->_rfConfig['WECHAT_APPID']);
        $gateway->setMchId($this->_rfConfig['WECHAT_MCHID']);
        $gateway->setApiKey($this->_rfConfig['WECHAT_API_KEY']);
        $gateway->setCertPath($this->_rfConfig['WECHAT_APICLIENT_CERT']);
        $gateway->setKeyPath($this->_rfConfig['WECHAT_APICLIENT_KEY']);

        return $gateway;
    }

    /**
     * 回调
     *
     * @return mixed
     */
    public function notify()
    {
        $gateway = $this->create('WechatPay');

        return $gateway->completePurchase([
            'request_params' => file_get_contents('php://input')
        ])->send();
    }

    /**
     * 微信APP支付网关
     * @param array $order
     *    [
     *        'body'              => 'The test order',
     *        'out_trade_no'      => date('YmdHis') . mt_rand(1000, 9999),
     *        'total_fee'         => 1, //=0.01
     *     ]
     * @param bool $debug
     * @return mixed
     */
    public function app($order, $debug = false)
    {
        $gateway = $this->create('WechatPay_App');
        $request  = $gateway->purchase(ArrayHelper::merge($this->_order, $order));
        $response = $request->send();

        return $debug ? $response->getData() : $response->getAppOrderData();
    }

    /**
     * 微信原生扫码支付支付网关
     *
     * @param array $order
     *    [
     *        'body'              => 'The test order',
     *        'out_trade_no'      => date('YmdHis') . mt_rand(1000, 9999),
     *        'total_fee'         => 1, //=0.01
     *     ]
     * @param bool $debug
     * @return mixed
     */
    public function native($order, $debug = false)
    {
        $gateway = $this->create('WechatPay_Native');
        $request  = $gateway->purchase(ArrayHelper::merge($this->_order, $order));
        $response = $request->send();

        return $debug ? $response->getData() : $response->getCodeUrl();
    }

    /**
     * 微信js支付支付网关
     *
     * @param array $order
     *    [
     *        'body'              => 'The test order',
     *        'out_trade_no'      => date('YmdHis') . mt_rand(1000, 9999),
     *        'total_fee'         => 1, //=0.01
     *        'openid'            => 'ojPztwJ5bRWRt_Ipg', //=0.01
     *     ]
     * @param bool $debug
     * @return mixed
     */
    public function js($order, $debug = false)
    {
        $gateway = $this->create('WechatPay_Js');
        $request  = $gateway->purchase(ArrayHelper::merge($this->_order, $order));
        $response = $request->send();

        return $debug ? $response->getData() : $response->getJsOrderData();
    }

    /**
     * 微信刷卡支付网关
     *
     * @param array $order
     *    [
     *        'body'              => 'The test order',
     *        'out_trade_no'      => date('YmdHis') . mt_rand(1000, 9999),
     *        'total_fee'         => 1, //=0.01,
     *        'auth_code'         => '',
     *     ]
     * @param bool $debug
     * @return mixed
     */
    public function pos($order, $debug = false)
    {
        $gateway = $this->create('WechatPay_Pos');
        $request  = $gateway->purchase(ArrayHelper::merge($this->_order, $order));
        $response = $request->send();

        return $debug ? $response->getData() : $response->getData();
    }

    /**
     * 微信H5支付网关
     * @param array $order
     *    [
     *        'body'              => 'The test order',
     *        'out_trade_no'      => date('YmdHis') . mt_rand(1000, 9999),
     *        'total_fee'         => 1, //=0.01
     *     ]
     * @param bool $debug
     * @return mixed
     */
    public function mweb($order, $debug = false)
    {
        $gateway = $this->create('WechatPay_Mweb');
        $request  = $gateway->purchase(ArrayHelper::merge($this->_order, $order));
        $response = $request->send();

        return $debug ? $response->getData() : $response->getData();
    }

    /**
     * 关闭订单
     *
     * 订单类型
     * @param $type WechatPay_App, WechatPay_Native, WechatPay_Js, WechatPay_Pos, WechatPay_Mweb
     * @param $out_trade_no
     */
    public function close($type, $out_trade_no)
    {
        $gateway = $this->create($type);
        $response = $gateway->close([
            'out_trade_no' => $out_trade_no, //The merchant trade no
        ])->send();

        return $response->getData();
    }

    /**
     * 查询订单
     *
     * 订单类型
     * @param $type WechatPay_App, WechatPay_Native, WechatPay_Js, WechatPay_Pos, WechatPay_Mweb
     * @param $transaction_id
     */
    public function query($type, $transaction_id)
    {
        $gateway = $this->create($type);
        $response = $gateway->query([
            'transaction_id' => $transaction_id, //The wechat trade no
        ])->send();

        return $response->getData();
    }

    /**
     * 退款
     *
     * 订单类型
     * @param $type WechatPay_App, WechatPay_Native, WechatPay_Js, WechatPay_Pos, WechatPay_Mweb
     *
     * @param $info
     * [
     *     'transaction_id' => $transaction_id, //The wechat trade no
     *     'out_refund_no'  => $outRefundNo,
     *     'total_fee'      => 1, //=0.01
     *      'refund_fee'    => 1, //=0.01
     * ]
     */
    public function refund($type, $info)
    {
        $gateway = $this->create($type);
        $response = $gateway->refund($info)->send();

        return $response->getData();
    }
}
