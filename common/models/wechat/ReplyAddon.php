<?php

namespace jianyan\basics\common\models\wechat;

use Yii;

/**
 * This is the model class for table "{{%wechat_reply_addon}}".
 *
 * @property integer $id
 * @property integer $rule_id
 * @property string $addon
 */
class ReplyAddon extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_reply_addon}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rule_id'], 'integer'],
            [['addon'], 'required'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'rule_id'   => '规则ID',
            'addon'     => '模块名称',
        ];
    }
}
