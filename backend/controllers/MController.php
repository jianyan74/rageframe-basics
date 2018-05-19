<?php
namespace jianyan\basics\backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\UnauthorizedHttpException;
use common\helpers\ResultDataHelper;

/**
 * 后台基类控制器
 *
 * Class MController
 * @package backend\controllers
 */
class MController extends \common\controllers\BaseController
{
    /**
     * EasyWechat Debug 模式，bool 值：true/false
     *
     * 当值为 false 时，所有的日志都不会记录
     */
    protected $_debug = true;

    /**
     * EasyWechat SDK
     *
     * @var
     */
    protected $_app;

    /**
     * csrf验证
     *
     * @var bool
     */
    public $enableCsrfValidation = true;

    /**
     * 默认自动加载地址
     *
     * @var string
     */
    public $layout = '@basics/backend/views/layouts/main';

    /**
     * 自动运行
     *
     */
    public function init()
    {
        parent::init();

        Yii::$app->params['wechatConfig']['debug'] = $this->_debug;

        // 实例化EasyWechat SDK
        $this->_app = Yii::$app->wechat->getApp();

        // 分页
        Yii::$app->config->info('SYS_PAGE') && $this->_pageSize = Yii::$app->config->info('SYS_PAGE');
    }

    /**
     * 统一加载
     * @inheritdoc
     */
    public function actions()
    {
        return [
            // 错误提示跳转页面
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * 行为控制
     *
     * @return array
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
                ],
            ],
        ];
    }

    /**
     * RBAC验证
     *
     * @param $action
     * @return bool
     * @throws UnauthorizedHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        // 验证是否登录且验证是否超级管理员
        if (!\Yii::$app->user->isGuest && Yii::$app->user->id === Yii::$app->params['adminAccount'])
        {
            return true;
        }

        if (!parent::beforeAction($action))
        {
            return false;
        }

        // 控制器+方法
        $permissionName = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
        // 加入模块验证
        if(Yii::$app->controller->module->id != "app-backend")
        {
            $permissionName = Yii::$app->controller->module->id . '/' . $permissionName;
        }

        // 不需要RBAC判断的路由全称
        $noAuthRoute = ArrayHelper::merge(Yii::$app->params['basicsNoAuthRoute'], Yii::$app->params['noAuthRoute']);

        // 不需要RBAC判断的方法
        $noAuthAction = ArrayHelper::merge(Yii::$app->params['basicsNoAuthAction'], Yii::$app->params['noAuthAction']);

        if(in_array($permissionName, $noAuthRoute) || in_array(Yii::$app->controller->action->id, $noAuthAction) )
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
     *
     * @param string $msgText 错误内容
     * @param string $skipUrl 跳转链接
     * @param string $msgType 提示类型
     * @param int $closeTime 提示关闭时间
     * @return mixed
     */
    public function message($msgText, $skipUrl, $msgType = "",$closeTime = 5)
    {
        $closeTime = (int)$closeTime;

        // 如果是成功的提示则默认为3秒关闭时间
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
     * 提示消息
     *
     * @param string $msg 消息提示
     * @param integer $closeTime 关闭时间
     * @return string
     */
    public function hintText($msg, $closeTime)
    {
        $text = $msg . " <span class='closeTimeYl'>" . $closeTime . "</span>秒后自动关闭...";
        return $text;
    }

    /**
     * 全局通用修改排序和状态
     *
     * @param $id
     * @return array
     */
    public function actionAjaxUpdate($id)
    {
        $insert_data = [];
        $data = Yii::$app->request->get();
        isset($data['status']) && $insert_data['status'] = $data['status'];
        isset($data['sort']) && $insert_data['sort'] = $data['sort'];

        $model = $this->findModel($id);
        $model->attributes = $insert_data;

        if(!$model->save())
        {
            return ResultDataHelper::result(422, $this->analysisError($model->getFirstErrors()));
        }

        return ResultDataHelper::result(200, '修改成功');
    }
}