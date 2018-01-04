<?php
/**
 * Project: yii2-easyWeChat.
 * Author: Max.wen
 * Date: <2016/05/10 - 14:31>
 */

namespace jianyan\basics\common\components;

use Yii;
use EasyWeChat\Factory;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Wechat
 * @package common\components
 *
 * @property Factory $app
 * @property WechatUser  $user
 * @property bool        $isWechat
 * @property string      $returnUrl
 */
class Wechat extends Component
{
	/**
	 * user identity class params
	 * @var array
	 */
	public $userOptions = [];

	/**
	 * wechat user info will be stored in session under this key
	 * @var string
	 */
	public $sessionParam = '_wechatUser';

	/**
	 * returnUrl param stored in session
	 * @var string
	 */
	public $returnUrlParam = '_wechatReturnUrl';

	/**
     * 微信SDK
     *
	 * @var Factory
	 */
	private static $_app;

    /**
     * 支付SKD
     *
     * @var Factory
     */
    private static $_payApp;

	/**
	 * @var WechatUser
	 */
	private static $_user;

    /**
     * 默认微信配置
     *
     * @var
     */
	protected $_defaultWechatConfig;

    /**
     * 默认微信支付配置
     *
     * @var
     */
    protected $_defaultWechatPayConfig;

    public function init()
    {
        $rfConfig = Yii::$app->config->infoAll();

        $this->_defaultWechatConfig = ArrayHelper::merge([
            /**
             * Debug 模式，bool 值：true/false
             *
             * 当值为 false 时，所有的日志都不会记录
             */
            'debug'  => true,

            /**
             * 账号基本信息，请从微信公众平台/开放平台获取
             */
            'app_id'  => $rfConfig['WECHAT_APPID'],              // AppID
            'secret'  => $rfConfig['WECHAT_APPSERCRET'],         // AppSecret
            'token'   => $rfConfig['WECHAT_TOKEN'],              // Token
            'aes_key' => $rfConfig['WECHAT_ENCODINGAESKEY'],     // EncodingAESKey，兼容与安全模式下请一定要填写！！！

            /**
             * 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
             * 使用自定义类名时，构造函数将会接收一个 `EasyWeChat\Kernel\Http\Response` 实例
             */
            'response_type' => 'array',

            /**
             * 日志配置
             *
             * level: 日志级别, 可选为：
             *         debug/info/notice/warning/error/critical/alert/emergency
             * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
             * file：日志文件位置(绝对路径!!!)，要求可写权限
             */
            'log' => [
                'level'      => 'debug',
                'permission' => 0777,
                'file'       => '/tmp/rageframe/wechat/' . date('Y-m') . '/' . date('d') . '/wechat.log',
            ],

            /**
             * 接口请求相关配置，超时时间等，具体可用参数请参考：
             * http://docs.guzzlephp.org/en/stable/request-options.html
             *
             * - retries: 重试次数，默认 1，指定当 http 请求失败时重试的次数。
             * - retry_delay: 重试延迟间隔（单位：ms），默认 500
             * - log_template: 指定 HTTP 日志模板，请参考：https://github.com/guzzle/guzzle/blob/master/src/MessageFormatter.php
             */
            'http' => [
                'retries'       => 1,
                'retry_delay'   => 500,
                'timeout'       => 5.0,
                'base_uri'      => 'https://api.weixin.qq.com/',
            ],

            /**
             * OAuth 配置
             *
             * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
             * callback：OAuth授权完成后的回调页地址
             */
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => '/examples/oauth_callback.php',
            ]
        ], Yii::$app->params['wechatConfig']);

        $this->_defaultWechatPayConfig = ArrayHelper::merge([
            'mch_id'             => $rfConfig['WECHAT_MCHID'],
            'key'                => $rfConfig['WECHAT_API_KEY'],            // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => $rfConfig['WECHAT_APICLIENT_CERT'],     // XXX: 绝对路径！！！！
            'key_path'           => $rfConfig['WECHAT_APICLIENT_KEY'],      // XXX: 绝对路径！！！！

            'notify_url'         => Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['we-notify/notify'])
        ], Yii::$app->params['wechatPayConfig']);

        parent::init();
    }

    /**
	 * @return yii\web\Response
	 */
	public function authorizeRequired()
	{
		if(Yii::$app->request->get('code'))
		{
			// callback and authorize
			return $this->authorize($this->app->oauth->user());
		}
		else 
		{
			// redirect to wechat authorize page
			$this->setReturnUrl(Yii::$app->request->getUrl());
			return Yii::$app->response->redirect($this->app->oauth->redirect()->getTargetUrl());
		}
	}
	
	/**
	 * @param \Overtrue\Socialite\User $user
	 * @return yii\web\Response
	 */
	public function authorize(\Overtrue\Socialite\User $user)
	{
		Yii::$app->session->set($this->sessionParam, $user->toJSON());
		return Yii::$app->response->redirect($this->getReturnUrl());
	}

	/**
	 * check if current user authorized
	 * @return bool
	 */
	public function isAuthorized()
	{
		$hasSession = Yii::$app->session->has($this->sessionParam);
		$sessionVal = Yii::$app->session->get($this->sessionParam);
		return ($hasSession && !empty($sessionVal));
	}

	/**
	 * @param string|array $url
	 */
	public function setReturnUrl($url)
	{
		Yii::$app->session->set($this->returnUrlParam, $url);
	}

	/**
	 * @param null $defaultUrl
	 * @return mixed|null|string
	 */
	public function getReturnUrl($defaultUrl = null)
	{
		$url = Yii::$app->session->get($this->returnUrlParam, $defaultUrl);
		if (is_array($url))
		{
			if (isset($url[0]))
			{
				return Yii::$app->getUrlManager()->createUrl($url);
			}
			else
			{
				$url = null;
			}
		}

		return $url === null ? Yii::$app->getHomeUrl() : $url;
	}

    /**
     * 获取 EasyWeChat 微信实例
     *
     * @return Factory|\EasyWeChat\OfficialAccount\Application
     */
	public function getApp()
	{
		if (!self::$_app instanceof Factory)
		{
			self::$_app = Factory::officialAccount($this->_defaultWechatConfig);
		}

		return self::$_app;
	}

    /**
     * 获取 EasyWeChat 微信支付实例
     *
     * @return Factory
     */
	public function getPayApp()
    {
        if (!self::$_payApp)
        {
            self::$_app = Factory::payment($this->_defaultWechatPayConfig);
        }

        return self::$_payApp;
    }

    /**
     * 获取微信身份信息
     *
     * @return WechatUser
     */
	public function getUser()
	{
		if (!$this->isAuthorized())
		{
			return new WechatUser();
		}

		if (! self::$_user instanceof WechatUser)
		{
			$userInfo = Yii::$app->session->get($this->sessionParam);
			$config = $userInfo ? json_decode($userInfo, true) : [];
			self::$_user = new WechatUser($config);
		}
		return self::$_user;
	}

	/**
	 * overwrite the getter in order to be compatible with this component
	 * @param $name
	 * @return mixed
	 * @throws \Exception
	 */
	public function __get($name)
	{
		try
        {
			return parent::__get($name);
		}
		catch (\Exception $e)
        {
			if($this->getApp()->$name)
			{
				return $this->app->$name;
			}
			else
			{
				throw $e->getPrevious();
			}
		}
	}

	/**
	 * check if client is wechat
	 * @return bool
	 */
	public function getIsWechat()
	{
		return strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger") !== false;
	}
}
