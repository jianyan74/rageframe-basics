<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use yii;
use yii\data\Pagination;
use jianyan\basics\common\models\wxapp\Account;
use jianyan\basics\common\models\wxapp\Versions;
use jianyan\basics\common\models\sys\Addons;
use backend\controllers\MController;

/**
 * 小程序控制器
 *
 * Class WxAppController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class WxAppController extends MController
{
    /**
     * 首页
     */
    public function actionIndex()
    {
        $data = Account::find();
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' =>$this->_pageSize]);
        $models = $data->offset($pages->offset)
            ->orderBy('id')
            ->limit($pages->limit)
            ->all();

        return $this->render('index',[
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    /**
     * 编辑/新增
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        $request  = Yii::$app->request;
        $id   = $request->get('id');
        $model    = $this->findModel($id);
        $versions = new Versions();

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            // 版本控制
            $id = $model->id;
            $versions->load(Yii::$app->request->post());
            $versions->account_id = $id;
            $versions->save();

            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'model'    => $model,
            'versions' => $versions,
            'addon_names' => Addons::getWxAppList(),
        ]);
    }

    public function actionView()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            return $this->redirect(['index']);
        }

        $socket = Yii::$app->request->hostInfo;
        $socket = str_replace('https','wss',$socket);
        $socket = str_replace('http','wss',$socket);

        return $this->render('view', [
            'model'    => $model,
            'socket'    => $socket,
            'versions' => $model->versions
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
     *
     * @param $id
     * @return $this|Account|static
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            $model = new Account;
            return $model->loadDefaultValues();
        }

        if (empty(($model = Account::findOne($id))))
        {
            return new Account;
        }

        return $model;
    }
}