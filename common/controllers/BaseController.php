<?php
namespace jianyan\basics\common\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use common\helpers\ResultDataHelper;

/**
 * 基类控制器
 *
 * Class BaseController
 * @package common\controllers
 */
class BaseController extends Controller
{
    /**
     * 默认分页大小
     *
     * @var int
     */
    protected $_pageSize = 20;

    /**
     * ajax信息返回
     *
     * @var
     */
    protected $_result;

    public function init()
    {
        $rfConfig = Yii::$app->config->infoAll();
        Yii::$app->params['wechatConfig'] = ArrayHelper::merge([
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
                'callback' => '/examples/oauth_callback.html',
            ]
        ], Yii::$app->params['wechatConfig']);

        Yii::$app->params['wechatPaymentConfig'] = ArrayHelper::merge([
            'app_id'             => $rfConfig['WECHAT_APPID'],
            'mch_id'             => $rfConfig['WECHAT_MCHID'],
            'key'                => $rfConfig['WECHAT_API_KEY'],            // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => $rfConfig['WECHAT_APICLIENT_CERT'],     // XXX: 绝对路径！！！！
            'key_path'           => $rfConfig['WECHAT_APICLIENT_KEY'],      // XXX: 绝对路径！！！！
            // 支付回调地址
            'notify_url'         => Yii::$app->request->hostInfo . Yii::$app->urlManager->createUrl(['we-notify/notify']),
            'sandbox'            => false, // 设置为 false 或注释则关闭沙箱模式
        ], Yii::$app->params['wechatPaymentConfig']);

        parent::init();
    }

    /**
     * 解析Yii2错误信息
     *
     * @param $errors
     * @return string
     */
    public function analysisError($errors)
    {
        $errors = array_values($errors)[0];
        return $errors ?? '操作失败';
    }

    /**
     * 写入消息返回
     *
     * @return ResultDataHelper
     */
    public function setResult()
    {
        return $this->_result = new ResultDataHelper();
    }

    /**
     * 获取消息返回
     *
     * @return array
     */
    public function getResult()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = [
            'code' => strval($this->_result->code),
            'message' => trim($this->_result->message),
            'data' => $this->_result->data ? ArrayHelper::toArray($this->_result->data) : [],
        ];

        // 测试环境显示处理时间信息 方便优化
        if (YII_ENV_TEST)
        {
            $result['use_time'] = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        }

        return $result;
    }

    /**
     * 打印调试
     * @param $array
     */
    public function p($array)
    {
        echo "<pre>";
        print_r($array);
    }
}