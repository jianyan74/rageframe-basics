<?php
namespace jianyan\basics\backend\modules\wechat\controllers;

use yii;
use yii\web\NotFoundHttpException;
use jianyan\basics\common\models\wechat\FansTags;

/**
 * 粉丝标签
 *
 * Class FansTagsController
 * @package jianyan\basics\backend\modules\wechat\controllers
 */
class FansTagsController extends WController
{
    /**
     * 标签首页
     *
     * @return string
     */
    public function actionList()
    {
        return $this->render('index',[
            'tags' => FansTags::getTags($this->_app)
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
        if($this->_app->user_tag->delete($id))
        {
            FansTags::updateTagsList($this->_app);
            return $this->message("删除成功",$this->redirect(['list']));
        }
        else
        {
            return $this->message("删除失败",$this->redirect(['list']),'error');
        }
    }

    /**
     * 更新修改数据
     *
     * @return yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate()
    {
        $request = Yii::$app->request;

        if($request->isPost)
        {
            $tag_add = $request->post('tag_add','');
            $tag_update = $request->post('tag_update','');

            // 更新标签
            if($tag_update)
            {
                foreach ($tag_update as $key => $value)
                {
                    if($value)
                    {
                        $this->_app->user_tag->update($key,$value);
                    }
                    else
                    {
                        $this->message("标签名称不能为空",$this->redirect(['list'],'error'));
                    }
                }
            }

            // 插入标签
            if($tag_add)
            {
                foreach ($tag_add as $value)
                {
                    $this->_app->user_tag->create($value);
                }
            }

            FansTags::updateTagsList($this->_app);
            return $this->redirect(['list']);
        }
        else
        {
            throw new NotFoundHttpException('请求失败.');
        }
    }

    /**
     * 同步粉丝
     *
     * @return mixed
     */
    public function actionSynchro()
    {
        FansTags::updateTagsList($this->_app);
        return $this->message("粉丝同步成功",$this->redirect(['list']));
    }

    /**
     * 返回标签模型
     *
     * @return array|FansTags|null|yii\db\ActiveRecord
     */
    protected function findModel()
    {
        if (empty(($model = FansTags::find()->one())))
        {
            return new FansTags;
        }

        return $model;
    }
}
