<?php
namespace jianyan\basics\common\payment;

use Yii;
use Omnipay\Omnipay;

/**
 * 支付宝支付
 *
 * Class AliPay
 * @package jianyan\basics\common\payment
 */
class AliPay extends BasePay
{
    /**
     * @var null|string
     */
    protected $_returnUrl;

    /**
     * @var null|string
     */
    protected $_notifyUrl;

    /**
     * UnionPay constructor.
     * @param null $returnUrl 同步通知
     * @param null $notifyUrl 异步通知
     */
    public function __construct($returnUrl = null, $notifyUrl = null)
    {
        $this->_returnUrl = $returnUrl ?? Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['we-notify/notify']);
        $this->_notifyUrl = $notifyUrl ?? Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['we-notify/notify']);

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
        $gateway->setSignType('RSA2'); // RSA/RSA2/MD5
        $gateway->setAppId($this->_rfConfig['ALIPAY_APPID']);
        $gateway->setPrivateKey($this->_rfConfig['ALIPAY_PRIVATE_KEY']);
        $gateway->setAlipayPublicKey($this->_rfConfig['ALIPAY_PUBLIC_KEY']);
        $gateway->setReturnUrl($this->_returnUrl);
        $gateway->setNotifyUrl($this->_notifyUrl);

        return $gateway;
    }

    /**
     * 电脑网站支付
     *
     * @param $config
     *
     * 参数说明
     * $config = [
     *     'subject'      => 'test',
     *     'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
     *     'total_amount' => '0.01',
     * ]
     */
    public function pc($order)
    {
        $gateway = $this->create('Alipay_AopPage');
        $order['product_code'] = 'FAST_INSTANT_TRADE_PAY';

        $request = $gateway->purchase();
        $request->setBizContent($order);

        /**
         * @var AopTradeAppPayResponse $response
         */
        $response = $request->send();
        // $redirectUrl = $response->getRedirectUrl();
        return $response->redirect();
    }

    /**
     * APP支付
     *
     * 参数说明
     * $config = [
     *     'subject'      => 'test',
     *     'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
     *     'total_amount' => '0.01',
     * ]
     *
     * iOS 客户端
     * [[AlipaySDK defaultService] payOrder:orderString fromScheme:appScheme callback:^(NSDictionary *resultDic) {
     *      NSLog(@"reslut = %@",resultDic);
     * }];
     *
     * Android 客户端
     * PayTask alipay = new PayTask(PayDemoActivity.this);
     * Map<String, String> result = alipay.payV2(orderString, true);
     * @param $config
     * @param $notifyUrl
     * @return mixed
     */
    public function app($order)
    {
        $gateway = $this->create('Alipay_AopApp');
        $order['product_code'] = 'QUICK_MSECURITY_PAY';

        $request = $gateway->purchase();
        $request->setBizContent($config);

        /**
         * @var AopTradeAppPayResponse $response
         */
        $response = $request->send();
        return $response->getOrderString();
    }

    /**
     * 面对面支付
     *
     * @param $order
     * @return mixed
     */
    public function f2f($order)
    {
        $gateway = $this->create('Alipay_AopF2F');
        $request = $gateway->purchase();
        $request->setBizContent($order);

        /**
         * @var AopTradeAppPayResponse $response
         */
        $response = $request->send();
        return $response->getQrCode();
    }

    /**
     * 手机网站支付
     *
     * @param $config
     * @param null $notifyUrl
     * @return mixed
     */
    public function wap($order)
    {
        $gateway = $this->create('Alipay_AopWap');
        $order['product_code'] = 'QUICK_WAP_PAY';

        $request = $gateway->purchase();
        $request->setBizContent($order);

        /**
         * @var AopTradeAppPayResponse $response
         */
        $response = $request->send();
        return $response->redirect();
    }

    /**
     * @param $order
     */
    public function js($order)
    {

    }

    /**
     * 退款
     *
     *[
     *     'out_trade_no' => 'The existing Order ID',
     *     'trade_no' => 'The Transaction ID received in the previous request',
     *     'refund_amount' => 18.4,
     *     'out_request_no' => date('YmdHis') . mt_rand(1000, 9999)
     *  ]
     */
    public function refund($type, array $info)
    {
        $gateway = $this->create($type);
        $request = $gateway->refund();
        $response = $request->setBizContent($info);

        return $response->getData();
    }
}
