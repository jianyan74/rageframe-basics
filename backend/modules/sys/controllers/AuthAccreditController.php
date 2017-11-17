<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use yii;
use jianyan\basics\backend\modules\sys\models\AuthItem;
use common\helpers\SysArrayHelper;
use backend\controllers\MController;

/**
 * RBAC权限控制器
 *
 * Class AuthAccreditController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class AuthAccreditController extends MController
{
    /**
     * 权限管理
     */
    public function actionIndex()
    {
        $models = AuthItem::find()->where(['type' => AuthItem::AUTH])
            ->asArray()
            ->orderBy('sort asc')
            ->all();

        return $this->render('index',[
            'models' => SysArrayHelper::itemsMerge($models,'key',0,'parent_key'),
        ]);
    }

    /**
     * 权限编辑
     */
    public function actionEdit()
    {
        $request  = Yii::$app->request;
        $name     = $request->get('name');
        $model    = $this->findModel($name);
        // 父级key
        $parent_key = $request->get('parent_key',0);
        $level = $request->get('level',1);
        $model->level = $level;// 等级
        $model->parent_key = $parent_key;
        $model->type = AuthItem::AUTH;

        if($model->load($request->post()))
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

        $parent_name = "暂无";
        if($parent_key != 0)
        {
            $prent = AuthItem::find()->where(['key'=>$parent_key])->one();
            $parent_name = $prent['description'];
        }

        return $this->renderAjax('edit', [
            'model'       => $model,
            'parent_name' => $parent_name,
        ]);
    }

    /**
     * 权限删除
     */
    public function actionDelete($name)
    {
        if($this->findModel($name)->delete())
        {
            $this->message('权限删除成功',$this->redirect(['index']));
        }
        else
        {
            $this->message('权限删除失败',$this->redirect(['index']),'error');
        }
    }

    /**
     * ajax修改
     *
     * @return array
     */
    public function actionAjaxUpdate($id)
    {
        $result = $this->setResult();
        $model = AuthItem::findOne(['key' => $id]);
        $model->sort = Yii::$app->request->get('sort');
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

    /**
     * 返回模型
     *
     * @param $id
     * @return $this|AuthItem|static
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            $model = new AuthItem;
            return $model->loadDefaultValues();
        }

        if (empty(($model = AuthItem::findOne($id))))
        {
            return new AuthItem;
        }

        return $model;
    }
}