<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use yii;
use yii\data\Pagination;
use common\helpers\SysArrayHelper;
use backend\controllers\MController;
use jianyan\basics\backend\modules\sys\models\AuthItem;
use jianyan\basics\backend\modules\sys\models\AuthItemChild;
use jianyan\basics\backend\modules\sys\models\AuthAssignment;

/**
 * RBAC角色控制器
 *
 * Class AuthRoleController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class AuthRoleController extends MController
{
    /**
     * 角色管理
     * @return string
     */
    public function actionIndex()
    {
        $data   = AuthItem::find()->where(['type' => AuthItem::ROLE]);
        $pages  = new Pagination(['totalCount' =>$data->count(), 'pageSize' =>$this->_pageSize]);
        $models = $data->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('index',[
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    /**
     * 编辑
     * @return array|mixed|string|\yii\web\Response
     */
    public function actionEdit()
    {
        $request = Yii::$app->request;
        $name = $request->get('name');
        $model = $this->findModel($name);

        if ($model->load(Yii::$app->request->post()))
        {
            if($request->isAjax)
            {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }
            else
            {
                $model->type = AuthItem::ROLE;
                $model->description = Yii::$app->user->identity->username."|添加了|".$model->name."|角色";
                return $model->save()
                    ? $this->redirect(['index'])
                    : $this->message($this->analysisError($model->getFirstErrors()),$this->redirect(['index']),'error');
            }
        }

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
            return $this->message('角色删除成功',$this->redirect(['index']));
        }
        else
        {
            return $this->message('角色删除失败',$this->redirect(['index']),'error');
        }
    }

    /**
     * 角色授权
     * @return mixed|string
     */
    public function actionAccredit()
    {
        $request  = Yii::$app->request;
        $parent   = $request->get('parent');

        $userAuth = [];
        // 验证是否总管理员
        if(Yii::$app->params['adminAccount'] != Yii::$app->user->id)
        {
            $itemNames = AuthAssignment::getUserItemName(Yii::$app->user->id);
            if(isset($itemNames['itemNameChild']))
            {
                foreach ($itemNames['itemNameChild'] as $child)
                {
                    $userAuth[] = $child['child'];
                }
            }
        }

        $auth   = AuthItem::find()
            ->where(['type' => AuthItem::AUTH])
            ->andFilterWhere(['in','name',$userAuth])
            ->with([
                'authItemChildren0' => function($query) use($parent){
                    $query->andWhere(['parent' => $parent]);
                },
            ])
            ->orderBy('sort asc')
            ->asArray()
            ->all();

        $auth = SysArrayHelper::itemsMerge($auth,'key',0,'parent_key');

        if ($request->isPost)
        {
            // 提交过来的信息
            $PostAuth = $request->post();
            // 授权
            $AuthItemChild = new AuthItemChild();
            if($AuthItemChild->accredit($PostAuth['parent'],$PostAuth['auth']))
            {
                return $this->message('授权成功',$this->redirect(['index']));
            }
            else
            {
                return $this->message('授权失败',$this->redirect(['index']),'error');
            }
        }

        return $this->render('accredit', [
            'auth'   => $auth,
            'parent' => $parent,
        ]);
    }

    /**
     * 返回模型
     * @param $id
     * @return AuthItem|static
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            return new AuthItem;
        }

        if (empty(($model = AuthItem::findOne($id))))
        {
            return new AuthItem;
        }

        return $model;
    }

}