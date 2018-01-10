<?php
namespace jianyan\basics\common\payment;

use Yii;
use yii\helpers\ArrayHelper;
use Omnipay\Omnipay;

/**
 * 银联支付类
 *
 * Class UnionPay
 * @package jianyan\basics\common\payment
 */
class UnionPay extends BasePay
{
    protected $_returnUrl;

    protected $_notifyUrl;

    /**
     * UnionPay constructor.
     * @param null $returnUrl 同步通知
     * @param null $notifyUrl 异步通知
     */
    public function __construct($returnUrl = null, $notifyUrl = null)
    {
        $this->_order = $returnUrl ?? Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['we-notify/notify']);
        $this->_order = $notifyUrl ?? Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['we-notify/notify']);

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
        $gateway->setMerId($this->_rfConfig['UNION_MER_ID']);
        $gateway->setCertId($this->_rfConfig['UNION_CERT_ID']);
        $gateway->setPrivateKey($this->_rfConfig['UNION_PRIVATE_KEY']); // path or content
        $gateway->setPublicKey($this->_rfConfig['UNION_PUBLIC_KEY']); // path or content
        $gateway->setReturnUrl($this->_returnUrl);
        $gateway->setNotifyUrl($this->_notifyUrl);

        return $gateway;
    }

    /**
     * 回调
     *
     * @return mixed
     */
    public function notify()
    {
        $gateway = $this->create('Union_Express');
        return $gateway->completePurchase(['request_params'=>$_REQUEST])->send();
    }


    /**
     * APP
     * @param $order
     * @param bool $debug
     * @return mixed
     */
    public function app($order, $debug = false)
    {
        $gateway = $this->create('Union_Express');
        $response = $gateway->createOrder($order)->send();

        return $debug ? $response->getData() : $response->getTradeNo();
    }

    /**
     * PC/Wap
     * @param $order
     * @param bool $debug
     * @return mixed
     */
    public function html($order, $debug = false)
    {
        $gateway = $this->create('Union_Express');
        $response = $gateway->createOrder($order)->send();

        return $debug ? $response->getData() : $response->getRedirectHtml();
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
     * @param $orderId 订单id
     * @param $txnTime 订单交易时间
     * @param $txnAmt 订单总费用
     * @return mixed
     */
    public function query($orderId, $txnTime, $txnAmt)
    {
        $gateway = $this->create('UnionPay_Express');
        $response = $gateway->query([
            'orderId' => $orderId, //Your site trade no, not union tn.
            'txnTime' => $txnTime, //Order trade time
            'txnAmt'  => $txnAmt, //Order total fee
        ])->send();

        return $response->getData();
    }

    /**
     * 退款
     *
     * @param $orderId 订单id
     * @param $txnTime 订单交易时间
     * @param $txnAmt 订单总费用
     * @return mixed
     */
    public function refund($orderId, $txnTime, $txnAmt)
    {
        $gateway = $this->create('UnionPay_Express');
        $response = $gateway->refund([
            'orderId' => $orderId, //Your site trade no, not union tn.
            'txnTime' => $txnTime, //Order trade time
            'txnAmt'  => $txnAmt, //Order total fee
        ])->send();

        return $response->getData();
    }
}
