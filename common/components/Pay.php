<?php
namespace jianyan\basics\common\components;

use yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use jianyan\basics\common\payment\AliPay;
use jianyan\basics\common\payment\UnionPay;
use jianyan\basics\common\payment\WechatPay;
use Omnipay\Omnipay;

/**
 * 公用支付类
 *
 * Class Pay
 * @package jianyan\basics\common\components
 */
class Pay extends Component
{
    /**
     * 公用配置
     *
     * @var
     */
    protected $_rfConfig;

    public function __construct(array $config = [])
    {
        $this->_rfConfig = Yii::$app->config->infoAll();

        parent::__construct($config);
    }

    /**
     * 支付宝支付
     *
     * @param $config
     */
    public function alipay(array $config = [])
    {
        $config = ArrayHelper::merge([
            'app_id' => $this->_rfConfig['ALIPAY_APPID'],
            'notify_url' => Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['notify/index']),
            'return_url' => Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['return/index']),
            'ali_public_key' => $this->_rfConfig['ALIPAY_PUBLIC_KEY'],
            // 加密方式： ** RSA2 **
            'private_key' => $this->_rfConfig['ALIPAY_PRIVATE_KEY'],
        ], $config);

        return new AliPay($config);
    }

    /**
     * 微信支付
     *
     * @param $config
     */
    public function wechat(array $config = [])
    {
        $config = ArrayHelper::merge([
            'app_id' => $this->_rfConfig['WECHAT_APPID'], // 公众号 APPID
            'mch_id' => $this->_rfConfig['WECHAT_MCHID'],
            'key' => $this->_rfConfig['WECHAT_API_KEY'],
            'cert_client' => $this->_rfConfig['WECHAT_APICLIENT_CERT'], // optional，退款等情况时用到
            'cert_key' => $this->_rfConfig['WECHAT_APICLIENT_KEY'],// optional，退款等情况时用到
        ], $config);

        return new WechatPay($config);
    }

    /**
     * 银联支付
     *
     * @param $config
     */
    public function union(array $config = [])
    {
        $config = ArrayHelper::merge([
            'mch_id' => $this->_rfConfig['UNION_MCHID'],
            'cert_id' => $this->_rfConfig['UNION_CERT_ID'],
            'notify_url' => Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['notify/index']),
            'return_url' => Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['return/index']),
            'public_key' => $this->_rfConfig['UNION_PUBLIC_KEY'],
            'private_key' => $this->_rfConfig['UNION_PRIVATE_KEY'],
        ], $config);

        return new WechatPay($config);
    }

    public function __get($name)
    {
        try
        {
            return parent::__get($name);
        }
        catch (\Exception $e)
        {
            if($this->$name())
            {
                return $this->$name([]);
            }
            else
            {
                throw $e->getPrevious();
            }
        }
    }
}