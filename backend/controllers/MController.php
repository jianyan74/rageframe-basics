<?php
namespace jianyan\basics\backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\UnauthorizedHttpException;
use common\controllers\BaseController;

/**
 * 后台基类控制器
 * Class MController
 * @package backend\controllers
 */
class MController extends BaseController
{
    /**
     * csrf验证
     * @var bool
     */
    public $enableCsrfValidation = true;

    /**
     * 默认自动加载地址
     */
    public $layout = '@basics/backend/views/layouts/main';

    /**
     * @var
     * 实例化SDK
     */
    protected $_app;

    /**
     * 自动运行
     */
    public function init()
    {
        /**
         * Debug 模式，bool 值：true/false
         *
         * 当值为 false 时，所有的日志都不会记录
         */
        Yii::$app->params['WECHAT']['debug'] = true;
        /**
         * 账号基本信息，请从微信公众平台/开放平台获取
         */
        Yii::$app->params['WECHAT']['app_id'] = Yii::$app->config->info('WECHAT_APPID');
        Yii::$app->params['WECHAT']['secret'] = Yii::$app->config->info('WECHAT_APPSERCRET');
        Yii::$app->params['WECHAT']['token'] = Yii::$app->config->info('WECHAT_TOKEN');
        Yii::$app->params['WECHAT']['aes_key'] = Yii::$app->config->info('WECHAT_ENCODINGAESKEY');// EncodingAESKey，安全模式下请一定要填写！！！
        /**
         * 日志配置
         *
         * level: 日志级别, 可选为：
         *         debug/info/notice/warning/error/critical/alert/emergency
         * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
         * file：日志文件位置(绝对路径!!!)，要求可写权限
         */
        Yii::$app->params['WECHAT']['log']['level'] = 'debug';
        Yii::$app->params['WECHAT']['log']['permission'] = 0777;
        Yii::$app->params['WECHAT']['log']['file'] = '/tmp/rageframe/backend/'.date('Y-m').'/'.date('d').'/wechat.log';
        $this->_app = Yii::$app->wechat->getApp();

        //分页
        Yii::$app->config->info('SYS_PAGE') && $this->_pageSize = Yii::$app->config->info('SYS_PAGE');

        parent::init();
    }

    /**
     * 统一加载
     * @inheritdoc
     */
    public function actions()
    {
        return [
            //错误提示跳转页面
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

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
                        'roles' => ['@'],//登录
                    ],
                ],
            ],
        ];
    }

    /**
     * RBAC验证
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        //验证是否登录
        if (!\Yii::$app->user->isGuest)
        {
            //验证是否超级管理员
            if(Yii::$app->user->identity->id === Yii::$app->params['adminAccount'])
            {
                return true;
            }
        }

        if (!parent::beforeAction($action))
        {
            return false;
        }

        //控制器+方法
        $permissionName = Yii::$app->controller->id.'/'.Yii::$app->controller->action->id;
        //加入模块验证
        if(Yii::$app->controller->module->id != "app-backend")
        {
            $permissionName = Yii::$app->controller->module->id.'/'.$permissionName;
        }

        //不需要RBAC判断的路由全称
        $noAuthRoute = ArrayHelper::merge(Yii::$app->params['basicsNoAuthRoute'],Yii::$app->params['noAuthRoute']);

        //不需要RBAC判断的方法
        $noAuthAction = ArrayHelper::merge(Yii::$app->params['basicsNoAuthAction'],Yii::$app->params['noAuthAction']);

        if(in_array($permissionName,$noAuthRoute) || in_array(Yii::$app->controller->action->id,$noAuthAction) )
        {
            return true;
        }

        if(!Yii::$app->user->can($permissionName) && Yii::$app->getErrorHandler()->exception === null)
        {
            throw new UnauthorizedHttpException('对不起，您现在还没获此操作的权限');
        }

        return true;
    }

    /**
     * 错误提示信息
     * @param $msgText  -错误内容
     * @param $skipUrl  -跳转链接
     * @param $msgType  -提示类型
     * @param int $closeTime -提示关闭时间
     * @return mixed
     */
    public function message($msgText,$skipUrl,$msgType="",$closeTime=5)
    {
        $closeTime = (int)$closeTime;

        //如果是成功的提示则默认为3秒关闭时间
        if(!$closeTime && $msgType == "success" || !$msgType)
        {
            $closeTime = 3;
        }

        $html = $this->hintText($msgText,$closeTime);

        switch ($msgType)
        {
            case "success" :

                Yii::$app->getSession()->setFlash('success',$html);

                break;

            case "error" :

                Yii::$app->getSession()->setFlash('error',$html);

                break;

            case "info" :

                Yii::$app->getSession()->setFlash('info',$html);

                break;

            case "warning" :

                Yii::$app->getSession()->setFlash('warning',$html);

                break;

            default :

                Yii::$app->getSession()->setFlash('success',$html);

                break;
        }

        return $skipUrl;
    }

    /**
     * @param $msg
     * @param $closeTime
     * @return string
     */
    public function hintText($msg, $closeTime)
    {
        $text = $msg." <span class='closeTimeYl'>".$closeTime."</span>秒后自动关闭...";
        return $text;
    }

    /**
     * 全局通用修改排序和状态
     * @param $id
     * @return array
     */
    public function actionAjaxUpdate($id)
    {
        $insert_data = [];
        $data = Yii::$app->request->get();
        isset($data['status']) && $insert_data['status'] = $data['status'];
        isset($data['sort']) && $insert_data['sort'] = $data['sort'];

        $result = $this->setResult();
        $model = $this->findModel($id);
        $model->attributes = $insert_data;

        if(!$model->save())
        {
            $result->code = 422;
            $result->message = $this->analysisError($model->getFirstErrors());
        }
        else
        {
            $result->code = 200;
            $result->message = '修改成功';
        }

        return $this->getResult();
    }
}