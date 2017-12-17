<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use Yii;
use jianyan\basics\common\models\sys\DeskMenu;
use common\helpers\SysArrayHelper;
use backend\controllers\MController;

/**
 * 前台导航控制器
 *
 * Class DeskMenuController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class DeskMenuController extends MController
{
    /**
     * 首页
     *
     * @return string
     */
    public function actionIndex()
    {
        $models = DeskMenu::find()
            ->orderBy('sort Asc,append Asc')
            ->asArray()
            ->all();

        $models = SysArrayHelper::itemsMerge($models,'id');

        return $this->render('index', [
            'models' => $models,
        ]);
    }

    /**
     * 编辑
     *
     * @return array|mixed|string|\yii\web\Response
     */
    public function actionEdit()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $level = $request->get('level');
        $pid = $request->get('pid');
        $parent_title = $request->get('parent_title','无');
        $model = $this->findModel($id);

        !empty($level) && $model->level = $level;// 等级
        !empty($pid) && $model->pid = $pid;// 上级id

        if ($model->load(Yii::$app->request->post()))
        {
            if($request->isAjax)
            {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }
            else
            {
                return $model->save()
                    ? $this->redirect(['index'])
                    : $this->message($this->analysisError($model->getFirstErrors()),$this->redirect(['index']),'error');
            }
        }

        return $this->renderAjax('edit', [
            'model' => $model,
            'parent_title' => $parent_title,
        ]);
    }

    /**
     * 删除
     *
     * @param $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if($this->findModel($id)->delete())
        {
            return $this->message("删除成功",$this->redirect(['index']));
        }
        else
        {
            return $this->message("删除失败",$this->redirect(['index']),'error');
        }
    }

    /**
     * 返回模型
     *
     * @param $id
     * @return $this|DeskMenu|static
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            $model = new DeskMenu;
            return $model->loadDefaultValues();
        }

        if (empty(($model = DeskMenu::findOne($id))))
        {
            return new DeskMenu;
        }

        return $model;
    }
}
