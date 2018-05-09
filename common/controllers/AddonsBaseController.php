<?php
namespace jianyan\basics\common\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use jianyan\basics\common\models\sys\Addons;
use common\controllers\BaseController;
use common\helpers\AddonsHelp;

/**
 * 模块基类控制器
 *
 * Class AddonsBaseController
 * @package jianyan\basics\common\controllers
 */
class AddonsBaseController extends BaseController
{
    /**
     * 渲染模块目录
     */
    protected static $skipPath = 'home';

    /**
     * 前台和微信插件页面实现
     *
     * @param string $route 路由
     * @param string $addon 模块名
     * @return bool
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionExecute($route, $addon)
    {
        !$route && $route = Yii::$app->request->post('route','');
        !$addon && $addon = Yii::$app->request->post('addon','');

        return $this->skip(AddonsHelp::analysisBusinessRoute($route, $addon, self::$skipPath));
    }

    /**
     * 转接
     *
     * @param $through
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function skip($through)
    {
        $class = $through['class'];
        $actionName = $through['actionName'];

        // 检测插件是否存在
        if(!($addon = Addons::getAddon($through['addon'])))
        {
            throw new NotFoundHttpException("插件不存在");
        }

        // 检测模块是否存在
        if(!class_exists($class))
        {
            throw new NotFoundHttpException('模块不存在');
        }

        // 检测方法是否存在
        $list = new $class($through['controller'], Yii::$app->module);
        if(!method_exists($list, $actionName))
        {
            throw new NotFoundHttpException('方法不存在');
        }

        return $list->$actionName();
    }
}
