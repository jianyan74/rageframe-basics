<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use Yii;
use yii\data\Pagination;
use yii\web\Response;
use yii\widgets\ActiveForm;
use jianyan\basics\backend\modules\sys\models\AuthRule;
use backend\controllers\MController;

/**
 * RBAC规则控制器
 * Class AuthRuleController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class AuthRuleController extends MController
{
    /**
     * 规则管理
     * @return string
     */
    public function actionIndex()
    {
        $data = AuthRule::find();
        $pages = new Pagination(['totalCount' => $data->count(), 'pageSize' => $this->_pageSize]);
        $models = $data->offset($pages->offset)
            ->orderBy('updated_at desc')
            ->limit($pages->limit)
            ->all();

        return $this->render('index', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    /**
     * 新增/编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        $request = Yii::$app->request;
        $name = $request->get('name');
        $model = $this->findModel($name);

        if ($model->load(Yii::$app->request->post()))
        {
            if ($request->isAjax)
            {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else
            {
                return $model->save()
                    ? $this->redirect(['index'])
                    : $this->message($this->analysisError($model->getFirstErrors()), $this->redirect(['index']), 'error');
            }
        }

        $model->className = AuthRule::getClassName($model->data);

        return $this->renderAjax('edit', [
            'model' => $model,
        ]);
    }

    /**
     * 角色删除
     * @param $name
     * @return mixed
     */
    public function actionDelete($name)
    {
        if($this->findModel($name)->delete())
        {
            return $this->message('规则删除成功',$this->redirect(['index']));
        }
        else
        {
            return $this->message('规则删除失败',$this->redirect(['index']),'error');
        }
    }

    /**
     * 返回模型
     * @param $name
     * @return $this|AuthRule|static
     */
    protected function findModel($name)
    {
        if (empty($name))
        {
            $model = new AuthRule;
            return $model->loadDefaultValues();
        }

        if (empty(($model = AuthRule::findOne($name))))
        {
            $model = new AuthRule;
            return $model->loadDefaultValues();
        }

        return $model;
    }
}