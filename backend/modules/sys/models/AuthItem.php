<?php
namespace jianyan\basics\backend\modules\sys\models;

use Yii;

/**
 * This is the model class for table "{{%auth_item}}".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthRule $ruleName
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemChildren0
 * @property AuthItem[] $children
 * @property AuthItem[] $parents
 */
class AuthItem extends \jianyan\basics\common\models\base\AuthItem
{
    /**
     * 规则类名
     * @var
     */
    protected $auth_rule;
    /**
     * 角色授权用户类
     * @var
     */
    protected $auth_assignment;
    /**
     * 角色路由类
     * @var
     */
    protected $auth_item;
    /**
     * 路由授权角色类
     * @var
     */
    protected $auth_item_child;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sys_auth_item}}';
    }

    public function init()
    {
        $this->auth_rule = AuthRule::className();
        $this->auth_assignment = AuthAssignment::className();
        $this->auth_item = AuthItem::className();
        $this->auth_item_child = AuthItemChild::className();

        parent::init();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(empty($this->rule_name))
        {
            $this->rule_name = null;
        }

        return parent::beforeSave($insert);
    }
}
