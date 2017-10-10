<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use yii;
use jianyan\basics\common\models\sys\ArticleSingle;
use backend\controllers\MController;

/**
 * 单页管理控制器
 * Class ArticleSingleController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class ArticleSingleController extends MController
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'upload' => Yii::$app->params['ueditorConfig']
        ];
    }

    /**
     * 首页
     * @return string
     */
    public function actionIndex()
    {
        $models = ArticleSingle::find()
            ->orderBy('sort Asc,append Asc')
            ->asArray()
            ->all();

        return $this->render('index', [
            'models' => $models,
        ]);
    }

    /**
     * 编辑/新增
     * @return string|yii\web\Response
     */
    public function actionEdit()
    {
        $request   = Yii::$app->request;
        $id = $request->get('id');
        $model     = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'model'         => $model,
        ]);
    }

    /**
     * 删除
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
     * @param $id
     * @return $this|ArticleSingle|static
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            $model = new ArticleSingle;
            return $model->loadDefaultValues();
        }

        if (empty(($model = ArticleSingle::findOne($id))))
        {
            return new ArticleSingle;
        }

        return $model;
    }
}