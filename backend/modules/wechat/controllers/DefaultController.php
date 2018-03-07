<?php
namespace jianyan\basics\backend\modules\wechat\controllers;

use yii;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use jianyan\basics\common\models\wechat\Account;
use jianyan\basics\common\models\wechat\Fans;
use jianyan\basics\common\models\wechat\ReplyDefault;
use jianyan\basics\common\models\wechat\MsgHistory;
use jianyan\basics\common\models\wechat\QrcodeStat;

/**
 * 微信请求处理控制器
 *
 * Class DefaultController
 * @package jianyan\basics\backend\modules\wechat\controllers
 */
class DefaultController extends WController
{
    /**
     * 微信请求关闭CSRF验证
     *
     * @var bool
     */
    public $enableCsrfValidation = false;

    /**
     * 响应信息
     *
     * @var array
     */
    protected $_reply = null;

    /**
     * 聊天记录
     *
     * @var
     */
    protected $_msgHistory;

    /**
     * 行为控制
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],// 登录
                    ],
                    [
                        'allow' => true,
                        'roles' => ['?'],// 游客
                        'actions' => ['index'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 接收微信消息
     *
     * @return array|mixed
     * @throws NotFoundHttpException
     * subscribe 订阅关注事件
     * unsubscribe 取消订阅
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        switch ($request->getMethod())
        {
            // 激活公众号
            case 'GET':

                if(Account::verifyToken($request->get('signature'), $request->get('timestamp'), $request->get('nonce')))
                {
                    // 进行账号激活
                    // 暂时未设置
                    return $request->get('echostr');
                }
                else
                {
                    throw new NotFoundHttpException('签名失败.');
                }

                break;

            // 接收数据
            case 'POST':

                $this->_app->server->push(function ($message)
                {
                    // 存入公共变量
                    Yii::$app->params['wxMessage'] = $message;

                    $openid = $message['FromUserName'];
                    // 默认回复消息
                    $this->_msgHistory = [
                        'openid' => $openid,
                        'type' => $message['MsgType'],
                        'rule_id' => MsgHistory::DEFAULT_RULE, // 规则id
                        'keyword_id' => MsgHistory::DEFAULT_KWYWORD, // 关键字id
                    ];

                    switch ($message['MsgType'])
                    {
                        // 接收类型为事件
                        case Account::TYPE_EVENT:

                            switch ($message['Event'])
                            {
                                // 关注事件
                                case Account::TYPE_SUBSCRIBE:

                                    Fans::follow($openid,$this->_app);
                                    $this->_msgHistory['type'] = $message['Event'];
                                    $this->_reply = ReplyDefault::defaultReply();

                                    break;

                                // 取消关注事件
                                case Account::TYPE_UN_SUBSCRIBE:

                                    Fans::unFollow($openid);

                                    break;

                                // 点击事件
                                case Account::TYPE_CILCK:

                                    Fans::follow($openid,$this->_app);
                                    $this->_msgHistory['type'] = $message['Event'];
                                    $this->_reply = ReplyDefault::defaultReply('text', $message['EventKey']);

                                    break;
                            }

                            // 二维码扫描事件[包含关注、扫描]
                            if($qrResult = QrcodeStat::scan($message))
                            {
                                $qrResult = ReplyDefault::defaultReply('text', $qrResult);
                                $qrResult && $this->_reply = $qrResult;
                            }

                            break;

                        // 接收文字消息
                        case Account::TYPE_TEXT :

                            $this->_reply = ReplyDefault::defaultReply('text', $message['Content']);

                            break;

                        // 默认特殊消息
                        default:

                            $this->_reply = ReplyDefault::defaultReply('special', $message);

                            break;
                    }

                    // 历史记录
                    MsgHistory::add($message, $this->_msgHistory, $this->_reply);
                    // 返回响应信息
                    return $this->_reply ? $this->_reply['content'] : $this->_reply;
                });

                // 将响应输出
                $response = $this->_app->server->serve();
                $response->send();

                break;

            default:
                throw new NotFoundHttpException('所请求的页面不存在.');
        }

        return false;
    }
}