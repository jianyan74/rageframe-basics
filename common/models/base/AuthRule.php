<?php

namespace jianyan\basics\common\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%auth_rule}}".
 *
 * @property string $name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthItem[] $authItems
 */
class AuthRule extends ActiveRecord
{
    /**
     * 规则类名
     *
     * @var
     */
    protected $auth_rule;

    /**
     * 角色授权用户类
     *
     * @var
     */
    protected $auth_assignment;

    /**
     * 角色路由类
     *
     * @var
     */
    protected $auth_item;

    /**
     * 路由授权角色类
     *
     * @var
     */
    protected $auth_item_child;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['data'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '规则名称',
            'data' => '序列化数据',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems()
    {
        return $this->hasMany($this->auth_item, ['rule_name' => 'name']);
    }

    /**
     * 行为
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
}
