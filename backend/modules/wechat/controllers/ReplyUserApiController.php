<?php
namespace jianyan\basics\backend\modules\wechat\controllers;

use Yii;
use jianyan\basics\common\models\wechat\Rule;
use jianyan\basics\common\models\wechat\ReplyUserApi;

/**
 * 自定义接口回复回复控制器
 * Class ReplyUserApiController
 * @package jianyan\basics\backend\modules\wechat\controllers
 */
class ReplyUserApiController extends RuleController
{
    protected $_module = Rule::RULE_MODULE_USER_API;

    /**
     * 返回模型
     * @param $id
     * @return array|ReplyUserApi|null|\yii\db\ActiveRecord
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            $model = new ReplyUserApi;
            return $model->loadDefaultValues();
        }

        if (empty(($model = ReplyUserApi::find()->where(['rule_id' => $id])->one())))
        {
            $model = new ReplyUserApi;
            return $model->loadDefaultValues();
        }

        return $model;
    }
}
