<?php
namespace jianyan\basics\backend\controllers;

use Yii;
use yii\base\InvalidConfigException;

/**
 * 快速创建增删改查基类
 *
 * Class CurdController
 * @package jianyan\basics\backend\controllers
 */
class CurdController extends \backend\controllers\MController
{
    /**
     * 基础属性类
     *
     * @var
     */
    public $modelClass;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->modelClass === null)
        {
            throw new InvalidConfigException('"modelClass" 属性必须设置');
        }
    }
    
    /**
     * 首页
     */
    public function actionIndex()
    {
        $data = $this->modelClass::find();
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' =>$this->_pageSize]);
        $models = $data->offset($pages->offset)
            ->orderBy('id desc')
            ->limit($pages->limit)
            ->all();

        return $this->render('index',[
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    /**
     * 编辑/新增
     *
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'model' => $model,
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
        if ($this->findModel($id)->delete())
        {
            return $this->message("删除成功", $this->redirect(['index']));
        }
        else
        {
            return $this->message("删除失败", $this->redirect(['index']), 'error');
        }
    }

    /**
     * 返回模型
     *
     * @param $id
     * @return $this|$this->modelClass|static
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            $model = new $this->modelClass;
            return $model->loadDefaultValues();
        }

        if (empty(($model = $this->modelClass::findOne($id))))
        {
            return new $this->modelClass;
        }

        return $model;
    }
}